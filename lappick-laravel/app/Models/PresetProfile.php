<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_id
 * @property string $name_en
 * @property string $description_id
 * @property string $description_en
 * @property array<int, array<int, float>> $pairwise_matrix
 * @property array<string, float> $weights
 * @property float $lambda_max
 * @property float $ci
 * @property float $ri
 * @property float $cr
 * @property bool $is_consistent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PresetProfile extends Model
{
    protected $table = 'preset_profiles';

    protected $fillable = [
        'slug',
        'name_id',
        'name_en',
        'description_id',
        'description_en',
        'pairwise_matrix',
        'weights',
        'lambda_max',
        'ci',
        'ri',
        'cr',
        'is_consistent',
    ];

    protected function casts(): array
    {
        return [
            'pairwise_matrix' => 'array',
            'weights' => 'array',
            'lambda_max' => 'float',
            'ci' => 'float',
            'ri' => 'float',
            'cr' => 'float',
            'is_consistent' => 'boolean',
        ];
    }

    public function localizedName(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        return $loc === 'en' ? $this->name_en : $this->name_id;
    }

    public function localizedDescription(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        return $loc === 'en' ? $this->description_en : $this->description_id;
    }
}
