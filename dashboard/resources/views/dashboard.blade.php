<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Early Warning System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#1e3a5f', light: '#2e5490' }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        .badge-critical { @apply inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 ring-1 ring-red-300; }
        .badge-high     { @apply inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700 ring-1 ring-orange-300; }
        .badge-low      { @apply inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 ring-1 ring-yellow-300; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen font-sans antialiased">

    {{-- ══════════════════════════════════════════════════════════════
         TOP NAVIGATION BAR
    ══════════════════════════════════════════════════════════════ --}}
    <header class="bg-brand shadow-lg">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Shield icon --}}
                <svg class="w-8 h-8 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3l7.5 3.75v5.25c0 4.97-3.15 9.09-7.5 10.5C7.65 21.09 4.5 16.97 4.5 12V6.75L12 3z"/>
                </svg>
                <div>
                    <h1 class="text-white text-xl font-bold leading-tight tracking-wide">Executive Early Warning System</h1>
                    <p class="text-sky-300 text-xs">Real-time KPI Monitoring &amp; Risk Intelligence</p>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-slate-300 text-xs">Report Date</p>
                <p class="text-white text-sm font-semibold">{{ now()->format('F j, Y') }}</p>
            </div>
        </div>
    </header>

    <main class="max-w-screen-xl mx-auto px-6 py-8 space-y-10">

        {{-- ══════════════════════════════════════════════════════════════
             EXECUTIVE SUMMARY BAR
        ══════════════════════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Executive Summary</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

                {{-- Total KPIs --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-800">{{ $metrics->count() }}</p>
                        <p class="text-xs text-slate-500 font-medium">KPIs Tracked</p>
                    </div>
                </div>

                {{-- Critical Alerts --}}
                <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-red-600">{{ $criticalCount }}</p>
                        <p class="text-xs text-slate-500 font-medium">Critical Alerts</p>
                    </div>
                </div>

                {{-- High Alerts --}}
                <div class="bg-white rounded-2xl shadow-sm border border-orange-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-orange-500">{{ $highCount }}</p>
                        <p class="text-xs text-slate-500 font-medium">High Alerts</p>
                    </div>
                </div>

                {{-- Low Alerts --}}
                <div class="bg-white rounded-2xl shadow-sm border border-yellow-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-yellow-600">{{ $lowCount }}</p>
                        <p class="text-xs text-slate-500 font-medium">Low Alerts</p>
                    </div>
                </div>

            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════
             KPI STATUS CARDS
        ══════════════════════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">KPI Performance Overview</h2>

            @if($metrics->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-10 text-center text-slate-400">
                    No KPI records found. Run <code class="bg-slate-100 px-1 rounded">php artisan db:seed --class=DashboardSeeder</code> to load sample data.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($metrics as $metric)
                        @php
                            $pct        = $metric->target_value > 0
                                          ? round(($metric->actual_value / $metric->target_value) * 100, 1)
                                          : 0;
                            $isOnTarget = $pct >= 95;
                            $isWarning  = $pct >= 75 && $pct < 95;
                            // colour scheme
                            $barColor  = $isOnTarget ? 'bg-emerald-500' : ($isWarning ? 'bg-amber-400' : 'bg-red-500');
                            $ringColor = $isOnTarget ? 'border-emerald-400' : ($isWarning ? 'border-amber-400' : 'border-red-400');
                            $dotColor  = $isOnTarget ? 'bg-emerald-400' : ($isWarning ? 'bg-amber-400' : 'bg-red-400');
                            $labelColor= $isOnTarget ? 'text-emerald-600' : ($isWarning ? 'text-amber-600' : 'text-red-600');
                            $statusTxt = $isOnTarget ? 'On Target' : ($isWarning ? 'At Risk' : 'Off Target');
                        @endphp

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">

                            {{-- Card header --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ $metric->department }}</p>
                                    <p class="text-base font-bold text-slate-800 leading-snug mt-0.5">{{ $metric->metric_name }}</p>
                                </div>
                                <span class="flex items-center gap-1.5 text-xs font-semibold {{ $labelColor }} shrink-0 mt-0.5">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                    {{ $statusTxt }}
                                </span>
                            </div>

                            {{-- Progress bar --}}
                            <div>
                                <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                                    <span>Progress to Target</span>
                                    <span class="font-bold {{ $labelColor }}">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-700"
                                         style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Actual vs Target --}}
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                    <p class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider mb-1">Actual</p>
                                    <p class="text-lg font-extrabold text-slate-800">
                                        {{ number_format((float)$metric->actual_value, 2) }}
                                    </p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                    <p class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider mb-1">Target</p>
                                    <p class="text-lg font-extrabold text-slate-500">
                                        {{ number_format((float)$metric->target_value, 2) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Date & alert count --}}
                            <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-100 pt-3">
                                <span>{{ \Carbon\Carbon::parse($metric->recorded_date)->format('M j, Y') }}</span>
                                @if($metric->alerts->isNotEmpty())
                                    <span class="text-red-500 font-semibold">
                                        {{ $metric->alerts->count() }} alert{{ $metric->alerts->count() > 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="text-emerald-500 font-semibold">No alerts</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ══════════════════════════════════════════════════════════════
             EARLY WARNING ALERTS TABLE
        ══════════════════════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Early Warning Alerts</h2>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                @if($allAlerts->isEmpty())
                    <div class="p-10 text-center text-slate-400">
                        No active alerts. All KPIs are performing within acceptable thresholds.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-left">
                                    <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 w-[130px]">Risk Level</th>
                                    <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-slate-500">Alert Title</th>
                                    <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hidden md:table-cell">KPI / Department</th>
                                    <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Root Cause</th>
                                    <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Recommended Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allAlerts as $alert)
                                    @php
                                        $rowBg = match($alert->risk_score) {
                                            'Critical' => 'bg-red-50/60 hover:bg-red-50',
                                            'High'     => 'bg-orange-50/60 hover:bg-orange-50',
                                            default    => 'bg-white hover:bg-slate-50',
                                        };
                                        $badgeClass = match($alert->risk_score) {
                                            'Critical' => 'badge-critical',
                                            'High'     => 'badge-high',
                                            default    => 'badge-low',
                                        };
                                        $badgeDot = match($alert->risk_score) {
                                            'Critical' => 'bg-red-500',
                                            'High'     => 'bg-orange-500',
                                            default    => 'bg-yellow-500',
                                        };
                                    @endphp
                                    <tr class="{{ $rowBg }} transition-colors">
                                        <td class="px-5 py-4">
                                            <span class="{{ $badgeClass }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                                {{ $alert->risk_score }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $alert->title }}</td>
                                        <td class="px-5 py-4 hidden md:table-cell">
                                            <p class="font-medium text-slate-700">{{ $alert->kpiMetric->metric_name }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $alert->kpiMetric->department }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 text-xs leading-relaxed hidden lg:table-cell max-w-xs">
                                            {{ $alert->root_cause }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 text-xs leading-relaxed hidden lg:table-cell max-w-xs">
                                            {{ $alert->recommended_action }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

    </main>

    {{-- Footer --}}
    <footer class="max-w-screen-xl mx-auto px-6 py-6 mt-4 border-t border-slate-200">
        <p class="text-center text-xs text-slate-400">
            Executive Early Warning System &mdash; Confidential &mdash; {{ now()->format('Y') }}
        </p>
    </footer>

</body>
</html>
