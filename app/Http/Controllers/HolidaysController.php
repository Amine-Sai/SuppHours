<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    /**
     * Display a listing of the holiday.
     */
    public function index()
    {
        return response()->json(Holiday::all());
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'startDate' => 'required|date',
            'duration' => 'required|integer',
        ]);

        $holiday = Holiday::create($validated);
        return response()->json($holiday, 201);
    }

    /**
     * Display the specified holiday.
     */
    public function show(Holiday $holiday)
    {
        return response()->json($holiday);
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'startDate' => 'sometimes|date',
            'duration' => 'sometimes|integer',
        ]);

        $holiday->update($validated);
        return response()->json($holiday);
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return response()->json(['message' => 'Holiday deleted successfully']);
    }
}
