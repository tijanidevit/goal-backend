<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialProfile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'total_monthly_income',
        'total_monthly_expenses',
        'total_monthly_debt_repayment',
        'available_monthly_savings',
        'salary_day_of_month',
    ];

    protected function casts(): array
    {
        return [
            'total_monthly_income' => 'decimal:2',
            'total_monthly_expenses' => 'decimal:2',
            'total_monthly_debt_repayment' => 'decimal:2',
            'available_monthly_savings' => 'decimal:2',
            'salary_day_of_month' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomeSources(): HasMany
    {
        return $this->hasMany(IncomeSource::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    protected static function booted()
    {
        static::saving(function ($profile) {
            $profile->available_monthly_savings = max(0, 
                ($profile->total_monthly_income ?? 0) 
                - ($profile->total_monthly_expenses ?? 0) 
                - ($profile->total_monthly_debt_repayment ?? 0)
            );
        });
    }
}
