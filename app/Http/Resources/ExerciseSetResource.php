<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseSetResource extends JsonResource
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
            'exercise_id' => $this->exercise_id,
            'set_number' => $this->set_number,
            'load' => $this->load,
            'reps' => $this->reps,
            'logged' => $this->logged,
            'updated_at' => $this->updated_at,
        ];
    }
}
