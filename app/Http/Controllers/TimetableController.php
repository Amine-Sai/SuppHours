<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{

    public function index()
{
    $timetables = Timetable::all();

    return response()->json([
        'Time Tables' => $timetables
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
