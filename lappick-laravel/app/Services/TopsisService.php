<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\Laptop;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TopsisService
{
    /**
     * Default 9 criteria definitions matching `03_ARCHITECTURE_SPK_LAPTOP.md` and `topsis_fixture.json`.
     *
     * @return array<int, array<string, string>>
     */
    public static function defaultCriteria(): array
    {
        return [
            ['code' => 'C1', 'name_id' => 'Harga',              'name_en' => 'Price',             'type' => 'cost',    'unit' => 'Rp',   'col' => 'price_idr'],
            ['code' => 'C2', 'name_id' => 'Kapasitas RAM',      'name_en' => 'RAM Capacity',      'type' => 'benefit', 'unit' => 'GB',   'col' => 'ram_gb'],
            ['code' => 'C3', 'name_id' => 'Storage Efektif',    'name_en' => 'Effective Storage', 'type' => 'benefit', 'unit' => 'GB',   'col' => 'storage_effective_gb'],
            ['code' => 'C4', 'name_id' => 'Peringkat CPU',      'name_en' => 'CPU Ranking',       'type' => 'cost',    'unit' => 'rank', 'col' => 'cpu_ranking'],
            ['code' => 'C5', 'name_id' => 'Skor Benchmark GPU', 'name_en' => 'GPU Benchmark',     'type' => 'benefit', 'unit' => 'pts',  'col' => 'gpu_benchmark'],
            ['code' => 'C6', 'name_id' => 'Daya Tahan Baterai', 'name_en' => 'Battery Life',      'type' => 'benefit', 'unit' => 'jam',  'col' => 'battery_hours'],
            ['code' => 'C7', 'name_id' => 'Bobot Laptop',       'name_en' => 'Weight',            'type' => 'cost',    'unit' => 'kg',   'col' => 'weight_kg'],
            ['code' => 'C8', 'name_id' => 'Rating Pengguna',    'name_en' => 'User Rating',       'type' => 'benefit', 'unit' => '/5',   'col' => 'user_rating'],
            ['code' => 'C9', 'name_id' => 'Refresh Rate Layar', 'name_en' => 'Refresh Rate',      'type' => 'benefit', 'unit' => 'Hz',   'col' => 'refresh_rate_hz'],
        ];
    }

    /**
     * Rank laptop alternatives using TOPSIS:
     * 1. Decision Matrix X (m x n)
     * 2. Vector Normalization: r_ij = x_ij / sqrt(sum(x_ij^2))
     * 3. Weighted Normalized Matrix: y_ij = w_j * r_ij
     * 4. Ideal Solutions A+ and A-
     * 5. Euclidean Distances D_i^+ and D_i^-
     * 6. Closeness Coefficient C_i = D_i^- / (D_i^+ + D_i^-)
     * 7. Deterministic tie-breaker: topsis_score DESC, id ASC
     *
     * @param  iterable<int, array<string, mixed>|Laptop>  $laptops
     * @param  array<string|int, float|int>  $weights
     * @param  array<int, array<string, mixed>>|Collection<int, Criterion>|null  $criteria
     * @return array<string, mixed>
     */
    public function rank(iterable $laptops, array $weights, array|Collection|null $criteria = null): array
    {
        $items = [];
        foreach ($laptops as $item) {
            $items[] = $item instanceof Laptop ? $item->toArray() : (array) $item;
        }

        $m = count($items);
        if ($m < 2) {
            throw new InvalidArgumentException('Minimal 2 alternatif laptop diperlukan untuk menjalankan perhitungan TOPSIS.');
        }

        $critList = $this->normalizeCriteriaList($criteria);
        $n = count($critList);

        // Normalize weights in canonical order C1..C9
        $wVec = [];
        foreach ($critList as $idx => $c) {
            $code = $c['code'];
            if (array_key_exists($code, $weights)) {
                $wVec[$idx] = (float) $weights[$code];
            } elseif (array_key_exists($idx, $weights)) {
                $wVec[$idx] = (float) $weights[$idx];
            } else {
                $wVec[$idx] = (float) ($c['default_weight'] ?? (1.0 / $n));
            }
        }

        $wSum = array_sum($wVec);
        if ($wSum <= 0.0) {
            throw new InvalidArgumentException('Total bobot kriteria harus lebih besar dari 0.');
        }
        for ($j = 0; $j < $n; $j++) {
            $wVec[$j] = $wVec[$j] / $wSum;
        }

        // Step 1: Decision Matrix X (m x n) & Vector Denom
        $X = [];
        $sumSq = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $m; $i++) {
            $X[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $col = $critList[$j]['col'];
                $val = (float) ($items[$i][$col] ?? 0.0);
                $X[$i][$j] = $val;
                $sumSq[$j] += $val * $val;
            }
        }

        $denom = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $d = sqrt($sumSq[$j]);
            $denom[$j] = $d > 0.0 ? $d : 1e-12;
        }

        // Step 2 & 3: Normalized Matrix R and Weighted Matrix Y
        $R = [];
        $Y = [];
        for ($i = 0; $i < $m; $i++) {
            $R[$i] = [];
            $Y[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $rij = $X[$i][$j] / $denom[$j];
                $yij = $rij * $wVec[$j];
                $R[$i][$j] = $rij;
                $Y[$i][$j] = $yij;
            }
        }

        // Step 4: Ideal Positive (A+) & Ideal Negative (A-)
        $idealPos = array_fill(0, $n, 0.0);
        $idealNeg = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $colVals = array_column($Y, $j);
            $maxVal = (float) max($colVals);
            $minVal = (float) min($colVals);
            if ($critList[$j]['type'] === 'benefit') {
                $idealPos[$j] = $maxVal;
                $idealNeg[$j] = $minVal;
            } else {
                $idealPos[$j] = $minVal;
                $idealNeg[$j] = $maxVal;
            }
        }

        // Step 5 & 6: Euclidean Distances D+, D- and Closeness Score Ci
        $ranked = [];
        for ($i = 0; $i < $m; $i++) {
            $sqPlus = 0.0;
            $sqMinus = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $diffP = $Y[$i][$j] - $idealPos[$j];
                $diffN = $Y[$i][$j] - $idealNeg[$j];
                $sqPlus += $diffP * $diffP;
                $sqMinus += $diffN * $diffN;
            }
            $dPlus = sqrt($sqPlus);
            $dMinus = sqrt($sqMinus);
            $distSum = $dPlus + $dMinus;
            $score = $distSum > 0.0 ? ($dMinus / $distSum) : 0.0;

            $row = $items[$i];
            $row['d_plus'] = $dPlus;
            $row['d_minus'] = $dMinus;
            $row['topsis_score'] = $score;
            $row['match_pct'] = round($score * 100.0, 2);
            $row['x_vec'] = $X[$i];
            $row['r_vec'] = $R[$i];
            $row['y_vec'] = $Y[$i];
            $ranked[] = $row;
        }

        // Step 7: Deterministic tie-breaker: topsis_score DESC, id ASC
        usort($ranked, function (array $a, array $b): int {
            if (abs($a['topsis_score'] - $b['topsis_score']) > 1e-15) {
                return $b['topsis_score'] <=> $a['topsis_score'];
            }
            return ((int) $a['id']) <=> ((int) $b['id']);
        });

        foreach ($ranked as $idx => &$r) {
            $r['rank'] = $idx + 1;
        }
        unset($r);

        return [
            'criteria' => $critList,
            'weights_normalized' => $wVec,
            'denom' => $denom,
            'ideal_pos' => $idealPos,
            'ideal_neg' => $idealNeg,
            'ranking' => $ranked,
            'total_candidates' => $m,
        ];
    }

    /**
     * Normalize criteria input from Eloquent Collection or array into canonical array format.
     *
     * @param  array<int, array<string, mixed>>|Collection<int, Criterion>|null  $criteria
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCriteriaList(array|Collection|null $criteria): array
    {
        if ($criteria === null) {
            return self::defaultCriteria();
        }

        $items = $criteria instanceof Collection ? $criteria->all() : $criteria;
        if (empty($items)) {
            return self::defaultCriteria();
        }

        $out = [];
        foreach ($items as $c) {
            if ($c instanceof Criterion) {
                $out[] = [
                    'code' => $c->code,
                    'name_id' => $c->name_id,
                    'name_en' => $c->name_en,
                    'type' => strtolower($c->type),
                    'unit' => $c->unit,
                    'col' => $c->column_name,
                    'default_weight' => $c->default_weight,
                ];
            } else {
                $out[] = [
                    'code' => (string) $c['code'],
                    'name_id' => (string) ($c['name_id'] ?? $c['code']),
                    'name_en' => (string) ($c['name_en'] ?? $c['code']),
                    'type' => strtolower((string) $c['type']),
                    'unit' => (string) ($c['unit'] ?? ''),
                    'col' => (string) ($c['col'] ?? $c['column_name']),
                    'default_weight' => (float) ($c['default_weight'] ?? 0.111111),
                ];
            }
        }

        return $out;
    }
}

