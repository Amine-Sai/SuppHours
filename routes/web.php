<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsencesController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\TimetableController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/createuser', [AuthController::class, 'createUser']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::prefix('absences')->group(function () {
    Route::get('/', [AbsencesController::class, 'index']);
    Route::post('/', [AbsencesController::class, 'store']);
    Route::get('/{teacher}', [AbsencesController::class, 'show']);
    Route::put('/{teacher}', [AbsencesController::class, 'update']);
    Route::delete('/{teacher}', [AbsencesController::class, 'destroy']);
    Route::post('/teachers/{teacher}/history', [AbsencesController::class, 'history']);
});

Route::prefix('grades')->group(function () {
    Route::get('/', [GradeController::class, 'index']);
    Route::post('/', [GradeController::class, 'store']);
    Route::get('/{grade}', [GradeController::class, 'show']);
    Route::put('/{grade}', [GradeController::class, 'update']);
    Route::delete('/{grade}', [GradeController::class, 'destroy']);
    Route::post('/teachers', [GradeController::class, 'addGradeToTeacher']); 
    Route::get('/teachers/{teacher}', [GradeController::class, 'getTeacherGrades']); 
    Route::delete('/teachers', [GradeController::class, 'removeGradeFromTeacher']); 
});


Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);           // GET /api/teachers
    Route::post('/', [TeacherController::class, 'store']);          // POST /api/teachers
    Route::get('/{teacher}', [TeacherController::class, 'show']);   // GET /api/teachers/{teacher}
    Route::put('/{teacher}', [TeacherController::class, 'update']); // PUT /api/teachers/{teacher}
    Route::delete('/{teacher}', [TeacherController::class, 'destroy']); // DELETE /api/teachers/{teacher>

        Route::get('/{teacher}/timetable', [TeacherController::class, 'getTimeTable']);
});


Route::prefix('periods')->group(function () {
    Route::get('/', [PeriodController::class, 'index']);   
    Route::post('/', [PeriodController::class, 'store']); 
    Route::get('/{period}', [PeriodController::class, 'show']);
    Route::put('/{period}', [PeriodController::class, 'update']); // Update a period
    Route::delete('/{period}', [PeriodController::class, 'destroy']); // Delete a period
    Route::get('/{period}/teachers/{teacher}/compensation', [PeriodController::class, 'show']); // Get compensation for a teacher in a period
    Route::get('/{period}/teachers/{teacher}/raw-taught-lectures', [PeriodController::class, 'getRawTaughtLectures']);
});



Route::prefix('holidays')->group(function () {
    Route::get('/', [HolidaysController::class, 'index']);
    Route::post('/', [HolidaysController::class, 'store']);
    Route::get('/{holiday}', [HolidaysController::class, 'show']);
    Route::put('/{holiday}', [HolidaysController::class, 'update']);
    Route::delete('/{holiday}', [HolidaysController::class, 'destroy']);
});

Route::prefix('lectures')->group(function () {
    Route::post('/', [LectureController::class, 'store']);
    Route::get('/{lecture}', [LectureController::class, 'show']);
Route::get('/teachers/{teacher}/timetables/{timetable}', [LectureController::class, 'getTeacherTimeTable']);
    Route::put('/{lecture}', [LectureController::class, 'update']);
    Route::delete('/{lecture}', [LectureController::class, 'destroy']);
    Route::post('/calculate-additional-hours', [LectureController::class, 'calculateAdditionalHours']);
});

// Timetable Routes
Route::prefix('timetables')->group(function () {
    Route::post('/', [TimetableController::class, 'store']);
    Route::get('/', [TimetableController::class, 'index']);
    Route::put('/{timetable}', [TimetableController::class, 'update']);
    Route::delete('/{timetable}', [TimetableController::class, 'destroy']);
    // Removed potentially misplaced route
});

// Teacher-Period specific routes
// Route::get('/periods/{period}/teachers/{teacher}/compensation', [PeriodController::class, 'show']);
// Route to get the organized lecture data for a teacher within a period (Excel download)
// Route::get('/teachers/{teacher}/periods/{period}/taught-lectures', [PeriodController::class, 'getTaughtLecturesByMonth']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // All the routes above are implicitly protected if you intend them to be
    // You can move the prefixes/groups inside this middleware group to explicitly protect them.
});