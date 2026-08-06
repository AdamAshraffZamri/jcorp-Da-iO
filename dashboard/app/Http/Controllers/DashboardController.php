<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $jsonPath = storage_path('app/anomalies_alerts.json');

        if (! file_exists($jsonPath)) {
            $raw = [];
        } else {
            $raw = json_decode(file_get_contents($jsonPath), true) ?? [];
        }

        // ── Funnel metrics ──────────────────────────────────────
        $funnel = $raw['funnel_metrics'] ?? [
            'total_signals' => 0,
            'suppressed'    => 0,
            'retained'      => 0,
            'escalated'     => 0,
        ];

        // ── Categorised incidents ────────────────────────────────
        $cats = $raw['categorized_incidents'] ?? [];

        $immediateAction = $cats['immediate_action'] ?? [];
        $emergingRisk    = $cats['emerging_risk']    ?? [];
        $monitor         = $cats['monitor']          ?? [];
        $opportunity     = $cats['opportunity']      ?? [];

        return view('dashboard', compact(
            'funnel',
            'immediateAction',
            'emergingRisk',
            'monitor',
            'opportunity'
        ));
    }
}
