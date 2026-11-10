@extends('layouts.app')

@section('title', __('lappick.admin.title') . ' — ' . __('lappick.app_name'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
            {{ __('lappick.admin.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-[#94A3B8] mt-1">
            {{ __('lappick.admin.subtitle') }}
        </p>
    </div>
    <div class="grid grid-cols-3 gap-3 text-center tabular-nums">
        <div class="surface-card px-4 py-2.5 rounded-xl">
            <div class="text-[11px] text-slate-400">Total Laptop</div>
            <div class="font-mono font-extrabold text-lg text-slate-900 dark:text-white">{{ $totalLaptops }}</div>
        </div>
        <div class="surface-card px-4 py-2.5 rounded-xl">
            <div class="text-[11px] text-slate-400">Imputed Rating</div>
            <div class="font-mono font-extrabold text-lg text-amber-600 dark:text-amber-400">{{ $imputedCount }}</div>
        </div>
        <div class="surface-card px-4 py-2.5 rounded-xl">
            <div class="text-[11px] text-slate-400">{{ app()->getLocale() === 'id' ? 'Simulasi SPK' : 'DSS Logs' }}</div>
            <div class="font-mono font-extrabold text-lg text-emerald-600 dark:text-emerald-400">{{ $historyCount }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Left: Add New Laptop Form -->
    <div class="surface-card rounded-2xl p-6 shadow-sm">
        <h2 class="font-display font-bold text-base mb-1 flex items-center gap-2">
            <x-icon name="plus" class="w-4 h-4 text-indigo-600 dark:text-cyan-300" />
            <span>{{ __('lappick.admin.add_laptop') }}</span>
        </h2>
        <p class="text-xs text-slate-500 dark:text-[#94A3B8] mb-4">
            {{ app()->getLocale() === 'id' ? 'Storage efektif (C3) dihitung otomatis: SSD × 1,0 atau HDD × 0,6.' : 'Effective storage (C3) is auto-computed: SSD × 1.0 or HDD × 0.6.' }}
        </p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 text-xs text-rose-700 dark:text-rose-300">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.laptops.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-semibold mb-1">{{ app()->getLocale() === 'id' ? 'Nama Lengkap Laptop' : 'Full Model Name' }}</label>
                <input type="text" name="name" value="{{ old('name', 'ASUS ROG Zephyrus G14 (2026 Custom Edition)') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold mb-1">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', 'ASUS') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">{{ app()->getLocale() === 'id' ? 'Kategori' : 'Category' }}</label>
                    <input type="text" name="type_name" value="{{ old('type_name', 'Gaming') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold mb-1">Processor (CPU)</label>
                    <input type="text" name="cpu_name" value="{{ old('cpu_name', 'AMD Ryzen 9 7940HS') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Graphics (GPU)</label>
                    <input type="text" name="gpu_name" value="{{ old('gpu_name', 'NVIDIA GeForce RTX 4060') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117]">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold mb-1">C1 Harga (Rp)</label>
                    <input type="number" name="price_idr" value="{{ old('price_idr', 21500000) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">C2 RAM (GB)</label>
                    <input type="number" name="ram_gb" value="{{ old('ram_gb', 16) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Storage (GB)</label>
                    <input type="number" name="storage_gb" value="{{ old('storage_gb', 1024) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold mb-1">C4 CPU Rank</label>
                    <input type="number" name="cpu_ranking" value="{{ old('cpu_ranking', 12) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">C5 GPU Score</label>
                    <input type="number" step="0.01" name="gpu_benchmark" value="{{ old('gpu_benchmark', 112.5) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">C6 Battery (h)</label>
                    <input type="number" step="0.1" name="battery_hours" value="{{ old('battery_hours', 8.5) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold mb-1">C7 Weight (kg)</label>
                    <input type="number" step="0.01" name="weight_kg" value="{{ old('weight_kg', 1.65) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">C8 Rating (1–5)</label>
                    <input type="number" step="0.1" name="user_rating" value="{{ old('user_rating', 4.7) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
                <div>
                    <label class="block font-semibold mb-1">C9 Panel (Hz)</label>
                    <input type="number" name="refresh_rate_hz" value="{{ old('refresh_rate_hz', 165) }}" required class="w-full px-2.5 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
                </div>
            </div>
            <label class="flex items-center gap-2 pt-1 cursor-pointer">
                <input type="checkbox" name="has_ssd" value="1" checked class="rounded text-indigo-600">
                <span>{{ app()->getLocale() === 'id' ? 'Media SSD (Faktor Efektif × 1,0)' : 'SSD Storage (Effective Factor × 1.0)' }}</span>
            </label>
            <button type="submit" class="w-full py-2.5 rounded-xl font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition">
                {{ app()->getLocale() === 'id' ? 'Simpan ke Database Katalog' : 'Save to Catalog Database' }}
            </button>
        </form>
    </div>

    <!-- Right 2 Columns -->
    <div class="lg:col-span-2 space-y-6">
        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <h2 class="font-display font-bold text-base mb-3">
                {{ __('lappick.admin.schema_title') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                @foreach($criteria as $c)
                    <div class="elevated-pill p-3 rounded-xl flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="font-mono font-bold text-indigo-600 dark:text-cyan-300">{{ $c->code }}</span>
                            <span class="font-semibold ml-1 truncate">{{ $c->localizedName() }}</span>
                            <div class="text-[10px] font-mono text-slate-400 truncate">{{ $c->column_name }} ({{ $c->unit }})</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shrink-0 {{ $c->type === 'benefit' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            {{ strtoupper($c->type) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface-card rounded-2xl p-6 shadow-sm">
            <h2 class="font-display font-bold text-base mb-3">
                {{ __('lappick.admin.catalog_list') }}
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse tabular-nums">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-[#2A2F3E] text-slate-400 uppercase text-[11px]">
                            <th class="py-2 pr-2">ID</th>
                            <th class="py-2 pr-2">Laptop</th>
                            <th class="py-2 pr-2">C1 Harga</th>
                            <th class="py-2 pr-2">RAM / SSD</th>
                            <th class="py-2 text-right">{{ app()->getLocale() === 'id' ? 'Aksi' : 'Action' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                        @foreach($laptops as $lap)
                            <tr>
                                <td class="py-2.5 pr-2 font-mono font-bold">#{{ $lap->id }}</td>
                                <td class="py-2.5 pr-2">
                                    <a href="{{ route('laptop.show', $lap) }}" class="font-semibold hover:underline">
                                        {{ $lap->brand }} — {{ \Illuminate\Support\Str::limit($lap->name, 46) }}
                                    </a>
                                </td>
                                <td class="py-2.5 pr-2 font-mono">Rp {{ number_format($lap->price_idr, 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-2 font-mono">{{ $lap->ram_gb }}GB / {{ $lap->storage_effective_gb }}GB</td>
                                <td class="py-2.5 text-right">
                                    <form action="{{ route('admin.laptops.destroy', $lap) }}" method="POST" onsubmit="return confirm('Hapus laptop #{{ $lap->id }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline font-semibold">
                                            {{ __('lappick.admin.delete_laptop') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $laptops->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
