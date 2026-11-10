<?php

namespace App\Services;

use InvalidArgumentException;

class AhpService
{
    /**
     * Saaty Random Index (RI) table for n = 1..10.
     *
     * @var array<int, float>
     */
    public const RI_TABLE = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];

    /**
     * Default 9 criteria codes in canonical order.
     *
     * @var array<int, string>
     */
    public const CRITERIA_CODES = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9'];

    /**
     * Compute AHP priority weights, lambda_max, CI, RI, CR, and top inconsistent pairs.
     * Matches Python `compute_ahp` in `notebooks/02_mcdm_prototype_validation.ipynb` within 1e-6.
     *
     * @param  array<int, array<int, float|int>>  $pairwiseMatrix
     * @param  array<int, string>  $codes
     * @return array<string, mixed>
     */
    public function compute(array $pairwiseMatrix, array $codes = self::CRITERIA_CODES): array
    {
        $n = count($pairwiseMatrix);
        if ($n < 1) {
            throw new InvalidArgumentException('Matriks AHP tidak boleh kosong.');
        }

        // Convert to 0-indexed float matrix and validate
        $A = [];
        for ($i = 0; $i < $n; $i++) {
            $row = array_values($pairwiseMatrix[$i] ?? []);
            if (count($row) !== $n) {
                throw new InvalidArgumentException('Matriks AHP harus berukuran persegi (n x n).');
            }
            $A[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $val = (float) $row[$j];
                if ($val <= 0.0) {
                    throw new InvalidArgumentException('Seluruh elemen matriks AHP harus bernilai positif (> 0).');
                }
                $A[$i][$j] = $val;
            }
        }

        // 1. Column sums
        $colSums = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            $sum = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sum += $A[$i][$j];
            }
            $colSums[$j] = $sum > 0.0 ? $sum : 1e-12;
        }

        // 2. Column-normalized matrix & row averages (priority weights)
        $normMatrix = [];
        $weights = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            $normMatrix[$i] = [];
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rij = $A[$i][$j] / $colSums[$j];
                $normMatrix[$i][$j] = $rij;
                $rowSum += $rij;
            }
            $weights[$i] = $rowSum / $n;
        }

        // Normalize weights to sum to exactly 1.0
        $wTotal = array_sum($weights);
        if ($wTotal <= 0.0) {
            throw new InvalidArgumentException('Jumlah bobot AHP tidak valid.');
        }
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = $weights[$i] / $wTotal;
        }

        // 3. Compute Aw, lambda_vec, lambda_max, CI, RI, CR
        $Aw = array_fill(0, $n, 0.0);
        $lambdaVec = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            $sum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $A[$i][$j] * $weights[$j];
            }
            $Aw[$i] = $sum;
            $safeW = $weights[$i] > 0.0 ? $weights[$i] : 1e-12;
            $lambdaVec[$i] = $sum / $safeW;
        }

        $lambdaMax = array_sum($lambdaVec) / $n;
        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0.0;
        $ri = self::RI_TABLE[$n] ?? 1.49;
        $cr = $ri > 0.0 ? ($ci / $ri) : 0.0;

        $weightsAssociative = [];
        for ($i = 0; $i < $n; $i++) {
            $code = $codes[$i] ?? ('C' . ($i + 1));
            $weightsAssociative[$code] = $weights[$i];
        }

        $inconsistentPairs = $this->findMostInconsistentPairs($A, $weights, $codes, 3);

        return [
            'n' => $n,
            'weights_vec' => $weights,
            'weights' => $weightsAssociative,
            'col_sums' => $colSums,
            'normalized_matrix' => $normMatrix,
            'Aw' => $Aw,
            'lambda_vec' => $lambdaVec,
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'ri' => $ri,
            'cr' => $cr,
            'is_consistent' => $cr <= 0.10,
            'inconsistent_pairs' => $inconsistentPairs,
        ];
    }

    /**
     * Build a guaranteed-consistent 9x9 Saaty pairwise matrix from importance scores (1..9).
     *
     * @param  array<int, float|int>  $importanceScores
     * @return array<int, array<int, float>>
     */
    public function buildSaatyMatrixFromScores(array $importanceScores): array
    {
        $scores = array_values($importanceScores);
        $n = count($scores);
        $M = [];
        for ($i = 0; $i < $n; $i++) {
            $M[$i] = array_fill(0, $n, 1.0);
        }

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $diff = (float) $scores[$i] - (float) $scores[$j];
                if ($diff >= 0.0) {
                    $scale = (float) min(9, max(1, (int) round($diff + 1.0)));
                    $M[$i][$j] = $scale;
                    $M[$j][$i] = 1.0 / $scale;
                } else {
                    $scale = (float) min(9, max(1, (int) round((-$diff) + 1.0)));
                    $M[$i][$j] = 1.0 / $scale;
                    $M[$j][$i] = $scale;
                }
            }
        }

        return $M;
    }

    /**
     * Identify upper-triangle pairs with largest deviation between a_ij and w_i / w_j.
     *
     * @param  array<int, array<int, float>>  $A
     * @param  array<int, float>  $weights
     * @param  array<int, string>  $codes
     * @return array<int, array<string, mixed>>
     */
    public function findMostInconsistentPairs(array $A, array $weights, array $codes = self::CRITERIA_CODES, int $topK = 3): array
    {
        $n = count($A);
        $eps = 1e-12;
        $pairs = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $impliedRatio = $weights[$i] / max($weights[$j], $eps);
                $actual = $A[$i][$j];
                $logDev = abs(log(max($actual, $eps)) - log(max($impliedRatio, $eps)));
                $cI = $codes[$i] ?? ('C' . ($i + 1));
                $cJ = $codes[$j] ?? ('C' . ($j + 1));
                $pairs[] = [
                    'pair' => "{$cI} vs {$cJ}",
                    'code_i' => $cI,
                    'code_j' => $cJ,
                    'a_ij' => $actual,
                    'implied_ratio' => $impliedRatio,
                    'log_deviation' => $logDev,
                ];
            }
        }

        usort($pairs, fn (array $a, array $b) => $b['log_deviation'] <=> $a['log_deviation']);

        return array_slice($pairs, 0, $topK);
    }
}

