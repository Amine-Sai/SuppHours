<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\Lecture;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return response()->json(Teacher::all());
    }

    public function store(Request $request)
    {
        $data= $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'isVacateur'=>'required|boolean',
        ]);
        
        $teacher = Teacher::create($data);
        return response([
            'teacher'=> $teacher,
        ]);
    }

    public function show(Teacher $teacher)
    {
        return response()->json($teacher);
    }

    public function update(Request $request, Teacher $teacher)
    {
        // Validate the incoming data, ensuring current email is excluded from uniqueness check
        $data = $request->validate([
            'isVacateur'=>'required',
            'fullName' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:teachers,email,' . $teacher->id,
        ]);
    
        $teacher->update($data);
    
        return response([
            'message'=>'updated',
            'teacher'=>$teacher
        ]);
    }

public function getTimeTable (Teacher $teacher, Request $request){
    $timetableId = $request->query('timetable_id');
// dd('Reached getTimeTable');
    if (!$timetableId) {
        $latestTimetable = Timetable::latest()->first();
        // dd($latestTimetable->id);

        if ($latestTimetable) {
            $timetable = Lecture::where('teacher_id', $teacher->id)
                ->where('timetable_id', $latestTimetable->id)
                ->get();
                
            return response([
                'time_table' => $timetable
            ]);
        } else {
            return response([
                'message' => 'No timetables found.'
            ], 404);
        }
    } else {
        $request->validate([
            'timetable_id' => 'exists:timetables,id'
        ]);
        $timetable = Lecture::where('teacher_id', $teacher->id)
            ->where('timetable_id', $timetableId)
            ->get();
        return response([
            'time_table' => $timetable
        ]);
    }
}
    
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return response([
            'message'=>'deleted'
        ]);
        
    }
}
