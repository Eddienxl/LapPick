@extends('layouts.app')

@section('title', __('lappick.nav.login') . ' — ' . __('lappick.app_name'))

@section('content')
<div class="max-w-md mx-auto mt-6">
    <div class="surface-card rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="text-center mb-6">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center mx-auto mb-3 shadow-sm">
                <x-icon name="cpu" class="w-5 h-5" />
            </div>
            <h1 class="font-display font-extrabold text-xl text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'id' ? 'Masuk ke LapPick SPK' : 'Sign in to LapPick DSS' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-[#94A3B8] mt-1">
                {{ app()->getLocale() === 'id' ? 'Pilih akun demo 1-klik untuk demonstrasi UAS, atau masukkan kredensial.' : 'Use a 1-click demo account for UAS walkthrough, or sign in manually.' }}
            </p>
        </div>

        <!-- 1-Click Demo Login -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <input type="hidden" name="quick_role" value="admin">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 text-white transition shadow-sm">
                    Demo Admin
                </button>
            </form>
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <input type="hidden" name="quick_role" value="user">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-semibold elevated-pill hover:border-indigo-500 transition">
                    {{ app()->getLocale() === 'id' ? 'Demo Mahasiswa' : 'Demo Student' }}
                </button>
            </form>
        </div>

        <div class="relative flex py-2 items-center mb-5">
            <div class="flex-grow border-t border-slate-200 dark:border-[#2A2F3E]"></div>
            <span class="flex-shrink mx-3 text-[11px] uppercase font-mono text-slate-400">
                {{ app()->getLocale() === 'id' ? 'kredensial manual' : 'manual credentials' }}
            </span>
            <div class="flex-grow border-t border-slate-200 dark:border-[#2A2F3E]"></div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', 'admin@lappick.id') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
            </div>
            <div>
                <label class="block font-semibold mb-1">Password</label>
                <input type="password" name="password" value="password123" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#0F1117] font-mono">
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl font-semibold bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 transition">
                {{ __('lappick.nav.login') }}
            </button>
        </form>
    </div>
</div>
@endsection
