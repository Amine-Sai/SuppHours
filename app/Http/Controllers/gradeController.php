<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class GradesController extends Controller
{
    /**
     * Display a listing of the grades.
     */
    public function index()
    {
        return response()->json(Grade::all());
    }

    /**
     * Store a newly created grade.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string',
            'perHour' => 'required|numeric',
            'startDate' => 'required|date',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $grade = Grade::create($validated);
        return response()->json($grade, 201);
    }

    /**
     * Display the specified grade.
     */
    public function show(Grade $grade)
    {
        return response()->json($grade);
    }

    /**
     * Update the specified grade.
     */
    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'value' => 'sometimes|string',
            'perHour' => 'sometimes|numeric',
            'startDate' => 'sometimes|date',
            'teacher_id' => 'sometimes|exists:teachers,id',
        ]);

        $grade->update($validated);
        return response()->json($grade);
    }

    /**
     * Remove the specified grade.
     */
    public function destroy(Grade $grade)
    {
        $grade->delete();
        return response()->json(['message' => 'Grade deleted successfully']);
    }
}
