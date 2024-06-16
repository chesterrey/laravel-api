<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeekResource extends JsonResource
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
            'week_number' => $this->week_number,
            'training_day_id' => $this->training_day_id,
            'deload' => $this->deload,
            'training_day' => $this->trainingDay->name,
            'done' => $this->done,
            'exercises' => $this->exercises->map(function ($exercise) {
                return [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'strength' => $exercise->strength,
                    'rpe' => $exercise->rpe,
                    'muscle_group' => $exercise->muscle_group,
                    'sets' => $exercise->sets->map(function ($set) {
                        return [
                            'id' => $set->id,
                            'set_number' => $set->set_number,
                            'load' => $set->load,
                            'reps' => $set->reps,
                            'logged' => $set->logged,
                        ];
                    }),
                ];
            }),
        ];
    }
}
