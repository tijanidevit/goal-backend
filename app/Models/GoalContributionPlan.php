<?php

namespace App\Models;

use App\Enums\EnumContributionFrequency;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalContributionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'goal_id',
        'amount',
        'frequency',
        'next_due_date',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_due_date' => 'date',
            'active' => 'boolean',
            'frequency' => EnumContributionFrequency::class,
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
