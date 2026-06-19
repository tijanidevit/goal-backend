<?php

namespace App\Services;

use App\Enums\EnumGoalHealth;
use App\Enums\EnumGoalHealthLabel;

class GoalHealthService
{
    /**
     * Determines if the user is on track to reach their goal.
     * 
     * @param float $requiredMonthlySavings
     * @param float $availableMonthlySavings
     * @return array
     */
    public function calculateHealth(float $requiredMonthlySavings, float $availableMonthlySavings): array
    {
        if ($requiredMonthlySavings <= 0) {
            // Already achieved or no savings required
            return [
                'status' => EnumGoalHealth::GREEN->value,
                'label' => EnumGoalHealthLabel::ON_TRACK->value
            ];
        }

        if ($availableMonthlySavings >= $requiredMonthlySavings * 1.05) {
            return [
                'status' => EnumGoalHealth::GREEN->value,
                'label' => EnumGoalHealthLabel::ON_TRACK->value
            ];
        }

        if ($availableMonthlySavings >= $requiredMonthlySavings * 0.90 && $availableMonthlySavings < $requiredMonthlySavings * 1.05) {
            return [
                'status' => EnumGoalHealth::YELLOW->value,
                'label' => EnumGoalHealthLabel::SLIGHTLY_BEHIND->value
            ];
        }

        return [
            'status' => EnumGoalHealth::RED->value,
            'label' => EnumGoalHealthLabel::AT_RISK->value
        ];
    }
}
