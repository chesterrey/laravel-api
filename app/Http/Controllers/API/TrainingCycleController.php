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
        try {
            // user's training cycles
            $trainingCycles = auth()->user()->trainingCycles;

            return $this->sendResponse(TrainingCycleResource::collection($trainingCycles), 'Training Cycles retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training cycles.', $e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

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
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while creating training cycle.', $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $trainingCycle = TrainingCycle::findOrFail($id);

            if($trainingCycle->user_id !== auth()->id()){
                return $this->sendError('Unauthorized.', ['You are not authorized to view this training cycle.']);
            }

            return $this->sendResponse(new TrainingCycleResource($trainingCycle), 'Training Cycle retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training cycle.', $e->getMessage());
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

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
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating training cycle.', $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $trainingCycle = TrainingCycle::findOrFail($id);

            if($trainingCycle->user_id !== auth()->id()){
                return $this->sendError('Unauthorized.', ['You are not authorized to delete this training cycle.']);
            }

            $trainingCycle->delete();

            return $this->sendResponse([], 'Training Cycle deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while deleting training cycle.', $e->getMessage());
        }
    }
}
