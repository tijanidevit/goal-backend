<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'financial_profile_id',
        'name',
        'amount_owed',
        'monthly_repayment',
    ];

    protected function casts(): array
    {
        return [
            'amount_owed' => 'decimal:2',
            'monthly_repayment' => 'decimal:2',
        ];
    }

    public function financialProfile(): BelongsTo
    {
        return $this->belongsTo(FinancialProfile::class);
    }
}
