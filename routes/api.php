<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TrainingCycleController;
use App\Http\Controllers\API\TrainingBlockController;
use App\Http\Controllers\API\TrainingDayController;
use App\Http\Controllers\API\WeekController;
use App\Http\Controllers\API\ExerciseController;
use App\Http\Controllers\API\ExerciseSetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('training-blocks/active', [TrainingBlockController::class, 'active']);
    Route::post('training-blocks/set-active', [TrainingBlockController::class, 'setActive']);
    Route::resource('training-cycles', TrainingCycleController::class);
    Route::resource('training-blocks', TrainingBlockController::class);
    Route::resource('training-days', TrainingDayController::class);
    Route::resource('weeks', WeekController::class);
    Route::resource('exercises', ExerciseController::class);
    Route::resource('exercise-sets', ExerciseSetController::class);
});
