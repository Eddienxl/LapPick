<?php

declare(strict_types=1);

require __DIR__ . '/../lappick-laravel/vendor/autoload.php';

use App\Services\AhpService;
use App\Services\TopsisService;

$payloadRaw = stream_get_contents(STDIN);
$payload = json_decode($payloadRaw, true, 512, JSON_THROW_ON_ERROR);

$ahp = new AhpService();
$topsis = new TopsisService();

$t0 = microtime(true);
$ahpRes = $ahp->compute($payload['pairwise_matrix']);
$topsisRes = $topsis->rank($payload['laptops'], $ahpRes['weights']);
$elapsedMs = (microtime(true) - $t0) * 1000.0;

$rankingCompact = array_map(fn ($r) => [
    'rank' => $r['rank'],
    'id' => $r['id'],
    'd_plus' => $r['d_plus'],
    'd_minus' => $r['d_minus'],
    'topsis_score' => $r['topsis_score'],
], $topsisRes['ranking']);

echo json_encode([
    'elapsed_ms' => $elapsedMs,
    'cr' => $ahpRes['cr'],
    'lambda_max' => $ahpRes['lambda_max'],
    'ci' => $ahpRes['ci'],
    'weights' => $ahpRes['weights_vec'],
    'denom' => $topsisRes['denom'],
    'ideal_pos' => $topsisRes['ideal_pos'],
    'ideal_neg' => $topsisRes['ideal_neg'],
    'ranking' => $rankingCompact,
], JSON_THROW_ON_ERROR);

