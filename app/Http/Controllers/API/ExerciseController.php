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
}
