<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'is_primary' => $this->resource['is_primary'],
            'category' => $this->resource['category'],
            'name' => $this->resource['name'],
            'goal_name' => $this->resource['goal_name'],
            'target_amount' => $this->resource['target_amount'],
            'target_date' => $this->resource['target_date'],
            'current_savings' => $this->resource['current_savings'],
            'remaining_amount' => $this->resource['remaining_amount'],
            'progress_percentage' => $this->resource['progress_percentage'],
            'available_monthly_savings' => $this->resource['available_monthly_savings'],
            'required_monthly_savings' => $this->resource['required_monthly_savings'],
            'projected_completion_date' => $this->resource['projected_completion_date'],
            'goal_health' => $this->resource['goal_health'],
            'current_milestone' => $this->resource['current_milestone'],
            'next_milestone' => $this->resource['next_milestone'],
            'amount_remaining_to_next_milestone' => $this->resource['amount_remaining_to_next_milestone'],
            'timeline' => $this->resource['timeline'],
            'if_you_continue_like_this' => $this->resource['if_you_continue_like_this'],
        ];
    }
}
