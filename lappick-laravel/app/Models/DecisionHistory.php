<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $profile_slug
 * @property string $mode
 * @property array<int, array<int, float>> $pairwise_matrix
 * @property array<string, float> $weights
 * @property float $lambda_max
 * @property float $ci
 * @property float $cr
 * @property bool $is_consistent
 * @property array<string, mixed> $filters
 * @property array<int, array<string, mixed>> $top_results
 * @property int $total_candidates
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 */
class DecisionHistory extends Model
{
    protected $table = 'decision_histories';

    protected $fillable = [
        'user_id',
        'profile_slug',
        'mode',
        'pairwise_matrix',
        'weights',
        'lambda_max',
        'ci',
        'cr',
        'is_consistent',
        'filters',
        'top_results',
        'total_candidates',
    ];

    protected function casts(): array
    {
        return [
            'pairwise_matrix' => 'array',
            'weights' => 'array',
            'lambda_max' => 'float',
            'ci' => 'float',
            'cr' => 'float',
            'is_consistent' => 'boolean',
            'filters' => 'array',
            'top_results' => 'array',
            'total_candidates' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
