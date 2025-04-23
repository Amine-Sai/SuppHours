<?php
namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodController extends Controller
{
    /**
     * Store a new period (en general mechi cond teacher 1 brk)
     */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);

        // check for overlapping periods
        $overlappingPeriod = Period::where('teacher_id', $request->teacher_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('startDate', [$request->startDate, $request->endDate])
                      ->orWhereBetween('endDate', [$request->startDate, $request->endDate])
                      ->orWhere(function($query) use ($request) {
                          $query->where('startDate', '<=', $request->startDate)
                                ->where('endDate', '>=', $request->endDate);
                      });
            })->first();

        if ($overlappingPeriod) {
            return response()->json([
                'message' => 'Period overlaps with existing period (ID: ' . $overlappingPeriod->id . ')',
                'conflict' => $overlappingPeriod
            ], 409);
        }

        $period = Period::create([
            'teacher_id' => $request->teacher_id,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        return response()->json($period, 201);
    }


    // supp hours history (provide start and end date)

    public function history(Teacher $teacher)
    {

        $holidays = Holidays::all();
        $startPeriod = Carbon::parse($teacher->period->startDate);
        $endPeriod = Carbon::parse($teacher->period->endDate);
        foreach ($holidays as $holiday) {
            $currentDate = Carbon::parse($holiday->startDate);
            $duration = $holiday->duration;
            
            if ($currentDate->between($startPeriod, $endPeriod)) {
                for ($i = 0; $i < $duration; $i++) {
                    $dayName = Carbon::parse($currentDate)->format('l');
    
                    $lectures = $teacher->lectures()->where('day', $dayName)->get();
    
                    foreach ($lectures as $lecture) {
                        Absence::create([
                            'teacher_id' => $teacher->id,
                            'lecture_id' => $lecture->id,
                            'date'       => $currentDate,
                            'start'      => $lecture->start,
                            'end'        => $lecture->end,
                            'justified'  => false,
                        ]);
                    }
    
                    $currentDate = Carbon::parse($currentDate)->addDay()->toDateString();
                }
            }
        }

        //new part

        $lectureHistory = [];

$current = $startPeriod->copy();
$absences = Absence::where('teacher_id', $teacher->id)->exists();
foreach ($absences as $absence) {
while ($current->lte($endPeriod)) {
    $dayName = $current->format('l');

    // Get all lectures for that day
    $lectures = $teacher->lectures()->where('day', $dayName)->get();

    if ($absence->date!==$current||$absences->isEmpty()) { 
        foreach ($lectures as $lecture) {
         //ida makanch absent fnhar hadak wla khlaso les absence yzid sway3 t3 nhar hadak      
           
                
                    $lectureHistory[] = [
                        'date'       => $current->toDateString(),
                        'day'        => $dayName,
                        'lecture_id' => $lecture->id,
                        'start'      => $lecture->start,
                        'end'        => $lecture->end,
                    ];
                
            }
        
    }else{

        //ida kayna absence fnhar hadak y loop 3la lectures 
        foreach ($lectures as $lecture) {
            //test en cas absences khlaso 9bl la date hadik
            if (!$absences->isEmpty()) {
                if ($absence->lecture_id===lecture_id){
                    $absences->shift();
                }
            }
            else{
            //ida mkanch absent yzidha
                $lectureHistory[] = [
                    'date'       => $current->toDateString(),
                    'day'        => $dayName,
                    'lecture_id' => $lecture->id,
                    'start'      => $lecture->start,
                    'end'        => $lecture->end,
                ];
            
            }
                 
        }
    }
    

    $current->addDay();
 }
    }
    return response()->json([
    'lectures' => $lectureHistory
    ]);

        
 }

    /**
     * Calculate supplementary hours for a period
     */
    public function show($periodId)
    {
        $period = Period::findOrFail($periodId);
        $teacher = $period->teacher;
    
        // Get all grade assignments for this teacher during the period
        $gradeAssignments = json_decode($teacher->grades, true) ?? [];
        
        // If no grade assignments, return empty result
        if (empty($gradeAssignments)) {
            return response()->json([
                'message' => 'No grade assignments found for this teacher during the period'
            ], 404);
        }
    
        // Sort grade assignments by start_date
        usort($gradeAssignments, function($a, $b) {
            return strtotime($a['start_date']) <=> strtotime($b['start_date']);
        });
    
        $result = [];
        $currentStart = Carbon::parse($period->startDate);
        $periodEnd = Carbon::parse($period->endDate);
    
        foreach ($gradeAssignments as $index => $assignment) {
            $gradeStart = Carbon::parse($assignment['start_date']);
            $grade = Grade::find($assignment['grade_id']);
            
            // Skip if grade assignment starts after period end
            if ($gradeStart->gt($periodEnd)) {
                continue;
            }
    
            // If this is not the first assignment and currentStart is before this grade starts
            if ($index > 0 && $currentStart->lt($gradeStart)) {
                $subPeriod = $this->createSubPeriod(
                    $teacher,
                    $currentStart,
                    $gradeStart->copy()->subDay(),
                    Grade::find($gradeAssignments[$index-1]['grade_id']),
                    $period
                );
                $result[] = $subPeriod;
            }
    
            // Set new current start date
            $currentStart = $gradeStart->gt($currentStart) ? $gradeStart : $currentStart;
        }
    
        // Add the final sub-period from last grade change to period end
        if ($currentStart->lt($periodEnd)) {
            $lastAssignment = end($gradeAssignments);
            $subPeriod = $this->createSubPeriod(
                $teacher,
                $currentStart,
                $periodEnd,
                Grade::find($lastAssignment['grade_id']),
                $period
            );
            $result[] = $subPeriod;
        }
    
        return response()->json($result);
    }
    
    private function createSubPeriod($teacher, $startDate, $endDate, $grade, $period)
    {
        // Create temporary period for calculation
        $tempPeriod = new Period([
            'teacher_id' => $teacher->id,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    
        $absenceController = new AbsenceController();
        $history = $absenceController->history($teacher, $tempPeriod);
    
        return [
            'period_id' => $period->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'grade' => $grade,
            'supplementary_hours' => $this->calculateTotalHours($history),
            'details' => $history
        ];
    }
    
    private function calculateTotalHours($history)
    {
        $totalMinutes = 0;
        
        if (isset($history->original['lectures'])) {
            foreach ($history->original['lectures'] as $lecture) {
                $start = Carbon::parse($lecture['start']);
                $end = Carbon::parse($lecture['end']);
                $totalMinutes += $end->diffInMinutes($start);
            }
        }
        
        return $totalMinutes / 60; // Convert to hours
    }


    /**
     * Display the specified period
    public function show(Period $period)
    {
        return response()->json($period);
    }
     */

    /**
     * Update the specified period
     */
    public function update(Request $request, Period $period)
    {
        $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after:startDate',
        ]);

        // Check for overlapping periods if dates are being changed
        if ($request->has('startDate') || $request->has('endDate')) {
            $startDate = $request->has('startDate') ? $request->startDate : $period->startDate;
            $endDate = $request->has('endDate') ? $request->endDate : $period->endDate;

            $overlappingPeriod = Period::where('teacher_id', $period->teacher_id)
                ->where('id', '!=', $period->id)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('startDate', [$startDate, $endDate])
                          ->orWhereBetween('endDate', [$startDate, $endDate])
                          ->orWhere(function($query) use ($startDate, $endDate) {
                              $query->where('startDate', '<=', $startDate)
                                    ->where('endDate', '>=', $endDate);
                          });
                })->first();

            if ($overlappingPeriod) {
                return response()->json([
                    'message' => 'Period overlaps with existing period (ID: ' . $overlappingPeriod->id . ')',
                    'conflict' => $overlappingPeriod
                ], 409);
            }
        }

        $period->update($request->all());

        return response()->json($period);
    }

    /**
     * Remove the specified period
     */
    public function destroy(Period $period)
    {
        $period->delete();
        return response()->json(['message' => 'Period deleted successfully']);
    }
}









/* 
public function calculateHours($periodId)
{
    $period = Period::findOrFail($periodId);
    $teacher = $period->teacher;
    $startDate = Carbon::parse($period->startDate);
    $endDate = Carbon::parse($period->endDate);

    // Get the teacher's grades
    $grades = $teacher->grades ?? [];
    if (is_string($grades)) {
        $grades = json_decode($grades, true) ?? [];
    }
    
    // If no grades, return empty result
    if (empty($grades)) {
        return response()->json([]);
    }
    
    // Sort grades by start_date
    usort($grades, function($a, $b) {
        return strtotime($a['start_date']) - strtotime($b['start_date']);
    });
    
    // Find grade segments within this period
    $gradeSegments = [];
    
    // For each grade, determine when it was active during the period
    for ($i = 0; $i < count($grades); $i++) {
        $gradeStartDate = Carbon::parse($grades[$i]['start_date']);
        
        // Skip this grade if it starts after the period ends
        if ($gradeStartDate->greaterThan($endDate)) {
            continue;
        }
        
        // Determine when this grade's effect ends
        // It's either the start of the next grade or the end of the period
        $gradeEndDate = $endDate->copy();
        if (isset($grades[$i + 1])) {
            $nextGradeStartDate = Carbon::parse($grades[$i + 1]['start_date']);
            if ($nextGradeStartDate->lessThan($endDate)) {
                $gradeEndDate = $nextGradeStartDate->copy()->subDay();
            }
        }
        
        // Skip if this grade was completely before the period
        if ($gradeEndDate->lessThan($startDate)) {
            continue;
        }
        
        // Calculate effective dates within the period
        $effectiveStartDate = max($startDate, $gradeStartDate);
        $effectiveEndDate = min($endDate, $gradeEndDate);
        
        $gradeSegments[] = [
            'grade_id' => $grades[$i]['grade_id'],
            'period_start_date' => $effectiveStartDate->toDateString(),
            'period_end_date' => $effectiveEndDate->toDateString()
        ];
    }
    
    // Calculate supplementary hours for each grade segment
    $result = [];
    
    foreach ($gradeSegments as $segment) {
        // Create temporary period for the history calculation
        $tempStartDate = $period->startDate;
        $tempEndDate = $period->endDate;
        
        $period->startDate = $segment['period_start_date'];
        $period->endDate = $segment['period_end_date'];
        
        // Get lecture history for this segment
        $history = $this->history($teacher);
        
        // Calculate hours
        $hours = $this->calculateTotalHours($history);
        
        // Get grade details
        $grade = Grade::findOrFail($segment['grade_id']);
        
        // Add to result
        $result[] = [
            'grade_id' => $segment['grade_id'],
            'grade_name' => $grade->name,
            'grade_value' => $grade->value,
            'start_date' => $segment['period_start_date'],
            'end_date' => $segment['period_end_date'],
            'supplementary_hours' => $hours
        ];
        
        // Restore original period dates
        $period->startDate = $tempStartDate;
        $period->endDate = $tempEndDate;
    }
    
    return response()->json($result);
} */
