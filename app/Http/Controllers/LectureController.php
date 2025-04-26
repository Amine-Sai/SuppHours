<?php

namespace App\Http\Controllers;

use App\Models\lecture;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    public function index()
    {
        return response()->json(Lecture::all());
    }

    public function calculateAdditionalHours(Request $request)
    {
        $lectures = $request->input('lectures'); // Assuming lectures are passed in the request
        
        // Define the lecture type values and processing order
        $typeValues = [
            'cours' => 1.5,
            'td' => 1,
            'tp' => 0.75
        ];
        
        $totalHours = 0;
        $supplementaryHours = 0;
        $processedLectures = [];
        
        // Process lectures in the specified order: cours, td, tp
        foreach (['cours', 'td', 'tp'] as $currentType) {
            foreach ($lectures as $lecture) {
                if ($lecture['type'] !== $currentType) {
                    continue;
                }
                
                $remainingDuration = $lecture['duration'];
                
                while ($remainingDuration > 0) {
                    $availableSpace = 9 - $totalHours;
                    
                    if ($availableSpace <= 0) {
                        // All remaining duration goes to supplementary
                        $supplementaryHours += $remainingDuration;
                        $processedLectures[] = [
                            'original_lecture' => $lecture,
                            'duration' => $remainingDuration,
                            'type' => 'supp',
                            'is_supplementary' => true
                        ];
                        $remainingDuration = 0;
                    } else {
                        $typeValue = $typeValues[$currentType];
                        $possibleDuration = min($remainingDuration, $availableSpace / $typeValue * $lecture['duration']);
                        
                        if ($possibleDuration >= $remainingDuration) {
                            // Entire duration fits
                            $totalHours += $remainingDuration * $typeValue;
                            $processedLectures[] = [
                                'original_lecture' => $lecture,
                                'duration' => $remainingDuration,
                                'type' => $currentType,
                                'is_supplementary' => false
                            ];
                            $remainingDuration = 0;
                        } else {
                            // Split the duration
                            $totalHours += $possibleDuration * $typeValue;
                            $processedLectures[] = [
                                'original_lecture' => $lecture,
                                'duration' => $possibleDuration,
                                'type' => $currentType,
                                'is_supplementary' => false
                            ];
                            
                            $remainingDuration -= $possibleDuration;
                            
                            // The rest goes to supplementary
                            $supplementaryHours += $remainingDuration;
                            $processedLectures[] = [
                                'original_lecture' => $lecture,
                                'duration' => $remainingDuration,
                                'type' => 'supp',
                                'is_supplementary' => true
                            ];
                            $remainingDuration = 0;
                        }
                    }
                }
            }
        }
        
        // Process any remaining lectures that might not have been processed
        foreach ($lectures as $lecture) {
            if (!in_array($lecture['type'], ['cours', 'td', 'tp'])) {
                // These are automatically supplementary
                $supplementaryHours += $lecture['duration'];
                $processedLectures[] = [
                    'original_lecture' => $lecture,
                    'duration' => $lecture['duration'],
                    'type' => 'supp',
                    'is_supplementary' => true
                ];
            }
        }
        
        return response()->json([
            'total_hours' => $totalHours,
            'supplementary_hours' => $supplementaryHours,
            'processed_lectures' => $processedLectures,
            'message' => 'Calculation completed successfully'
        ]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'duration' => 'required|numeric',
            'subject_id' => 'required|string',
            'type' => 'required|in:cours,td,tp,supp',
            'state' => 'required|in:intern,extern',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'teacher_id' => 'required|exists:teachers,id',
        ]);
        //dd($data);
        $lecture = Lecture::create($data);
        
        return response()->json($lecture, 201);
    }

    public function show(Lecture $lecture)
    {
        return response()->json($lecture);
    }

    public function update(Request $request, Lecture $lecture)
    {
        $request->validate([
            'start' => 'sometimes|date_format:H:i',
            'end' => 'sometimes|date_format:H:i|after:start',
            'duration' => 'sometimes|numeric',
            'subject_id' => 'sometimes|string',
            'type' => 'sometimes|in:cours,td,tp,supp',
            'state' => 'sometimes|in:intern,extern',
            'day' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'teacher_id' => 'sometimes|exists:teachers,id',
        ]);

        $lecture->update($request->all());

        return response()->json($lecture);
    }

    public function destroy(Lecture $lecture)
    {
        $lecture->delete();
        return response()->json(['message' => 'Lecture deleted successfully']);
    }

    public function showTimeTable(Teacher $teacher)
{
    return response()->json([
        'lectures' => $teacher->lectures
    ]);
}

}
