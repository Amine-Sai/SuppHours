<?php

namespace App\Http\Controllers;

use App\Models\Absence;
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
        return response()->json(Absence::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'justified'   => 'sometimes|boolean',
            'date'        => 'required|date',
            'teacher_id'  => 'required|exists:teachers,id',
            'lecture_id'  => 'required|exists:lectures,id',
        ]);

        $absence = Absence::create([
            'justified'   => $request->justified,
            'date'        => $request->date,
            'teacher_id'  => $request->teacher_id,
            'lecture_id'  => $request->lecture_id,
        ]);

        return response()->json($absence, 201);
    }

    public function show(Absence $absence)
    {
        return response()->json($absence);
    }

    public function update(Absence $absence, Request $request)
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

    public function destroy(Absence $absence)
    {
        $absence->delete();
        return response()->json(['message' => 'Absence deleted successfully']);
    }
}
