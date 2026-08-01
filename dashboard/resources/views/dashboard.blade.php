<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Early Warning System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        glass: 'rgba(255,255,255,0.05)',
                    },
                    backdropBlur: { xs: '2px' },
                    boxShadow: {
                        glass: '0 4px 32px 0 rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.08)',
                        'glass-lg': '0 8px 48px 0 rgba(0,0,0,0.55), inset 0 1px 0 rgba(255,255,255,0.10)',
                        neon: '0 0 12px rgba(56,189,248,0.25)',
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">

        /* ── Liquid Glass base card ─────────────────────────────────── */
        .glass-card {
            @apply relative overflow-hidden rounded-2xl
                   bg-slate-800/80 backdrop-blur-md
                   border border-white/[0.07]
                   shadow-glass;
        }
        .glass-card::before {
            content: '';
            @apply absolute inset-0 pointer-events-none rounded-2xl;
            background: linear-gradient(135deg,rgba(255,255,255,0.06) 0%,transparent 60%);
        }

        /* ── Badges ────────────────────────────────────────────────── */
        .badge-critical {
            @apply inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                   text-xs font-bold tracking-wide
                   bg-red-500/20 text-red-300
                   ring-1 ring-red-500/40;
        }
        .badge-high {
            @apply inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                   text-xs font-bold tracking-wide
                   bg-orange-500/20 text-orange-300
                   ring-1 ring-orange-500/40;
        }
        .badge-low {
            @apply inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                   text-xs font-bold tracking-wide
                   bg-yellow-500/20 text-yellow-300
                   ring-1 ring-yellow-500/40;
        }

        /* ── Animated dot ────────────────────────────────────────── */
        .dot-pulse {
            @apply relative inline-block w-2 h-2 rounded-full;
        }
        .dot-pulse::after {
            content: '';
            @apply absolute inset-0 rounded-full animate-ping opacity-60;
            background: inherit;
        }
    </style>
</head>

<body class="bg-slate-900 min-h-screen font-sans antialiased text-slate-100"
      style="background-image: radial-gradient(ellipse at 20% 0%, rgba(56,189,248,0.07) 0%, transparent 50%),
                                radial-gradient(ellipse at 80% 100%, rgba(239,68,68,0.06) 0%, transparent 50%);">

    {{-- ══════════════════════════════════════════════════════════════
         TOP NAVIGATION BAR — Liquid Glass
    ══════════════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-50 border-b border-white/[0.06]"
            style="background:rgba(15,23,42,0.75);backdrop-filter:blur(20px);">
        <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between">

            <div class="flex items-center gap-3">
                {{-- Shield glow icon --}}
                <div class="relative">
                    <div class="absolute inset-0 rounded-xl bg-sky-500/30 blur-md"></div>
                    <div class="relative w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3l7.5 3.75v5.25c0 4.97-3.15 9.09-7.5 10.5C7.65 21.09 4.5 16.97 4.5 12V6.75L12 3z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-white text-lg font-bold leading-tight tracking-wide">
                        Executive Early Warning System
                    </h1>
                    <p class="text-sky-400/70 text-xs font-medium">Real-time KPI Monitoring &amp; Risk Intelligence</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Live indicator --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                    <span class="dot-pulse w-1.5 h-1.5 bg-emerald-400"></span>
                    <span class="text-emerald-400 text-xs font-semibold">LIVE</span>
                </div>
                <div class="text-right hidden sm:block">
                    <p class="text-slate-500 text-[10px] uppercase tracking-wider">Report Date</p>
                    <p class="text-slate-200 text-sm font-semibold">{{ now()->format('F j, Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-screen-xl mx-auto px-6 py-8 space-y-10">

        {{-- ══════════════════════════════════════════════════════════════
             EXECUTIVE SUMMARY BAR
        ══════════════════════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-4">
                Executive Summary
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

                {{-- Total KPIs --}}
                <div class="glass-card p-5 flex items-center gap-4 group hover:border-sky-500/30 transition-all duration-300">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 rounded-xl bg-sky-500/20 blur-sm group-hover:blur-md transition-all"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-white">{{ $metrics->count() }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">KPIs Tracked</p>
                    </div>
                </div>

                {{-- Critical Alerts --}}
                <div class="glass-card p-5 flex items-center gap-4 group hover:border-red-500/30 transition-all duration-300
                            {{ $criticalCount > 0 ? 'ring-1 ring-red-500/20' : '' }}">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 rounded-xl bg-red-500/20 blur-sm group-hover:blur-md transition-all {{ $criticalCount > 0 ? 'animate-pulse-slow' : '' }}"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-red-400">{{ $criticalCount }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Critical Alerts</p>
                    </div>
                </div>

                {{-- High Alerts --}}
                <div class="glass-card p-5 flex items-center gap-4 group hover:border-orange-500/30 transition-all duration-300">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 rounded-xl bg-orange-500/20 blur-sm group-hover:blur-md transition-all"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-orange-400">{{ $highCount }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">High Alerts</p>
                    </div>
                </div>

                {{-- Low Alerts --}}
                <div class="glass-card p-5 flex items-center gap-4 group hover:border-yellow-500/30 transition-all duration-300">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 rounded-xl bg-yellow-500/20 blur-sm group-hover:blur-md transition-all"></div>
                        <div class="relative w-12 h-12 rounded-xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-yellow-400">{{ $lowCount }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Low Alerts</p>
                    </div>
                </div>

            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════
             KPI STATUS CARDS
        ══════════════════════════════════════════════════════════════ --}}
        <section>
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-4">
                KPI Performance Overview
            </h2>

            @if($metrics->isEmpty())
                <div class="glass-card p-10 text-center text-slate-500">
                    No KPI records found. Run
                    <code class="bg-slate-700/60 text-sky-300 px-1.5 py-0.5 rounded text-xs">php artisan db:seed --class=DashboardSeeder</code>
                    to load sample data.
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

                            // Progress bar colour
                            $barColor    = $isOnTarget ? 'from-emerald-500 to-teal-400'
                                         : ($isWarning  ? 'from-amber-500 to-yellow-400'
                                                        : 'from-red-600 to-rose-500');
                            $barGlow     = $isOnTarget ? 'shadow-[0_0_10px_rgba(16,185,129,0.5)]'
                                         : ($isWarning  ? 'shadow-[0_0_10px_rgba(245,158,11,0.5)]'
                                                        : 'shadow-[0_0_10px_rgba(239,68,68,0.5)]');
                            $dotColor    = $isOnTarget ? 'bg-emerald-400' : ($isWarning ? 'bg-amber-400' : 'bg-red-400');
                            $dotPulse    = !$isOnTarget;
                            $labelColor  = $isOnTarget ? 'text-emerald-400' : ($isWarning ? 'text-amber-400' : 'text-red-400');
                            $cardBorder  = $isOnTarget ? 'hover:border-emerald-500/30'
                                         : ($isWarning  ? 'hover:border-amber-500/30'
                                                        : 'hover:border-red-500/30 ring-1 ring-red-500/10');
                            $statusTxt   = $isOnTarget ? 'On Target' : ($isWarning ? 'At Risk' : 'Off Target');
                        @endphp

                        <div class="glass-card p-5 flex flex-col gap-4 {{ $cardBorder }} transition-all duration-300 hover:translate-y-[-2px] hover:shadow-glass-lg">

                            {{-- Card header --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                                        {{ $metric->department }}
                                    </p>
                                    <p class="text-base font-bold text-slate-100 leading-snug mt-1">
                                        {{ $metric->metric_name }}
                                    </p>
                                </div>
                                <span class="flex items-center gap-1.5 text-xs font-bold {{ $labelColor }} shrink-0 mt-0.5">
                                    @if($dotPulse)
                                        <span class="dot-pulse {{ $dotColor }}"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                    @endif
                                    {{ $statusTxt }}
                                </span>
                            </div>

                            {{-- Progress bar --}}
                            <div>
                                <div class="flex justify-between text-xs text-slate-500 mb-2">
                                    <span class="font-medium">Progress to Target</span>
                                    <span class="font-bold {{ $labelColor }}">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-slate-700/60 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r {{ $barColor }} {{ $barGlow }} h-2 rounded-full transition-all duration-1000"
                                         style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Actual vs Target --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl p-3 bg-slate-700/40 border border-white/[0.05]">
                                    <p class="text-[9px] uppercase font-bold text-slate-500 tracking-[0.15em] mb-1">Actual</p>
                                    <p class="text-lg font-extrabold text-slate-100">
                                        {{ number_format((float)$metric->actual_value, 2) }}
                                    </p>
                                </div>
                                <div class="rounded-xl p-3 bg-slate-700/40 border border-white/[0.05]">
                                    <p class="text-[9px] uppercase font-bold text-slate-500 tracking-[0.15em] mb-1">Target</p>
                                    <p class="text-lg font-extrabold text-slate-400">
                                        {{ number_format((float)$metric->target_value, 2) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Date & alert count --}}
                            <div class="flex items-center justify-between text-xs text-slate-600 border-t border-white/[0.05] pt-3">
                                <span>{{ \Carbon\Carbon::parse($metric->recorded_date)->format('M j, Y') }}</span>
                                @if($metric->alerts->isNotEmpty())
                                    <span class="text-red-400 font-bold">
                                        {{ $metric->alerts->count() }} alert{{ $metric->alerts->count() > 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="text-emerald-400 font-bold">No alerts</span>
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
            <h2 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-4">
                Early Warning Alerts
            </h2>

            <div class="glass-card overflow-hidden">
                @if($allAlerts->isEmpty())
                    <div class="p-12 text-center text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-3 text-emerald-500/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                        No active alerts — all KPIs are within acceptable thresholds.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/[0.06] text-left"
                                    style="background:rgba(255,255,255,0.03)">
                                    <th class="px-5 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 w-[140px]">Risk Level</th>
                                    <th class="px-5 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500">Alert Title</th>
                                    <th class="px-5 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 hidden md:table-cell">KPI / Department</th>
                                    <th class="px-5 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 hidden lg:table-cell">Root Cause</th>
                                    <th class="px-5 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 hidden lg:table-cell">Recommended Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.04]">
                                @foreach($allAlerts as $alert)
                                    @php
                                        $rowBg = match($alert->risk_score) {
                                            'Critical' => 'bg-red-500/[0.05] hover:bg-red-500/10',
                                            'High'     => 'bg-orange-500/[0.05] hover:bg-orange-500/10',
                                            default    => 'hover:bg-white/[0.03]',
                                        };
                                        $leftBorder = match($alert->risk_score) {
                                            'Critical' => 'border-l-2 border-l-red-500',
                                            'High'     => 'border-l-2 border-l-orange-500',
                                            default    => 'border-l-2 border-l-yellow-500',
                                        };
                                        $badgeClass = match($alert->risk_score) {
                                            'Critical' => 'badge-critical',
                                            'High'     => 'badge-high',
                                            default    => 'badge-low',
                                        };
                                        $dotColor = match($alert->risk_score) {
                                            'Critical' => 'bg-red-400',
                                            'High'     => 'bg-orange-400',
                                            default    => 'bg-yellow-400',
                                        };
                                        $doPulse = $alert->risk_score === 'Critical';
                                    @endphp
                                    <tr class="{{ $rowBg }} {{ $leftBorder }} transition-colors duration-200">
                                        <td class="px-5 py-4">
                                            <span class="{{ $badgeClass }}">
                                                @if($doPulse)
                                                    <span class="dot-pulse {{ $dotColor }}"></span>
                                                @else
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                                @endif
                                                {{ $alert->risk_score }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-slate-200">
                                            {{ $alert->title }}
                                        </td>
                                        <td class="px-5 py-4 hidden md:table-cell">
                                            <p class="font-medium text-slate-300">{{ $alert->kpiMetric->metric_name }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $alert->kpiMetric->department }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-slate-400 text-xs leading-relaxed hidden lg:table-cell max-w-xs">
                                            {{ $alert->root_cause }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-400 text-xs leading-relaxed hidden lg:table-cell max-w-xs">
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
    <footer class="max-w-screen-xl mx-auto px-6 py-6 mt-6 border-t border-white/[0.05]">
        <p class="text-center text-xs text-slate-600 tracking-wide">
            Executive Early Warning System &mdash; Confidential &mdash; {{ now()->format('Y') }}
        </p>
    </footer>

</body>
</html>
