<?php

namespace App\Http\Controllers;

use App\Models\teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return response()->json(teacher::all());
    }

    public function store(Request $request)
    {
        $data= $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'isVacataire'=>'required',
        ]);
        
        $teacher = Teacher::create($data);
        return response([
            'teacher'=> $teacher,
        ]);
    }

    public function show(teacher $teacher)
    {
        return response()->json($teacher);
    }

    public function update(Request $request, Teacher $teacher)
    {
        // Validate the incoming data, ensuring current email is excluded from uniqueness check
        $data = $request->validate([
            'isVacataire'=>'required',
            'fullName' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:teachers,email,' . $teacher->id,
        ]);
    
        // Update the teacher with validated data
        $teacher->update($data);
    
        // Return the updated teacher as a JSON response
        return response([
            'message'=>'updated',
            'teacher'=>$teacher
        ]);
    }
    
    public function destroy(teacher $teacher)
    {
        $teacher->delete();
        return response([
            'message'=>'deleted'
        ]);
        
    }
}
