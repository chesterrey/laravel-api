<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\TrainingBlock;
use App\Models\TrainingCycle;
use App\Models\Exercise;
use App\Models\Week;
use App\Models\TrainingDay;

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
            'order' => 'required',
            'training_days' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $trainingBlock = TrainingBlock::create(
            [
                'training_cycle_id' => $input['training_cycle_id'],
                'weeks' => $input['weeks'],
                'order' => $input['order'],
            ]
        );

        foreach($input['training_days'] as $trainingDay){
            $newTrainingDay = $trainingBlock->trainingDays()->create(
                [
                    'day' => $trainingDay['day'],
                    'name' => 'Day ' . $trainingDay['day'],
                ]
            );

            for ($i = 1; $i <= $input['weeks']; $i++) {
                $newWeek = $newTrainingDay->weeks()->create(
                    [
                        'week_number' => $i,
                        'deload' => false,
                    ]
                );

                foreach($trainingDay['exercises'] as $exercise){
                    error_log($exercise['name']);
                    $newWeek->exercises()->create(
                        [
                            'name' => $exercise['name'],
                            'strength' => $exercise['strength'],
                            'rpe' => 6,
                        ]
                    );
                }
            }
        }




        return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block created successfully.');
    }

    public function show($id): JsonResponse
    {
        $trainingBlock = TrainingBlock::findOrFail($id);

        // check if user has access to the training cycle
        $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
        if($trainingCycle->user_id != auth()->user()->id){
            return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
        }

        return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block retrieved successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'weeks' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $trainingBlock = TrainingBlock::findOrFail($id);

        // check if user has access to the training cycle
        $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
        if($trainingCycle->user_id != auth()->user()->id){
            return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
        }

        $trainingBlock->update($input);

        return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block updated successfully.');
    }

    public function destroy($id): JsonResponse
    {
        $trainingBlock = TrainingBlock::findOrFail($id);

        // check if user has access to the training cycle
        $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
        if($trainingCycle->user_id != auth()->user()->id){
            return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
        }

        $trainingBlock->delete();

        return $this->sendResponse([], 'Training Block deleted successfully.');
    }
}
