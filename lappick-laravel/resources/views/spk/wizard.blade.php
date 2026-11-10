@extends('layouts.app')

@section('title', __('lappick.wizard.title') . ' — ' . __('lappick.app_name'))

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
            {{ __('lappick.wizard.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-[#94A3B8] mt-1">
            {{ __('lappick.wizard.subtitle') }}
        </p>
    </div>

    <!-- Clean 3-Step Flow Indicator -->
    <div class="hidden sm:flex items-center gap-2 text-xs font-mono">
        <span class="px-2.5 py-1 rounded-md bg-indigo-600 text-white font-semibold">1. AHP Weights</span>
        <x-icon name="arrow-right" class="w-3.5 h-3.5 text-slate-400" />
        <span class="px-2.5 py-1 rounded-md elevated-pill font-semibold">2. Hard Filters</span>
        <x-icon name="arrow-right" class="w-3.5 h-3.5 text-slate-400" />
        <span class="px-2.5 py-1 rounded-md {{ $topsisResult ? 'bg-emerald-600 text-white' : 'elevated-pill' }} font-semibold">3. TOPSIS Ranking</span>
    </div>
</div>

<!-- Wizard Form -->
<form action="{{ route('wizard') }}" method="GET" class="space-y-6 mb-8">
    <!-- Step 1: AHP Mode & Weights -->
    <div class="surface-card rounded-2xl p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="font-display font-bold text-base">
                {{ __('lappick.wizard.step1') }}
            </h2>
            <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-[#0F1117] border border-slate-200 dark:border-[#2A2F3E] text-xs font-semibold">
                <label class="cursor-pointer px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $mode === 'preset' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">
                    <input type="radio" name="mode" value="preset" class="hidden" {{ $mode === 'preset' ? 'checked' : '' }} onchange="this.form.submit()">
                    {{ __('lappick.wizard.mode_preset') }}
                </label>
                <label class="cursor-pointer px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $mode === 'saaty_scores' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">
                    <input type="radio" name="mode" value="saaty_scores" class="hidden" {{ $mode === 'saaty_scores' ? 'checked' : '' }} onchange="this.form.submit()">
                    {{ __('lappick.wizard.mode_saaty') }}
                </label>
                <label class="cursor-pointer px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $mode === 'custom_matrix' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">
                    <input type="radio" name="mode" value="custom_matrix" class="hidden" {{ $mode === 'custom_matrix' ? 'checked' : '' }} onchange="this.form.submit()">
                    {{ __('lappick.wizard.mode_matrix') }}
                </label>
            </div>
        </div>

        @if($mode === 'preset')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($presets as $slug => $p)
                    <label class="cursor-pointer rounded-xl p-4 border-2 transition block {{ $selectedSlug === $slug ? 'border-indigo-600 bg-indigo-50/50 dark:border-cyan-400 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-[#2A2F3E] hover:border-indigo-400' }}">
                        <input type="radio" name="preset_slug" value="{{ $slug }}" class="hidden" {{ $selectedSlug === $slug ? 'checked' : '' }} onchange="this.form.submit()">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="font-display font-bold text-sm leading-snug">{{ $p->localizedName() }}</span>
                            <span class="font-mono text-[11px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 tabular-nums whitespace-nowrap shrink-0">
                                CR {{ number_format($p->cr, 4) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-[#94A3B8] line-clamp-2">
                            {{ $p->localizedDescription() }}
                        </p>
                    </label>
                @endforeach
            </div>
        @elseif($mode === 'saaty_scores')
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4">
                {{ app()->getLocale() === 'id' ? 'Atur tingkat kepentingan tiap kriteria (1 = Sangat Rendah s.d. 9 = Mutlak Penting). Matriks berpasangan Saaty aᵢⱼ dibangun otomatis secara konsisten.' : 'Set importance level per criterion (1 = Lowest to 9 = Extreme Importance). Pairwise Saaty matrix aᵢⱼ is synthesized consistently.' }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($criteria as $idx => $c)
                    @php $sc = (int) ($saatyScores[$idx] ?? 5); @endphp
                    <div class="elevated-pill p-3 rounded-xl">
                        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                            <span><span class="font-mono text-indigo-600 dark:text-cyan-300">{{ $c->code }}</span> {{ $c->localizedName() }}</span>
                            <span class="font-mono font-bold text-indigo-600 dark:text-cyan-300 tabular-nums" id="saaty_val_{{ $idx }}">{{ $sc }}/9</span>
                        </div>
                        <input type="range" name="saaty[{{ $idx }}]" min="1" max="9" step="1" value="{{ $sc }}"
                            oninput="document.getElementById('saaty_val_{{ $idx }}').innerText = this.value + '/9'"
                            class="w-full accent-indigo-600 dark:accent-cyan-400">
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-3">
                {{ app()->getLocale() === 'id' ? 'Isi segitiga atas matriks perbandingan berpasangan (skala Saaty 0,111 s.d. 9,0). Segitiga bawah otomatis dihitung sebagai kebalikan (1/aᵢⱼ). Uji guardrail CR > 0,10 aktif.' : 'Edit upper triangle of the 9×9 pairwise matrix (0.111 to 9.0). Lower triangle auto-computes reciprocal (1/aᵢⱼ). CR > 0.10 guardrail is active.' }}
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-[11px] font-mono border-collapse tabular-nums">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-[#2A2F3E]">
                            <th class="p-1.5 text-left">{{ app()->getLocale() === 'id' ? 'Kriteria' : 'Criterion' }}</th>
                            @foreach($criteria as $c)
                                <th class="p-1.5 text-center">{{ $c->code }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 9; $i++)
                            <tr class="border-b border-slate-200/60 dark:border-[#2A2F3E]/60">
                                <td class="p-1.5 font-bold text-indigo-600 dark:text-cyan-300">{{ $criteria[$i]->code }}</td>
                                @for($j = 0; $j < 9; $j++)
                                    <td class="p-1 text-center">
                                        @if($i === $j)
                                            <span class="inline-block w-14 py-1 rounded bg-slate-200 dark:bg-[#222634] font-bold">1.00</span>
                                        @elseif($i < $j)
                                            <input type="number" step="0.01" min="0.11" max="9"
                                                name="matrix[{{ $i }}][{{ $j }}]"
                                                value="{{ round($pairwiseMatrix[$i][$j] ?? 1.0, 3) }}"
                                                class="w-14 px-1 py-1 text-center rounded border border-indigo-300 dark:border-cyan-700 bg-white dark:bg-[#0F1117] font-bold">
                                        @else
                                            <span class="inline-block w-14 py-1 text-slate-400">{{ number_format($pairwiseMatrix[$i][$j] ?? 1.0, 2) }}</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endif

        <!-- AHP Resulting Weights & CR Status Bar -->
        <div class="mt-5 pt-4 border-t border-slate-200 dark:border-[#2A2F3E]">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3 text-xs">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold">{{ __('lappick.wizard.cr_status') }}:</span>
                    <span class="inline-flex items-center gap-1.5 font-mono px-2.5 py-0.5 rounded-md font-semibold tabular-nums {{ $ahpResult['is_consistent'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                        @if($ahpResult['is_consistent'])
                            <x-icon name="check" class="w-3.5 h-3.5" />
                            <span>CR = {{ number_format($ahpResult['cr'], 4) }} ≤ 0.10 ({{ __('lappick.wizard.cr_ok') }})</span>
                        @else
                            <x-icon name="alert" class="w-3.5 h-3.5" />
                            <span>CR = {{ number_format($ahpResult['cr'], 4) }} > 0.10 ({{ __('lappick.wizard.cr_bad') }})</span>
                        @endif
                    </span>
                </div>
                <span class="font-mono text-slate-400 tabular-nums">
                    λ<sub>max</sub> = {{ number_format($ahpResult['lambda_max'], 4) }} · CI = {{ number_format($ahpResult['ci'], 4) }} · RI(9) = 1.45
                </span>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-9 gap-2 tabular-nums">
                @foreach($criteria as $idx => $c)
                    @php $wPct = ($ahpResult['weights'][$c->code] ?? $ahpResult['weights_vec'][$idx] ?? 0) * 100; @endphp
                    <div class="elevated-pill p-2.5 rounded-xl text-center">
                        <div class="text-[10px] font-mono text-slate-400">{{ $c->code }} ({{ $c->type === 'cost' ? 'Cost' : 'Ben' }})</div>
                        <div class="font-mono font-extrabold text-sm text-indigo-600 dark:text-cyan-300">{{ number_format($wPct, 2) }}%</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ $c->localizedName() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Step 2: Pre-TOPSIS Hard Filters -->
    <div class="surface-card rounded-2xl p-6 shadow-sm">
        <h2 class="font-display font-bold text-base mb-4">
            {{ __('lappick.wizard.step2') }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div>
                <label class="block font-semibold mb-1">{{ __('lappick.wizard.max_budget_label') }}</label>
                <input type="number" step="0.5" min="3" max="60" name="max_budget_m" value="{{ $maxBudgetM }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
            </div>
            <div>
                <label class="block font-semibold mb-1">{{ __('lappick.catalog.brand_label') }}</label>
                <select name="brand" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                    <option value="">{{ __('lappick.catalog.brand') }}</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ $filterBrand === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">{{ __('lappick.catalog.type_label') }}</label>
                <select name="type_name" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                    <option value="">{{ __('lappick.catalog.type') }}</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ $filterType === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">{{ __('lappick.catalog.min_ram') }}</label>
                <select name="min_ram" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                    @foreach([4, 8, 16, 32] as $r)
                        <option value="{{ $r }}" {{ $minRam === $r ? 'selected' : '' }}>≥ {{ $r }} GB</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">{{ __('lappick.catalog.min_rr') }}</label>
                <select name="min_rr" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                    @foreach([60, 120, 144, 165, 240] as $hz)
                        <option value="{{ $hz }}" {{ $minRr === $hz ? 'selected' : '' }}>≥ {{ $hz }} Hz</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col justify-end gap-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="only_ssd" value="1" {{ $onlySsd ? 'checked' : '' }} class="rounded text-indigo-600">
                    <span class="font-semibold">{{ __('lappick.wizard.only_ssd') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="save_history" value="1" class="rounded text-emerald-600">
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ __('lappick.wizard.save_history') }}</span>
                </label>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                {{ __('lappick.wizard.candidates_count', ['count' => $candidates->count()]) }}
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('wizard') }}" class="px-4 py-2 rounded-xl text-xs font-semibold elevated-pill">
                    {{ __('lappick.catalog.reset_btn') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition shadow-sm">
                    <x-icon name="sliders" class="w-3.5 h-3.5" />
                    <span>{{ __('lappick.wizard.run_btn') }}</span>
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Guardrail / Error Alerts -->
@if($minCandidatesError)
    <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-400 text-amber-900 dark:text-amber-200 mb-8">
        <h3 class="font-display font-bold text-base mb-1 flex items-center gap-2">
            <x-icon name="alert" class="w-4 h-4" />
            <span>{{ app()->getLocale() === 'id' ? 'Kandidat Tidak Mencukupi' : 'Insufficient Candidates' }} ({{ $candidates->count() }})</span>
        </h3>
        <p class="text-xs">{{ __('lappick.wizard.min_candidates_err') }}</p>
    </div>
@elseif($guardrailBlocked)
    <div class="p-6 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-500 text-rose-900 dark:text-rose-200 mb-8">
        <h3 class="font-display font-bold text-lg mb-1 flex items-center gap-2">
            <x-icon name="alert" class="w-5 h-5" />
            <span>{{ __('lappick.wizard.cr_guardrail_title') }}: <em>CR</em> = {{ number_format($ahpResult['cr'], 4) }} &gt; 0.10</span>
        </h3>
        <p class="text-xs mb-3 leading-relaxed">
            {{ __('lappick.wizard.cr_guardrail_desc') }}
        </p>
        <div class="text-xs font-semibold mb-2">
            {{ app()->getLocale() === 'id' ? 'Pasangan Kriteria Paling Menyimpang dari Rasio Konsistensi' : 'Most Inconsistent Pairwise Judgments' }} (|<em>a<sub>ij</sub></em> &minus; <em>w<sub>i</sub></em>/<em>w<sub>j</sub></em>|):
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-4 tabular-nums">
            @foreach($ahpResult['inconsistent_pairs'] as $pair)
                <div class="p-3 rounded-xl bg-white/80 dark:bg-[#0F1117]/80 border border-rose-300 dark:border-rose-800 font-mono text-xs">
                    <strong>{{ $pair['pair'] ?? (($pair['code_i'] ?? 'C1') . ' vs ' . ($pair['code_j'] ?? 'C2')) }}</strong>: Input <em>a<sub>ij</sub></em> = {{ number_format((float)($pair['a_ij'] ?? 1), 3) }}, Ideal <em>w<sub>i</sub></em>/<em>w<sub>j</sub></em> = {{ number_format((float)($pair['implied_ratio'] ?? 1), 3) }}
                </div>
            @endforeach
        </div>
        <a href="{{ request()->fullUrlWithQuery(['force_inconsistent' => 1]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white transition">
            <span>{{ __('lappick.wizard.cr_override') }}</span>
            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>
@endif

@if($topsisResult)
    <!-- Top-3 Podium Cards -->
    <div class="mb-8">
        <h2 class="font-display font-extrabold text-xl mb-4">
            {{ __('lappick.wizard.podium_title') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach(array_slice($topsisResult['ranking'], 0, 3) as $idx => $win)
                @php
                    $rankLabels = ['#1 TOP PICK', '#2 RUNNER-UP', '#3 FINALIST'];
                    $borders = [
                        'border-2 border-indigo-600 dark:border-cyan-400 shadow-md',
                        'border border-slate-200 dark:border-[#2A2F3E]',
                        'border border-slate-200 dark:border-[#2A2F3E]',
                    ];
                @endphp
                <div class="surface-card rounded-2xl p-5 flex flex-col justify-between {{ $borders[$idx] ?? '' }}">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2.5 tabular-nums">
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-mono font-bold {{ $idx === 0 ? 'bg-indigo-600 text-white dark:bg-cyan-400 dark:text-slate-950' : 'elevated-pill text-slate-600 dark:text-slate-300' }}">
                                {{ $rankLabels[$idx] }}
                            </span>
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ __('lappick.wizard.match_score') }} {{ number_format($win['match_pct'], 2) }}% (<em>C<sub>i</sub></em>={{ number_format($win['topsis_score'], 4) }})
                            </span>
                        </div>
                        <a href="{{ route('laptop.show', $win['id']) }}" class="font-display font-bold text-base hover:text-indigo-600 dark:hover:text-cyan-300 line-clamp-2 leading-snug">
                            [ID #{{ $win['id'] }}] {{ $win['name'] }}
                        </a>
                        <div class="font-mono font-extrabold text-xl text-slate-900 dark:text-white mt-2 tabular-nums">
                            Rp {{ number_format($win['price_idr'], 0, ',', '.') }}
                        </div>

                        <div class="grid grid-cols-2 gap-1.5 mt-3 text-[11px] font-mono tabular-nums">
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">RAM: <strong>{{ $win['ram_gb'] }} GB</strong></div>
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">Storage: <strong>{{ $win['storage_effective_gb'] }} GB</strong></div>
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">CPU Rank: <strong>#{{ $win['cpu_ranking'] }}</strong></div>
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">GPU: <strong>{{ number_format($win['gpu_benchmark'], 1) }} pts</strong></div>
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">Mass/Bat: <strong>{{ $win['weight_kg'] }}kg · {{ $win['battery_hours'] }}h</strong></div>
                            <div class="elevated-pill px-2.5 py-1.5 rounded-lg">Panel: <strong>{{ $win['refresh_rate_hz'] }}Hz · {{ $win['user_rating'] }}/5</strong></div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-[#2A2F3E] flex items-center justify-between gap-2 text-xs">
                        <span class="font-mono text-[11px] text-slate-400 tabular-nums">
                            D<sup>+</sup>={{ number_format($win['d_plus'], 4) }} · D<sup>&minus;</sup>={{ number_format($win['d_minus'], 4) }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('compare.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="laptop_id" value="{{ $win['id'] }}">
                                <button type="submit" class="px-2.5 py-1 rounded-lg font-semibold elevated-pill hover:border-indigo-400 transition">
                                    + Compare
                                </button>
                            </form>
                            <a href="{{ route('laptop.show', $win['id']) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-semibold bg-indigo-600 text-white dark:bg-indigo-500">
                                <span>Detail</span>
                                <x-icon name="arrow-right" class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Radar Chart & Top-25 Ranking Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <h3 class="font-display font-bold text-base mb-1">
                {{ __('lappick.wizard.top4_radar_title') }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4">
                {{ __('lappick.wizard.top4_radar_sub') }}
            </p>
            <div class="aspect-square max-w-[320px] mx-auto">
                <canvas id="top4RadarChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-2 surface-card rounded-2xl p-6 shadow-sm">
            <h3 class="font-display font-bold text-base mb-3">
                {{ __('lappick.wizard.top25_title') }}
            </h3>
            <div class="overflow-x-auto max-h-[420px]">
                <table class="w-full text-left text-xs border-collapse tabular-nums">
                    <thead class="sticky top-0 bg-white dark:bg-[#1A1D27] border-b border-slate-200 dark:border-[#2A2F3E] text-slate-400 uppercase text-[11px]">
                        <tr>
                            <th class="py-2 pr-2">Rank</th>
                            <th class="py-2 pr-2">Laptop</th>
                            <th class="py-2 pr-2">C1 Harga</th>
                            <th class="py-2 pr-2">Specs</th>
                            <th class="py-2 pr-2 text-right">D<sup>+</sup></th>
                            <th class="py-2 pr-2 text-right">D<sup>&minus;</sup></th>
                            <th class="py-2 text-right">C<sub>i</sub></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                        @foreach(array_slice($topsisResult['ranking'], 0, 25) as $r)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-[#222634]/40">
                                <td class="py-2.5 pr-2 font-mono font-bold text-indigo-600 dark:text-cyan-300">#{{ $r['rank'] }}</td>
                                <td class="py-2.5 pr-2">
                                    <a href="{{ route('laptop.show', $r['id']) }}" class="font-semibold hover:underline">
                                        [ID #{{ $r['id'] }}] {{ $r['brand'] }} — {{ \Illuminate\Support\Str::limit($r['name'], 40) }}
                                    </a>
                                    @if($r['is_imputed'])
                                        <span title="{{ __('lappick.catalog.imputed_tooltip') }}" class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Imputed</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-2 font-mono">Rp {{ number_format($r['price_idr'], 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-2 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $r['ram_gb'] }}GB · {{ $r['storage_effective_gb'] }}GB · CPU#{{ $r['cpu_ranking'] }} · {{ $r['refresh_rate_hz'] }}Hz
                                </td>
                                <td class="py-2.5 pr-2 font-mono text-right text-slate-400">{{ number_format($r['d_plus'], 5) }}</td>
                                <td class="py-2.5 pr-2 font-mono text-right text-slate-400">{{ number_format($r['d_minus'], 5) }}</td>
                                <td class="py-2.5 font-mono font-bold text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($r['topsis_score'], 6) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Step-by-Step Mathematical Transparency Accordion (Zero Unrendered LaTeX Dollars) -->
    <details class="surface-card rounded-2xl p-6 shadow-sm group">
        <summary class="cursor-pointer font-display font-bold text-base flex items-center justify-between list-none">
            <span>{{ __('lappick.wizard.transparency_btn') }}</span>
            <span class="text-indigo-600 dark:text-cyan-300 group-open:rotate-180 transition">
                <x-icon name="chevron-down" class="w-4 h-4" />
            </span>
        </summary>

        <div class="mt-6 space-y-6 text-xs border-t border-slate-200 dark:border-[#2A2F3E] pt-5">
            <div>
                <h4 class="font-display font-bold text-sm mb-2">
                    1. Pembagi Normalisasi Euclidean &radic;(&sum;<em>x<sub>ij</sub></em><sup>2</sup>), Bobot AHP (<em>w<sub>j</sub></em>), Solusi Ideal Positif (<em>A</em><sup>+</sup>) &amp; Negatif (<em>A</em><sup>&minus;</sup>)
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left font-mono border-collapse tabular-nums">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-[#2A2F3E] text-slate-400">
                                <th class="py-2 pr-3">Parameter</th>
                                @foreach($criteria as $c)
                                    <th class="py-2 pr-3">{{ $c->code }} ({{ $c->type === 'cost' ? 'Cost' : 'Ben' }})</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                            <tr>
                                <td class="py-2 pr-3 font-bold">Bobot AHP (<em>w<sub>j</sub></em>)</td>
                                @foreach($ahpResult['weights'] as $w)
                                    <td class="py-2 pr-3 text-indigo-600 dark:text-cyan-300 font-bold">{{ number_format($w, 6) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="py-2 pr-3 font-bold">Pembagi ||<em>X<sub>j</sub></em>||<sub>2</sub></td>
                                @foreach($topsisResult['denom'] as $den)
                                    <td class="py-2 pr-3">{{ number_format($den, 4) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="py-2 pr-3 font-bold text-emerald-600 dark:text-emerald-400">Solusi Ideal Positif (<em>A</em><sup>+</sup>)</td>
                                @foreach($topsisResult['ideal_pos'] as $ap)
                                    <td class="py-2 pr-3 text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($ap, 6) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="py-2 pr-3 font-bold text-rose-600 dark:text-rose-400">Solusi Ideal Negatif (<em>A</em><sup>&minus;</sup>)</td>
                                @foreach($topsisResult['ideal_neg'] as $an)
                                    <td class="py-2 pr-3 text-rose-600 dark:text-rose-400 font-bold">{{ number_format($an, 6) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h4 class="font-display font-bold text-sm mb-2">
                    2. Matriks Ternormalisasi Terbobot (<em>Y</em> = <em>v<sub>ij</sub></em> = <em>w<sub>j</sub></em> &middot; <em>r<sub>ij</sub></em>) &amp; Jarak Euclidean (<em>D<sub>i</sub></em><sup>+</sup>, <em>D<sub>i</sub></em><sup>&minus;</sup>, <em>C<sub>i</sub></em>) untuk Top-10
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left font-mono border-collapse text-[11px] tabular-nums">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-[#2A2F3E] text-slate-400">
                                <th class="py-2 pr-2">Rank / ID</th>
                                @foreach($criteria as $c)
                                    <th class="py-2 pr-2">v({{ $c->code }})</th>
                                @endforeach
                                <th class="py-2 pr-2 text-right">D<sup>+</sup></th>
                                <th class="py-2 pr-2 text-right">D<sup>&minus;</sup></th>
                                <th class="py-2 text-right">C<sub>i</sub></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                            @foreach(array_slice($topsisResult['ranking'], 0, 10) as $r)
                                <tr>
                                    <td class="py-2 pr-2 font-bold">#{{ $r['rank'] }} (ID {{ $r['id'] }})</td>
                                    @foreach($r['y_vec'] as $yv)
                                        <td class="py-2 pr-2">{{ number_format($yv, 5) }}</td>
                                    @endforeach
                                    <td class="py-2 pr-2 text-right">{{ number_format($r['d_plus'], 5) }}</td>
                                    <td class="py-2 pr-2 text-right">{{ number_format($r['d_minus'], 5) }}</td>
                                    <td class="py-2 font-bold text-right text-emerald-600 dark:text-emerald-400">{{ number_format($r['topsis_score'], 6) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </details>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('top4RadarChart');
        if (!ctx) return;
        const radarData = @json($radarData);
        const labels = @json($criteria->map(fn($c) => $c->code)->values());
        let top4Chart = null;

        function renderTop4Radar() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(148, 163, 184, 0.2)' : 'rgba(15, 23, 42, 0.1)';
            const labelColor = isDark ? '#E6E8EE' : '#334155';
            const legendColor = isDark ? '#94A3B8' : '#475569';
            const palette = isDark
                ? [
                    { border: '#22D3EE', bg: 'rgba(34, 211, 238, 0.16)' },
                    { border: '#A78BFA', bg: 'rgba(167, 139, 250, 0.14)' },
                    { border: '#34D399', bg: 'rgba(52, 211, 153, 0.14)' },
                    { border: '#FBBF24', bg: 'rgba(251, 191, 36, 0.12)' },
                  ]
                : [
                    { border: '#4F46E5', bg: 'rgba(79, 70, 229, 0.16)' },
                    { border: '#0EA5E9', bg: 'rgba(14, 165, 233, 0.14)' },
                    { border: '#10B981', bg: 'rgba(16, 185, 129, 0.14)' },
                    { border: '#F59E0B', bg: 'rgba(245, 158, 11, 0.12)' },
                  ];

            if (top4Chart) top4Chart.destroy();
            top4Chart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: radarData.map((d, i) => ({
                        label: d.label,
                        data: d.data,
                        borderColor: palette[i % palette.length].border,
                        backgroundColor: palette[i % palette.length].bg,
                        borderWidth: 2
                    }))
                },
                options: {
                    responsive: true,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            ticks: { display: false },
                            grid: { color: gridColor },
                            angleLines: { color: gridColor },
                            pointLabels: { color: labelColor, font: { family: 'JetBrains Mono', size: 11, weight: 'bold' } }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: legendColor, boxWidth: 10, font: { size: 10, family: 'JetBrains Mono' } }
                        }
                    }
                }
            });
        }

        renderTop4Radar();
        window.addEventListener('lappick-theme-changed', renderTop4Radar);
    });
    </script>
@endif
@endsection
