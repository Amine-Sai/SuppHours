<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the absences.
     */
    public function index()
    {
        return response()->json(Absence::all());
    }

    /**
     * Store a newly created absence.
     */
    public function store(Request $request)
    {
        $request->validate([
            'justified' => 'required|boolean',
            'date' => 'required|date',
            'teacher_id' => 'required|exists:teachers,id',
            'lecture_id' => 'required|exists:lectures,id',
        ]);

        $absence = Absence::create($request->all());

        return response()->json($absence, 201);
    }

    /**
     * Display the specified absence.
     */
    public function show(Absence $absence)
    {
        return response()->json($absence);
    }

    /**
     * Update the specified absence.
     */
    public function update(Request $request, Absence $absence)
    {
        $request->validate([
            'justified' => 'sometimes|boolean',
            'date' => 'sometimes|date',
            'teacher_id' => 'sometimes|exists:teachers,id',
            'lecture_id' => 'sometimes|exists:lectures,id',
        ]);

        $absence->update($request->all());

        return response()->json($absence);
    }

    /**
     * Remove the specified absence.
     */
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return response()->json(['message' => 'Absence deleted successfully']);
    }
}
