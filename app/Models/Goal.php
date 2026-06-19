<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'category',
        'name',
        'target_amount',
        'target_date',
        'status',
        'currency_code',
        'is_primary',
    ];

    protected $appends = ['current_savings'];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'target_date' => 'date',
            'is_primary' => 'boolean',
        ];
    }

    protected function currentSavings(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->contributions()->sum('amount'),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(GoalMilestone::class);
    }

    public function contributionPlans(): HasMany
    {
        return $this->hasMany(GoalContributionPlan::class);
    }
}
