<?php

namespace App\Http\Controllers;

use App\Models\timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{

    public function index(Teacher $teacher)
{
    return response()->json([
        'Time Tables' => $teacher->timetable
    ]);
} 

    public function store(Request $request)
    {
        $data= $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date',
        ]);
        
        $timetable = Timetable::create($data);
        return response([
            'message'=> 'success',
        ]);
    }

    public function show(Timetable $timetable)
    {
        $data = $request->validate(['teacher_id'=> 'required|exists:teachers,id',]);
        $latestTimetable = Timetable::latest()->first();
        if (!$latestTimetable) {
            return response()->json([
                'message' => 'No timetables found in the system.',
                'lectures' => []
            ], 404);
        }
        $lectures = Lecture::where('teacher_id', $validated['teacher_id'])
                           ->where('timetable_id', $latestTimetable->id)
                           ->get(); 
        return response()->json([
            'message' => 'Lectures for teacher found in the latest timetable.',
            'time table' =>$latestTimetable,
            'lectures' => $lectures
        ]);
    }

    public function update(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date',
        ]);
    
        $timetable->update($data);
    
        return response([
            'message'=>'updated',
        ]);
    }
    
}
