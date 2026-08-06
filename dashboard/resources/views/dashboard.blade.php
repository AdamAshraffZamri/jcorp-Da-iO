<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>jcorp Da-iO &mdash; Executive Intelligence Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; }
        .progress-bar { transition: width 0.8s ease; }
        .badge-correlated { background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%); }
        @keyframes pulse-red { 0%,100%{opacity:1} 50%{opacity:.6} }
        .pulse-red { animation: pulse-red 2s infinite; }

        /* Modal slide-in */
        #detail-panel { transform: translateX(100%); transition: transform 0.3s cubic-bezier(.4,0,.2,1); }
        #detail-panel.open { transform: translateX(0); }
        #modal-backdrop { opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        #modal-backdrop.open { opacity: 1; pointer-events: auto; }

        .clickable-card { cursor: pointer; }
        .clickable-card:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(0,0,0,0.4); }
        .clickable-card { transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s; }
        .clickable-row { cursor: pointer; }
<<<<<<< HEAD
=======

        /* Funnel */
        .funnel-arrow { color: #4b5563; font-size: 1.5rem; line-height: 1; }
        .funnel-stage { min-width: 150px; }

        /* Category tabs */
        .tab-btn { transition: all 0.15s; cursor: pointer; }
        .tab-btn.tab-active { background: #4f46e5 !important; color: white !important; border-color: #4f46e5 !important; }

        /* Accordion chevron */
        .chevron { transition: transform 0.2s ease; display: inline-block; }
        .chevron.rotate-180 { transform: rotate(180deg); }
>>>>>>> claude
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

{{-- ══════════════════ TOP NAV ══════════════════ --}}
<nav class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white text-sm">J</div>
        <span class="text-lg font-semibold tracking-tight">jcorp <span class="text-indigo-400">Da-iO</span></span>
<<<<<<< HEAD
        <span class="ml-2 text-xs bg-indigo-900 text-indigo-300 px-2 py-0.5 rounded-full">AI Engine v2</span>
=======
        <span class="ml-2 text-xs bg-indigo-900 text-indigo-300 px-2 py-0.5 rounded-full">AI Engine v3 &middot; Zero-Fatigue</span>
>>>>>>> claude
    </div>
    <div class="flex items-center gap-4 text-sm text-gray-400">
        <span>Last scan: {{ now()->format('d M Y, H:i') }}</span>
        <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
        <span class="text-green-400">Live</span>
    </div>
</nav>

<<<<<<< HEAD
{{-- ══════════════════ STAT BAR ══════════════════ --}}
<div class="bg-gray-900 border-b border-gray-800 px-6 py-3">
    <div class="max-w-7xl mx-auto flex flex-wrap gap-6">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-white">{{ $totalCount }}</span>
            <span class="text-gray-400 text-sm">Total Alerts</span>
        </div>
        <div class="w-px bg-gray-700"></div>
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-red-400">{{ $criticalCount }}</span>
            <span class="text-gray-400 text-sm">Critical</span>
        </div>
        <div class="w-px bg-gray-700"></div>
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-yellow-400">{{ $highCount }}</span>
            <span class="text-gray-400 text-sm">High</span>
        </div>
        <div class="w-px bg-gray-700"></div>
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-purple-400">{{ $correlatedCount }}</span>
            <span class="text-gray-400 text-sm">Cross-Domain</span>
        </div>
        <div class="ml-auto text-xs text-gray-600 self-center">Click any card or row for full details</div>
    </div>
</div>

{{-- ══════════════════ ALL ALERTS DATA (JSON island for JS) ══════════════════ --}}
=======
{{-- ══════════════════ SIGNAL REDUCTION FUNNEL ══════════════════ --}}
<div class="bg-gray-900 border-b border-gray-800 px-6 py-5">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Zero-Fatigue Signal Reduction</h2>
            <span class="text-xs text-gray-600">Filtering noise so executives only see what matters</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="funnel-stage bg-gray-800 border border-gray-700 rounded-xl px-5 py-3">
                <div class="text-2xl font-bold text-white">{{ number_format($funnel['total_signals']) }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Signals Analyzed</div>
            </div>
            <div class="funnel-arrow">&rarr;</div>
            <div class="funnel-stage bg-gray-800 border border-gray-700 rounded-xl px-5 py-3">
                <div class="text-2xl font-bold text-gray-400">{{ number_format($funnel['suppressed_count']) }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Low-Priority Suppressed</div>
            </div>
            <div class="funnel-arrow">&rarr;</div>
            <div class="funnel-stage bg-gray-800 border border-yellow-800 rounded-xl px-5 py-3">
                <div class="text-2xl font-bold text-yellow-400">{{ number_format($funnel['retained_count']) }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Anomalies Retained</div>
            </div>
            <div class="funnel-arrow">&rarr;</div>
            <div class="funnel-stage bg-gradient-to-br from-red-900 to-red-950 border border-red-700 rounded-xl px-5 py-3 shadow-lg shadow-red-950/40">
                <div class="text-2xl font-bold text-red-300">{{ number_format($funnel['escalated_count']) }}</div>
                <div class="text-xs text-red-200/80 mt-0.5">Escalated to Executive</div>
            </div>
            <div class="ml-auto text-xs text-gray-600 self-center hidden lg:block">
                {{ $funnel['total_signals'] > 0 ? round(($funnel['suppressed_count'] / $funnel['total_signals']) * 100) : 0 }}% noise eliminated before it reaches leadership
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════ ALL ALERTS DATA (JSON island for JS drill-down) ══════════════════ --}}
>>>>>>> claude
<script id="alerts-data" type="application/json">
    {!! json_encode($alerts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>

<<<<<<< HEAD
<div class="max-w-7xl mx-auto px-6 py-8 space-y-10">

{{-- ══════════════════ SECTION 1 — PRIORITY ACTION INBOX ══════════════════ --}}
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="w-3 h-3 bg-red-500 rounded-full pulse-red"></div>
        <h2 class="text-xl font-bold tracking-tight">Priority Action Inbox</h2>
        <span class="bg-red-900 text-red-300 text-xs font-semibold px-2.5 py-0.5 rounded-full">
            {{ $criticalCount }} Critical
        </span>
    </div>

    @if($critical)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($critical as $i => $alert)
            @php
                $forecastNum = (int) filter_var($alert['7_day_forecast'], FILTER_SANITIZE_NUMBER_INT);
                $forecastColor = $forecastNum >= 80 ? 'bg-red-500' : ($forecastNum >= 50 ? 'bg-yellow-400' : 'bg-green-500');
                $forecastTextColor = $forecastNum >= 80 ? 'text-red-400' : ($forecastNum >= 50 ? 'text-yellow-400' : 'text-green-400');
                $isCorrelated = !empty($alert['correlated_domain']);
                $alertIndex = array_search($alert, $alerts);
            @endphp
            <div class="clickable-card bg-gray-900 border border-red-800 rounded-xl p-5 shadow-lg shadow-red-950/40 hover:border-red-500"
                 onclick="openDetail({{ $alertIndex }})">

                {{-- "Click for details" hint --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">CRITICAL</span>
                        <span class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded-md">{{ $alert['primary_domain'] }}</span>
                        @if($isCorrelated)
                        <span class="badge-correlated text-white text-xs font-semibold px-2.5 py-1 rounded-md">
                            {{ $alert['primary_domain'] }} + {{ $alert['correlated_domain'] }}
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-gray-500 text-xs">{{ $alert['date_detected'] }}</span>
                        <span class="text-indigo-400 text-xs">View details &rsaquo;</span>
                    </div>
                </div>

                <div class="flex items-baseline gap-3 mb-1">
                    <h3 class="text-lg font-bold text-white">{{ str_replace('_', ' ', $alert['metric_name']) }}</h3>
                    <span class="text-red-400 text-sm font-semibold">
                        {{ $alert['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($alert['variance_percentage'], 1) }}%
                    </span>
                </div>
                @if(!empty($alert['subsidiary']))
                <p class="text-indigo-400 text-xs mb-2">{{ $alert['subsidiary'] }}</p>
                @endif

                <p class="text-gray-400 text-sm mb-1 line-clamp-2">
                    <span class="text-gray-500 uppercase text-xs tracking-wider mr-1">Root Cause:</span>
                    {{ $alert['root_cause_alert'] }}
                </p>

                @if($isCorrelated)
                <div class="bg-indigo-950 border border-indigo-800 rounded-lg px-3 py-2 mb-3 mt-2">
                    <p class="text-indigo-300 text-xs line-clamp-2">
                        <span class="font-semibold">Cross-Domain:</span> {{ $alert['correlation_note'] }}
                    </p>
                </div>
                @endif

                <div class="mb-1 mt-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">7-Day Recurrence Risk</span>
                        <span class="{{ $forecastTextColor }} font-semibold">{{ $forecastNum }}%</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="progress-bar {{ $forecastColor }} h-2 rounded-full" style="width: {{ $forecastNum }}%"></div>
                    </div>
=======
@php
    $categoryMeta = [
        'Immediate Action' => ['badge' => 'bg-red-600 text-white', 'border' => 'border-red-800', 'dot' => 'bg-red-500', 'text' => 'text-red-400'],
        'Emerging Risk'    => ['badge' => 'bg-orange-600 text-white', 'border' => 'border-orange-800', 'dot' => 'bg-orange-400', 'text' => 'text-orange-400'],
        'Monitor'          => ['badge' => 'bg-blue-600 text-white', 'border' => 'border-blue-800', 'dot' => 'bg-blue-400', 'text' => 'text-blue-400'],
        'Opportunity'      => ['badge' => 'bg-emerald-600 text-white', 'border' => 'border-emerald-800', 'dot' => 'bg-emerald-400', 'text' => 'text-emerald-400'],
    ];
@endphp

<div class="max-w-7xl mx-auto px-6 py-8 space-y-10">

{{-- ══════════════════ SECTION 1 — TOP EXECUTIVE INCIDENTS ══════════════════ --}}
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="w-3 h-3 bg-red-500 rounded-full pulse-red"></div>
        <h2 class="text-xl font-bold tracking-tight">Top Executive Incidents</h2>
        <span class="bg-red-900 text-red-300 text-xs font-semibold px-2.5 py-0.5 rounded-full">
            {{ count($topIncidents) }} Requiring Intervention
        </span>
    </div>

    @if($topIncidents)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($topIncidents as $i => $inc)
            @php
                $meta = $categoryMeta[$inc['category']];
                $accId = 'top-acc-' . $i;
            @endphp
            <div class="bg-gray-900 border {{ $meta['border'] }} rounded-xl p-5 shadow-lg shadow-black/40 hover:border-red-500 transition-colors">

                <div class="flex items-start justify-between mb-3 gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="{{ $meta['badge'] }} text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">{{ $inc['category'] }}</span>
                        <span class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded-md">{{ $inc['primary_domain'] }}</span>
                        @if($inc['correlated_domain'])
                        <span class="badge-correlated text-white text-xs font-semibold px-2.5 py-1 rounded-md">
                            {{ $inc['primary_domain'] }} + {{ $inc['correlated_domain'] }}
                        </span>
                        @endif
                    </div>
                    <span class="text-gray-500 text-xs whitespace-nowrap">{{ $inc['date_detected'] }}</span>
                </div>

                <h3 class="text-lg font-bold text-white mb-1">{{ $inc['title'] }}</h3>
                @if($inc['subsidiary'])
                <p class="text-indigo-400 text-xs mb-2">{{ $inc['subsidiary'] }}</p>
                @endif

                <p class="text-gray-400 text-sm mb-3 line-clamp-2">
                    <span class="text-gray-500 uppercase text-xs tracking-wider mr-1">Root Cause:</span>
                    {{ $inc['root_cause_summary'] }}
                </p>

                {{-- Multi-factor priority scoring --}}
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="bg-gray-800 rounded-lg px-3 py-2">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Business Impact</div>
                        <div class="text-sm font-semibold text-white">{{ $inc['impact_label'] }}</div>
                    </div>
                    <div class="bg-gray-800 rounded-lg px-3 py-2">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Urgency</div>
                        <div class="text-sm font-semibold text-white">{{ $inc['urgency'] }}</div>
                    </div>
                    <div class="bg-gray-800 rounded-lg px-3 py-2">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">AI Confidence</div>
                        <div class="text-sm font-semibold text-white">{{ $inc['confidence'] }}%</div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">
                        <span class="badge-correlated text-white text-[10px] font-semibold px-2 py-0.5 rounded mr-1">{{ $inc['grouped_alerts_count'] }}</span>
                        raw alert{{ $inc['grouped_alerts_count'] > 1 ? 's' : '' }} merged into this incident
                    </span>
                    <button onclick="toggleAccordion('{{ $accId }}')" class="text-indigo-400 text-xs font-medium flex items-center gap-1 hover:text-indigo-300">
                        Drill down <span class="chevron" id="{{ $accId }}-chevron">&#9660;</span>
                    </button>
                </div>

                <div id="{{ $accId }}" class="hidden mt-3 pt-3 border-t border-gray-800 space-y-1.5">
                    @foreach($inc['sub_alerts'] as $sa)
                    @php $saIndex = array_search($sa, $alerts); @endphp
                    <div class="clickable-row flex items-center justify-between bg-gray-800/60 hover:bg-gray-800 rounded-lg px-3 py-2 text-xs"
                         onclick="openDetail({{ $saIndex }})">
                        <span class="text-gray-400">{{ $sa['date_detected'] }}</span>
                        <span class="text-gray-300">{{ str_replace('_', ' ', $sa['metric_name']) }}</span>
                        <span class="{{ $sa['variance_percentage'] < 0 ? 'text-red-400' : 'text-green-400' }} font-semibold">
                            {{ $sa['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($sa['variance_percentage'], 1) }}%
                        </span>
                        <span class="text-indigo-400">&rsaquo;</span>
                    </div>
                    @endforeach
                    <p class="text-gray-300 text-xs pt-2"><span class="text-gray-500 uppercase tracking-wider mr-1">Recommended Action:</span>{{ $inc['recommended_action'] }}</p>
>>>>>>> claude
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-8 text-center text-gray-500">
<<<<<<< HEAD
            No critical alerts detected. System operating normally.
=======
            No incidents currently require executive intervention. System operating normally.
>>>>>>> claude
        </div>
    @endif
</section>

<<<<<<< HEAD
{{-- ══════════════════ SECTION 2 — WATCHLIST ══════════════════ --}}
<section>
    <div class="flex items-center gap-3 mb-5">
        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
        <h2 class="text-xl font-bold tracking-tight">Watchlist</h2>
        <span class="bg-yellow-900 text-yellow-300 text-xs font-semibold px-2.5 py-0.5 rounded-full">
            {{ $highCount }} High
        </span>
    </div>

    @if($high)
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-gray-400 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Domain</th>
                    <th class="px-4 py-3 text-left">Metric</th>
                    <th class="px-4 py-3 text-right">Variance</th>
                    <th class="px-4 py-3 text-left">7-Day Forecast</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($high as $alert)
                @php
                    $forecastNum = (int) filter_var($alert['7_day_forecast'], FILTER_SANITIZE_NUMBER_INT);
                    $forecastColor = $forecastNum >= 80 ? 'bg-red-500' : ($forecastNum >= 50 ? 'bg-yellow-400' : 'bg-green-500');
                    $forecastTextColor = $forecastNum >= 80 ? 'text-red-400' : ($forecastNum >= 50 ? 'text-yellow-400' : 'text-green-400');
                    $isCorrelated = !empty($alert['correlated_domain']);
                    $alertIndex = array_search($alert, $alerts);
                @endphp
                <tr class="clickable-row hover:bg-gray-800/70 transition-colors"
                    onclick="openDetail({{ $alertIndex }})">
                    <td class="px-4 py-3 text-gray-400">{{ $alert['date_detected'] }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-gray-300">{{ $alert['primary_domain'] }}</span>
                            @if($isCorrelated)
                            <span class="badge-correlated text-white text-xs px-1.5 py-0.5 rounded">
                                +{{ $alert['correlated_domain'] }}
                            </span>
                            @endif
                        </div>
                        @if(!empty($alert['subsidiary']))
                        <div class="text-indigo-400 text-xs mt-0.5">{{ $alert['subsidiary'] }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-white">{{ str_replace('_', ' ', $alert['metric_name']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold {{ $alert['variance_percentage'] < 0 ? 'text-red-400' : 'text-green-400' }}">
                        {{ $alert['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($alert['variance_percentage'], 1) }}%
                    </td>
                    <td class="px-4 py-3 min-w-[140px]">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-700 rounded-full h-1.5">
                                <div class="progress-bar {{ $forecastColor }} h-1.5 rounded-full" style="width: {{ $forecastNum }}%"></div>
                            </div>
                            <span class="{{ $forecastTextColor }} text-xs font-medium w-8 text-right">{{ $forecastNum }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-400 max-w-[220px]">
                        <p class="truncate">{{ $alert['recommended_action'] }}</p>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-indigo-400 text-xs whitespace-nowrap">&rsaquo; Details</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-8 text-center text-gray-500">
            No high-risk alerts detected.
=======
{{-- ══════════════════ SECTION 2 — INCIDENT BROWSER (CATEGORY TABS) ══════════════════ --}}
<section>
    <div class="flex items-center gap-3 mb-5 flex-wrap">
        <h2 class="text-xl font-bold tracking-tight">All Incidents</h2>
        <div class="flex items-center gap-2 ml-auto flex-wrap">
            <button class="tab-btn tab-active bg-gray-800 border border-gray-700 text-gray-300 text-xs font-medium px-3 py-1.5 rounded-lg" onclick="setCategoryTab('All', this)">
                All <span class="opacity-60">({{ count($incidents) }})</span>
            </button>
            @foreach($categoryCounts as $cat => $count)
            <button class="tab-btn bg-gray-800 border border-gray-700 text-gray-300 text-xs font-medium px-3 py-1.5 rounded-lg" onclick="setCategoryTab('{{ $cat }}', this)">
                {{ $cat }} <span class="opacity-60">({{ $count }})</span>
            </button>
            @endforeach
        </div>
    </div>

    @if($incidents)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            @foreach($incidents as $i => $inc)
            @php
                $meta = $categoryMeta[$inc['category']];
                $accId = 'all-acc-' . $i;
            @endphp
            <div class="incident-card bg-gray-900 border {{ $meta['border'] }} rounded-xl p-4" data-category="{{ $inc['category'] }}">
                <div class="flex items-start justify-between mb-2 gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-2 h-2 rounded-full {{ $meta['dot'] }} inline-block"></span>
                        <span class="{{ $meta['text'] }} text-[11px] font-semibold uppercase tracking-wider">{{ $inc['category'] }}</span>
                        <span class="bg-gray-800 text-gray-400 text-[11px] px-2 py-0.5 rounded">{{ $inc['primary_domain'] }}</span>
                    </div>
                    <span class="text-gray-600 text-[11px] whitespace-nowrap">{{ $inc['date_detected'] }}</span>
                </div>

                <h4 class="text-sm font-semibold text-white mb-2">{{ $inc['title'] }}</h4>

                <div class="flex items-center gap-3 text-[11px] text-gray-500 mb-2 flex-wrap">
                    <span>{{ $inc['impact_label'] }}</span>
                    <span>&bull;</span>
                    <span>{{ $inc['urgency'] }}</span>
                    <span>&bull;</span>
                    <span>{{ $inc['confidence'] }}% confidence</span>
                    <span>&bull;</span>
                    <span class="text-indigo-400">{{ $inc['grouped_alerts_count'] }} merged</span>
                </div>

                <button onclick="toggleAccordion('{{ $accId }}')" class="text-indigo-400 text-[11px] font-medium flex items-center gap-1 hover:text-indigo-300">
                    View underlying alerts <span class="chevron" id="{{ $accId }}-chevron">&#9660;</span>
                </button>

                <div id="{{ $accId }}" class="hidden mt-2 pt-2 border-t border-gray-800 space-y-1">
                    @foreach($inc['sub_alerts'] as $sa)
                    @php $saIndex = array_search($sa, $alerts); @endphp
                    <div class="clickable-row flex items-center justify-between bg-gray-800/60 hover:bg-gray-800 rounded px-2 py-1.5 text-[11px]"
                         onclick="openDetail({{ $saIndex }})">
                        <span class="text-gray-500">{{ $sa['date_detected'] }}</span>
                        <span class="text-gray-300">{{ str_replace('_', ' ', $sa['metric_name']) }}</span>
                        <span class="{{ $sa['variance_percentage'] < 0 ? 'text-red-400' : 'text-green-400' }} font-semibold">
                            {{ $sa['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($sa['variance_percentage'], 1) }}%
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-900 border border-gray-700 rounded-xl p-8 text-center text-gray-500">
            No incidents to display.
>>>>>>> claude
        </div>
    @endif
</section>

<<<<<<< HEAD
=======
{{-- ══════════════════ SECTION 3 — RAW ALERT LOG (FULL DRILL-DOWN) ══════════════════ --}}
<section>
    <details class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <summary class="px-5 py-4 flex items-center gap-3 cursor-pointer select-none hover:bg-gray-800/50">
            <h2 class="text-base font-semibold tracking-tight">Raw Alert Log</h2>
            <span class="bg-gray-800 text-gray-400 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $totalCount }} alerts</span>
            <span class="text-gray-600 text-xs ml-auto">Every retained signal, pre-grouping</span>
        </summary>
        <div class="border-t border-gray-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-gray-400 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Domain</th>
                        <th class="px-4 py-3 text-left">Metric</th>
                        <th class="px-4 py-3 text-left">Risk</th>
                        <th class="px-4 py-3 text-right">Variance</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($alerts as $idx => $alert)
                    <tr class="clickable-row hover:bg-gray-800/70 transition-colors" onclick="openDetail({{ $idx }})">
                        <td class="px-4 py-3 text-gray-400">{{ $alert['date_detected'] }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $alert['primary_domain'] }}</td>
                        <td class="px-4 py-3 font-medium text-white">{{ str_replace('_', ' ', $alert['metric_name']) }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $alert['risk_score'] === 'Critical' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300' }}">
                                {{ $alert['risk_score'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $alert['variance_percentage'] < 0 ? 'text-red-400' : 'text-green-400' }}">
                            {{ $alert['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($alert['variance_percentage'], 1) }}%
                        </td>
                        <td class="px-4 py-3 text-right"><span class="text-indigo-400 text-xs">&rsaquo; Details</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</section>

>>>>>>> claude
</div>{{-- /max-w-7xl --}}

<footer class="border-t border-gray-800 mt-10 px-6 py-4 text-center text-gray-600 text-xs">
    jcorp Da-iO &mdash; AI-powered KPI Intelligence &bull; Zero-fatigue filtering active &bull; Cross-domain correlation engine v2
</footer>

{{-- ══════════════════════════════════════════════════════════
     MODAL BACKDROP
══════════════════════════════════════════════════════════ --}}
<div id="modal-backdrop"
     class="fixed inset-0 bg-black/70 z-40 backdrop-blur-sm"
     onclick="closeDetail()">
</div>

{{-- ══════════════════════════════════════════════════════════
     DETAIL SLIDE-OVER PANEL
══════════════════════════════════════════════════════════ --}}
<aside id="detail-panel"
       class="fixed top-0 right-0 h-full w-full max-w-lg bg-gray-900 border-l border-gray-700 z-50 overflow-y-auto shadow-2xl">

    {{-- Panel Header --}}
    <div class="sticky top-0 bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span id="panel-risk-badge" class="text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider"></span>
            <span id="panel-domain-badge" class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded-md"></span>
            <span id="panel-corr-badge" class="badge-correlated text-white text-xs font-semibold px-2.5 py-1 rounded-md hidden"></span>
        </div>
        <button onclick="closeDetail()"
                class="text-gray-500 hover:text-white text-xl leading-none p-1 rounded hover:bg-gray-800 transition-colors"
                aria-label="Close">&times;</button>
    </div>

    <div class="px-6 py-6 space-y-6">

        {{-- Metric + Date + Subsidiary --}}
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Metric</p>
            <h2 id="panel-metric" class="text-2xl font-bold text-white"></h2>
            <p id="panel-subsidiary" class="text-indigo-400 text-xs font-medium mt-1"></p>
            <p id="panel-date" class="text-gray-500 text-sm mt-0.5"></p>
        </div>

        {{-- Variance --}}
        <div class="bg-gray-800 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Variance from Target</p>
                <p id="panel-variance" class="text-3xl font-bold"></p>
            </div>
            <div id="panel-variance-icon" class="text-5xl opacity-20"></div>
        </div>

        {{-- Root Cause --}}
        <div class="bg-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Root Cause Analysis</p>
            <p id="panel-root-cause" class="text-gray-200 text-sm leading-relaxed"></p>
        </div>

        {{-- Cross-Domain Correlation --}}
        <div id="panel-corr-block" class="bg-indigo-950 border border-indigo-800 rounded-xl p-4 hidden">
            <p class="text-xs text-indigo-400 uppercase tracking-wider mb-2">Cross-Domain Correlation</p>
            <div class="flex items-center gap-2 mb-2">
                <span id="panel-corr-from" class="bg-indigo-800 text-indigo-200 text-xs px-2 py-0.5 rounded"></span>
                <span class="text-indigo-500 text-sm">&rarr;</span>
                <span id="panel-corr-to" class="bg-pink-900 text-pink-200 text-xs px-2 py-0.5 rounded"></span>
            </div>
            <p id="panel-corr-note" class="text-indigo-300 text-sm leading-relaxed"></p>
        </div>

        {{-- 7-Day Forecast --}}
        <div class="bg-gray-800 rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <p class="text-xs text-gray-500 uppercase tracking-wider">7-Day Recurrence Forecast</p>
                <span id="panel-forecast-pct" class="text-lg font-bold"></span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-3 mb-2">
                <div id="panel-forecast-bar" class="progress-bar h-3 rounded-full" style="width:0%"></div>
            </div>
            <p id="panel-forecast-label" class="text-xs text-gray-500"></p>
        </div>

        {{-- Recommended Action --}}
        <div class="bg-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Recommended Action</p>
            <p id="panel-action" class="text-gray-100 text-sm leading-relaxed"></p>
        </div>

        {{-- Raw data (collapsible) --}}
        <details class="bg-gray-800 rounded-xl overflow-hidden">
            <summary class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-300 select-none">
                Raw Alert Data
            </summary>
            <pre id="panel-raw" class="px-4 pb-4 text-xs text-green-400 overflow-x-auto leading-relaxed"></pre>
        </details>

    </div>
</aside>

{{-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ --}}
<script>
const ALERTS = JSON.parse(document.getElementById('alerts-data').textContent);

<<<<<<< HEAD
=======
function toggleAccordion(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden');
    const chevron = document.getElementById(id + '-chevron');
    if (chevron) chevron.classList.toggle('rotate-180');
}

function setCategoryTab(cat, btn) {
    document.querySelectorAll('.incident-card').forEach(el => {
        if (cat === 'All' || el.dataset.category === cat) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-active'));
    btn.classList.add('tab-active');
}

>>>>>>> claude
function openDetail(idx) {
    const a = ALERTS[idx];
    if (!a) return;

    const isCritical = a.risk_score === 'Critical';
    const forecastNum = parseInt(a['7_day_forecast']) || 0;
    const isCorrelated = !!a.correlated_domain;
    const varPct = parseFloat(a.variance_percentage);

    // Risk badge
    const riskBadge = document.getElementById('panel-risk-badge');
    riskBadge.textContent = a.risk_score;
    riskBadge.className = isCritical
        ? 'text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider bg-red-600 text-white'
        : 'text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider bg-yellow-600 text-white';

    // Domain
    document.getElementById('panel-domain-badge').textContent = a.primary_domain;

    // Correlated badge
    const corrBadge = document.getElementById('panel-corr-badge');
    if (isCorrelated) {
        corrBadge.textContent = a.primary_domain + ' + ' + a.correlated_domain;
        corrBadge.classList.remove('hidden');
    } else {
        corrBadge.classList.add('hidden');
    }

    // Metric, subsidiary & date
    document.getElementById('panel-metric').textContent =
        a.metric_name.replace(/_/g, ' ');
    const subEl = document.getElementById('panel-subsidiary');
    subEl.textContent = a.subsidiary ? a.subsidiary : '';
    document.getElementById('panel-date').textContent = 'Detected: ' + a.date_detected;

    // Variance
    const varEl = document.getElementById('panel-variance');
    varEl.textContent = (varPct > 0 ? '+' : '') + varPct.toFixed(1) + '%';
    varEl.className = 'text-3xl font-bold ' + (varPct < 0 ? 'text-red-400' : 'text-green-400');
    document.getElementById('panel-variance-icon').textContent = varPct < 0 ? '↓' : '↑';

    // Root cause
    document.getElementById('panel-root-cause').textContent = a.root_cause_alert;

    // Correlation block
    const corrBlock = document.getElementById('panel-corr-block');
    if (isCorrelated) {
        corrBlock.classList.remove('hidden');
        document.getElementById('panel-corr-from').textContent = a.primary_domain;
        document.getElementById('panel-corr-to').textContent = a.correlated_domain;
        document.getElementById('panel-corr-note').textContent = a.correlation_note;
    } else {
        corrBlock.classList.add('hidden');
    }

    // Forecast
    const barEl = document.getElementById('panel-forecast-bar');
    const forecastColor = forecastNum >= 80 ? 'bg-red-500' : (forecastNum >= 50 ? 'bg-yellow-400' : 'bg-green-500');
    const forecastTextColor = forecastNum >= 80 ? 'text-red-400' : (forecastNum >= 50 ? 'text-yellow-400' : 'text-green-400');
    barEl.style.width = '0%';
    barEl.className = 'progress-bar h-3 rounded-full ' + forecastColor;
    setTimeout(() => { barEl.style.width = forecastNum + '%'; }, 50);

    const pctEl = document.getElementById('panel-forecast-pct');
    pctEl.textContent = forecastNum + '%';
    pctEl.className = 'text-lg font-bold ' + forecastTextColor;

    document.getElementById('panel-forecast-label').textContent = a['7_day_forecast'];

    // Action
    document.getElementById('panel-action').textContent = a.recommended_action;

    // Raw JSON
    document.getElementById('panel-raw').textContent = JSON.stringify(a, null, 2);

    // Open
    document.getElementById('detail-panel').classList.add('open');
    document.getElementById('modal-backdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detail-panel').classList.remove('open');
    document.getElementById('modal-backdrop').classList.remove('open');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>

</body>
</html>
