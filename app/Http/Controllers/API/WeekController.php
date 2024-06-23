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
        //
        $weeks = Week::all();

        return $this->sendResponse(WeekResource::collection($weeks), 'Weeks retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $week = Week::findOrFail($id);

        return $this->sendResponse(new WeekResource($week), 'Week retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $week = Week::findOrFail($id);

        $week->update($input);

        return $this->sendResponse(new WeekResource($week), 'Week updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Week $week)
    {
        //
    }
}
