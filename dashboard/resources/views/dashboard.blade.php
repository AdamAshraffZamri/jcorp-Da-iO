<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>jcorp Da-iO &mdash; Executive Intelligence Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #030712; }

        /* ── Funnel ribbon ── */
        .funnel-step { position: relative; }
        .funnel-step:not(:last-child)::after {
            content: '➔';
            position: absolute; right: -18px; top: 50%;
            transform: translateY(-50%);
            color: #4f46e5; font-size: 1.1rem; font-weight: 700;
        }

        /* ── Impact score ring ── */
        .score-ring {
            width: 64px; height: 64px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 800;
            border: 3px solid;
        }

        /* ── Tab system ── */
        .tab-btn { transition: all .2s; }
        .tab-btn.active { background: #4f46e5; color: #fff; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Accordion ── */
        .accordion-body { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
        .accordion-body.open { max-height: 2000px; }
        .accordion-chevron { transition: transform .25s; }
        .accordion-chevron.open { transform: rotate(180deg); }

        /* ── Metric pill ── */
        .metric-pill { display: inline-flex; flex-direction: column; align-items: center;
                       padding: .5rem .9rem; border-radius: .75rem; font-size: .72rem; }

        /* ── Hero card pulse ── */
        @keyframes border-pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.4); }
            50%      { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
        }
        .hero-card-critical { animation: border-pulse 3s infinite; }

        /* ── Progress bar ── */
        .pbar { transition: width .8s ease; }

        /* ── Slide-over panel ── */
        #detail-panel { transform: translateX(100%); transition: transform .3s cubic-bezier(.4,0,.2,1); }
        #detail-panel.open { transform: translateX(0); }
        #modal-backdrop { opacity:0; pointer-events:none; transition: opacity .3s; }
        #modal-backdrop.open { opacity:1; pointer-events:auto; }

        .clickable { cursor: pointer; }
        .clickable:hover { filter: brightness(1.08); }
    </style>
</head>
<body class="text-gray-100 min-h-screen">

{{-- ═══════════════════ NAV ═══════════════════ --}}
<nav class="bg-gray-900/80 backdrop-blur border-b border-gray-800 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-black text-white text-sm">J</div>
        <span class="text-lg font-bold tracking-tight">jcorp <span class="text-indigo-400">Da-iO</span></span>
        <span class="ml-1 text-xs bg-indigo-900 text-indigo-300 px-2 py-0.5 rounded-full">AI Engine v3</span>
    </div>
    <div class="flex items-center gap-3 text-xs text-gray-500">
        <span>{{ now()->format('d M Y, H:i') }}</span>
        <span class="w-2 h-2 bg-green-400 rounded-full"></span>
        <span class="text-green-400 font-medium">Live</span>
    </div>
</nav>

{{-- ═══════════════════ SIGNAL FUNNEL RIBBON ═══════════════════ --}}
<div class="bg-gradient-to-r from-gray-900 via-indigo-950/40 to-gray-900 border-b border-indigo-900/50 px-6 py-5">
    <div class="max-w-6xl mx-auto">
        <p class="text-xs text-indigo-400 uppercase tracking-widest font-semibold mb-3">Signal Intelligence Funnel</p>
        <div class="flex flex-wrap items-center gap-8">

            <div class="funnel-step flex flex-col items-center gap-1 pr-8">
                <span class="text-3xl font-black text-white">{{ number_format($funnel['total_signals']) }}</span>
                <span class="text-xs text-gray-400 text-center leading-tight">signals<br>analysed</span>
            </div>

            <div class="funnel-step flex flex-col items-center gap-1 pr-8">
                <span class="text-3xl font-black text-gray-500">{{ number_format($funnel['suppressed']) }}</span>
                <span class="text-xs text-gray-600 text-center leading-tight">low-priority<br>suppressed</span>
            </div>

            <div class="funnel-step flex flex-col items-center gap-1 pr-8">
                <span class="text-3xl font-black text-yellow-400">{{ number_format($funnel['retained']) }}</span>
                <span class="text-xs text-yellow-600 text-center leading-tight">signals<br>retained</span>
            </div>

            <div class="flex flex-col items-center gap-1">
                <span class="text-3xl font-black text-red-400">{{ number_format($funnel['escalated']) }}</span>
                <span class="text-xs text-red-500 text-center leading-tight">escalated for<br>executive action</span>
            </div>

            <div class="ml-auto hidden lg:block text-right">
                <p class="text-xs text-gray-600 max-w-xs leading-relaxed">
                    Zero-fatigue filtering active &mdash; only what demands your attention reaches this board.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════ HERO — IMMEDIATE ACTION ═══════════════════ --}}
<div class="max-w-6xl mx-auto px-6 pt-10 pb-4">
    <div class="flex items-center gap-3 mb-6">
        <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
        <h1 class="text-2xl font-black tracking-tight">Immediate Action Required</h1>
        <span class="bg-red-900/70 text-red-300 text-xs font-bold px-3 py-1 rounded-full border border-red-800">
            {{ count($immediateAction) }} incident{{ count($immediateAction) !== 1 ? 's' : '' }}
        </span>
    </div>

    @if(count($immediateAction))
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        @foreach($immediateAction as $inc)
        @php
            $isCrit   = $inc['risk_level'] === 'Critical';
            $score    = $inc['impact_score'];
            $scoreCol = $score >= 70 ? ['ring'=>'border-red-500','bg'=>'bg-red-950','text'=>'text-red-400']
                      : ($score >= 40 ? ['ring'=>'border-yellow-500','bg'=>'bg-yellow-950','text'=>'text-yellow-400']
                                      : ['ring'=>'border-green-500','bg'=>'bg-green-950','text'=>'text-green-400']);
            $hasCorr  = !empty($inc['correlated_domain']);
        @endphp
        <div class="clickable bg-gray-900 border {{ $isCrit ? 'border-red-700 hero-card-critical' : 'border-yellow-700' }} rounded-2xl p-6 shadow-xl"
             onclick='openDetail(@json($inc))'>

            {{-- Card top row --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1 pr-4">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="{{ $isCrit ? 'bg-red-600' : 'bg-yellow-600' }} text-white text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">
                            {{ $inc['risk_level'] }}
                        </span>
                        <span class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded-md">{{ $inc['primary_domain'] }}</span>
                        @if($hasCorr)
                        <span class="bg-gradient-to-r from-indigo-600 to-pink-600 text-white text-xs font-semibold px-2.5 py-1 rounded-md">
                            Cross-Domain
                        </span>
                        @endif
                    </div>
                    <h2 class="text-lg font-bold text-white leading-snug">{{ $inc['incident_name'] }}</h2>
                    @if(count($inc['subsidiaries']))
                    <p class="text-indigo-400 text-xs mt-0.5">{{ implode(', ', $inc['subsidiaries']) }}</p>
                    @endif
                    <p class="text-gray-500 text-xs mt-1">{{ $inc['date_detected'] }}</p>
                </div>
                {{-- Impact Score Ring --}}
                <div class="score-ring {{ $scoreCol['ring'] }} {{ $scoreCol['bg'] }} {{ $scoreCol['text'] }} shrink-0">
                    {{ $score }}
                </div>
            </div>

            {{-- Metric pills --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($inc['metrics'] as $m)
                @php
                    $mFav   = $m['is_favourable'];
                    $mColor = $mFav ? 'bg-green-900/60 text-green-300' : 'bg-red-900/60 text-red-300';
                @endphp
                <div class="metric-pill {{ $mColor }}">
                    <span class="font-bold text-sm">{{ ($m['variance_pct'] > 0 ? '+' : '') . number_format($m['variance_pct'],1) }}%</span>
                    <span class="opacity-80 mt-0.5">{{ $m['display_name'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Narrative --}}
            <p class="text-gray-300 text-sm leading-relaxed mb-4 line-clamp-3">{{ $inc['combined_narrative'] }}</p>

            {{-- Cross-domain --}}
            @if($hasCorr)
            <div class="bg-indigo-950/70 border border-indigo-800 rounded-xl px-4 py-2.5 mb-4">
                <p class="text-indigo-300 text-xs leading-relaxed">
                    <span class="font-semibold text-indigo-200">Cross-Domain &rarr; {{ $inc['correlated_domain'] }}:</span>
                    {{ Str::limit($inc['correlation_note'], 120) }}
                </p>
            </div>
            @endif

            {{-- Forecast bar --}}
            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-500">7-Day Recurrence Risk</span>
                    <span class="{{ $inc['forecast_pct'] >= 75 ? 'text-red-400' : 'text-yellow-400' }} font-bold">{{ $inc['forecast_pct'] }}%</span>
                </div>
                <div class="w-full bg-gray-800 rounded-full h-2">
                    <div class="pbar {{ $inc['forecast_pct'] >= 75 ? 'bg-red-500' : 'bg-yellow-400' }} h-2 rounded-full"
                         style="width:{{ $inc['forecast_pct'] }}%"></div>
                </div>
            </div>

            {{-- Action --}}
            <div class="bg-gray-800/80 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Recommended Action</p>
                <p class="text-gray-200 text-xs leading-relaxed line-clamp-2">{{ $inc['primary_action'] }}</p>
            </div>

            <p class="text-right text-indigo-400 text-xs mt-3">Tap for full briefing &rsaquo;</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-10 text-center text-gray-500">
        No immediate action incidents detected. System operating normally.
    </div>
    @endif
</div>

{{-- ═══════════════════ SECONDARY — TABBED SECTIONS ═══════════════════ --}}
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center gap-3 mb-5">
        <h2 class="text-base font-bold text-gray-400 uppercase tracking-widest">Further Intelligence</h2>
        <span class="text-xs text-gray-600">({{ count($emergingRisk) + count($monitor) + count($opportunity) }} grouped incidents)</span>
    </div>

    {{-- Tab buttons --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <button onclick="switchTab('emerging')"  id="tab-btn-emerging"  class="tab-btn active   text-sm font-semibold px-4 py-2 rounded-lg bg-gray-800 text-gray-300 border border-gray-700">
            Emerging Risk <span class="ml-1 bg-yellow-900 text-yellow-300 text-xs px-2 py-0.5 rounded-full">{{ count($emergingRisk) }}</span>
        </button>
        <button onclick="switchTab('monitor')"   id="tab-btn-monitor"   class="tab-btn text-sm font-semibold px-4 py-2 rounded-lg bg-gray-800 text-gray-300 border border-gray-700">
            Monitor <span class="ml-1 bg-gray-700 text-gray-400 text-xs px-2 py-0.5 rounded-full">{{ count($monitor) }}</span>
        </button>
        <button onclick="switchTab('opportunity')" id="tab-btn-opportunity" class="tab-btn text-sm font-semibold px-4 py-2 rounded-lg bg-gray-800 text-gray-300 border border-gray-700">
            Opportunity <span class="ml-1 bg-green-900 text-green-300 text-xs px-2 py-0.5 rounded-full">{{ count($opportunity) }}</span>
        </button>
    </div>

    {{-- ── Tab: Emerging Risk ── --}}
    <div id="tab-emerging" class="tab-panel active">
        @forelse($emergingRisk as $inc)
        @php $hasCorr = !empty($inc['correlated_domain']); @endphp
        <div class="accordion mb-3 bg-gray-900 border border-yellow-900/50 rounded-xl overflow-hidden">
            <button onclick="toggleAccordion(this)"
                    class="w-full flex items-center justify-between px-5 py-4 text-left">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="bg-yellow-700 text-white text-xs font-bold px-2.5 py-0.5 rounded uppercase">High</span>
                    <span class="font-semibold text-white">{{ $inc['incident_name'] }}</span>
                    <span class="text-gray-500 text-xs">{{ $inc['primary_domain'] }}</span>
                    @if($hasCorr)<span class="text-xs bg-indigo-900 text-indigo-300 px-2 py-0.5 rounded">Cross-Domain</span>@endif
                    <span class="text-yellow-400 text-xs font-bold">Score {{ $inc['impact_score'] }}</span>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-gray-500 text-xs">{{ $inc['date_detected'] }}</span>
                    <svg class="accordion-chevron w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            <div class="accordion-body">
                <div class="px-5 pb-5 border-t border-gray-800 pt-4 space-y-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach($inc['metrics'] as $m)
                        <div class="metric-pill {{ $m['is_favourable'] ? 'bg-green-900/50 text-green-300' : 'bg-red-900/50 text-red-300' }}">
                            <span class="font-bold">{{ ($m['variance_pct']>0?'+':'').number_format($m['variance_pct'],1) }}%</span>
                            <span class="opacity-70">{{ $m['display_name'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">{{ $inc['combined_narrative'] }}</p>
                    @if($hasCorr)
                    <div class="bg-indigo-950/50 border border-indigo-900 rounded-lg px-3 py-2">
                        <p class="text-indigo-300 text-xs">{{ $inc['correlation_note'] }}</p>
                    </div>
                    @endif
                    <div class="bg-gray-800 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-500 mb-1">Recommended Action</p>
                        <p class="text-gray-200 text-xs leading-relaxed">{{ $inc['primary_action'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-600 text-sm py-4">No emerging risk incidents.</p>
        @endforelse
    </div>

    {{-- ── Tab: Monitor ── --}}
    <div id="tab-monitor" class="tab-panel">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-800 text-gray-400 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Incident</th>
                        <th class="px-4 py-3 text-left">Domain</th>
                        <th class="px-4 py-3 text-right">Score</th>
                        <th class="px-4 py-3 text-right">Forecast</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($monitor as $inc)
                    <tr class="clickable hover:bg-gray-800/60 transition-colors" onclick='openDetail(@json($inc))'>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $inc['date_detected'] }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-white">{{ $inc['incident_name'] }}</p>
                            @if(count($inc['subsidiaries']))<p class="text-indigo-400 text-xs">{{ implode(', ', $inc['subsidiaries']) }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $inc['primary_domain'] }}</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-300">{{ $inc['impact_score'] }}</td>
                        <td class="px-4 py-3 text-right text-xs {{ $inc['forecast_pct'] >= 75 ? 'text-red-400' : 'text-yellow-400' }}">{{ $inc['forecast_pct'] }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-600">No monitor incidents.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tab: Opportunity ── --}}
    <div id="tab-opportunity" class="tab-panel">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($opportunity as $inc)
            <div class="clickable bg-gray-900 border border-green-900/50 rounded-xl p-4 hover:border-green-600 transition-colors"
                 onclick='openDetail(@json($inc))'>
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs bg-green-900 text-green-300 font-bold px-2 py-0.5 rounded uppercase">Opportunity</span>
                        <p class="text-white font-semibold text-sm mt-1">{{ $inc['incident_name'] }}</p>
                        <p class="text-green-400 text-xs">{{ $inc['primary_domain'] }}</p>
                    </div>
                    <span class="text-2xl font-black text-green-400">{{ $inc['impact_score'] }}</span>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach($inc['metrics'] as $m)
                    <span class="text-xs bg-green-900/40 text-green-300 px-2 py-0.5 rounded-full font-semibold">
                        +{{ number_format($m['variance_pct'],1) }}% {{ $m['display_name'] }}
                    </span>
                    @endforeach
                </div>
                <p class="text-gray-400 text-xs line-clamp-2">{{ $inc['combined_narrative'] }}</p>
                <p class="text-green-500 text-xs mt-2 text-right">{{ $inc['date_detected'] }}</p>
            </div>
            @empty
            <p class="text-gray-600 text-sm py-4 col-span-3">No opportunity signals detected.</p>
            @endforelse
        </div>
    </div>
</div>

<footer class="border-t border-gray-800 mt-6 px-6 py-4 text-center text-gray-700 text-xs max-w-6xl mx-auto">
    jcorp Da-iO &mdash; AI-powered Incident Intelligence &bull; Zero-fatigue filtering active &bull; Cross-domain correlation engine v3
</footer>

{{-- ═══════════════════ MODAL BACKDROP ═══════════════════ --}}
<div id="modal-backdrop" class="fixed inset-0 bg-black/75 z-40 backdrop-blur-sm" onclick="closeDetail()"></div>

{{-- ═══════════════════ DETAIL SLIDE-OVER ═══════════════════ --}}
<aside id="detail-panel"
       class="fixed top-0 right-0 h-full w-full max-w-lg bg-gray-900 border-l border-gray-700 z-50 overflow-y-auto shadow-2xl">

    <div class="sticky top-0 bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2 flex-wrap">
            <span id="dp-risk" class="text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider"></span>
            <span id="dp-domain" class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded-md"></span>
            <span id="dp-corr-badge" class="bg-gradient-to-r from-indigo-600 to-pink-600 text-white text-xs font-semibold px-2.5 py-1 rounded-md hidden">Cross-Domain</span>
        </div>
        <button onclick="closeDetail()" class="text-gray-500 hover:text-white text-xl p-1 rounded hover:bg-gray-800 transition-colors">&times;</button>
    </div>

    <div class="px-6 py-6 space-y-5">
        <div>
            <h2 id="dp-name" class="text-xl font-bold text-white"></h2>
            <p id="dp-subs" class="text-indigo-400 text-xs mt-1"></p>
            <p id="dp-date" class="text-gray-500 text-xs mt-0.5"></p>
        </div>

        <div class="flex items-center gap-4 bg-gray-800 rounded-xl p-4">
            <div id="dp-score-ring" class="score-ring shrink-0"></div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Impact Score</p>
                <p class="text-gray-300 text-xs mt-1">Higher score = more urgent board-level attention required.</p>
            </div>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Affected Metrics</p>
            <div id="dp-metrics" class="flex flex-wrap gap-2"></div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Combined Root-Cause Narrative</p>
            <p id="dp-narrative" class="text-gray-200 text-sm leading-relaxed"></p>
        </div>

        <div id="dp-corr-block" class="bg-indigo-950 border border-indigo-800 rounded-xl p-4 hidden">
            <p class="text-xs text-indigo-400 uppercase tracking-wider mb-2">Cross-Domain Signal</p>
            <div class="flex items-center gap-2 mb-2">
                <span id="dp-corr-from" class="bg-indigo-800 text-indigo-200 text-xs px-2 py-0.5 rounded"></span>
                <span class="text-indigo-400">&rarr;</span>
                <span id="dp-corr-to" class="bg-pink-900 text-pink-200 text-xs px-2 py-0.5 rounded"></span>
            </div>
            <p id="dp-corr-note" class="text-indigo-300 text-sm leading-relaxed"></p>
        </div>

        <div class="bg-gray-800 rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs text-gray-500 uppercase tracking-wider">7-Day Recurrence Risk</p>
                <span id="dp-forecast-pct" class="font-bold text-sm"></span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-3">
                <div id="dp-forecast-bar" class="pbar h-3 rounded-full" style="width:0%"></div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Recommended Action</p>
            <p id="dp-action" class="text-gray-100 text-sm leading-relaxed"></p>
        </div>

        <details class="bg-gray-800 rounded-xl overflow-hidden">
            <summary class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-300 select-none">Raw Incident Data</summary>
            <pre id="dp-raw" class="px-4 pb-4 text-xs text-green-400 overflow-x-auto leading-relaxed max-h-64 overflow-y-auto"></pre>
        </details>
    </div>
</aside>

{{-- ═══════════════════ JS ═══════════════════ --}}
<script>
// ── Tab switching ──────────────────────────────────────────
function switchTab(name) {
    ['emerging','monitor','opportunity'].forEach(t => {
        document.getElementById('tab-' + t).classList.remove('active');
        document.getElementById('tab-btn-' + t).classList.remove('active');
        document.getElementById('tab-btn-' + t).classList.add('bg-gray-800','text-gray-300');
    });
    document.getElementById('tab-' + name).classList.add('active');
    const btn = document.getElementById('tab-btn-' + name);
    btn.classList.add('active');
    btn.classList.remove('bg-gray-800','text-gray-300');
}

// ── Accordion ─────────────────────────────────────────────
function toggleAccordion(btn) {
    const body    = btn.nextElementSibling;
    const chevron = btn.querySelector('.accordion-chevron');
    body.classList.toggle('open');
    chevron.classList.toggle('open');
}

// ── Detail panel ──────────────────────────────────────────
function openDetail(inc) {
    const isCrit = inc.risk_level === 'Critical';
    const score  = inc.impact_score;
    const fp     = inc.forecast_pct || 0;
    const isCorr = !!inc.correlated_domain;

    // Risk badge
    const riskEl = document.getElementById('dp-risk');
    riskEl.textContent = inc.risk_level;
    riskEl.className = 'text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider '
                     + (isCrit ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white');

    document.getElementById('dp-domain').textContent = inc.primary_domain;

    const corrBadge = document.getElementById('dp-corr-badge');
    isCorr ? corrBadge.classList.remove('hidden') : corrBadge.classList.add('hidden');

    document.getElementById('dp-name').textContent = inc.incident_name;
    document.getElementById('dp-subs').textContent = (inc.subsidiaries || []).join(', ');
    document.getElementById('dp-date').textContent = 'Detected: ' + inc.date_detected;

    // Impact score ring
    const ringEl = document.getElementById('dp-score-ring');
    const ringCol = score >= 70 ? 'border-red-500 bg-red-950 text-red-400'
                  : score >= 40 ? 'border-yellow-500 bg-yellow-950 text-yellow-400'
                                : 'border-green-500 bg-green-950 text-green-400';
    ringEl.className = 'score-ring shrink-0 ' + ringCol;
    ringEl.textContent = score;

    // Metric pills
    const metricsEl = document.getElementById('dp-metrics');
    metricsEl.innerHTML = '';
    (inc.metrics || []).forEach(m => {
        const col = m.is_favourable ? 'bg-green-900/60 text-green-300' : 'bg-red-900/60 text-red-300';
        const sign = m.variance_pct > 0 ? '+' : '';
        metricsEl.innerHTML += `<div class="metric-pill ${col}">
            <span class="font-bold text-sm">${sign}${m.variance_pct.toFixed(1)}%</span>
            <span class="opacity-80 mt-0.5 text-center">${m.display_name}</span>
        </div>`;
    });

    document.getElementById('dp-narrative').textContent = inc.combined_narrative;

    // Correlation
    const corrBlock = document.getElementById('dp-corr-block');
    if (isCorr) {
        corrBlock.classList.remove('hidden');
        document.getElementById('dp-corr-from').textContent = inc.primary_domain;
        document.getElementById('dp-corr-to').textContent   = inc.correlated_domain;
        document.getElementById('dp-corr-note').textContent = inc.correlation_note;
    } else {
        corrBlock.classList.add('hidden');
    }

    // Forecast
    const barEl = document.getElementById('dp-forecast-bar');
    const fCol  = fp >= 75 ? 'bg-red-500' : 'bg-yellow-400';
    barEl.className  = 'pbar h-3 rounded-full ' + fCol;
    barEl.style.width = '0%';
    setTimeout(() => { barEl.style.width = fp + '%'; }, 50);
    const fpcEl = document.getElementById('dp-forecast-pct');
    fpcEl.textContent = fp + '%';
    fpcEl.className = 'font-bold text-sm ' + (fp >= 75 ? 'text-red-400' : 'text-yellow-400');

    document.getElementById('dp-action').textContent = inc.primary_action;
    document.getElementById('dp-raw').textContent    = JSON.stringify(inc, null, 2);

    document.getElementById('detail-panel').classList.add('open');
    document.getElementById('modal-backdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detail-panel').classList.remove('open');
    document.getElementById('modal-backdrop').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>
</body>
</html>
