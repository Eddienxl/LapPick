<?php

namespace Tests\Unit;

use App\Services\AhpService;
use App\Services\TopsisService;
use PHPUnit\Framework\TestCase;

class McdmContractTest extends TestCase
{
    private function dataCleanPath(string $filename): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'clean' . DIRECTORY_SEPARATOR . $filename;
    }

    public function test_ahp_service_matches_all_preset_profiles_within_1e6(): void
    {
        $path = $this->dataCleanPath('preset_profiles.json');
        $this->assertFileExists($path);

        $presets = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($presets);
        $this->assertCount(4, $presets);

        $ahp = new AhpService();

        foreach ($presets as $preset) {
            $res = $ahp->compute($preset['pairwise_matrix']);

            $this->assertTrue($res['is_consistent'], "Preset {$preset['slug']} should be consistent");
            $this->assertEqualsWithDelta((float) $preset['lambda_max'], $res['lambda_max'], 1e-6);
            $this->assertEqualsWithDelta((float) $preset['ci'], $res['ci'], 1e-6);
            $this->assertEqualsWithDelta((float) $preset['cr'], $res['cr'], 1e-6);

            foreach ($preset['weights'] as $code => $expectedWeight) {
                $this->assertEqualsWithDelta(
                    (float) $expectedWeight,
                    (float) $res['weights'][$code],
                    1e-6,
                    "Weight mismatch for {$preset['slug']} - {$code}"
                );
            }
        }
    }

    public function test_topsis_service_matches_python_fixture_within_1e6(): void
    {
        $path = $this->dataCleanPath('topsis_fixture.json');
        $this->assertFileExists($path);

        $fixture = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($fixture);

        $topsis = new TopsisService();
        $res = $topsis->rank(
            $fixture['sample_10_laptops_input'],
            $fixture['weights_used'],
            $fixture['criteria']
        );

        foreach ($fixture['expected_denom'] as $j => $expectedDenom) {
            $this->assertEqualsWithDelta((float) $expectedDenom, (float) $res['denom'][$j], 1e-6, "Denom mismatch at col {$j}");
        }

        foreach ($fixture['expected_ideal_pos'] as $j => $expectedPos) {
            $this->assertEqualsWithDelta((float) $expectedPos, (float) $res['ideal_pos'][$j], 1e-6, "Ideal+ mismatch at col {$j}");
        }

        foreach ($fixture['expected_ideal_neg'] as $j => $expectedNeg) {
            $this->assertEqualsWithDelta((float) $expectedNeg, (float) $res['ideal_neg'][$j], 1e-6, "Ideal- mismatch at col {$j}");
        }

        $this->assertCount(count($fixture['expected_ranking']), $res['ranking']);

        foreach ($fixture['expected_ranking'] as $idx => $expectedRow) {
            $actualRow = $res['ranking'][$idx];
            $this->assertSame((int) $expectedRow['rank'], (int) $actualRow['rank']);
            $this->assertSame((int) $expectedRow['id'], (int) $actualRow['id']);
            $this->assertEqualsWithDelta((float) $expectedRow['d_plus'], (float) $actualRow['d_plus'], 1e-6);
            $this->assertEqualsWithDelta((float) $expectedRow['d_minus'], (float) $actualRow['d_minus'], 1e-6);
            $this->assertEqualsWithDelta((float) $expectedRow['topsis_score'], (float) $actualRow['topsis_score'], 1e-6);
        }
    }
}

