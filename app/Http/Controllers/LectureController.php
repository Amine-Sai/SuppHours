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

    //calc duration
    private function calculateDuration($start, $end): float
        {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return $start->diffInMinutes($end) / 60;
        }


    public function index()
    {
        return response()->json(Lecture::all());
    }

    public function calculateAdditionalHours(Request $request){
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',  
        ]);

        $teacherId = $validated['teacher_id'];
        $teacher = Teacher:: where('id', $teacherId)->get();
        if ($teacher->isVacateur) {
            foreach ($lectures as $lecture) {
                $lecture->type = "supp";
                $lecture->save();
            }
            return response()->json(201);
        }
        $lectures = Lecture::where('teacher_id', $teacherId)->get();

        // valeurs d sway3
        $typeValues = [
            'cours' => 1.5,
            'td'    => 1,
            'tp'    => 0.75,
        ];

        $totalHours = 0;

        // cours -> td -> tp
        foreach (['cours', 'td', 'tp'] as $currentType) {
            foreach ($lectures as $lecture) {
                if ($lecture->type !== $currentType) {
                    continue;
                }

                $remainingDuration = $this->calculateDuration($lecture->start, $lecture->end);

                while ($remainingDuration > 0) {
                    $availableSpace = 9 - $totalHours;

                    if ($availableSpace <= 0) {
                        // all remaining duration goes to supplementary
                        $lecture->type = "supp";
                        $lecture->save();
                        $remainingDuration = 0;
                    } else {
                        $typeValue = $typeValues[$currentType];
                        $valuexduration = $typeValue * $remainingDuration;
                        $possibleDuration = min($availableSpace, $valuexduration);

                        if ($availableSpace >= $valuexduration) { 
                            $totalHours += $valuexduration;
                            $remainingDuration = 0;
                        } else {
                            // split the duration
                            $firstSplitDuration = $availableSpace / $typeValue;

                            $totalHours += $availableSpace;

                            // split into 2 parts, bdel end ta3 lwla based on remaining time
                            $newEnd = Carbon::parse($lecture->start)->addHours($firstSplitDuration);
                            $lecture->end = $newEnd->format('H:i');
                            $lecture->save();

                            // nos 2eme ta3 sceance
                            $secondHalf = $lecture->replicate();
                            $secondHalf->start = $lecture->end;
                            $secondHalf->end = $newEnd->addHours($remainingDuration - $firstSplitDuration)->format('H:i');
                            $secondHalf->type = "supp";
                            $secondHalf->save();

                            $remainingDuration = 0;
                        }
                    }
                }
            }
        }
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'lectures' => 'required|array|min:1',
            'teacher_id' => 'required|exists:teachers,id',
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i',
            'subject' => 'required|string',
            'type' => 'required|in:cours,td,tp,supp',
            'state' => 'required|in:intern,extern',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);
        // foreach ($request->input('lectures') as $index => $lecture) {
            if ($lecture['end'] <= $lecture['start']) {
                return response()->json([
                    'message' => "Lecture at index $index has an end time before or equal to start time."
                ], 422);
            };
        // }
        // $teacherId = $validated['teacher_id'];
        // $newLectures = $validated['lectures'];
    
        // check overlaps
        // $existingLectures = Lecture::where('teacher_id', $teacherId)
        //     ->get(['id', 'day', 'start', 'end']);
    
        // foreach ($newLectures as $i => $lectureA) {
        //     foreach ($newLectures as $j => $lectureB) {
        //         if ($i >= $j) continue; 
    
        //         if ($lectureA['day'] === $lectureB['day'] && 
        //             $this->timeRangesOverlap(
        //                 $lectureA['start'], $lectureA['end'],
        //                 $lectureB['start'], $lectureB['end']
        //             )) {
        //             return response()->json([
        //                 'message' => 'Conflict between new lectures',
        //                 'conflicts' => [
        //                     'lecture_1' => $lectureA,
        //                     'lecture_2' => $lectureB
        //                 ]
        //             ], 422);
        //         }
        //     }
        // }
    
        // // existing  & new
        // foreach ($newLectures as $newLecture) {
        //     foreach ($existingLectures as $existing) {
        //         if ($newLecture['day'] === $existing->day && 
        //             $this->timeRangesOverlap(
        //                 $newLecture['start'], $newLecture['end'],
        //                 $existing->start, $existing->end
        //             )) {
        //             return response()->json([
        //                 'message' => 'Lecture conflicts with existing schedule',
        //                 'conflicts' => [
        //                     'new_lecture' => $newLecture,
        //                     'existing_lecture' => $existing
        //                 ]
        //             ], 422);
        //         }
        //     }
        // }
    
        // foreach ($newLectures as $lecture) {
        //     $lecture['teacher_id'] = $teacherId; 
        //     $createdLectures[] = 
            Lecture::create($lecture);
        // }
    
        return response()->json(201);
    }
    

    public function show(Lecture $lecture)
    {
        return response()->json($lecture);
    }

    public function update(Request $request, Lecture $lecture)
{
    $validatedData = $request->validate([
        'start' => 'sometimes|date_format:H:i',
        'end' => 'sometimes|date_format:H:i|after:start',
        'subject' => 'sometimes|string',
        'type' => 'sometimes|in:cours,td,tp,supp',
        'state' => 'sometimes|in:intern,extern',
        'day' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
    ]);
        if ($validatedData['end'] <= $validatedData['start']) {
            return response()->json([
                'message' => 'End time must be after start time.'
            ], 422);
        }

    // $existingLectures = Lecture::where('teacher_id', $lecture->teacher_id)
    //     ->where('day', $lecture->day)
    //     ->where('id', '!=', $lecture->id) 
    //     ->get(['id', 'start', 'end', 'day']);

    // foreach ($existingLectures as $existing) {
    //     if ($this->timeRangesOverlap(
    //         $lecture->start, $lecture->end,
    //         $existing->start, $existing->end
    //     )) {
    //         return response()->json([
    //             'message' => 'Lecture conflicts with existing schedule',
    //             'conflicts' => [
    //                 'new_lecture' => [
    //                     'start' => $lecture->start,
    //                     'end' => $lecture->end,
    //                     'day' => $newDay,
    //                 ],
    //                 'existing_lecture' => $existing
    //             ]
    //         ], 422);
    //     }
    // }

    $lecture->update($validatedData);

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
