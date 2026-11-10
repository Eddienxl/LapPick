<?php

namespace Database\Seeders;

use App\Models\Laptop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LaptopSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('../data/clean/laptops_clean.csv');
        if (! file_exists($csvPath)) {
            throw new RuntimeException("Cleaned laptop CSV not found at: {$csvPath}");
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Failed to open CSV: {$csvPath}");
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            throw new RuntimeException("Empty CSV header in: {$csvPath}");
        }
        $headers = array_map(fn ($h) => trim((string) $h), $headers);

        $now = now()->toDateTimeString();
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }
            $r = array_combine($headers, $data);
            $rows[] = [
                'id' => (int) $r['id'],
                'name' => trim((string) $r['name']),
                'brand' => trim((string) $r['brand']),
                'type_name' => trim((string) $r['type_name']),
                'os' => trim((string) $r['os']),
                'processor_brand' => trim((string) $r['processor_brand']),
                'cpu_name' => trim((string) $r['cpu_name']),
                'gpu_name' => trim((string) $r['gpu_name']),
                'ram_type' => trim((string) $r['ram_type']),
                'ram_type_score' => (int) $r['ram_type_score'],
                'has_ssd' => ((int) $r['has_ssd']) === 1,
                'expandable_memory' => ((int) $r['expandable_memory']) === 1,
                'touchscreen' => ((int) $r['touchscreen']) === 1,
                'dedicated_vram_gb' => (float) $r['dedicated_vram_gb'],
                'screen_inches' => (float) $r['screen_inches'],
                'screen_resolution' => trim((string) $r['screen_resolution']),
                'source_link' => trim((string) $r['source_link']),
                'price_inr' => (int) round((float) $r['price_inr']),
                'price_idr' => (int) round((float) $r['price_idr']),
                'ram_gb' => (int) $r['ram_gb'],
                'storage_gb' => (int) $r['storage_gb'],
                'storage_effective_gb' => (float) $r['storage_effective_gb'],
                'cpu_ranking' => (int) $r['cpu_ranking'],
                'gpu_benchmark' => (float) $r['gpu_benchmark'],
                'battery_hours' => (float) $r['battery_hours'],
                'weight_kg' => (float) $r['weight_kg'],
                'user_rating' => (float) $r['user_rating'],
                'refresh_rate_hz' => (int) $r['refresh_rate_hz'],
                'is_imputed' => ((int) $r['is_imputed']) === 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        fclose($handle);

        DB::transaction(function () use ($rows) {
            // Chunk size 100 ensures SQLite bind variable limit (999/32766) is never exceeded
            foreach (array_chunk($rows, 100) as $chunk) {
                Laptop::upsert(
                    $chunk,
                    ['id'],
                    array_keys($chunk[0])
                );
            }
        });
    }
}

