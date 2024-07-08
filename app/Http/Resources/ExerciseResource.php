<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
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
            'name' => $this->name,
            'strength' => $this->strength,
            'rpe' => $this->rpe,
            'muscle_group' => $this->muscle_group,
            'sets' => $this->sets->map(function ($set) {
                return [
                    'id' => $set->id,
                    'set_number' => $set->set_number,
                    'load' => $set->load,
                    'reps' => $set->reps,
                    'logged' => $set->logged,
                    'updated_at' => $set->updated_at,
                ];
            }),
        ];
    }
}
