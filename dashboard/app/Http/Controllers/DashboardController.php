<?php

namespace App\Http\Controllers;

use App\Models\KpiMetric;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = KpiMetric::with('alerts')
            ->orderBy('department')
            ->orderBy('recorded_date', 'desc')
            ->get();

        $allAlerts = $metrics
            ->flatMap(fn ($m) => $m->alerts)
            ->sortByDesc(fn ($a) => match ($a->risk_score) {
                'Critical' => 3,
                'High'     => 2,
                default    => 1,
            })
            ->values();

        $criticalCount = $allAlerts->where('risk_score', 'Critical')->count();
        $highCount     = $allAlerts->where('risk_score', 'High')->count();
        $lowCount      = $allAlerts->where('risk_score', 'Low')->count();

        return view('dashboard', compact('metrics', 'allAlerts', 'criticalCount', 'highCount', 'lowCount'));
    }
}
