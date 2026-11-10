@extends('layouts.app')

@section('title', __('lappick.compare.title') . ' — ' . __('lappick.app_name'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-slate-900 dark:text-white">
            {{ __('lappick.compare.title') }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-[#94A3B8] mt-1">
            {{ __('lappick.compare.subtitle') }}
        </p>
    </div>

    <form action="{{ route('compare.toggle') }}" method="POST" class="flex items-center gap-2">
        @csrf
        <select name="laptop_id" class="px-3 py-2 rounded-xl text-xs surface-card font-medium max-w-xs">
            @foreach($allLaptopsList as $item)
                <option value="{{ $item->id }}">
                    #{{ $item->id }} · {{ $item->brand }} — {{ \Illuminate\Support\Str::limit($item->name, 40) }} (Rp {{ number_format($item->price_idr / 1000000, 1) }}Jt)
                </option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition whitespace-nowrap">
            <x-icon name="plus" class="w-3.5 h-3.5" />
            <span>{{ __('lappick.compare.toggle_btn') }}</span>
        </button>
    </form>
</div>

@if($laptops->count() < 2)
    <div class="surface-card rounded-2xl p-8 text-center">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-4">
            {{ __('lappick.compare.empty') }}
        </p>
        <a href="{{ route('compare', ['ids' => '326,240,499']) }}" class="inline-block px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 text-white">
            {{ app()->getLocale() === 'id' ? 'Muat 3 Laptop Contoh Pemenang Profil' : 'Load 3 Preset Winner Samples' }}
        </a>
    </div>
@else
    <div class="surface-card rounded-2xl overflow-x-auto shadow-sm">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-200 dark:border-[#2A2F3E] bg-slate-50/70 dark:bg-[#0F1117]/70">
                    <th class="p-4 font-display font-bold text-slate-500 uppercase tracking-wider w-52">
                        {{ app()->getLocale() === 'id' ? 'Kriteria Keputusan' : 'Decision Criterion' }}
                    </th>
                    @foreach($laptops as $lap)
                        <th class="p-4 min-w-[220px] align-top border-l border-slate-200 dark:border-[#2A2F3E]">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold elevated-pill text-indigo-600 dark:text-cyan-300">
                                    #{{ $lap->id }} · {{ $lap->brand }}
                                </span>
                                <form action="{{ route('compare.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="laptop_id" value="{{ $lap->id }}">
                                    <button type="submit" class="text-xs font-mono text-rose-500 hover:underline font-semibold">
                                        {{ app()->getLocale() === 'id' ? 'Hapus' : 'Remove' }}
                                    </button>
                                </form>
                            </div>
                            <a href="{{ route('laptop.show', $lap) }}" class="font-display font-bold text-sm text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-cyan-300 line-clamp-2 leading-snug">
                                {{ $lap->name }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-1">{{ $lap->type_name }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-[#2A2F3E]">
                @foreach($criteria as $c)
                    @php
                        $col = $c->column_name;
                        $winVal = $winners[$c->code] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-[#222634]/40">
                        <td class="p-3.5 font-semibold">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-indigo-600 dark:text-cyan-300">{{ $c->code }}</span>
                                <span>{{ $c->localizedName() }}</span>
                            </div>
                            <span class="inline-block mt-1 text-[10px] font-mono px-1.5 py-0.5 rounded {{ $c->type === 'benefit' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                {{ strtoupper($c->type) }} ({{ $c->type === 'benefit' ? 'Max' : 'Min' }})
                            </span>
                        </td>
                        @foreach($laptops as $lap)
                            @php
                                $val = (float) $lap->{$col};
                                $isWinner = $winVal !== null && abs($val - $winVal) < 1e-6;
                            @endphp
                            <td class="p-3.5 font-mono tabular-nums border-l border-slate-200 dark:border-[#2A2F3E] {{ $isWinner ? 'bg-emerald-50/70 dark:bg-emerald-950/30 font-bold text-emerald-700 dark:text-emerald-300' : '' }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span>
                                        @if($c->code === 'C1')
                                            Rp {{ number_format($val, 0, ',', '.') }}
                                        @elseif($c->code === 'C3')
                                            {{ number_format($val, 0) }} GB <span class="text-[10px] text-slate-400 block">({{ $lap->storage_gb }}GB {{ $lap->has_ssd ? 'SSD' : 'HDD' }})</span>
                                        @elseif($c->code === 'C4')
                                            Rank #{{ (int) $val }} <span class="block text-[10px] font-sans font-normal text-slate-400">{{ $lap->cpu_name }}</span>
                                        @elseif($c->code === 'C5')
                                            {{ number_format($val, 2) }} pts <span class="block text-[10px] font-sans font-normal text-slate-400">{{ $lap->gpu_name }}</span>
                                        @elseif($c->code === 'C7')
                                            {{ number_format($val, 2) }} kg
                                        @elseif($c->code === 'C8')
                                            {{ number_format($val, 1) }} / 5.0
                                        @else
                                            {{ $val }} {{ $c->unit }}
                                        @endif
                                    </span>
                                    @if($isWinner)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-sans font-bold bg-emerald-600 text-white shrink-0">
                                            <x-icon name="check" class="w-3 h-3" />
                                            <span>{{ __('lappick.compare.winner') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
