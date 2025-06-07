<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Teacher;
use App\Models\Timetable;


use Illuminate\Http\Request;
use Carbon\Carbon;


class LectureController extends Controller
{
    // time overlap
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


    // public function index(Request $request)
    // {
    //     $data = $request->validate(['teacher_id'=> 'required|exists:teachers,id', ]);
    //     return response()->json(Lecture::where('id', $data->teacher_id));
    // }

 public function calculateAdditionalHours(Request $request)
{
    $data = $request->validate(['teacher_id' => 'required|exists:teachers,id']);
    $teacherId = $data['teacher_id'];
    $latestTimetable = Timetable::latest()->first();

    if (!$latestTimetable) {
        return response()->json([
            'message' => 'No timetables found in the system.',
            'lectures' => []
        ], 404);
    }

    $teacher = Teacher::where('id', $teacherId)->first();

    if (!$teacher) {
        return response()->json([
            'message' => 'Teacher not found.'
        ], 404);
    }

    $lectures = Lecture::where('teacher_id', $teacherId)
                       ->where('timetable_id', $latestTimetable->id)
                       ->orderBy('start') // Order by start time to process chronologically
                       ->get();

    if ($teacher->isVacateur) {
        foreach ($lectures as $lecture) {
            $lecture->type = "supp";
            $lecture->save();
        }
        return response()->json([
            'message' => 'All lectures set to supplementary for vacateur teacher.'
        ], 200);
    }

    $typeValues = [
        'cours' => 1.5,
        'td'    => 1,
        'tp'    => 0.75,
    ];

    $totalHours = 0;

    foreach ($lectures as $lecture) {
        $remainingLectureDuration = $this->calculateDuration($lecture->start, $lecture->end);

        if ($totalHours >= 9) {
            $lecture->type = "supp";
            $lecture->save();
            continue;
        }

        $availableSpace = 9 - $totalHours;
        $typeValue = $typeValues[$lecture->type];
        $possibleDuration = min($availableSpace, $typeValue * $remainingLectureDuration) / $typeValue; 

        if ($availableSpace >= $typeValue * $remainingLectureDuration) {
            $totalHours += $typeValue * $remainingLectureDuration;
        } else {
            $totalHours += $availableSpace;

            $firstPartEnd = Carbon::parse($lecture->start)->addHours($possibleDuration)->format('H:i');
            $secondPartStart = $firstPartEnd;
            $secondPartEnd = $lecture->end;

            $lecture->end = $firstPartEnd;
            $lecture->save();

            $newLecture = $lecture->replicate();
            $newLecture->start = $secondPartStart;
            $newLecture->end = $secondPartEnd;
            $newLecture->type = "supp";
            $newLecture->save();
        }
    }

    return response()->json([
        'message' => 'Additional hours calculated and lectures updated.'
    ], 200);
}

    public function store(Request $request)
    {
        $lecture = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'timetable_id' => 'required|exists:timetables,id',
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i',
            'subject' => 'required|string',
            'type' => 'required|in:cours,td,tp,supp',
            'state' => 'required|in:intern,extern',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);


        // $latestTimetable = Timetable::latest()->first();
        // if (!$latestTimetable) {
        //     return response()->json([
        //         'message' => 'No timetables found in the system, create a time table then try again.',
        //     ], 404);
        // };
        // $lecture[timetable_id] = $latestTimetable->id;
        
        
        // foreach ($request->input('lectures') as $index => $lecture) {
            // if ($lecture['end'] <= $lecture['start']) {
            //     return response()->json([
            //         'message' => "Lecture at index $index has an end time before or equal to start time."
            //     ], 422);
            // };
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
    public function getTeacherTimeTable(Teacher $teacher, Timetable $timetable)
    {
        $lectures = Lecture::where('teacher_id', $teacher->id)
                    ->where('timetable_id', $timetable->id)
                    ->get();

        return response()->json($lectures);
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



}
