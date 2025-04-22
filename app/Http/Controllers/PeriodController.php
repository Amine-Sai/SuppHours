<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /**
     * Display a listing of the periods.
     */
    public function index()
    {
        return response()->json(Period::with('teacher', 'holidays')->get());
    }

    /**
     * Store a newly created period.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $period = Period::create($validated);
        return response()->json($period, 201);
    }

    /**
     * Display the specified period.
     */
    public function show(Period $period)
    {
        return response()->json($period->load('teacher', 'holidays'));
    }

    /**
     * Update the specified period.
     */
    public function update(Request $request, Period $period)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $period->update($validated);
        return response()->json($period);
    }

    /**
     * Remove the specified period.
     */
    public function destroy(Period $period)
    {
        $period->delete();
        return response()->json(['message' => 'Period deleted successfully']);
    }
}
