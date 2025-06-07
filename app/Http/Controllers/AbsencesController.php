<?php

namespace App\Http\Controllers;

use App\Models\Absences;
use App\Models\Teacher;
use App\Models\Lecture;
use App\Models\Holidays;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

class AbsencesController extends Controller
{

    public function index()
    {
        return response()->json(Absences::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'justified'   => 'sometimes|boolean',
            'reason' => 'sometimes|string',
            'date'        => 'required|date',
            'teacher_id'  => 'required|exists:teachers,id',
            'lecture_id'  => 'required|exists:lectures,id',
        ]);

        $absence = Absences::create([
            'justified'   => $request->justified,
            'date'        => $request->date,
            'reason' =>$request->reason,
            'teacher_id'  => $request->teacher_id,
            'lecture_id'  => $request->lecture_id,
        ]);

        return response()->json($absence, 201);
    }

    public function show(Teacher $teacher)
    {
        return response()->json($teacher->absences);
    }

    public function update(Absences $absence, Request $request)
    {
        $request->validate([
            'id' => 'required|exists:absences,id',
            'justified'   => 'sometimes|boolean',
            'date'        => 'sometimes|date',
            'teacher_id'  => 'sometimes|exists:teachers,id',
            'lecture_id'  => 'sometimes|exists:lectures,id',
        ]);

        $absence->update($request->all());

        return response()->json($absence);
    }

    public function destroy(Absences $absence)
    {
        $absence->delete();
        return response()->json(['message' => 'Absence deleted successfully']);
    }
}
