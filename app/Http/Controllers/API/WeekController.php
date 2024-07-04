<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Week;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\WeekResource;

class WeekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $weeks = Week::all();

            return $this->sendResponse(WeekResource::collection($weeks), 'Weeks retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving weeks.', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $week = Week::findOrFail($id);

            return $this->sendResponse(new WeekResource($week), 'Week retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while retrieving week.', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->all();
            $week = Week::findOrFail($id);
            $week->update($input);

            return $this->sendResponse(new WeekResource($week), 'Week updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error occurred while updating week.', $e->getMessage());
        }

    }
}
