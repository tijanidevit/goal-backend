<?php

namespace App\Http\Requests\FinancialProfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total_monthly_income' => ['numeric', 'min:0'],
            'total_monthly_expenses' => ['numeric', 'min:0'],
            'total_monthly_debt_repayment' => ['numeric', 'min:0'],
            'salary_day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'income_sources' => ['array'],
            'income_sources.*.source_name' => ['required_with:income_sources', 'string'],
            'income_sources.*.amount' => ['required_with:income_sources', 'numeric', 'min:0'],
            'expense_categories' => ['array'],
            'expense_categories.*.category_name' => ['required_with:expense_categories', 'string'],
            'expense_categories.*.amount' => ['required_with:expense_categories', 'numeric', 'min:0'],
            'debts' => ['array'],
            'debts.*.name' => ['required_with:debts', 'string'],
            'debts.*.amount_owed' => ['required_with:debts', 'numeric', 'min:0'],
            'debts.*.monthly_repayment' => ['required_with:debts', 'numeric', 'min:0'],
        ];
    }
}
