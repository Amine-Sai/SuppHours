<?php

// routes/web.php
use App\Http\Controllers\{
    AbsencesController,
    GradeController,
    LectureController,
    TeacherController,
    AuthController
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
    Route::get('/{teacher}', [TeacherController::class, 'show']);
    Route::put('/{teacher}', [TeacherController::class, 'update']);
    Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
    // ... other teacher routes
});

// Grades
Route::prefix('grades')->group(function () {
    Route::post('/', [GradeController::class, 'store']);
    Route::get('/', [GradeController::class, 'index']);
    Route::get('/{teacher}', [GradeController::class, 'show']);
    Route::put('/{teacher}', [GradeController::class, 'update']);
    Route::delete('/{teacher}', [GradeController::class, 'destroy']);
    // ... other grade routes
});

// Absences
Route::prefix('absences')->group(function () {
    Route::post('/', [AbsencesController::class, 'store']);
    Route::get('/', [AbsencesController::class, 'index']);
    Route::get('/{teacher}', [AbsencesController::class, 'show']);
    Route::put('/{teacher}', [AbsencesController::class, 'update']);
    Route::delete('/{teacher}', [AbsencesController::class, 'destroy']);
    // ... other absence routes
});

// Public routes
Route::post('/register', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});