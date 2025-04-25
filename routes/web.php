<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PeriodController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/createuser', [AuthController::class, 'createUser']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/teachers/{teacher}/history', [AbsencesController::class, 'history']);
    Route::prefix('absences')->group(function () {
        Route::post('/', [AbsencesController::class, 'store']);
        Route::get('/', [AbsencesController::class, 'index']);
        Route::get('/{teacher}', [AbsencesController::class, 'show']);
        Route::put('/{teacher}', [AbsencesController::class, 'update']);
        Route::delete('/{teacher}', [AbsencesController::class, 'destroy']);
    
        // ... other absence routes
    });
    Route::prefix('grades')->group(function () {
        Route::post('/', [GradeController::class, 'store']);
        Route::get('/', [GradeController::class, 'index']);
        Route::get('/{teacher}', [GradeController::class, 'show']);
        Route::put('/{teacher}', [GradeController::class, 'update']);
        Route::delete('/{teacher}', [GradeController::class, 'destroy']);
        // ... other grade routes
    });
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{teacher}', [TeacherController::class, 'show']);
        Route::put('/{teacher}', [TeacherController::class, 'update']);
        Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
        // ... other teacher routes
    });
    Route::apiResource('period', PeriodController::class);
    Route::apiResource('holidays', PeriodController::class);

    Route::prefix('lectures')->group(function () {
        Route::get('/', [LectureController::class, 'index']);
        Route::post('/', [LectureController::class, 'store']);
        Route::get('/{lecture}', [LectureController::class, 'show']);
        Route::get('/teacher/{teacher}', [LectureController::class, 'showTimeTable']);
        Route::put('/{lecture}', [LectureController::class, 'update']);
        Route::delete('/{lecture}', [LectureController::class, 'destroy']);
    });
    
});