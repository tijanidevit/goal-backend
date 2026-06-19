<?php

namespace App\Services;

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
                'status' => 'green',
                'label' => 'On Track'
            ];
        }

        if ($availableMonthlySavings >= $requiredMonthlySavings * 1.05) {
            return [
                'status' => 'green',
                'label' => 'On Track'
            ];
        }

        if ($availableMonthlySavings >= $requiredMonthlySavings * 0.90 && $availableMonthlySavings < $requiredMonthlySavings * 1.05) {
            return [
                'status' => 'yellow',
                'label' => 'Slightly Behind'
            ];
        }

        return [
            'status' => 'red',
            'label' => 'At Risk'
        ];
    }
}
