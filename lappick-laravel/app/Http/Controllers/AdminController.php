<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\DecisionHistory;
use App\Models\Laptop;
use App\Models\PresetProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function showLogin(): View
    {
        return view('spk.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Support 1-click quick login for UAS demonstration
        if ($request->filled('quick_role')) {
            $email = $request->input('quick_role') === 'admin' ? 'admin@lappick.id' : 'fadli@uin-suska.ac.id';
            $user = User::where('email', $email)->first();
            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('admin.index')->with('status', "Berhasil masuk sebagai {$user->name} ({$user->role}).");
            }
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.index'))->with('status', 'Berhasil masuk.');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar.');
    }

    public function index(Request $request): View
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $presets = PresetProfile::orderBy('id')->get();
        $laptops = Laptop::orderByDesc('id')->paginate(12);
        $totalLaptops = Laptop::count();
        $imputedCount = Laptop::where('is_imputed', true)->count();
        $historyCount = DecisionHistory::count();

        return view('spk.admin', compact(
            'criteria',
            'presets',
            'laptops',
            'totalLaptops',
            'imputedCount',
            'historyCount'
        ));
    }

    public function storeLaptop(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:64'],
            'type_name' => ['required', 'string', 'max:64'],
            'cpu_name' => ['required', 'string', 'max:160'],
            'gpu_name' => ['required', 'string', 'max:160'],
            'price_idr' => ['required', 'numeric', 'min:1000000'],
            'ram_gb' => ['required', 'integer', 'min:2', 'max:128'],
            'storage_gb' => ['required', 'integer', 'min:32', 'max:8192'],
            'has_ssd' => ['nullable', 'boolean'],
            'cpu_ranking' => ['required', 'integer', 'min:1', 'max:1500'],
            'gpu_benchmark' => ['required', 'numeric', 'min:0.5', 'max:500'],
            'battery_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'weight_kg' => ['required', 'numeric', 'min:0.5', 'max:6.0'],
            'user_rating' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'refresh_rate_hz' => ['required', 'integer', 'min:60', 'max:480'],
        ]);

        $nextId = ((int) Laptop::max('id')) + 1;
        $hasSsd = $request->boolean('has_ssd', true);
        $storageGb = (int) $validated['storage_gb'];
        $storageEffective = $hasSsd ? ($storageGb * 1.0) : ($storageGb * 0.6);

        Laptop::create([
            'id' => $nextId,
            'name' => $validated['name'],
            'brand' => $validated['brand'],
            'type_name' => $validated['type_name'],
            'os' => 'Windows',
            'processor_brand' => str_contains(strtolower($validated['cpu_name']), 'ryzen') ? 'AMD' : 'Intel',
            'cpu_name' => $validated['cpu_name'],
            'gpu_name' => $validated['gpu_name'],
            'ram_type' => 'ddr4',
            'ram_type_score' => 4,
            'has_ssd' => $hasSsd,
            'expandable_memory' => true,
            'touchscreen' => false,
            'dedicated_vram_gb' => 4.0,
            'screen_inches' => 15.6,
            'screen_resolution' => '1080',
            'source_link' => 'https://www.kaggle.com/datasets/dhanushbommavaram/laptop-ranked-dataset',
            'price_inr' => (int) round(((float) $validated['price_idr']) / 190.0),
            'price_idr' => (int) round((float) $validated['price_idr']),
            'ram_gb' => (int) $validated['ram_gb'],
            'storage_gb' => $storageGb,
            'storage_effective_gb' => $storageEffective,
            'cpu_ranking' => (int) $validated['cpu_ranking'],
            'gpu_benchmark' => (float) $validated['gpu_benchmark'],
            'battery_hours' => (float) $validated['battery_hours'],
            'weight_kg' => (float) $validated['weight_kg'],
            'user_rating' => (float) $validated['user_rating'],
            'refresh_rate_hz' => (int) $validated['refresh_rate_hz'],
            'is_imputed' => false,
        ]);

        return redirect()->route('admin.index')->with('status', "Laptop baru #{$nextId} ({$validated['name']}) berhasil ditambahkan ke katalog.");
    }

    public function destroyLaptop(Laptop $laptop): RedirectResponse
    {
        $title = "#{$laptop->id} {$laptop->name}";
        $laptop->delete();

        return redirect()->route('admin.index')->with('status', "Laptop {$title} berhasil dihapus.");
    }
}

