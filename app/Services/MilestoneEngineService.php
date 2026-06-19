<?php

namespace App\Services;

class MilestoneEngineService
{
    const MILESTONES = [10, 25, 50, 75, 100];

    /**
     * Calculate simple automatic milestone detection.
     * 
     * @param float $currentSavings
     * @param float $targetAmount
     * @return array
     */
    public function calculateMilestones(float $currentSavings, float $targetAmount): array
    {
        if ($targetAmount <= 0) {
            return [
                'current_progress_percentage' => 100,
                'current_milestone' => 100,
                'next_milestone' => null,
                'amount_remaining_to_next_milestone' => 0
            ];
        }

        $progressPercentage = ($currentSavings / $targetAmount) * 100;
        $progressPercentage = min(100, max(0, $progressPercentage));

        $currentMilestone = 0;
        $nextMilestone = self::MILESTONES[0];

        foreach (self::MILESTONES as $milestone) {
            if ($progressPercentage >= $milestone) {
                $currentMilestone = $milestone;
            } else {
                $nextMilestone = $milestone;
                break;
            }
        }

        if ($currentMilestone == 100) {
            $nextMilestone = null;
        }

        $amountRemainingToNextMilestone = 0;
        if ($nextMilestone !== null) {
            $targetForNextMilestone = ($nextMilestone / 100) * $targetAmount;
            $amountRemainingToNextMilestone = max(0, $targetForNextMilestone - $currentSavings);
        }

        return [
            'current_progress_percentage' => round($progressPercentage, 2),
            'current_milestone' => $currentMilestone,
            'next_milestone' => $nextMilestone,
            'amount_remaining_to_next_milestone' => round($amountRemainingToNextMilestone, 2)
        ];
    }
}
