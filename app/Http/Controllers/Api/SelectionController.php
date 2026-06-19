<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\EnumGoalCategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelectionController extends Controller
{
    use ResponseTrait;

    /**
     * Format enum cases into a standardized value/label array for selections.
     *
     * @param array $cases
     * @return array
     */
    protected function formatEnum(array $cases): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }, $cases);
    }

    public function goalCategories(): JsonResponse
    {
        $categories = $this->formatEnum(EnumGoalCategory::cases());

        return $this->successResponse(
            'Goal categories fetched successfully',
            $categories
        );
    }
}
