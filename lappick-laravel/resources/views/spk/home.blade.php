@extends('layouts.app')

@section('content')
    <!-- Asymmetric Two-Column Hero Section (Copy + Live Preset Winners Preview) -->
    <section class="surface-card rounded-2xl p-6 sm:p-10 mb-8 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left Column: Core Value Prop & Single-Line Actions -->
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-cyan-300 border border-indigo-200 dark:border-indigo-800 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>{{ __('lappick.hero.badge') }}</span>
                </div>

                <h1 class="font-display font-extrabold text-3xl sm:text-4xl lg:text-[2.65rem] tracking-tight text-slate-900 dark:text-[#E6E8EE] leading-[1.15] mb-4">
                    {{ __('lappick.hero.title') }}
                </h1>

                <p class="text-sm sm:text-base text-slate-600 dark:text-[#94A3B8] mb-7 leading-relaxed max-w-2xl">
                    {{ __('lappick.hero.subtitle') }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('wizard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-display font-semibold text-sm bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white shadow-sm whitespace-nowrap transition">
                        <span>{{ __('lappick.hero.cta_wizard') }}</span>
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-display font-semibold text-sm elevated-pill text-slate-700 dark:text-slate-200 hover:border-indigo-400 dark:hover:border-cyan-500 whitespace-nowrap transition">
                        <x-icon name="laptop" class="w-4 h-4" />
                        <span>{{ __('lappick.hero.cta_catalog') }}</span>
                    </a>
                    <a href="http://localhost:8501" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-indigo-600 dark:text-cyan-300 hover:underline whitespace-nowrap">
                        <x-icon name="chart" class="w-3.5 h-3.5" />
                        <span>{{ __('lappick.hero.cta_lab') }}</span>
                        <x-icon name="external" class="w-3 h-3" />
                    </a>
                </div>
            </div>

            <!-- Right Column: Live Preset Top-1 Showcase Panel -->
            <div class="lg:col-span-5">
                <div class="rounded-xl bg-slate-50/90 dark:bg-[#0F1117]/90 border border-slate-200 dark:border-[#2A2F3E] p-5">
                    <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-200 dark:border-[#2A2F3E]">
                        <div>
                            <div class="text-xs font-display font-bold text-slate-900 dark:text-white">
                                {{ __('lappick.hero.pipeline_title') }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ __('lappick.hero.pipeline_sub') }}
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                            TOPSIS
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($featuredLaptops->take(3) as $idx => $fl)
                            @php
                                $profileLabels = ['Student Top-1', 'Gamer & Creator Top-1', 'Runner-Up Balanced'];
                            @endphp
                            <a href="{{ route('laptop.show', $fl) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-[#1A1D27] border border-slate-200/80 dark:border-[#2A2F3E] hover:border-indigo-400 dark:hover:border-cyan-400 transition group">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 text-[10px] font-mono uppercase tracking-wider text-slate-400 mb-0.5">
                                        <span class="font-bold text-indigo-600 dark:text-cyan-400">#{{ $fl->id }} · {{ $fl->brand }}</span>
                                        <span>·</span>
                                        <span>{{ $profileLabels[$idx] ?? $fl->type_name }}</span>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate group-hover:text-indigo-600 dark:group-hover:text-cyan-300 transition">
                                        {{ $fl->name }}
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">
                                        {{ $fl->ram_gb }}GB · {{ (int) $fl->storage_effective_gb }}GB · {{ $fl->refresh_rate_hz }}Hz
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-mono font-bold text-xs text-slate-900 dark:text-white tabular-nums">
                                        {{ $fl->formattedPriceIdr() }}
                                    </div>
                                    <div class="text-[10px] font-mono text-indigo-600 dark:text-cyan-400 mt-0.5">
                                        CPU #{{ $fl->cpu_ranking }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4 Key Metrics (Uniform Single-Line Hierarchy, No Multi-Color Rainbow Slop) -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <div class="surface-card rounded-xl p-5 flex flex-col justify-between">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                {{ __('lappick.metrics.alternatives_label') }}
            </div>
            <div class="font-mono font-bold text-2xl text-slate-900 dark:text-white tabular-nums whitespace-nowrap">
                {{ __('lappick.metrics.alternatives_value') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                {{ __('lappick.metrics.alternatives_sub', ['brands' => $brandsCount]) }}
            </div>
        </div>

        <div class="surface-card rounded-xl p-5 flex flex-col justify-between">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                {{ __('lappick.metrics.criteria_label') }}
            </div>
            <div class="font-mono font-bold text-2xl text-slate-900 dark:text-white tabular-nums whitespace-nowrap">
                {{ __('lappick.metrics.criteria_value') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                {{ __('lappick.metrics.criteria_sub') }}
            </div>
        </div>

        <div class="surface-card rounded-xl p-5 flex flex-col justify-between">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                {{ __('lappick.metrics.cr_label') }}
            </div>
            <div class="font-mono font-bold text-2xl text-emerald-600 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                {{ __('lappick.metrics.cr_value') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                {{ __('lappick.metrics.cr_sub') }}
            </div>
        </div>

        <div class="surface-card rounded-xl p-5 flex flex-col justify-between">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                {{ __('lappick.metrics.precision_label') }}
            </div>
            <div class="font-mono font-bold text-2xl text-indigo-600 dark:text-cyan-300 tabular-nums whitespace-nowrap">
                {{ __('lappick.metrics.precision_value') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                {{ __('lappick.metrics.precision_sub') }}
            </div>
        </div>
    </section>

    <!-- 4 Preset Persona Cards -->
    <section class="mb-10">
        <div class="mb-4">
            <h2 class="font-display font-bold text-xl text-slate-900 dark:text-white">
                {{ __('lappick.home.preset_title') }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-[#94A3B8] mt-0.5">
                {{ __('lappick.home.preset_sub') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($presets as $preset)
                @php
                    $topWeights = $preset->weights;
                    arsort($topWeights);
                    $top3W = array_slice($topWeights, 0, 3, true);
                @endphp
                <a href="{{ route('wizard', ['mode' => 'preset', 'preset_slug' => $preset->slug]) }}" class="surface-card rounded-xl p-5 hover:border-indigo-500 dark:hover:border-cyan-400 transition group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-mono font-bold uppercase px-2 py-0.5 rounded-md elevated-pill text-slate-700 dark:text-slate-200">
                                {{ $preset->slug }}
                            </span>
                            <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400 font-semibold tabular-nums">
                                CR {{ number_format($preset->cr, 4) }}
                            </span>
                        </div>
                        <h3 class="font-display font-bold text-base text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-cyan-300 transition mb-1.5">
                            {{ $preset->localizedName() }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4 leading-relaxed">
                            {{ $preset->localizedDescription() }}
                        </p>
                    </div>

                    <div>
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                            {{ __('lappick.home.top3_label') }}
                        </div>
                        <div class="space-y-1.5 mb-4">
                            @foreach($top3W as $cCode => $wVal)
                                @php $pct = round($wVal * 100, 1); @endphp
                                <div class="flex items-center gap-2 text-[11px] font-mono tabular-nums">
                                    <span class="w-6 font-bold text-slate-600 dark:text-slate-300">{{ $cCode }}</span>
                                    <div class="flex-1 h-1.5 rounded-full bg-slate-200 dark:bg-[#222634] overflow-hidden">
                                        <div class="h-full rounded-full bg-indigo-600 dark:bg-cyan-400" style="width: {{ min(100, $pct * 2.5) }}%"></div>
                                    </div>
                                    <span class="w-11 text-right text-slate-600 dark:text-slate-300">{{ number_format($pct, 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-cyan-300 pt-2 border-t border-slate-100 dark:border-[#2A2F3E] w-full">
                            <span>{{ __('lappick.home.run_preset') }}</span>
                            <x-icon name="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 9 Decision Criteria Architecture Table -->
    <section class="surface-card rounded-2xl p-6">
        <h2 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">
            {{ __('lappick.home.criteria_title') }}
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left border-collapse">
                <thead class="text-[11px] uppercase tracking-wider bg-slate-50 dark:bg-[#0F1117] text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-[#2A2F3E]">
                    <tr>
                        <th class="px-4 py-3 font-mono">{{ __('lappick.home.col_code') }}</th>
                        <th class="px-4 py-3">{{ __('lappick.home.col_name') }}</th>
                        <th class="px-4 py-3">{{ __('lappick.home.col_type') }}</th>
                        <th class="px-4 py-3 font-mono">{{ __('lappick.home.col_unit') }}</th>
                        <th class="px-4 py-3 font-mono">{{ __('lappick.home.col_column') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                    @foreach($criteria as $c)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-[#222634]/40">
                            <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-cyan-300">{{ $c->code }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">
                                {{ $c->name_id }} <span class="text-slate-400 font-normal">/ {{ $c->name_en }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($c->type === 'benefit')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <x-icon name="trend-up" class="w-3 h-3" />
                                        <span>{{ __('lappick.home.benefit_desc') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                        <x-icon name="trend-down" class="w-3 h-3" />
                                        <span>{{ __('lappick.home.cost_desc') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $c->unit }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-400">{{ $c->column_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
