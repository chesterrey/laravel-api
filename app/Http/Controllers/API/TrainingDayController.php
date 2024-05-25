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
        return $this->sendResponse(TrainingDayResource::collection(TrainingDay::all()), 'Training days retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {

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
                    'week_number' => $i + 1,
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
    }
}
