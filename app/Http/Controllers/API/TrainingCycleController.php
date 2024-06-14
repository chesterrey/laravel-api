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

    public function show($id): JsonResponse
    {
        $trainingCycle = TrainingCycle::findOrFail($id);

        if($trainingCycle->user_id !== auth()->id()){
            return $this->sendError('Unauthorized.', ['You are not authorized to view this training cycle.']);
        }

        return $this->sendResponse(new TrainingCycleResource($trainingCycle), 'Training Cycle retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->all();

        if (!$input['name']) {
            return $this->sendError('Validation Error.', ['Name is required.']);
        }

        $trainingCycle = TrainingCycle::findOrFail($id);

        if($trainingCycle->user_id !== auth()->id()){
            return $this->sendError('Unauthorized.', ['You are not authorized to update this training cycle.']);
        }

        $trainingCycle->name = $input['name'];

        $trainingCycle->save();

        return $this->sendResponse(new TrainingCycleResource($trainingCycle), 'Training Cycle updated successfully.');
    }

    public function destroy($id): JsonResponse
    {
        $trainingCycle = TrainingCycle::findOrFail($id);

        if($trainingCycle->user_id !== auth()->id()){
            return $this->sendError('Unauthorized.', ['You are not authorized to delete this training cycle.']);
        }

        $trainingCycle->delete();

        return $this->sendResponse([], 'Training Cycle deleted successfully.');
    }
}
