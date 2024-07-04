<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingDay;
use App\Http\Resources\TrainingDayResource;
use Illuminate\Http\JsonResponse;
use App\Models\TrainingBlock;
use App\Models\TrainingCycle;
use App\Models\Week;
use Illuminate\Support\Facades\Validator;

class TrainingDayController extends Controller
{
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

            return $this->sendResponse(TrainingDayResource::collection($trainingDays), 'Training days retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training days.', $e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $input = $request->all();

            $validator = Validator::make($input, [
                'training_block_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $trainingBlock = TrainingBlock::findOrFail($request->training_block_id);

            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You do not have access to this training cycle.']);
            }

            $trainingDaysCount = $trainingBlock->trainingDays->count();

            if($trainingDaysCount >= 7) {
                return $this->sendError('Validation Error.', ['You have reached the maximum number of training days for this block.']);
            }

            $input['day'] = $trainingDaysCount + 1;
            $input['name'] = 'Day ' . $input['day'];

            $trainingDay = TrainingDay::create($input);

            $weeks = $trainingBlock->weeks; // weeks = integer

            for ($i = 1; $i <= $weeks; $i++) {
                if ($i === $weeks) {
                    Week::create([
                        'week_number' => $i,
                        'training_day_id' => $trainingDay->id,
                        'deload' => true,
                    ]);
                } else {
                    Week::create([
                        'week_number' => $i,
                        'training_day_id' => $trainingDay->id,
                        'deload' => false,
                    ]);
                }
            }


            return $this->sendResponse(new TrainingDayResource($trainingDay), 'Training day created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while creating training day.', $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $trainingDay = TrainingDay::findOrFail($id);

            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to view this training day.']);
            }

            return $this->sendResponse(new TrainingDayResource($trainingDay), 'Training day retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving training day.', $e->getMessage());
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $input = $request->all();

            $validator = Validator::make($input, [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $trainingDay = TrainingDay::findOrFail($id);
            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to update this training day.']);
            }

            $trainingDay->name = $input['name'];
            $trainingDay->save();

            return $this->sendResponse(new TrainingDayResource($trainingDay), 'Training day updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating training day.', $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $trainingDay = TrainingDay::findOrFail($id);
            $trainingBlock = TrainingBlock::findOrFail($trainingDay->training_block_id);
            $trainingCycle = TrainingCycle::findOrFail($trainingBlock->training_cycle_id);

            if ($trainingCycle->user_id !== auth()->id()) {
                return $this->sendError('Unauthorized.', ['You are not authorized to delete this training day.']);
            }

            $trainingDay->delete();

            return $this->sendResponse([], 'Training day deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while deleting training day.', $e->getMessage());
        }
    }
}
