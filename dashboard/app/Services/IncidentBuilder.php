<?php

namespace App\Services;

/**
 * Groups raw per-metric KPI alerts into executive-level "incidents".
 *
 * This is the heart of the Zero-Fatigue pipeline: it takes the flat list of
 * Critical/High alerts produced by ai-engine/kpi_analyzer.py and collapses
 * related signals (same domain+metric repeats, or cross-domain correlated
 * pairs) into a single incident card, so executives see a handful of things
 * to act on instead of dozens of raw rows.
 */
class IncidentBuilder
{
    /** Human-readable labels for raw metric keys. */
    protected array $metricLabels = [
        'Daily_Revenue_RM_Mil' => 'Daily Revenue',
        'Daily_Gross_Profit_RM_Mil' => 'Gross Profit',
        'Daily_Finance_Cost_RM_Mil' => 'Finance Cost',
        'FFB_Processed_Tonnes' => 'FFB Processed',
        'Patient_Admissions' => 'Patient Admissions',
        'Units_Under_Construction' => 'Units Under Construction',
        'Outlet_Transactions' => 'Outlet Transactions',
        'Revenue' => 'Revenue',
        'Units_Produced' => 'Units Produced',
    ];

    /** Metrics denominated in RM millions — used for "value at risk" framing. */
    protected array $rmMetrics = [
        'Daily_Revenue_RM_Mil',
        'Daily_Gross_Profit_RM_Mil',
        'Daily_Finance_Cost_RM_Mil',
    ];

    protected array $categoryOrder = [
        'Immediate Action' => 0,
        'Emerging Risk' => 1,
        'Monitor' => 2,
        'Opportunity' => 3,
    ];

    /**
     * @param  array  $alerts  Flat alert list (as decoded from anomalies_alerts.json)
     * @return array           List of incident arrays, sorted by category then impact
     */
    public function build(array $alerts): array
    {
        // Re-index numerically so we can track which alerts have been consumed.
        $alerts = array_values($alerts);
        $used = array_fill(0, count($alerts), false);
        $incidents = [];

        // ── Pass 1: cross-domain correlated CRITICAL pairs → single incident ──
        foreach ($alerts as $i => $a) {
            if ($used[$i] || $a['risk_score'] !== 'Critical' || empty($a['correlated_domain']) || !empty($a['is_favourable'])) {
                continue;
            }

            $partnerIdx = $this->findCorrelatedPartner($alerts, $used, $i, $a);

            if ($partnerIdx !== null) {
                $b = $alerts[$partnerIdx];
                $used[$i] = true;
                $used[$partnerIdx] = true;

                $incidents[] = $this->makeIncident(
                    title: $this->domainShort($a['primary_domain']).' × '.$this->domainShort($b['primary_domain']).': '.
                           $this->metricLabel($a['metric_name']).' + '.$this->metricLabel($b['metric_name']),
                    category: 'Immediate Action',
                    subAlerts: [$a, $b],
                    correlatedDomain: $b['primary_domain'],
                );
            } else {
                $used[$i] = true;
                $incidents[] = $this->makeIncident(
                    title: $this->domainShort($a['primary_domain']).': '.$this->metricLabel($a['metric_name']).' — Critical Deviation',
                    category: 'Immediate Action',
                    subAlerts: [$a],
                    correlatedDomain: $a['correlated_domain'],
                );
            }
        }

        // ── Pass 2: remaining unfavourable CRITICAL alerts, grouped by domain+metric ──
        foreach ($this->groupRemaining($alerts, $used, function ($a) {
            return $a['risk_score'] === 'Critical' && empty($a['is_favourable']);
        }) as $group) {
            $incidents[] = $this->makeIncident(
                title: $this->domainShort($group[0]['primary_domain']).': '.$this->metricLabel($group[0]['metric_name']).' — Critical Deviation',
                category: 'Immediate Action',
                subAlerts: $group,
            );
        }

        // ── Pass 3: unfavourable HIGH alerts, grouped by domain+metric → Emerging Risk / Monitor ──
        foreach ($this->groupRemaining($alerts, $used, function ($a) {
            return $a['risk_score'] === 'High' && empty($a['is_favourable']);
        }) as $group) {
            $worsening = $this->isWorsening($group);
            $incidents[] = $this->makeIncident(
                title: $this->domainShort($group[0]['primary_domain']).': '.$this->metricLabel($group[0]['metric_name']).
                       ($worsening ? ' — Developing Trend' : ' — Under Watch'),
                category: $worsening ? 'Emerging Risk' : 'Monitor',
                subAlerts: $group,
            );
        }

        // ── Pass 4: favourable anomalies (any severity), grouped by domain+metric → Opportunity ──
        foreach ($this->groupRemaining($alerts, $used, function ($a) {
            return ! empty($a['is_favourable']);
        }) as $group) {
            $incidents[] = $this->makeIncident(
                title: $this->domainShort($group[0]['primary_domain']).': '.$this->metricLabel($group[0]['metric_name']).' — Positive Anomaly',
                category: 'Opportunity',
                subAlerts: $group,
            );
        }

        // ── Sort: category priority first, then impact score within category ──
        usort($incidents, function ($x, $y) {
            $catDiff = $this->categoryOrder[$x['category']] <=> $this->categoryOrder[$y['category']];
            return $catDiff !== 0 ? $catDiff : ($y['impact_score'] <=> $x['impact_score']);
        });

        // ── Flag the top "Immediate Action" incidents as escalated (max 5) ──
        $escalatedSoFar = 0;
        foreach ($incidents as $idx => $inc) {
            $isEscalated = $inc['category'] === 'Immediate Action' && $escalatedSoFar < 5;
            $incidents[$idx]['escalated'] = $isEscalated;
            if ($isEscalated) {
                $escalatedSoFar++;
            }
        }

        return array_values($incidents);
    }

    /**
     * Find an unused Critical alert in the correlated partner domain,
     * detected within ±1 day of the source alert.
     */
    protected function findCorrelatedPartner(array $alerts, array $used, int $sourceIdx, array $source): ?int
    {
        $sourceTs = strtotime($source['date_detected']);

        foreach ($alerts as $j => $b) {
            if ($j === $sourceIdx || $used[$j]) {
                continue;
            }
            if ($b['risk_score'] !== 'Critical' || $b['primary_domain'] !== $source['correlated_domain']) {
                continue;
            }
            $days = abs($sourceTs - strtotime($b['date_detected'])) / 86400;
            if ($days <= 1) {
                return $j;
            }
        }

        return null;
    }

    /**
     * Group not-yet-used alerts matching $filter by (domain, metric).
     * Marks matched alerts as used and returns an array of alert-groups.
     */
    protected function groupRemaining(array $alerts, array &$used, callable $filter): array
    {
        $buckets = [];

        foreach ($alerts as $i => $a) {
            if ($used[$i] || ! $filter($a)) {
                continue;
            }
            $key = $a['primary_domain'].'|'.$a['metric_name'];
            $buckets[$key][] = $a;
            $used[$i] = true;
        }

        return array_values($buckets);
    }

    /** Trend is "worsening" if the group's most recent reading has the largest |variance|. */
    protected function isWorsening(array $group): bool
    {
        if (count($group) < 2) {
            // Single reading — use its own forecast confidence as the signal.
            $forecastNum = (int) filter_var($group[0]['7_day_forecast'] ?? '0', FILTER_SANITIZE_NUMBER_INT);
            return $forecastNum >= 50;
        }

        usort($group, fn ($x, $y) => strcmp($x['date_detected'], $y['date_detected']));
        $latest = abs($group[count($group) - 1]['variance_percentage']);
        $earliest = abs($group[0]['variance_percentage']);

        return $latest >= $earliest;
    }

    /**
     * Build the final incident array with computed impact/urgency/confidence.
     */
    protected function makeIncident(string $title, string $category, array $subAlerts, ?string $correlatedDomain = null): array
    {
        // Representative (most severe, most recent) alert for headline fields.
        usort($subAlerts, function ($x, $y) {
            $rank = ['Critical' => 0, 'High' => 1];
            $r = ($rank[$x['risk_score']] ?? 2) <=> ($rank[$y['risk_score']] ?? 2);
            return $r !== 0 ? $r : strcmp($y['date_detected'], $x['date_detected']);
        });
        $lead = $subAlerts[0];

        $avgAbsVariance = array_sum(array_map(fn ($a) => abs($a['variance_percentage']), $subAlerts)) / count($subAlerts);

        $impactScore = (int) min(100, round($avgAbsVariance * 1.6));
        $confidence = (int) min(99, 65 + round(min(34, $avgAbsVariance / 1.5)));

        $urgency = match (true) {
            $category === 'Immediate Action' => 'Immediate — within 2 hours',
            $category === 'Emerging Risk' => 'Within 24 hours',
            $category === 'Opportunity' => 'Review within 3 days',
            default => 'This week',
        };

        return [
            'title' => $title,
            'category' => $category,
            'primary_domain' => $lead['primary_domain'],
            'correlated_domain' => $correlatedDomain,
            'subsidiary' => $lead['subsidiary'] ?? '',
            'date_detected' => $lead['date_detected'],
            'risk_score' => $lead['risk_score'],
            'impact_score' => $impactScore,
            'impact_label' => $this->impactLabel($subAlerts, $impactScore),
            'urgency' => $urgency,
            'confidence' => $confidence,
            'grouped_alerts_count' => count($subAlerts),
            'root_cause_summary' => $lead['root_cause_alert'],
            'recommended_action' => $lead['recommended_action'],
            'variance_percentage' => $lead['variance_percentage'],
            'sub_alerts' => $subAlerts,
        ];
    }

    /** "RM X.Xmil at risk" for RM metrics, otherwise a High/Medium/Low impact label. */
    protected function impactLabel(array $subAlerts, int $impactScore): string
    {
        $rmAlerts = array_filter($subAlerts, fn ($a) => in_array($a['metric_name'], $this->rmMetrics, true));

        if (! empty($rmAlerts)) {
            $totalDelta = array_sum(array_map(
                fn ($a) => abs(($a['actual_value'] ?? 0) - ($a['target_value'] ?? 0)),
                $rmAlerts
            ));
            // Show RM thousands for small deltas so they don't read as "0.0mil".
            return $totalDelta < 0.1
                ? 'RM '.number_format($totalDelta * 1000, 0).'k at risk'
                : 'RM '.number_format($totalDelta, 2).'mil at risk';
        }

        return match (true) {
            $impactScore >= 70 => 'High business impact',
            $impactScore >= 40 => 'Medium business impact',
            default => 'Low business impact',
        };
    }

    protected function metricLabel(string $metric): string
    {
        return $this->metricLabels[$metric] ?? str_replace('_', ' ', $metric);
    }

    protected function domainShort(string $domain): string
    {
        return match ($domain) {
            'Wellness and healthcare' => 'Healthcare',
            'Real estate and infrastructure' => 'Real Estate',
            default => $domain,
        };
    }
}
