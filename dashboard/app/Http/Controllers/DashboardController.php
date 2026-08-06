<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Services\IncidentBuilder;
>>>>>>> claude
use Illuminate\Http\Request;

class DashboardController extends Controller
{
<<<<<<< HEAD
    public function index()
    {
        $jsonPath = storage_path('app/anomalies_alerts.json');

        if (! file_exists($jsonPath)) {
            $alerts = [];
        } else {
            $alerts = json_decode(file_get_contents($jsonPath), true) ?? [];
        }

        // Separate Critical vs High
        $critical = array_values(array_filter($alerts, fn($a) => $a['risk_score'] === 'Critical'));
        $high     = array_values(array_filter($alerts, fn($a) => $a['risk_score'] === 'High'));

        // Counts
        $totalCount    = count($alerts);
        $criticalCount = count($critical);
        $highCount     = count($high);
        $correlatedCount = count(array_filter($alerts, fn($a) => ! empty($a['correlated_domain'])));
=======
    public function index(IncidentBuilder $incidentBuilder)
    {
        $alerts = $this->loadJson('anomalies_alerts.json') ?? [];
        $funnelStats = $this->loadJson('signal_funnel.json') ?? [];

        // Separate Critical vs High (kept for the raw drill-down view)
        $critical = array_values(array_filter($alerts, fn ($a) => $a['risk_score'] === 'Critical'));
        $high = array_values(array_filter($alerts, fn ($a) => $a['risk_score'] === 'High'));

        $totalCount = count($alerts);
        $criticalCount = count($critical);
        $highCount = count($high);
        $correlatedCount = count(array_filter($alerts, fn ($a) => ! empty($a['correlated_domain'])));

        // ── Zero-Fatigue incident grouping ──
        $incidents = $incidentBuilder->build($alerts);

        $escalatedIncidents = array_values(array_filter($incidents, fn ($i) => $i['escalated']));
        $topIncidents = array_slice($escalatedIncidents, 0, 5);

        $categoryCounts = [
            'Immediate Action' => 0,
            'Emerging Risk' => 0,
            'Monitor' => 0,
            'Opportunity' => 0,
        ];
        foreach ($incidents as $inc) {
            $categoryCounts[$inc['category']] = ($categoryCounts[$inc['category']] ?? 0) + 1;
        }

        // ── Signal Reduction Funnel — real counts from the KPI engine run ──
        $funnel = [
            'total_signals' => $funnelStats['total_signals'] ?? $totalCount,
            'suppressed_count' => $funnelStats['suppressed_count'] ?? 0,
            'retained_count' => $funnelStats['retained_count'] ?? $totalCount,
            'escalated_count' => count($escalatedIncidents),
        ];
>>>>>>> claude

        return view('dashboard', compact(
            'alerts',
            'critical',
            'high',
            'totalCount',
            'criticalCount',
            'highCount',
<<<<<<< HEAD
            'correlatedCount'
        ));
    }
=======
            'correlatedCount',
            'incidents',
            'topIncidents',
            'categoryCounts',
            'funnel'
        ));
    }

    /**
     * Load a JSON file produced by the ai-engine pipeline.
     *
     * Primary location is storage/app/{file} (where a deploy step is expected
     * to copy the engine's output). Falls back to the ai-engine directory
     * itself so the dashboard still renders real data if that copy step was
     * skipped — this is what was silently breaking the dashboard before.
     */
    protected function loadJson(string $filename): ?array
    {
        $candidates = [
            storage_path('app/'.$filename),
            base_path('../ai-engine/'.$filename),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $decoded = json_decode(file_get_contents($path), true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }
>>>>>>> claude
}
