<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TrainingCycle;
use App\Http\Resources\TrainingCycleResource;
use Illuminate\Support\Facades\Validator;

class TrainingCycleController extends Controller
{
    public function index(): JsonResponse
    {
        // user's training cycles
        $trainingCycles = auth()->user()->trainingCycles;

        return $this->sendResponse(TrainingCycleResource::collection(TrainingCycle::all()), 'Training Cycles retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        $input['user_id'] = auth()->user()->id;

        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $trainingCycle = TrainingCycle::create($input);

        return $this->sendResponse(new TrainingCycleResource($trainingCycle), 'Training Cycle created successfully.');
    }
}
