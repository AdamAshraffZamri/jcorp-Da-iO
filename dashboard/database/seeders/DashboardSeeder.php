<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\KpiMetric;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── KPI 1: Revenue (Finance) ─────────────────────────────────────────
        $revenue = KpiMetric::create([
            'department'    => 'Finance',
            'metric_name'   => 'Monthly Revenue',
            'target_value'  => 500000.00,
            'actual_value'  => 312000.00,
            'recorded_date' => '2026-07-31',
        ]);

        // ── KPI 2: Customer Satisfaction (Customer Success) ──────────────────
        $csat = KpiMetric::create([
            'department'    => 'Customer Success',
            'metric_name'   => 'Customer Satisfaction Score (CSAT)',
            'target_value'  => 90.00,
            'actual_value'  => 67.50,
            'recorded_date' => '2026-07-31',
        ]);

        // ── KPI 3: System Uptime (Engineering) ───────────────────────────────
        KpiMetric::create([
            'department'    => 'Engineering',
            'metric_name'   => 'System Uptime (%)',
            'target_value'  => 99.90,
            'actual_value'  => 99.85,
            'recorded_date' => '2026-07-31',
        ]);

        // ── Alert 1: Critical – Revenue shortfall ────────────────────────────
        Alert::create([
            'kpi_metric_id'      => $revenue->id,
            'title'              => 'Severe Revenue Shortfall in July',
            'risk_score'         => 'Critical',
            'root_cause'         => 'A combination of delayed enterprise contract closures and a '
                                  . '15% decline in new customer acquisitions drove Monthly Revenue '
                                  . '38% below the July target of $500,000.',
            'recommended_action' => 'Convene an emergency sales review by August 5. Prioritise the '
                                  . 'top 10 stalled pipeline deals with executive-level outreach, and '
                                  . 'activate the Q3 discount incentive programme to accelerate closures.',
        ]);

        // ── Alert 2: High – CSAT drop ────────────────────────────────────────
        Alert::create([
            'kpi_metric_id'      => $csat->id,
            'title'              => 'Customer Satisfaction Score Below Threshold',
            'risk_score'         => 'High',
            'root_cause'         => 'Post-deployment feedback indicates a 25% increase in support '
                                  . 'ticket volume related to the v3.2 UI redesign, resulting in a '
                                  . 'CSAT drop from 84 to 67.5 over the past 30 days.',
            'recommended_action' => 'Roll back the most contentious UI changes by August 8. Assign '
                                  . 'two additional support agents to the v3.2 queue and schedule '
                                  . 'follow-up surveys with affected customers within 14 days.',
        ]);
    }
}
