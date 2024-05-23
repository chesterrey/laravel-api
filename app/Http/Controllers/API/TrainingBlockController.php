<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TrainingBlock;
use App\Models\TrainingCycle;
use App\Http\Resources\TrainingBlockResource;
use Illuminate\Support\Facades\Validator;

class TrainingBlockController extends Controller
{
    public function index(): JsonResponse
    {
        // user's training cycles
        $trainingCycles = auth()->user()->trainingCycles;

        // all training blocks for user's training cycles
        $trainingBlocks = TrainingBlock::whereIn('training_cycle_id', $trainingCycles->pluck('id'))->get();

        return $this->sendResponse(TrainingBlockResource::collection($trainingBlocks), 'Training Blocks retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        $trainingCycleId = $input['training_cycle_id'];

        // check if user has access to the training cycle
        $trainingCycle = TrainingCycle::find($trainingCycleId);
        if($trainingCycle->user_id != auth()->user()->id){
            return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
        }
        $trainingBlocksCount = $trainingCycle->trainingBlocks->count();
        $input['order'] = $trainingBlocksCount + 1;

        $validator = Validator::make($input, [
            'training_cycle_id' => 'required',
            'weeks' => 'required',
            'days' => 'required',
            'order' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $trainingBlock = TrainingBlock::create($input);

        return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block created successfully.');
    }
}
