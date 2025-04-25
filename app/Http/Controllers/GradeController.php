<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Teacher;
use Illuminate\Http\Request;

class GradeController extends Controller
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
            'name' => 'required|string',
            'value' => 'required|numeric',
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
            'name' => 'sometimes|string',
            'value' => 'sometimes|numeric',
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
    
    /**
     * Add a grade to a teacher
     */
    public function addGradeToTeacher(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'grade_id' => 'required|exists:grades,id',
            'start_date' => 'required|date',
        ]);
        
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        
        // Get current grades
        $grades = $teacher->grades ?? [];
        if (is_string($grades)) {
            $grades = json_decode($grades, true) ?? [];
        }
        
        // Add new grade entry
        $grades[] = [
            'grade_id' => $validated['grade_id'],
            'start_date' => $validated['start_date'],
        ];
        
        // Sort by start_date (newest first)
        usort($grades, function($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });
        
        $teacher->grades = $grades;
        $teacher->save();
        
        return response()->json($teacher);
    }
    
    /**
     * Get a teacher's grades history
     */
    public function getTeacherGrades(Teacher $teacher)
    {
        $grades = $teacher->grades ?? [];
        if (is_string($grades)) {
            $grades = json_decode($grades, true) ?? [];
        }
        
        // Sort by start_date (newest first)
        usort($grades, function($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });
        
        return response()->json($grades);
    }
    
    /**
     * Get a teacher's current grade
     */
    public function getCurrentGrade(Teacher $teacher)
    {
        $grades = $teacher->grades ?? [];
        if (is_string($grades)) {
            $grades = json_decode($grades, true) ?? [];
        }
        
        if (empty($grades)) {
            return response()->json(null);
        }
        
        // Sort by start_date (newest first)
        usort($grades, function($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });
        
        // Current grade is the most recent one
        return response()->json($grades[0]);
    }
    
    /**
     * Remove a grade from a teacher
     */
    public function removeGradeFromTeacher(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'grade_id' => 'required|exists:grades,id',
            'start_date' => 'required|date',
        ]);
        
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        
        // Get current grades
        $grades = $teacher->grades ?? [];
        if (is_string($grades)) {
            $grades = json_decode($grades, true) ?? [];
        }
        
        // Find and remove the specific grade entry
        $filteredGrades = array_filter($grades, function($grade) use ($validated) {
            return !($grade['grade_id'] == $validated['grade_id'] && 
                    $grade['start_date'] == $validated['start_date']);
        });
        
        $teacher->grades = array_values($filteredGrades); // Reset array keys
        $teacher->save();
        
        return response()->json($teacher);
    }
}