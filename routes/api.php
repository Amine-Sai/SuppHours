<?php

// routes/web.php
use App\Http\Controllers\{
    AbsencesController,
    GradeController,
    LectureController,
    TeacherController
};

// Lectures
Route::prefix('lectures')->group(function () {
    Route::get('/', [LectureController::class, 'index']);
    Route::post('/', [LectureController::class, 'store']);
    Route::get('/{lecture}', [LectureController::class, 'show']);
    Route::put('/{lecture}', [LectureController::class, 'update']);
    Route::delete('/{lecture}', [LectureController::class, 'destroy']);
});

// Teachers
Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index']);
    Route::post('/', [TeacherController::class, 'store']);
    // ... other teacher routes
});

// Grades
Route::prefix('grades')->group(function () {
    Route::get('/', [GradeController::class, 'index']);
    // ... other grade routes
});

// Absences
Route::prefix('absences')->group(function () {
    Route::get('/', [AbsencesController::class, 'index']);
    // ... other absence routes
});