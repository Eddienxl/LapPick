@extends('layouts.app')

@section('title', $laptop->name . ' — ' . __('lappick.app_name'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-cyan-300 hover:underline mb-2">
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>{{ app()->getLocale() === 'id' ? 'Kembali ke Katalog 949 Laptop' : 'Back to 949 Laptops Catalog' }}</span>
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold elevated-pill text-indigo-600 dark:text-cyan-300">
                ID #{{ $laptop->id }} · {{ $laptop->brand }}
            </span>
            <span class="px-2.5 py-0.5 rounded-md text-xs font-medium elevated-pill">
                {{ $laptop->type_name }}
            </span>
            @if($laptop->is_imputed)
                <span title="{{ __('lappick.catalog.imputed_tooltip') }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <x-icon name="alert" class="w-3.5 h-3.5" />
                    <span>{{ __('lappick.catalog.imputed_badge') }}</span>
                </span>
            @endif
        </div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mt-2">
            {{ $laptop->name }}
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
        <form action="{{ route('compare.toggle') }}" method="POST">
            @csrf
            <input type="hidden" name="laptop_id" value="{{ $laptop->id }}">
            @php $inCompare = in_array($laptop->id, $compareIds, true); @endphp
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold transition {{ $inCompare ? 'bg-indigo-600 text-white' : 'elevated-pill hover:border-indigo-500' }}">
                @if($inCompare)
                    <x-icon name="check" class="w-3.5 h-3.5" />
                    <span>{{ __('lappick.catalog.in_compare') }}</span>
                @else
                    <x-icon name="scale" class="w-3.5 h-3.5" />
                    <span>{{ __('lappick.catalog.add_compare') }}</span>
                @endif
            </button>
        </form>
        @if($laptop->source_link)
            <a href="{{ $laptop->source_link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white transition">
                <span>{{ app()->getLocale() === 'id' ? 'Sumber Spesifikasi Asli' : 'Original Spec Source' }}</span>
                <x-icon name="external" class="w-3.5 h-3.5" />
            </a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left 2 Columns -->
    <div class="lg:col-span-2 space-y-6">
        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-slate-200 dark:border-[#2A2F3E]">
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        {{ app()->getLocale() === 'id' ? 'Harga Terstandardisasi (1 INR = Rp 190)' : 'Standardized Price (1 INR = Rp 190)' }}
                    </div>
                    <div class="font-mono font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mt-0.5 tabular-nums">
                        Rp {{ number_format($laptop->price_idr, 0, ',', '.') }}
                    </div>
                    <div class="text-xs font-mono text-slate-400 mt-0.5 tabular-nums">
                        ₹ {{ number_format($laptop->price_inr, 0, ',', '.') }} INR
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2.5 text-center tabular-nums">
                    <div class="elevated-pill px-3.5 py-2 rounded-xl">
                        <div class="text-[10px] uppercase text-slate-400">RAM</div>
                        <div class="font-mono font-bold text-sm">{{ $laptop->ram_gb }} GB</div>
                    </div>
                    <div class="elevated-pill px-3.5 py-2 rounded-xl">
                        <div class="text-[10px] uppercase text-slate-400">Storage</div>
                        <div class="font-mono font-bold text-sm">{{ $laptop->storage_gb }}GB {{ $laptop->has_ssd ? 'SSD' : 'HDD' }}</div>
                    </div>
                    <div class="elevated-pill px-3.5 py-2 rounded-xl">
                        <div class="text-[10px] uppercase text-slate-400">Panel</div>
                        <div class="font-mono font-bold text-sm">{{ $laptop->refresh_rate_hz }} Hz</div>
                    </div>
                </div>
            </div>

            <h2 class="font-display font-bold text-base mt-5 mb-3">
                {{ app()->getLocale() === 'id' ? 'Spesifikasi Perangkat Keras Lengkap' : 'Complete Hardware Specifications' }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">Processor (CPU)</span>
                    <span class="font-semibold text-right">{{ $laptop->cpu_name }} (Rank #{{ $laptop->cpu_ranking }})</span>
                </div>
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">Graphics (GPU)</span>
                    <span class="font-semibold text-right">{{ $laptop->gpu_name }} ({{ number_format($laptop->gpu_benchmark, 2) }} pts)</span>
                </div>
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">Memory Architecture</span>
                    <span class="font-mono font-semibold">{{ strtoupper($laptop->ram_type) }} · {{ $laptop->expandable_memory ? 'Expandable' : 'Soldered' }}</span>
                </div>
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">Dedicated VRAM</span>
                    <span class="font-mono font-semibold tabular-nums">{{ number_format($laptop->dedicated_vram_gb, 1) }} GB</span>
                </div>
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">Display Specs</span>
                    <span class="font-mono font-semibold tabular-nums">{{ $laptop->screen_inches }}" ({{ $laptop->screen_resolution }}p · {{ $laptop->refresh_rate_hz }}Hz)</span>
                </div>
                <div class="elevated-pill p-3 rounded-xl flex justify-between gap-2">
                    <span class="text-slate-500 dark:text-slate-400">OS &amp; Touchscreen</span>
                    <span class="font-mono font-semibold">{{ $laptop->os }} · {{ $laptop->touchscreen ? 'Touch' : 'Non-Touch' }}</span>
                </div>
            </div>
        </div>

        <!-- 9 Criteria Normalized Bars -->
        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <h2 class="font-display font-bold text-base mb-1">
                {{ app()->getLocale() === 'id' ? 'Skor Relatif 9 Kriteria (C1–C9 terhadap 949 Laptop)' : '9-Criteria Relative Scores (C1–C9 vs 949 Catalog)' }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4">
                {{ app()->getLocale() === 'id' ? 'Skor 0–100% disesuaikan menurut arah kriteria (Cost dibalik agar bar lebih panjang selalu lebih baik).' : '0–100% normalized score adjusted by criterion direction (Cost inverted so longer bar always means better).' }}
            </p>
            <div class="space-y-3">
                @foreach($stats as $st)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold">
                                <span class="font-mono text-indigo-600 dark:text-cyan-300">{{ $st['code'] }}</span> — {{ $st['name'] }}
                                <span class="ml-1 text-[10px] font-mono px-1.5 py-0.5 rounded {{ $st['type'] === 'benefit' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ strtoupper($st['type']) }}
                                </span>
                            </span>
                            <span class="font-mono font-semibold tabular-nums">
                                @if($st['code'] === 'C1')
                                    Rp {{ number_format($st['raw'], 0, ',', '.') }}
                                @else
                                    {{ $st['raw'] }} {{ $st['unit'] }}
                                @endif
                                <span class="text-slate-400 ml-1">({{ $st['norm_pct'] }}%)</span>
                            </span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-[#222634] overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-600 dark:bg-cyan-400" style="width: {{ max(4, $st['norm_pct']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Theme-Reactive Radar Chart -->
    <div class="surface-card rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h2 class="font-display font-bold text-base mb-1">
                {{ app()->getLocale() === 'id' ? 'Profil Radar 9 Dimensi' : '9-Dimension Radar Profile' }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4">
                {{ app()->getLocale() === 'id' ? 'Visualisasi keseimbangan spesifikasi C1–C9 (0–100).' : 'Visual balance across C1–C9 specifications (0–100).' }}
            </p>
            <div class="aspect-square w-full max-w-[320px] mx-auto">
                <canvas id="laptopRadarChart"></canvas>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200 dark:border-[#2A2F3E]">
            <a href="{{ route('wizard') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition">
                <span>{{ app()->getLocale() === 'id' ? 'Uji Peringkat di Wizard AHP + TOPSIS' : 'Test Rank in AHP + TOPSIS Wizard' }}</span>
                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('laptopRadarChart');
    if (!ctx) return;
    const labels = @json(array_map(fn($s) => $s['code'], $stats));
    const values = @json(array_map(fn($s) => $s['norm_pct'], $stats));
    let radarInstance = null;

    function renderDetailRadar() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.2)' : 'rgba(15, 23, 42, 0.1)';
        const labelColor = isDark ? '#E6E8EE' : '#334155';
        const strokeColor = isDark ? '#22D3EE' : '#4F46E5';
        const fillColor = isDark ? 'rgba(34, 211, 238, 0.18)' : 'rgba(79, 70, 229, 0.18)';

        if (radarInstance) radarInstance.destroy();
        radarInstance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: @json($laptop->brand . ' #' . $laptop->id),
                    data: values,
                    backgroundColor: fillColor,
                    borderColor: strokeColor,
                    pointBackgroundColor: strokeColor,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 25, display: false },
                        grid: { color: gridColor },
                        angleLines: { color: gridColor },
                        pointLabels: { color: labelColor, font: { family: 'JetBrains Mono', size: 11, weight: 'bold' } }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    renderDetailRadar();
    window.addEventListener('lappick-theme-changed', renderDetailRadar);
});
</script>
@endsection
