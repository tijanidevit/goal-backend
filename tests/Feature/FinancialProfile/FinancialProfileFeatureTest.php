<?php

namespace Tests\Feature\FinancialProfile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_financial_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('financial-profile.store'), [
            'total_monthly_income' => 5000,
            'total_monthly_expenses' => 2000,
            'total_monthly_debt_repayment' => 500,
            'salary_day_of_month' => 25,
            'income_sources' => [
                ['source_name' => 'Salary', 'amount' => 5000],
            ],
            'expense_categories' => [
                ['category_name' => 'Rent', 'amount' => 1500],
                ['category_name' => 'Food', 'amount' => 500],
            ],
            'debts' => [
                ['name' => 'Car Loan', 'amount_owed' => 15000, 'monthly_repayment' => 500],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.available_monthly_savings', "2500.00")
                 ->assertJsonCount(1, 'data.income_sources')
                 ->assertJsonCount(2, 'data.expense_categories')
                 ->assertJsonCount(1, 'data.debts');

        $this->assertDatabaseHas('financial_profiles', [
            'user_id' => $user->id,
            'available_monthly_savings' => 2500,
            'salary_day_of_month' => 25,
        ]);

        $this->assertDatabaseHas('income_sources', [
            'source_name' => 'Salary',
        ]);
        
        $this->assertDatabaseHas('expense_categories', [
            'category_name' => 'Rent',
        ]);

        $this->assertDatabaseHas('debts', [
            'name' => 'Car Loan',
        ]);
    }
}
