<?php

namespace App\Http\Controllers;

use App\Models\lecture;
use Illuminate\Http\Request;



class LectureController extends Controller
{


    // time overlap
    use Carbon\Carbon;
    private function timeRangesOverlap($start1, $end1, $start2, $end2): bool
    {
        return Carbon::parse($start1)->lt(Carbon::parse($end2)) && 
               Carbon::parse($end1)->gt(Carbon::parse($start2));
    }


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
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'lectures' => 'required|array|min:1',
            'lectures.*.start' => 'required|date_format:H:i',
            'lectures.*.end' => 'required|date_format:H:i|after:lectures.*.start',
            'lectures.*.subject_id' => 'required|string',
            'lectures.*.type' => 'required|in:cours,td,tp,supp',
            'lectures.*.state' => 'required|in:intern,extern',
            'lectures.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
    
        $teacherId = $validated['teacher_id'];
        $newLectures = $validated['lectures'];
    
        $existingLectures = Lecture::where('teacher_id', $teacherId)
            ->get(['id', 'day', 'start', 'end', 'subject_id']);
    
        // check overlaps
        foreach ($newLectures as $i => $lectureA) {
            foreach ($newLectures as $j => $lectureB) {
                if ($i >= $j) continue; 
    
                if ($lectureA['day'] === $lectureB['day'] && 
                    $this->timeRangesOverlap(
                        $lectureA['start'], $lectureA['end'],
                        $lectureB['start'], $lectureB['end']
                    )) {
                    return response()->json([
                        'message' => 'Conflict between new lectures',
                        'conflicts' => [
                            'lecture_1' => $lectureA,
                            'lecture_2' => $lectureB
                        ]
                    ], 422);
                }
            }
        }
    
        // existing  & new
        foreach ($newLectures as $newLecture) {
            foreach ($existingLectures as $existing) {
                if ($newLecture['day'] === $existing->day && 
                    $this->timeRangesOverlap(
                        $newLecture['start'], $newLecture['end'],
                        $existing->start, $existing->end
                    )) {
                    return response()->json([
                        'message' => 'Lecture conflicts with existing schedule',
                        'conflicts' => [
                            'new_lecture' => $newLecture,
                            'existing_lecture' => $existing
                        ]
                    ], 422);
                }
            }
        }
    
        // Create all lectures if no conflicts
        $createdLectures = [];
        foreach ($newLectures as $lecture) {
            $lecture['teacher_id'] = $teacherId; 
            $createdLectures[] = Lecture::create($lecture);
        }
    
        return response()->json($createdLectures, 201);
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
