<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Http\Resources\ExerciseResource;
use Illuminate\Http\JsonResponse;
use App\Models\Week;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
{
    //
    public function index(): JsonResponse
    {
        try {

            $user = auth()->user();
            $trainingCycles = $user->trainingCycles;
            $trainingBlocks = $trainingCycles->map(function ($trainingCycle) {
                return $trainingCycle->trainingBlocks;
            })->flatten();
            $trainingDays = $trainingBlocks->map(function ($trainingBlock) {
                return $trainingBlock->trainingDays;
            })->flatten();
            $weeks = $trainingDays->map(function ($trainingDay) {
                return $trainingDay->weeks;
            })->flatten();

            $exercises = $weeks->map(function ($week) {
                return $week->exercises;
            })->flatten();

            return $this->sendResponse(ExerciseResource::collection($exercises), 'Exercises retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving exercises.', $e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $input = $request->all();

            $validator = Validator::make($input, [
                'week_id' => 'required',
                'name' => 'required',
                'strength' => 'required',
                'rpe' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $week = Week::findOrFail($input['week_id']);
            $trainingDay = $week->trainingDay;
            $trainingBlock = $trainingDay->trainingBlock;
            $trainingCycle = $trainingBlock->trainingCycle;

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to create an exercise for this training cycle.']);
            }

            $exercise = Exercise::create($input);



            return $this->sendResponse(new ExerciseResource($exercise), 'Exercise created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while creating exercise.', $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $exercise = Exercise::findOrFail($id);

            return $this->sendResponse(new ExerciseResource($exercise), 'Exercise retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving exercise.', $e->getMessage());
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

            $input = $request->all();

            if (!isset($input['name']) && !isset($input['strength']) && !isset($input['rpe'])) {
                return $this->sendError('Validation Error.', ['Name, strength, or rpe is required.']);
            }


            $exercise = Exercise::findOrFail($id);

            $week = Week::findOrFail($exercise->week_id);
            $trainingDay = $week->trainingDay;
            $trainingBlock = $trainingDay->trainingBlock;
            $trainingCycle = $trainingBlock->trainingCycle;

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to update this exercise.']);
            }

            if (isset($input['name'])) {
                $exercise->name = $input['name'];
            }

            if (isset($input['strength'])) {
                $exercise->strength = $input['strength'];
            }

            if (isset($input['rpe'])) {
                $exercise->rpe = $input['rpe'];
            }

            if (isset($input['muscle_group'])) {
                $exercise->muscle_group = $input['muscle_group'];
            }

            $exercise->save();

            return $this->sendResponse(new ExerciseResource($exercise), 'Exercise updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating exercise.', $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {

            $exercise = Exercise::findOrFail($id);

            $week = Week::findOrFail($exercise->week_id);
            $trainingDay = $week->trainingDay;
            $trainingBlock = $trainingDay->trainingBlock;
            $trainingCycle = $trainingBlock->trainingCycle;

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to delete this exercise.']);
            }

            $exercise->delete();

            return $this->sendResponse([], 'Exercise deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while deleting exercise.', $e->getMessage());
        }
    }
}
