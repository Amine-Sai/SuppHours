<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    /**
     * Display a listing of the lectures.
     */
    public function index()
    {
        return response()->json(Lecture::all());
    }

    /**
     * Store a newly created lecture.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'subject_id' => 'required|string',
            'type' => 'required|in:cours,td,tp,supp',
            'state' => 'required|in:intern,extern',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $lecture = Lecture::create($request->all());

        return response()->json($lecture, 201);
    }

    /**
     * Display the specified lecture.
     */
    public function show(Lecture $lecture)
    {
        return response()->json($lecture);
    }

    /**
     * Update the specified lecture.
     */
    public function update(Request $request, Lecture $lecture)
    {
        $request->validate([
            'start' => 'sometimes|date_format:H:i',
            'end' => 'sometimes|date_format:H:i|after:start',
            'subject_id' => 'sometimes|string',
            'type' => 'sometimes|in:cours,td,tp,supp',
            'state' => 'sometimes|in:intern,extern',
            'day' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'teacher_id' => 'sometimes|exists:teachers,id',
        ]);

        $lecture->update($request->all());

        return response()->json($lecture);
    }

    /**
     * Remove the specified lecture.
     */
    public function destroy(Lecture $lecture)
    {
        $lecture->delete();
        return response()->json(['message' => 'Lecture deleted successfully']);
    }
}
