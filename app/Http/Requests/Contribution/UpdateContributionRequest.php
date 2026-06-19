<?php

namespace App\Http\Requests\Contribution;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'contribution_date' => ['sometimes', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
