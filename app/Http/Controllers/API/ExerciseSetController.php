<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExerciseSet;
use App\Http\Resources\ExerciseSetResource;
use Illuminate\Http\JsonResponse;
use App\Models\Exercise;
use App\Models\Week;
use App\Models\TrainingDay;
use App\Models\TrainingBlock;
use App\Models\TrainingCycle;
use Illuminate\Support\Facades\Validator;

class ExerciseSetController extends Controller
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
            $exerciseSets = $exercises->map(function ($exercise) {
                return $exercise->sets;
            })->flatten();

            return $this->sendResponse(ExerciseSetResource::collection($exerciseSets), 'Exercise sets retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving exercise sets.', $e->getMessage());
        }

    }

    public function store(Request $request): JsonResponse
    {
        try {

            $input = $request->all();

            $validator = Validator::make($input, [
                'exercise_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $exercise = Exercise::findOrFail($input['exercise_id']);
            $week = Week::findOrFail($exercise->week_id);
            $trainingDay = TrainingDay::findOrFail($week->training_day_id);
            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to create an exercise set for this training cycle.']);
            }

            $set_count = $exercise->sets->count();
            $input['set_number'] = $set_count + 1;
            $input['logged'] = false;

            $exerciseSet = ExerciseSet::create($input);

            return $this->sendResponse(new ExerciseSetResource($exerciseSet), 'Exercise set created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while creating exercise set.', $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $exerciseSet = ExerciseSet::findOrFail($id);

            return $this->sendResponse(new ExerciseSetResource($exerciseSet), 'Exercise set retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving exercise set.', $e->getMessage());
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

            $input = $request->all();

            if (!isset($input['load']) && !isset($input['reps']) && !isset($input['logged'])) {
                return $this->sendError('Validation Error.', ['You must provide either a load, reps, or logged status.']);
            }

            $exerciseSet = ExerciseSet::findOrFail($id);
            $exercise = Exercise::findOrFail($exerciseSet->exercise_id);
            $week = Week::findOrFail($exercise->week_id);
            $trainingDay = TrainingDay::findOrFail($week->training_day_id);
            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to update an exercise set for this training cycle.']);
            }

            if (isset($input['load'])) {
                if ($input['load'] < 0) {
                    $exerciseSet->load = null;
                } else {
                    $exerciseSet->load = $input['load'];
                }
            }

            if (isset($input['reps'])) {
                if ($input['reps'] < 0) {
                    $exerciseSet->reps = null;
                } else {
                    $exerciseSet->reps = $input['reps'];
                }
            }

            if (isset($input['logged'])) {
                $exerciseSet->logged = $input['logged'];
            }

            $exerciseSet->save();

            return $this->sendResponse(new ExerciseSetResource($exerciseSet), 'Exercise set updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating exercise set.', $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $exerciseSet = ExerciseSet::findOrFail($id);
            $exercise = Exercise::findOrFail($exerciseSet->exercise_id);
            $week = Week::findOrFail($exercise->week_id);
            $trainingDay = TrainingDay::findOrFail($week->training_day_id);
            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to delete an exercise set for this training cycle.']);
            }

            $exerciseSet->delete();

            return $this->sendResponse([], 'Exercise set deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while deleting exercise set.', $e->getMessage());
        }
    }
}
