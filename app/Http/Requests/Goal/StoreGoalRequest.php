<?php

namespace App\Http\Requests\Goal;

use App\Enums\EnumGoalCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(EnumGoalCategory::class)],
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
            'currency_code' => ['nullable', 'string', 'size:3'],
        ];
    }
}
