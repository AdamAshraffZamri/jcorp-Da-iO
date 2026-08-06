<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
        $totalCount      = count($alerts);
        $criticalCount   = count($critical);
        $highCount       = count($high);
        $correlatedCount = count(array_filter($alerts, fn($a) => ! empty($a['correlated_domain'])));

        return view('dashboard', compact(
            'alerts',
            'critical',
            'high',
            'totalCount',
            'criticalCount',
            'highCount',
            'correlatedCount'
        ));
    }
}
