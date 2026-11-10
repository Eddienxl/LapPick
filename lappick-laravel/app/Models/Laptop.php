<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $brand
 * @property string $type_name
 * @property string $os
 * @property string $processor_brand
 * @property string $cpu_name
 * @property string $gpu_name
 * @property string $ram_type
 * @property int $ram_type_score
 * @property bool $has_ssd
 * @property bool $expandable_memory
 * @property bool $touchscreen
 * @property float $dedicated_vram_gb
 * @property float $screen_inches
 * @property string $screen_resolution
 * @property string|null $source_link
 * @property int $price_inr
 * @property int $price_idr
 * @property int $ram_gb
 * @property int $storage_gb
 * @property float $storage_effective_gb
 * @property int $cpu_ranking
 * @property float $gpu_benchmark
 * @property float $battery_hours
 * @property float $weight_kg
 * @property float $user_rating
 * @property int $refresh_rate_hz
 * @property bool $is_imputed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Laptop extends Model
{
    protected $table = 'laptops';

    /**
     * Preserve exact CSV primary key IDs (1..949) for 1e-6 tie-breaker contract.
     */
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'brand',
        'type_name',
        'os',
        'processor_brand',
        'cpu_name',
        'gpu_name',
        'ram_type',
        'ram_type_score',
        'has_ssd',
        'expandable_memory',
        'touchscreen',
        'dedicated_vram_gb',
        'screen_inches',
        'screen_resolution',
        'source_link',
        'price_inr',
        'price_idr',
        'ram_gb',
        'storage_gb',
        'storage_effective_gb',
        'cpu_ranking',
        'gpu_benchmark',
        'battery_hours',
        'weight_kg',
        'user_rating',
        'refresh_rate_hz',
        'is_imputed',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ram_type_score' => 'integer',
            'has_ssd' => 'boolean',
            'expandable_memory' => 'boolean',
            'touchscreen' => 'boolean',
            'dedicated_vram_gb' => 'float',
            'screen_inches' => 'float',
            'price_inr' => 'integer',
            'price_idr' => 'integer',
            'ram_gb' => 'integer',
            'storage_gb' => 'integer',
            'storage_effective_gb' => 'float',
            'cpu_ranking' => 'integer',
            'gpu_benchmark' => 'float',
            'battery_hours' => 'float',
            'weight_kg' => 'float',
            'user_rating' => 'float',
            'refresh_rate_hz' => 'integer',
            'is_imputed' => 'boolean',
        ];
    }

    public function formattedPriceIdr(): string
    {
        return 'Rp ' . number_format((float) $this->price_idr, 0, ',', '.');
    }
}
