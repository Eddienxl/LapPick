@extends('layouts.app')

@section('title', __('lappick.history.title') . ' — ' . __('lappick.app_name'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
            {{ __('lappick.history.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-[#94A3B8] mt-1">
            {{ __('lappick.history.subtitle') }}
        </p>
    </div>
    <div class="flex items-center gap-2 no-print">
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold elevated-pill hover:border-indigo-400 transition">
            <x-icon name="printer" class="w-3.5 h-3.5" />
            <span>{{ __('lappick.history.print_pdf') }}</span>
        </button>
        <a href="{{ route('wizard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition">
            <x-icon name="plus" class="w-3.5 h-3.5" />
            <span>{{ __('lappick.history.new_sim') }}</span>
        </a>
    </div>
</div>

<div class="space-y-6">
    @foreach($histories as $hist)
        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-200 dark:border-[#2A2F3E]">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold elevated-pill text-indigo-600 dark:text-cyan-300">
                        #{{ $hist->id }} · {{ strtoupper($hist->profile_slug) }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-semibold tabular-nums {{ $hist->is_consistent ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-50 text-rose-700' }}">
                        CR = {{ number_format($hist->cr, 4) }} (λmax = {{ number_format($hist->lambda_max, 4) }})
                    </span>
                    <span class="text-xs text-slate-400 font-mono tabular-nums">
                        {{ $hist->created_at?->format('Y-m-d H:i') }} · {{ $hist->total_candidates }} {{ app()->getLocale() === 'id' ? 'kandidat' : 'candidates' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 no-print">
                    <a href="{{ route('history.export', $hist) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition">
                        <x-icon name="download" class="w-3.5 h-3.5" />
                        <span>{{ __('lappick.history.export_csv') }}</span>
                    </a>
                </div>
            </div>

            <!-- AHP Weights Summary -->
            <div class="mt-4 mb-5">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'id' ? 'Vektor Bobot Kriteria AHP (w1–w9):' : 'AHP Criteria Weight Vector (w1–w9):' }}
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-9 gap-2">
                    @foreach($criteria as $idx => $c)
                        @php $w = $hist->weights[$c->code] ?? $hist->weights[$idx] ?? 0; @endphp
                        <div class="elevated-pill p-2 rounded-lg text-center tabular-nums">
                            <div class="text-[10px] font-mono text-slate-400">{{ $c->code }}</div>
                            <div class="font-mono font-bold text-xs text-indigo-600 dark:text-cyan-300">{{ number_format($w * 100, 1) }}%</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Top-5 Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-[#2A2F3E] text-slate-400 uppercase text-[11px]">
                            <th class="py-2 pr-3">Rank</th>
                            <th class="py-2 pr-3">Laptop</th>
                            <th class="py-2 pr-3">Harga (C1)</th>
                            <th class="py-2 pr-3">RAM / Storage</th>
                            <th class="py-2 pr-3">CPU / GPU</th>
                            <th class="py-2 pr-3 text-right">D<sup>+</sup> / D<sup>&minus;</sup></th>
                            <th class="py-2 text-right">C<sub>i</sub> (Match)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E] tabular-nums">
                        @foreach(array_slice($hist->top_results ?? [], 0, 5) as $r)
                            <tr>
                                <td class="py-2.5 pr-3 font-mono font-bold text-indigo-600 dark:text-cyan-300">#{{ $r['rank'] }}</td>
                                <td class="py-2.5 pr-3 font-semibold">
                                    <a href="{{ route('laptop.show', $r['id']) }}" class="hover:underline">
                                        [ID #{{ $r['id'] }}] {{ $r['brand'] }} — {{ \Illuminate\Support\Str::limit($r['name'], 50) }}
                                    </a>
                                </td>
                                <td class="py-2.5 pr-3 font-mono">Rp {{ number_format($r['price_idr'], 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-3 font-mono">{{ $r['ram_gb'] }}GB / {{ $r['storage_effective_gb'] }}GB</td>
                                <td class="py-2.5 pr-3 font-mono">CPU #{{ $r['cpu_ranking'] }} · GPU {{ number_format($r['gpu_benchmark'], 1) }}</td>
                                <td class="py-2.5 pr-3 font-mono text-right text-slate-400">
                                    {{ number_format($r['d_plus'] ?? 0, 4) }} / {{ number_format($r['d_minus'] ?? 0, 4) }}
                                </td>
                                <td class="py-2.5 font-mono font-bold text-right text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($r['topsis_score'], 6) }} ({{ number_format($r['match_pct'] ?? ($r['topsis_score'] * 100), 2) }}%)
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="mt-4">
        {{ $histories->links() }}
    </div>
</div>
@endsection
