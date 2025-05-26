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
Route::get('/logout', [AuthController::class, 'logout']);

Route::prefix('absences')->group(function () {
    Route::post('/', [AbsencesController::class, 'store']);
    Route::get('/', [AbsencesController::class, 'index']);
    Route::get('/{teacher}', [AbsencesController::class, 'show']); // Assuming 'teacher' is the ID
    Route::put('/{teacher}', [AbsencesController::class, 'update']); // Assuming 'teacher' is the ID
    Route::delete('/{teacher}', [AbsencesController::class, 'destroy']); // Assuming 'teacher' is the ID
    Route::post('/teachers/{teacher}/history', [AbsencesController::class, 'history']);
});

Route::prefix('grades')->group(function () {
    Route::post('/', [GradeController::class, 'store']);
    Route::get('/', [GradeController::class, 'index']);
    Route::get('/{grade}', [GradeController::class, 'show']); // Corrected to use 'grade' for single resource
    Route::put('/{grade}', [GradeController::class, 'update']); // Corrected to use 'grade' for single resource
    Route::delete('/{grade}', [GradeController::class, 'destroy']); // Corrected to use 'grade' for single resource
    Route::post('/teachers/grades', [GradeController::class, 'addGradeToTeacher']); // For adding a grade to a teacher
    Route::get('/teachers/{teacher}/grades', [GradeController::class, 'getTeacherGrades']); // To get a teacher's grades
    Route::delete('/teachers/grades', [GradeController::class, 'removeGradeFromTeacher']); // To remove a grade from a teacher
});

Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);
    Route::post('/', [TeacherController::class, 'store']);
    Route::get('/{teacher}', [TeacherController::class, 'show']);
    Route::put('/{teacher}', [TeacherController::class, 'update']);
    Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
});

Route::apiResource('periods', PeriodController::class); // Corrected to plural 'periods' for consistency
Route::apiResource('holidays', PeriodController::class);

Route::prefix('lectures')->group(function () {
    Route::get('/', [LectureController::class, 'index']);
    Route::post('/', [LectureController::class, 'store']);
    Route::get('/{lecture}', [LectureController::class, 'show']);
    // Assuming you want to use the 'show' method with a request for teacher_id
    Route::get('/timetable', [LectureController::class, 'show']);
    // The route below seems specific, let's keep it
    // Route::get('/teacher/{teacher}', [LectureController::class, 'showTimeTable']);
    Route::put('/{lecture}', [LectureController::class, 'update']);
    Route::delete('/{lecture}', [LectureController::class, 'destroy']);
    Route::post('/calculate-additional-hours', [LectureController::class, 'calculateAdditionalHours']);
});

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Your authenticated routes here
});