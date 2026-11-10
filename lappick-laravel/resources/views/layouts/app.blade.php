<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('lappick.app_name') . ' — ' . __('lappick.tagline'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    }
                }
            }
        };
        (function() {
            const saved = localStorage.getItem('lappick_theme') || 'light';
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
            }
        })();
        function toggleLapPickTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            if (isDark) {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('lappick_theme', 'light');
            } else {
                html.classList.remove('light');
                html.classList.add('dark');
                localStorage.setItem('lappick_theme', 'dark');
            }
            window.dispatchEvent(new Event('lappick-theme-changed'));
        }
        function toggleMobileMenu() {
            const drawer = document.getElementById('mobile-nav-drawer');
            if (drawer) {
                drawer.classList.toggle('hidden');
            }
        }
    </script>
    <style>
        /* Design Tokens aligned strictly with 02_DESIGN_SYSTEM_SPK_LAPTOP.md (Light Opsi C / Dark Opsi B) */
        :root {
            --bg-primary: #F8FAFC;
            --bg-surface: #FFFFFF;
            --bg-elevated: #F1F5F9;
            --text-primary: #0F172A;
            --text-secondary: #64748B;
            --accent-primary: #4F46E5;
            --accent-secondary: #10B981;
            --border-color: #E2E8F0;
        }
        html.dark {
            --bg-primary: #0F1117;
            --bg-surface: #1A1D27;
            --bg-elevated: #222634;
            --text-primary: #E6E8EE;
            --text-secondary: #94A3B8;
            --accent-primary: #8B5CF6;
            --accent-secondary: #22D3EE;
            --border-color: #2A2F3E;
        }
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .surface-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
        }
        .elevated-pill {
            background-color: var(--bg-elevated);
            border: 1px solid var(--border-color);
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col transition-colors duration-150">
    @php
        $compareCount = count((array) session('compare_ids', [326, 240, 499]));
        $currLocale = app()->getLocale();
    @endphp

    <!-- Single-Row Responsive SaaS Navbar -->
    <header class="sticky top-0 z-50 border-b border-slate-200/80 dark:border-[#2A2F3E] bg-white/95 dark:bg-[#131620]/95 backdrop-blur-md no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Brand Wordmark -->
            <a href="{{ route('home') }}" title="LapPick — AHP + TOPSIS Decision Support System" class="flex items-center gap-2.5 shrink-0 whitespace-nowrap group">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center text-white shadow-sm group-hover:bg-indigo-700 dark:group-hover:bg-indigo-400 transition">
                    <x-icon name="cpu" class="w-4 h-4" />
                </div>
                <span class="font-display font-bold text-lg tracking-tight text-slate-900 dark:text-[#E6E8EE]">
                    LapPick
                </span>
                <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-slate-100 dark:bg-[#222634] text-slate-600 dark:text-cyan-300 border border-slate-200 dark:border-[#2A2F3E]">
                    AHP·TOPSIS
                </span>
            </a>

            <!-- Desktop Primary Navigation (strictly single-line whitespace-nowrap) -->
            <nav class="hidden lg:flex items-center gap-1 text-[13px] font-medium whitespace-nowrap">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('home') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="home" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.home') }}</span>
                </a>
                <a href="{{ route('wizard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('wizard') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="sliders" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.wizard') }}</span>
                </a>
                <a href="{{ route('catalog') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('catalog*') || request()->routeIs('laptop.*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="laptop" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.catalog') }}</span>
                    <span class="text-[11px] font-mono opacity-75">949</span>
                </a>
                <a href="{{ route('compare') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('compare*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="scale" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.compare') }}</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[11px] font-mono font-bold {{ request()->routeIs('compare*') ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-cyan-300' }}">{{ $compareCount }}</span>
                </a>
                <a href="{{ route('history') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('history*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="clock" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.history') }}</span>
                </a>
                <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap transition {{ request()->routeIs('admin*') ? 'bg-indigo-600 text-white dark:bg-indigo-500 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#222634]' }}">
                    <x-icon name="shield" class="w-4 h-4 shrink-0" />
                    <span>{{ __('lappick.nav.admin') }}</span>
                </a>
                <a href="http://localhost:8501" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg whitespace-nowrap border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-cyan-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 font-semibold transition">
                    <x-icon name="chart" class="w-3.5 h-3.5 shrink-0" />
                    <span>{{ __('lappick.nav.lab') }}</span>
                    <x-icon name="external" class="w-3 h-3 shrink-0 opacity-75" />
                </a>
            </nav>

            <!-- Right Controls (Clean ID|EN, Icon Theme Toggle, Auth, Mobile Menu) -->
            <div class="flex items-center gap-2 shrink-0 whitespace-nowrap">
                <!-- Language Segmented Control (Clean Text, No Flag Emoji Bugs) -->
                <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-[#2A2F3E] p-0.5 bg-slate-100 dark:bg-[#0F1117] text-xs font-mono font-bold">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" class="px-2 py-1 rounded-md transition whitespace-nowrap {{ $currLocale === 'id' ? 'bg-white dark:bg-[#1A1D27] text-indigo-600 dark:text-cyan-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                        ID
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="px-2 py-1 rounded-md transition whitespace-nowrap {{ $currLocale === 'en' ? 'bg-white dark:bg-[#1A1D27] text-indigo-600 dark:text-cyan-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                        EN
                    </a>
                </div>

                <!-- Icon Theme Toggle Button -->
                <button type="button" onclick="toggleLapPickTheme()" aria-label="Toggle color theme" title="Light Mode (Opsi C) / Dark Mode (Opsi B)" class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-[#2A2F3E] bg-slate-100 dark:bg-[#1A1D27] text-slate-700 dark:text-cyan-300 hover:bg-slate-200 dark:hover:bg-[#222634] transition shrink-0">
                    <span class="dark:hidden"><x-icon name="moon" class="w-4 h-4" /></span>
                    <span class="hidden dark:inline"><x-icon name="sun" class="w-4 h-4" /></span>
                </button>

                @auth
                    <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 whitespace-nowrap hover:opacity-90 transition">
                            <span>{{ __('lappick.nav.logout') }}</span>
                            <span class="font-mono text-[10px] opacity-80">({{ auth()->user()->role }})</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white dark:bg-indigo-500 dark:hover:bg-indigo-400 whitespace-nowrap transition">
                        {{ __('lappick.nav.login') }}
                    </a>
                @endauth

                <!-- Mobile Hamburger Toggle -->
                <button type="button" onclick="toggleMobileMenu()" aria-label="Open menu" class="lg:hidden w-9 h-9 inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-[#2A2F3E] text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#222634] transition">
                    <x-icon name="menu" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Mobile Collapsible Drawer -->
        <div id="mobile-nav-drawer" class="hidden lg:hidden border-t border-slate-200 dark:border-[#2A2F3E] bg-white dark:bg-[#1A1D27] px-4 py-3 space-y-1 text-sm font-medium">
            <a href="{{ route('home') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('home') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="home" class="w-4 h-4" /> {{ __('lappick.nav.home') }}
            </a>
            <a href="{{ route('wizard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('wizard') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="sliders" class="w-4 h-4" /> {{ __('lappick.nav.wizard') }}
            </a>
            <a href="{{ route('catalog') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('catalog*') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="laptop" class="w-4 h-4" /> {{ __('lappick.nav.catalog') }} (949)
            </a>
            <a href="{{ route('compare') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('compare*') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="scale" class="w-4 h-4" /> {{ __('lappick.nav.compare') }} ({{ $compareCount }})
            </a>
            <a href="{{ route('history') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('history*') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="clock" class="w-4 h-4" /> {{ __('lappick.nav.history') }}
            </a>
            <a href="{{ route('admin.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('admin*') ? 'bg-indigo-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                <x-icon name="shield" class="w-4 h-4" /> {{ __('lappick.nav.admin') }}
            </a>
            <a href="http://localhost:8501" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-2 rounded-lg text-indigo-600 dark:text-cyan-300">
                <x-icon name="chart" class="w-4 h-4" /> {{ __('lappick.nav.lab') }}
            </a>
            @guest
                <a href="{{ route('login') }}" class="flex items-center justify-center mt-2 px-3 py-2 rounded-lg bg-slate-900 dark:bg-indigo-500 text-white font-semibold">
                    {{ __('lappick.nav.login') }}
                </a>
            @endguest
        </div>
    </header>

    @if(session('status'))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4 no-print">
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 text-xs font-medium flex items-center justify-between gap-4">
                <span class="inline-flex items-center gap-2">
                    <x-icon name="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span>{{ session('status') }}</span>
                </span>
                <a href="{{ route('compare') }}" class="inline-flex items-center gap-1 font-semibold underline shrink-0">
                    <span>{{ __('lappick.nav.compare') }} ({{ $compareCount }})</span>
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 dark:border-[#2A2F3E] bg-white/60 dark:bg-[#131620]/60 py-6 mt-12 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div>
                <strong class="text-slate-800 dark:text-slate-200">LapPick SPK</strong> — Proyek UAS Sistem Pendukung Keputusan Semester 7 · Teknik Informatika UIN Suska Riau
            </div>
            <div class="font-mono tabular-nums">
                Ahmad Fadli Pratama (NIM. 12350111030) · AHP + TOPSIS (1e-6 Verified)
            </div>
        </div>
    </footer>
</body>
</html>
