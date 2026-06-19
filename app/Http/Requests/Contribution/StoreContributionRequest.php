<?php

namespace App\Http\Requests\Contribution;

use Illuminate\Foundation\Http\FormRequest;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'contribution_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
