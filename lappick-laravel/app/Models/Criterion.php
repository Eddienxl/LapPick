<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name_id
 * @property string $name_en
 * @property string $type
 * @property string $unit
 * @property string $column_name
 * @property float $default_weight
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Criterion extends Model
{
    protected $table = 'criteria';

    protected $fillable = [
        'code',
        'name_id',
        'name_en',
        'type',
        'unit',
        'column_name',
        'default_weight',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_weight' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function localizedName(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        return $loc === 'en' ? $this->name_en : $this->name_id;
    }
}
