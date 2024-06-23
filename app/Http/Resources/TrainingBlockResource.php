<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,
            'weeks' => $this->weeks,
            'training_cycle' => $this->trainingCycle,
            'training_days' => $this->trainingDays->map(function($trainingDay){
                return [
                    'id' => $trainingDay->id,
                    'day' => $trainingDay->day,
                    'name' => $trainingDay->name,
                    'weeks' => $trainingDay->weeks->map(function($week){
                        return [
                            'id' => $week->id,
                            'day' => $week->trainingDay->day,
                            'week_number' => $week->week_number,
                            'deload' => $week->deload,
                            'done' => $week->done,
                            'exercises' => $week->exercises->map(function($exercise){
                                return [
                                    'id' => $exercise->id,
                                    'name' => $exercise->name,
                                    'strength' => $exercise->strength,
                                    'rpe' => $exercise->rpe,
                                    'muscle_group' => $exercise->muscle_group,
                                    'sets' => $exercise->sets->map(function($set){
                                        return [
                                            'id' => $set->id,
                                            'reps' => $set->reps,
                                            'load' => $set->load,
                                            'logged' => $set->logged,
                                        ];
                                    }),
                                ];
                            }),
                            'created_at' => $week->created_at,
                            'updated_at' => $week->updated_at,
                        ];
                    }),
                ];
            }),
            'order' => $this->order,
            'training_cycle_id' => $this->training_cycle_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
