@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white mb-1">
            {{ __('lappick.catalog.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-[#94A3B8]">
            {{ __('lappick.catalog.subtitle') }}
        </p>
    </div>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('catalog') }}" class="surface-card rounded-2xl p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('lappick.catalog.search_label') }}</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('lappick.catalog.search') }}" class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-sm focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('lappick.catalog.brand_label') }}</label>
                <select name="brand" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-sm">
                    <option value="">{{ __('lappick.catalog.brand') }}</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('lappick.catalog.type_label') }}</label>
                <select name="type_name" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-sm">
                    <option value="">{{ __('lappick.catalog.type') }}</option>
                    @foreach($types as $tp)
                        <option value="{{ $tp }}" @selected(request('type_name') === $tp)>{{ $tp }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('lappick.catalog.specs_label') }}</label>
                <div class="flex gap-1.5">
                    <select name="min_ram" class="w-1/2 px-2 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-xs font-mono">
                        <option value="">RAM</option>
                        @foreach([4, 8, 16, 32] as $r)
                            <option value="{{ $r }}" @selected((int)request('min_ram') === $r)>≥{{ $r }}GB</option>
                        @endforeach
                    </select>
                    <select name="min_rr" class="w-1/2 px-2 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-xs font-mono">
                        <option value="">Hz</option>
                        @foreach([60, 90, 120, 144, 165, 240] as $hz)
                            <option value="{{ $hz }}" @selected((int)request('min_rr') === $hz)>≥{{ $hz }}Hz</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('lappick.catalog.sort_label') }}</label>
                <select name="sort" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] text-sm">
                    <option value="id_asc" @selected(request('sort') === 'id_asc')>ID Default (1–949)</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ app()->getLocale() === 'id' ? 'Harga Terendah' : 'Lowest Price' }}</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ app()->getLocale() === 'id' ? 'Harga Tertinggi' : 'Highest Price' }}</option>
                    <option value="gpu_desc" @selected(request('sort') === 'gpu_desc')>{{ app()->getLocale() === 'id' ? 'GPU Benchmark Tertinggi' : 'Highest GPU Score' }}</option>
                    <option value="cpu_asc" @selected(request('sort') === 'cpu_asc')>{{ app()->getLocale() === 'id' ? 'Peringkat CPU Terbaik' : 'Best CPU Rank' }}</option>
                    <option value="rating_desc" @selected(request('sort') === 'rating_desc')>{{ app()->getLocale() === 'id' ? 'Rating Tertinggi' : 'Highest Rating' }}</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-200 dark:border-[#2A2F3E]">
            <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                {{ __('lappick.catalog.showing', ['from' => $laptops->firstItem() ?? 0, 'to' => $laptops->lastItem() ?? 0, 'total' => $laptops->total()]) }}
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('catalog') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold elevated-pill hover:border-slate-400 transition">
                    {{ __('lappick.catalog.reset_btn') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition">
                    <x-icon name="search" class="w-3.5 h-3.5" />
                    <span>{{ __('lappick.catalog.filter_btn') }}</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Laptop Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($laptops as $lap)
            @php
                $inCompare = in_array($lap->id, $compareIds, true);
            @endphp
            <div class="surface-card rounded-xl p-5 flex flex-col justify-between hover:border-indigo-400 dark:hover:border-cyan-500 transition">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-md elevated-pill text-indigo-600 dark:text-cyan-300 tabular-nums">
                            #{{ $lap->id }} · {{ $lap->brand }}
                        </span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md elevated-pill text-slate-500 dark:text-slate-400">
                            {{ $lap->type_name }}
                        </span>
                    </div>

                    <a href="{{ route('laptop.show', $lap) }}" class="font-display font-bold text-sm text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-cyan-300 line-clamp-2 mb-2 block leading-snug">
                        {{ $lap->name }}
                    </a>

                    <div class="font-mono font-extrabold text-lg text-slate-900 dark:text-white tabular-nums mb-3">
                        {{ $lap->formattedPriceIdr() }}
                        <span class="text-xs font-normal text-slate-400">(₹{{ number_format($lap->price_inr) }})</span>
                    </div>

                    <!-- Structured Spec Mini-Grid (Zero Emoji Clutter) -->
                    <div class="grid grid-cols-2 gap-1.5 text-[11px] font-mono tabular-nums mb-3">
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">RAM</span>
                            <span class="font-semibold">{{ $lap->ram_gb }}GB {{ strtoupper($lap->ram_type) }}</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">{{ $lap->has_ssd ? 'SSD' : 'HDD' }}</span>
                            <span class="font-semibold">{{ (int) $lap->storage_effective_gb }}GB eff.</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">CPU</span>
                            <span class="font-semibold">Rank #{{ $lap->cpu_ranking }}</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">GPU</span>
                            <span class="font-semibold">{{ number_format($lap->gpu_benchmark, 1) }} pts</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">Panel</span>
                            <span class="font-semibold">{{ $lap->refresh_rate_hz }} Hz</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md elevated-pill flex justify-between">
                            <span class="text-slate-400">Mass/Bat</span>
                            <span class="font-semibold">{{ number_format($lap->weight_kg, 2) }}kg · {{ $lap->battery_hours }}h</span>
                        </div>
                    </div>

                    @if($lap->is_imputed)
                        <div title="{{ __('lappick.catalog.imputed_tooltip') }}" class="inline-flex items-center gap-1.5 w-full text-[11px] text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 rounded-lg px-2.5 py-1 mb-3">
                            <x-icon name="alert" class="w-3.5 h-3.5 shrink-0" />
                            <span>{{ __('lappick.catalog.imputed_badge') }} ({{ number_format($lap->user_rating, 1) }}/5)</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-[#2A2F3E] mt-2">
                    <a href="{{ route('laptop.show', $lap) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-cyan-300 hover:underline">
                        <span>{{ __('lappick.catalog.detail') }}</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                    <form action="{{ route('compare.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="laptop_id" value="{{ $lap->id }}">
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-semibold transition {{ $inCompare ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'elevated-pill text-slate-700 dark:text-slate-300 hover:border-indigo-400' }}">
                            @if($inCompare)
                                <x-icon name="check" class="w-3 h-3" />
                                <span>{{ __('lappick.catalog.in_compare') }}</span>
                            @else
                                <span>{{ __('lappick.catalog.add_compare') }}</span>
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $laptops->links() }}
    </div>
@endsection
