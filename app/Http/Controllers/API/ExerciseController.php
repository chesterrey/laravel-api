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
        return $this->sendResponse(ExerciseResource::collection(Exercise::all()), 'Exercises retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
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
    }

    public function show($id): JsonResponse
    {
        $exercise = Exercise::findOrFail($id);

        return $this->sendResponse(new ExerciseResource($exercise), 'Exercise retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->all();

        if (!isset($input['name']) && !isset($input['strength']) && !isset($input['rpe'])) {
            return $this->sendError('Validation Error.', ['Name, strength, or rpe is required.']);
        }

        $exercise = Exercise::findOrFail($id);

        $week = Week::findOrFail($input['week_id']);
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

        $exercise->save();

        return $this->sendResponse(new ExerciseResource($exercise), 'Exercise updated successfully.');
    }
}
