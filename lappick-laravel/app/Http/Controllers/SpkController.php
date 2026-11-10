<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\DecisionHistory;
use App\Models\Laptop;
use App\Models\PresetProfile;
use App\Services\AhpService;
use App\Services\TopsisService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpkController extends Controller
{
    public function __construct(
        private readonly AhpService $ahpService,
        private readonly TopsisService $topsisService,
    ) {}

    public function home(): View
    {
        $presets = PresetProfile::query()->orderBy('id')->get();
        $criteria = $this->getOrderedCriteria();
        $totalLaptops = Laptop::query()->count();
        $brandsCount = Laptop::query()->distinct('brand')->count('brand');
        $highRefreshCount = Laptop::query()->where('refresh_rate_hz', '>=', 120)->count();

        // Preset winners showcase in deterministic order: Student #1 (#326), Gamer/Creator #1 (#240), Creator RTX 3080 Ti (#155)
        $featuredOrder = [326, 240, 155];
        /** @var EloquentCollection<int, Laptop> $rawFeatured */
        $rawFeatured = Laptop::query()->whereIn('id', $featuredOrder)->get();
        $featuredLaptops = $rawFeatured
            ->sortBy(fn (Laptop $l) => array_search($l->id, $featuredOrder, true))
            ->values();

        return view('spk.home', compact(
            'presets',
            'criteria',
            'totalLaptops',
            'brandsCount',
            'highRefreshCount',
            'featuredLaptops'
        ));
    }

    public function catalog(Request $request): View
    {
        $query = Laptop::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('cpu_name', 'like', "%{$q}%")
                    ->orWhere('gpu_name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', (string) $request->input('brand'));
        }

        if ($request->filled('type_name')) {
            $query->where('type_name', (string) $request->input('type_name'));
        }

        if ($request->filled('max_price_m')) {
            $maxIdr = (float) $request->input('max_price_m') * 1_000_000;
            if ($maxIdr > 0) {
                $query->where('price_idr', '<=', (int) round($maxIdr));
            }
        }

        if ($request->filled('min_ram')) {
            $query->where('ram_gb', '>=', (int) $request->input('min_ram'));
        }

        if ($request->filled('min_rr')) {
            $query->where('refresh_rate_hz', '>=', (int) $request->input('min_rr'));
        }

        if ($request->boolean('only_ssd')) {
            $query->where('has_ssd', true);
        }

        $sort = (string) $request->input('sort', 'id_asc');
        match ($sort) {
            'price_asc' => $query->orderBy('price_idr', 'asc')->orderBy('id', 'asc'),
            'price_desc' => $query->orderBy('price_idr', 'desc')->orderBy('id', 'asc'),
            'gpu_desc' => $query->orderBy('gpu_benchmark', 'desc')->orderBy('id', 'asc'),
            'cpu_asc' => $query->orderBy('cpu_ranking', 'asc')->orderBy('id', 'asc'),
            'rating_desc' => $query->orderBy('user_rating', 'desc')->orderBy('id', 'asc'),
            default => $query->orderBy('id', 'asc'),
        };

        $laptops = $query->paginate(18)->withQueryString();
        $brands = Laptop::query()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $types = Laptop::query()->select('type_name')->distinct()->orderBy('type_name')->pluck('type_name');
        $compareIds = (array) $request->session()->get('compare_ids', [326, 240, 499]);

        return view('spk.catalog', compact('laptops', 'brands', 'types', 'compareIds'));
    }

    public function showLaptop(Request $request, Laptop $laptop): View
    {
        $criteria = $this->getOrderedCriteria();
        $compareIds = (array) $request->session()->get('compare_ids', [326, 240, 499]);

        $stats = [];
        foreach ($criteria as $c) {
            $col = $c->column_name;
            $min = (float) Laptop::query()->min($col);
            $max = (float) Laptop::query()->max($col);
            $val = (float) $laptop->{$col};
            if ($max == $min) {
                $pct = 100.0;
            } elseif ($c->type === 'benefit') {
                $pct = 100.0 * ($val - $min) / ($max - $min);
            } else {
                $pct = 100.0 * ($max - $val) / ($max - $min);
            }
            $stats[] = [
                'code' => $c->code,
                'name' => $c->localizedName(),
                'type' => $c->type,
                'unit' => $c->unit,
                'raw' => $val,
                'norm_pct' => round($pct, 1),
            ];
        }

        return view('spk.laptop_detail', compact('laptop', 'criteria', 'stats', 'compareIds'));
    }

    public function wizard(Request $request): View
    {
        /** @var EloquentCollection<string, PresetProfile> $presets */
        $presets = PresetProfile::query()->orderBy('id')->get()->keyBy('slug');
        $criteria = $this->getOrderedCriteria();
        $brands = Laptop::query()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $types = Laptop::query()->select('type_name')->distinct()->orderBy('type_name')->pluck('type_name');

        $mode = (string) $request->input('mode', 'preset');
        $selectedSlug = (string) $request->input('preset_slug', 'student');
        if (! $presets->has($selectedSlug)) {
            $selectedSlug = 'student';
        }
        /** @var PresetProfile $activePreset */
        $activePreset = $presets->get($selectedSlug);

        $defaultSaatyScores = [
            'student' => [8, 5, 4, 4, 2, 6, 7, 4, 1],
            'gamer'   => [3, 6, 5, 7, 8, 2, 1, 4, 7],
            'creator' => [3, 7, 6, 8, 7, 4, 3, 5, 5],
            'office'  => [6, 5, 4, 6, 2, 8, 7, 6, 2],
        ];
        $saatyScores = (array) $request->input('saaty', $defaultSaatyScores[$selectedSlug] ?? [5, 5, 5, 5, 5, 5, 5, 5, 5]);
        $pairwiseMatrix = $this->resolvePairwiseMatrix($request, $mode, $activePreset, $saatyScores);

        $ahpResult = $this->ahpService->compute($pairwiseMatrix);
        $forceInconsistent = $request->boolean('force_inconsistent');

        $maxBudgetM = (float) $request->input('max_budget_m', 60);
        $filterBrand = (string) $request->input('brand', '');
        $filterType = (string) $request->input('type_name', '');
        $minRam = (int) $request->input('min_ram', 4);
        $minRr = (int) $request->input('min_rr', 60);
        $onlySsd = $request->boolean('only_ssd');

        $candidates = $this->queryFilteredCandidates($maxBudgetM, $filterBrand, $filterType, $minRam, $minRr, $onlySsd);
        $topsisResult = null;
        $guardrailBlocked = false;
        $minCandidatesError = false;
        $radarData = [];

        if ($candidates->count() < 2) {
            $minCandidatesError = true;
        } elseif (! $ahpResult['is_consistent'] && ! $forceInconsistent) {
            $guardrailBlocked = true;
        } else {
            $topsisResult = $this->topsisService->rank($candidates, $ahpResult['weights'], $criteria);
            $radarData = $this->buildTop4RadarData($topsisResult['ranking'], $criteria);

            if ($request->isMethod('post') || $request->boolean('save_history')) {
                $this->recordDecisionHistory(
                    $mode,
                    $selectedSlug,
                    $pairwiseMatrix,
                    $ahpResult,
                    $topsisResult,
                    [
                        'max_budget_m' => $maxBudgetM,
                        'brand' => $filterBrand,
                        'type_name' => $filterType,
                        'min_ram' => $minRam,
                        'min_rr' => $minRr,
                        'only_ssd' => $onlySsd,
                    ],
                    $candidates->count()
                );
            }
        }

        $compareIds = (array) $request->session()->get('compare_ids', [326, 240, 499]);

        return view('spk.wizard', compact(
            'presets',
            'criteria',
            'brands',
            'types',
            'mode',
            'selectedSlug',
            'activePreset',
            'saatyScores',
            'pairwiseMatrix',
            'ahpResult',
            'forceInconsistent',
            'maxBudgetM',
            'filterBrand',
            'filterType',
            'minRam',
            'minRr',
            'onlySsd',
            'candidates',
            'topsisResult',
            'guardrailBlocked',
            'minCandidatesError',
            'radarData',
            'compareIds'
        ));
    }

    public function compare(Request $request): View
    {
        if ($request->filled('ids')) {
            $rawIds = explode(',', (string) $request->query('ids'));
            $ids = \array_slice(array_values(array_unique(array_filter(array_map('intval', $rawIds)))), 0, 4);
            $request->session()->put('compare_ids', $ids);
        } else {
            $ids = (array) $request->session()->get('compare_ids', [326, 240, 499]);
        }

        /** @var EloquentCollection<int, Laptop> $rawCompared */
        $rawCompared = Laptop::query()->whereIn('id', $ids)->get();
        $laptops = $rawCompared->sortBy(fn (Laptop $l) => array_search($l->id, $ids, true))->values();
        $criteria = $this->getOrderedCriteria();
        $allLaptopsList = Laptop::query()->select('id', 'brand', 'name', 'price_idr')->orderBy('brand')->orderBy('price_idr')->get();

        $winners = [];
        if ($laptops->count() >= 2) {
            foreach ($criteria as $c) {
                $col = $c->column_name;
                $vals = $laptops->pluck($col)->map(fn ($v) => (float) $v)->all();
                $winners[$c->code] = $c->type === 'benefit' ? max($vals) : min($vals);
            }
        }

        return view('spk.compare', compact('laptops', 'criteria', 'winners', 'ids', 'allLaptopsList'));
    }

    public function toggleCompare(Request $request): RedirectResponse
    {
        $id = (int) $request->input('laptop_id');
        $ids = (array) $request->session()->get('compare_ids', [326, 240, 499]);

        if (\in_array($id, $ids, true)) {
            $ids = array_values(array_diff($ids, [$id]));
        } else {
            if (\count($ids) >= 4) {
                array_shift($ids);
            }
            $ids[] = $id;
        }

        $request->session()->put('compare_ids', $ids);

        return back()->with('status', 'Daftar perbandingan diperbarui (' . \count($ids) . ' laptop).');
    }

    public function history(): View
    {
        if (DecisionHistory::query()->count() === 0) {
            $student = PresetProfile::query()->where('slug', 'student')->first();
            if ($student instanceof PresetProfile) {
                /** @var EloquentCollection<int, Laptop> $cands */
                $cands = Laptop::query()->orderBy('id')->get();
                $res = $this->topsisService->rank($cands, $student->weights);
                $top10 = \array_slice($res['ranking'], 0, 10);
                DecisionHistory::query()->create([
                    'user_id' => Auth::id(),
                    'profile_slug' => 'student',
                    'mode' => 'preset',
                    'pairwise_matrix' => $student->pairwise_matrix,
                    'weights' => $student->weights,
                    'lambda_max' => $student->lambda_max,
                    'ci' => $student->ci,
                    'cr' => $student->cr,
                    'is_consistent' => true,
                    'filters' => ['max_budget_m' => 60, 'brand' => '', 'type_name' => ''],
                    'top_results' => $top10,
                    'total_candidates' => $cands->count(),
                ]);
            }
        }

        $histories = DecisionHistory::with('user')->latest()->paginate(10);
        $criteria = $this->getOrderedCriteria();

        return view('spk.history', compact('histories', 'criteria'));
    }

    public function exportHistoryCsv(DecisionHistory $history): StreamedResponse
    {
        $filename = "lappick_decision_{$history->id}_{$history->profile_slug}.csv";

        return response()->streamDownload(function () use ($history) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, [
                'Rank', 'ID', 'Brand', 'Laptop Name', 'Category',
                'Price (IDR)', 'RAM (GB)', 'Effective Storage (GB)',
                'CPU Ranking', 'GPU Benchmark', 'Battery (h)',
                'Weight (kg)', 'User Rating', 'Refresh Rate (Hz)',
                'D+', 'D-', 'TOPSIS Score (Ci)', 'Match (%)',
            ]);
            foreach ($history->top_results as $r) {
                fputcsv($out, [
                    $r['rank'] ?? '',
                    $r['id'] ?? '',
                    $r['brand'] ?? '',
                    $r['name'] ?? '',
                    $r['type_name'] ?? '',
                    $r['price_idr'] ?? '',
                    $r['ram_gb'] ?? '',
                    $r['storage_effective_gb'] ?? '',
                    $r['cpu_ranking'] ?? '',
                    $r['gpu_benchmark'] ?? '',
                    $r['battery_hours'] ?? '',
                    $r['weight_kg'] ?? '',
                    $r['user_rating'] ?? '',
                    $r['refresh_rate_hz'] ?? '',
                    $r['d_plus'] ?? '',
                    $r['d_minus'] ?? '',
                    $r['topsis_score'] ?? '',
                    $r['match_pct'] ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Retrieve the 9 decision criteria ordered by canonical sort_order.
     *
     * @return EloquentCollection<int, Criterion>
     */
    private function getOrderedCriteria(): EloquentCollection
    {
        /** @var EloquentCollection<int, Criterion> $criteria */
        $criteria = Criterion::query()->orderBy('sort_order')->get();

        return $criteria;
    }

    /**
     * Resolve the 9x9 pairwise matrix for preset, saaty_scores, or custom_matrix mode.
     *
     * @param  array<int|string, mixed>  $saatyScores
     * @return array<int, array<int, float>>
     */
    private function resolvePairwiseMatrix(Request $request, string $mode, PresetProfile $activePreset, array $saatyScores): array
    {
        if ($mode === 'saaty_scores') {
            $scoresVec = [];
            for ($i = 0; $i < 9; $i++) {
                $scoresVec[$i] = (float) ($saatyScores[$i] ?? 5);
            }

            return $this->ahpService->buildSaatyMatrixFromScores($scoresVec);
        }

        $rawMat = $request->input('matrix');
        if ($mode === 'custom_matrix' && \is_array($rawMat)) {
            $pairwiseMatrix = [];
            for ($i = 0; $i < 9; $i++) {
                $pairwiseMatrix[$i] = [];
                for ($j = 0; $j < 9; $j++) {
                    if ($i === $j) {
                        $pairwiseMatrix[$i][$j] = 1.0;
                    } elseif ($i < $j) {
                        $pairwiseMatrix[$i][$j] = max(0.111111, min(9.0, (float) ($rawMat[$i][$j] ?? 1.0)));
                    } else {
                        $upper = max(0.111111, min(9.0, (float) ($rawMat[$j][$i] ?? 1.0)));
                        $pairwiseMatrix[$i][$j] = 1.0 / $upper;
                    }
                }
            }

            return $pairwiseMatrix;
        }

        return $activePreset->pairwise_matrix;
    }

    /**
     * Apply Pre-TOPSIS hard filters to query qualifying Laptop alternatives.
     *
     * @return EloquentCollection<int, Laptop>
     */
    private function queryFilteredCandidates(
        float $maxBudgetM,
        string $filterBrand,
        string $filterType,
        int $minRam,
        int $minRr,
        bool $onlySsd,
    ): EloquentCollection {
        $candQuery = Laptop::query()->where('price_idr', '<=', (int) round($maxBudgetM * 1_000_000));
        if ($filterBrand !== '') {
            $candQuery->where('brand', $filterBrand);
        }
        if ($filterType !== '') {
            $candQuery->where('type_name', $filterType);
        }
        if ($minRam > 4) {
            $candQuery->where('ram_gb', '>=', $minRam);
        }
        if ($minRr > 60) {
            $candQuery->where('refresh_rate_hz', '>=', $minRr);
        }
        if ($onlySsd) {
            $candQuery->where('has_ssd', true);
        }

        /** @var EloquentCollection<int, Laptop> $candidates */
        $candidates = $candQuery->orderBy('id')->get();

        return $candidates;
    }

    /**
     * Build 0-100 normalized radar dataset for the Top-4 ranked laptops.
     *
     * @param  array<int, array<string, mixed>>  $ranking
     * @param  EloquentCollection<int, Criterion>  $criteria
     * @return array<int, array<string, mixed>>
     */
    private function buildTop4RadarData(array $ranking, EloquentCollection $criteria): array
    {
        $top4 = \array_slice($ranking, 0, 4);
        $colBounds = [];
        foreach ($criteria as $c) {
            $col = $c->column_name;
            $colBounds[$col] = [
                'min' => (float) Laptop::query()->min($col),
                'max' => (float) Laptop::query()->max($col),
                'type' => $c->type,
            ];
        }

        $radarData = [];
        foreach ($top4 as $row) {
            $normVals = [];
            foreach ($criteria as $c) {
                $col = $c->column_name;
                $b = $colBounds[$col];
                $v = (float) $row[$col];
                if ($b['max'] == $b['min']) {
                    $normVals[] = 100.0;
                } elseif ($b['type'] === 'benefit') {
                    $normVals[] = round(100.0 * ($v - $b['min']) / ($b['max'] - $b['min']), 1);
                } else {
                    $normVals[] = round(100.0 * ($b['max'] - $v) / ($b['max'] - $b['min']), 1);
                }
            }
            $radarData[] = [
                'label' => "#{$row['rank']} {$row['brand']} (ID {$row['id']})",
                'data' => $normVals,
            ];
        }

        return $radarData;
    }

    /**
     * Persist a DecisionHistory audit log entry with Top-10 TOPSIS results.
     *
     * @param  array<int, array<int, float>>  $pairwiseMatrix
     * @param  array<string, mixed>  $ahpResult
     * @param  array<string, mixed>  $topsisResult
     * @param  array<string, mixed>  $filters
     */
    private function recordDecisionHistory(
        string $mode,
        string $selectedSlug,
        array $pairwiseMatrix,
        array $ahpResult,
        array $topsisResult,
        array $filters,
        int $totalCandidates,
    ): void {
        $top10Clean = array_map(fn (array $r) => [
            'rank' => $r['rank'],
            'id' => $r['id'],
            'brand' => $r['brand'],
            'name' => $r['name'],
            'type_name' => $r['type_name'],
            'price_idr' => $r['price_idr'],
            'ram_gb' => $r['ram_gb'],
            'storage_effective_gb' => $r['storage_effective_gb'],
            'cpu_ranking' => $r['cpu_ranking'],
            'gpu_benchmark' => $r['gpu_benchmark'],
            'battery_hours' => $r['battery_hours'],
            'weight_kg' => $r['weight_kg'],
            'user_rating' => $r['user_rating'],
            'refresh_rate_hz' => $r['refresh_rate_hz'],
            'd_plus' => round($r['d_plus'], 6),
            'd_minus' => round($r['d_minus'], 6),
            'topsis_score' => round($r['topsis_score'], 6),
            'match_pct' => $r['match_pct'],
        ], \array_slice($topsisResult['ranking'], 0, 10));

        DecisionHistory::query()->create([
            'user_id' => Auth::id(),
            'profile_slug' => $mode === 'preset' ? $selectedSlug : 'custom',
            'mode' => $mode,
            'pairwise_matrix' => $pairwiseMatrix,
            'weights' => $ahpResult['weights'],
            'lambda_max' => $ahpResult['lambda_max'],
            'ci' => $ahpResult['ci'],
            'cr' => $ahpResult['cr'],
            'is_consistent' => $ahpResult['is_consistent'],
            'filters' => $filters,
            'top_results' => $top10Clean,
            'total_candidates' => $totalCandidates,
        ]);
    }
}
