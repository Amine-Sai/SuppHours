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
        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:teacher,email',
            'password' => 'required|string|min:8'
        ]);
        
        $teacher = teacher::create($request->all());
        return response()->json($teacher, 201);
    }

    public function show(teacher $teacher)
    {
        return response()->json($teacher);
    }

    public function update(Request $request, teacher $teacher)
    {
        $request->validate([
            'fullName' => 'sometimes|string|max:255',
        ]);

        $teacher->update($request->all());
        return response()->json($teacher);
    }

    public function destroy(teacher $teacher)
    {
        $teacher->delete();
        return response()->json(null, 204);
    }
}
