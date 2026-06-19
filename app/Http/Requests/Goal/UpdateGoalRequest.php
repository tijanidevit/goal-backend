<?php

namespace App\Http\Requests\Goal;

use App\Enums\EnumGoalCategory;
use App\Enums\EnumGoalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', Rule::enum(EnumGoalCategory::class)],
            'name' => ['sometimes', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'numeric', 'min:1'],
            'target_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', Rule::enum(EnumGoalStatus::class)],
        ];
    }
}
