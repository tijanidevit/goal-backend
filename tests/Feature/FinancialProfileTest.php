<?php

namespace Tests\Feature;

use App\Models\FinancialProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_monthly_savings_is_calculated_automatically()
    {
        $user = User::factory()->create();

        $profile = new FinancialProfile([
            'user_id' => $user->id,
            'total_monthly_income' => 10000,
            'total_monthly_expenses' => 5000,
            'total_monthly_debt_repayment' => 2000,
        ]);
        
        $profile->save();

        $this->assertEquals(3000, $profile->available_monthly_savings);
    }

    public function test_available_monthly_savings_cannot_be_negative()
    {
        $user = User::factory()->create();

        $profile = new FinancialProfile([
            'user_id' => $user->id,
            'total_monthly_income' => 3000,
            'total_monthly_expenses' => 5000,
            'total_monthly_debt_repayment' => 2000,
        ]);
        
        $profile->save();

        // 3000 - 5000 - 2000 = -4000, but we use max(0, ...)
        $this->assertEquals(0, $profile->available_monthly_savings);
    }
}
