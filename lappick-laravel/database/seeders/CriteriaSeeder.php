<?php

namespace Database\Seeders;

use App\Models\Criterion;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'code' => 'C1',
                'name_id' => 'Harga',
                'name_en' => 'Price',
                'type' => 'cost',
                'unit' => 'Rp',
                'column_name' => 'price_idr',
                'default_weight' => 0.111111,
                'sort_order' => 1,
            ],
            [
                'code' => 'C2',
                'name_id' => 'Kapasitas RAM',
                'name_en' => 'RAM Capacity',
                'type' => 'benefit',
                'unit' => 'GB',
                'column_name' => 'ram_gb',
                'default_weight' => 0.111111,
                'sort_order' => 2,
            ],
            [
                'code' => 'C3',
                'name_id' => 'Storage Efektif',
                'name_en' => 'Effective Storage',
                'type' => 'benefit',
                'unit' => 'GB',
                'column_name' => 'storage_effective_gb',
                'default_weight' => 0.111111,
                'sort_order' => 3,
            ],
            [
                'code' => 'C4',
                'name_id' => 'Peringkat CPU',
                'name_en' => 'CPU Ranking',
                'type' => 'cost',
                'unit' => 'rank',
                'column_name' => 'cpu_ranking',
                'default_weight' => 0.111111,
                'sort_order' => 4,
            ],
            [
                'code' => 'C5',
                'name_id' => 'Skor Benchmark GPU',
                'name_en' => 'GPU Benchmark',
                'type' => 'benefit',
                'unit' => 'pts',
                'column_name' => 'gpu_benchmark',
                'default_weight' => 0.111111,
                'sort_order' => 5,
            ],
            [
                'code' => 'C6',
                'name_id' => 'Daya Tahan Baterai',
                'name_en' => 'Battery Life',
                'type' => 'benefit',
                'unit' => 'jam',
                'column_name' => 'battery_hours',
                'default_weight' => 0.111111,
                'sort_order' => 6,
            ],
            [
                'code' => 'C7',
                'name_id' => 'Bobot Laptop',
                'name_en' => 'Weight',
                'type' => 'cost',
                'unit' => 'kg',
                'column_name' => 'weight_kg',
                'default_weight' => 0.111111,
                'sort_order' => 7,
            ],
            [
                'code' => 'C8',
                'name_id' => 'Rating Pengguna',
                'name_en' => 'User Rating',
                'type' => 'benefit',
                'unit' => '/5',
                'column_name' => 'user_rating',
                'default_weight' => 0.111111,
                'sort_order' => 8,
            ],
            [
                'code' => 'C9',
                'name_id' => 'Refresh Rate Layar',
                'name_en' => 'Refresh Rate',
                'type' => 'benefit',
                'unit' => 'Hz',
                'column_name' => 'refresh_rate_hz',
                'default_weight' => 0.111111,
                'sort_order' => 9,
            ],
        ];

        foreach ($criteria as $item) {
            Criterion::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}

