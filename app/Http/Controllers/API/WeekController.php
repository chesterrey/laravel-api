<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Week;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WeekResource;

class WeekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $weeks = Week::all();

            return $this->sendResponse(WeekResource::collection($weeks), 'Weeks retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving weeks.', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $week = Week::findOrFail($id);

            return $this->sendResponse(new WeekResource($week), 'Week retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving week.', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->all();
            $week = Week::findOrFail($id);


            if ($input['done']) {
                $input['date_completed'] = now();

                $trainingDay = $week->trainingDay;

                if ($week->week_number < $trainingDay->weeks->count()) {
                    $nextWeek = $trainingDay->weeks()->where('week_number', $week->week_number + 1)->first();

                    if ($nextWeek->exercises->count() <= 0) {
                        foreach ($week->exercises as $exercise) {
                            $newExercise = $nextWeek->exercises()->create([
                                'name' => $exercise['name'],
                                'strength' => $exercise['strength'],
                                'muscle_group' => $exercise['muscle_group'],
                                'rpe' => $exercise['rpe'],
                            ]);

                            foreach ($exercise->sets as $set) {
                                $newExercise->sets()->create([
                                    'set_number' => $set['set_number'],
                                    'reps' => null,
                                    'load' => null,
                                    'logged' => false,
                                ]);
                            }
                        }
                    }
                }

            }

            $week->update($input);

            return $this->sendResponse(new WeekResource($week), 'Week updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating week.', $e->getMessage());
        }

    }
}
