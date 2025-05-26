<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\HolidayController; // Added HolidayController

Route::post('/login', [AuthController::class, 'login']);
Route::post('/createuser', [AuthController::class, 'createUser']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::prefix('absences')->group(function () {
    Route::post('/', [AbsencesController::class, 'store']);
    Route::get('/', [AbsencesController::class, 'index']);
    Route::get('/{teacher}', [AbsencesController::class, 'show']);
    Route::put('/{teacher}', [AbsencesController::class, 'update']);
    Route::delete('/{teacher}', [AbsencesController::class, 'destroy']);
    Route::post('/teachers/{teacher}/history', [AbsencesController::class, 'history']);
});

Route::prefix('grades')->group(function () {
    Route::post('/', [GradeController::class, 'store']);
    Route::get('/', [GradeController::class, 'index']);
    Route::get('/{grade}', [GradeController::class, 'show']);
    Route::put('/{grade}', [GradeController::class, 'update']);
    Route::delete('/{grade}', [GradeController::class, 'destroy']);
    Route::post('/teachers/grades', [GradeController::class, 'addGradeToTeacher']);
    Route::get('/teachers/{teacher}/grades', [GradeController::class, 'getTeacherGrades']);
    Route::delete('/teachers/grades', [GradeController::class, 'removeGradeFromTeacher']);
});

Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);
    Route::post('/', [TeacherController::class, 'store']);
    Route::get('/{teacher}', [TeacherController::class, 'show']);
    Route::put('/{teacher}', [TeacherController::class, 'update']);
    Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
});

Route::apiResource('periods', PeriodController::class);
Route::apiResource('holidays', HolidayController::class); 

Route::prefix('lectures')->group(function () {
    Route::get('/', [LectureController::class, 'index']);
    Route::post('/', [LectureController::class, 'store']);
    Route::get('/{lecture}', [LectureController::class, 'show']);
    // Route::get('/teacher/{teacher}', [LectureController::class, 'showTimeTable']);
    Route::put('/{lecture}', [LectureController::class, 'update']);
    Route::delete('/{lecture}', [LectureController::class, 'destroy']);
    Route::post('/calculate-additional-hours', [LectureController::class, 'calculateAdditionalHours']);
});

// Timetable Routes
Route::prefix('timetables')->group(function () {
    Route::post('/', [TimetableController::class, 'store']);
    Route::put('/{timetable}', [TimetableController::class, 'update']);
    Route::delete('/{timetable}', [TimetableController::class, 'destroy']);
    Route::get('/latest-for-teacher-lectures', [TimetableController::class, 'getLecturesForTeacherInLatestTimetable']);
});

// Route for listing timetables for a specific teacher (nested resource)
Route::get('/teachers/{teacher}/timetables', [TimetableController::class, 'index']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Your authenticated routes here
});