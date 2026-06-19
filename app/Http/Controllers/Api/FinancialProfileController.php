<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialProfile\StoreFinancialProfileRequest;
use App\Http\Requests\FinancialProfile\UpdateFinancialProfileRequest;
use App\Models\FinancialProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->financialProfile()->with(['incomeSources', 'expenseCategories', 'debts'])->first();
        
        if (!$profile) {
            return $this->notFoundResponse('Profile not found');
        }

        return $this->successResponse('Profile retrieved successfully', $profile);
    }

    public function store(StoreFinancialProfileRequest $request): JsonResponse
    {
        if ($request->user()->financialProfile) {
            return $this->errorResponse('Profile already exists', [], 409);
        }

        $validated = $request->validated();

        $profile = $request->user()->financialProfile()->create([
            'total_monthly_income' => $validated['total_monthly_income'] ?? 0,
            'total_monthly_expenses' => $validated['total_monthly_expenses'] ?? 0,
            'total_monthly_debt_repayment' => $validated['total_monthly_debt_repayment'] ?? 0,
            'available_monthly_savings' => ($validated['total_monthly_income'] ?? 0) - ($validated['total_monthly_expenses'] ?? 0) - ($validated['total_monthly_debt_repayment'] ?? 0),
            'salary_day_of_month' => $validated['salary_day_of_month'] ?? null,
        ]);

        if (isset($validated['income_sources'])) {
            $profile->incomeSources()->createMany($validated['income_sources']);
        }

        if (isset($validated['expense_categories'])) {
            $profile->expenseCategories()->createMany($validated['expense_categories']);
        }

        if (isset($validated['debts'])) {
            $profile->debts()->createMany($validated['debts']);
        }

        return $this->createdResponse('Profile created successfully', $profile->load(['incomeSources', 'expenseCategories', 'debts']));
    }

    public function update(UpdateFinancialProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->financialProfile;

        if (!$profile) {
            return $this->notFoundResponse('Profile not found');
        }

        $validated = $request->validated();

        $profile->update([
            'total_monthly_income' => $validated['total_monthly_income'] ?? $profile->total_monthly_income,
            'total_monthly_expenses' => $validated['total_monthly_expenses'] ?? $profile->total_monthly_expenses,
            'total_monthly_debt_repayment' => $validated['total_monthly_debt_repayment'] ?? $profile->total_monthly_debt_repayment,
            'salary_day_of_month' => $validated['salary_day_of_month'] ?? $profile->salary_day_of_month,
        ]);

        $profile->update([
            'available_monthly_savings' => $profile->total_monthly_income - $profile->total_monthly_expenses - $profile->total_monthly_debt_repayment,
        ]);

        if (isset($validated['income_sources'])) {
            $profile->incomeSources()->delete();
            $profile->incomeSources()->createMany($validated['income_sources']);
        }

        if (isset($validated['expense_categories'])) {
            $profile->expenseCategories()->delete();
            $profile->expenseCategories()->createMany($validated['expense_categories']);
        }

        if (isset($validated['debts'])) {
            $profile->debts()->delete();
            $profile->debts()->createMany($validated['debts']);
        }

        return $this->successResponse('Profile updated successfully', $profile->load(['incomeSources', 'expenseCategories', 'debts']));
    }
}
