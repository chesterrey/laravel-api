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
        try {
            // user's training cycles
            $trainingCycles = auth()->user()->trainingCycles;

            // all training blocks for user's training cycles
            $trainingBlocks = TrainingBlock::whereIn('training_cycle_id', $trainingCycles->pluck('id'))->get();

            return $this->sendResponse(TrainingBlockResource::collection($trainingBlocks), 'Training Blocks retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training blocks.', $e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

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
                            'done' => false,
                        ]
                    );

                    foreach($trainingDay['exercises'] as $exercise){
                        $newExercise = $newWeek->exercises()->create(
                            [
                                'name' => $exercise['name'],
                                'strength' => $exercise['strength'],
                                'muscle_group' => $exercise['muscle_group'],
                                'rpe' => 6,
                            ]
                        );

                        for ($j = 1; $j <= 3; $j++) {
                            $newExercise->sets()->create(
                                [
                                    'set_number' => $j,
                                    'reps' => null,
                                    'load' => null,
                                    'logged' => false,
                                ]
                            );
                        }
                    }
                }
            }

            return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while creating training block.', $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $trainingBlock = TrainingBlock::findOrFail($id);

            // check if user has access to the training cycle
            $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
            if($trainingCycle->user_id != auth()->user()->id){
                return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
            }

            return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Training Block retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training block.', $e->getMessage());
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

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
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating training block.', $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $trainingBlock = TrainingBlock::findOrFail($id);

            // check if user has access to the training cycle
            $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
            if($trainingCycle->user_id != auth()->user()->id){
                return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
            }

            $trainingBlock->delete();

            return $this->sendResponse([], 'Training Block deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while deleting training block.', $e->getMessage());
        }
    }

    public function active(): JsonResponse
    {
        $user = auth()->user();
        try {
            $trainingBlock = TrainingBlock::findOrFail($user->training_block_id);

            if($trainingBlock){
                return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Active Training Block retrieved successfully.');
            }

        } catch (\Exception $e) {
            return $this->sendResponse([], 'No active Training Block found.');
        }
    }

    public function setActive(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'training_block_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            $trainingBlock = TrainingBlock::findOrFail($input['training_block_id']);

            // check if user has access to the training cycle
            $trainingCycle = TrainingCycle::find($trainingBlock->training_cycle_id);
            if($trainingCycle->user_id != auth()->user()->id){
                return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
            }

            $user = auth()->user();
            $user->training_block_id = $trainingBlock->id;
            $user->save();

            return $this->sendResponse(new TrainingBlockResource($trainingBlock), 'Active Training Block set successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Not Found.', ['Training Block not found.']);
        }

    }
}
