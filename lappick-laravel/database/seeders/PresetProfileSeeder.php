<?php

namespace Database\Seeders;

use App\Models\PresetProfile;
use Illuminate\Database\Seeder;
use RuntimeException;

class PresetProfileSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('../data/clean/preset_profiles.json');
        if (! file_exists($jsonPath)) {
            throw new RuntimeException("Preset profile JSON not found at: {$jsonPath}");
        }

        $profiles = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($profiles)) {
            throw new RuntimeException("Invalid JSON in: {$jsonPath}");
        }

        foreach ($profiles as $prof) {
            PresetProfile::updateOrCreate(
                ['slug' => $prof['slug']],
                [
                    'name_id' => $prof['name_id'],
                    'name_en' => $prof['name_en'],
                    'description_id' => $prof['description_id'],
                    'description_en' => $prof['description_en'],
                    'pairwise_matrix' => $prof['pairwise_matrix'],
                    'weights' => $prof['weights'],
                    'lambda_max' => (float) $prof['lambda_max'],
                    'ci' => (float) $prof['ci'],
                    'ri' => (float) ($prof['ri'] ?? 1.45),
                    'cr' => (float) $prof['cr'],
                    'is_consistent' => (bool) ($prof['is_consistent'] ?? true),
                ]
            );
        }
    }
}

