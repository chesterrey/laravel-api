<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingDayResource extends JsonResource
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
            'training_block_id' => $this->training_block_id,
            'day' => $this->day,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'weeks' => $this->weeks->map(function ($week) {
                return [
                    'id' => $week->id,
                    'week_number' => $week->week_number,
                    'training_day_id' => $week->training_day_id,
                    'deload' => $week->deload,
                    'exercises' => $week->exercises->map(function ($exercise) {
                        return [
                            'id' => $exercise->id,
                            'name' => $exercise->name,
                            'strength' => $exercise->strength,
                            'rpe' => $exercise->rpe,
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
                    'created_at' => $week->created_at,
                    'updated_at' => $week->updated_at,
                ];
            }),
        ];
    }
}
