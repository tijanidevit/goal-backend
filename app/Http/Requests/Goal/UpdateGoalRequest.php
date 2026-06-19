<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'numeric', 'min:1'],
            'target_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'status' => ['sometimes', 'string', 'in:active,achieved,paused,abandoned'],
        ];
    }
}
