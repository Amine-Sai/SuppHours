<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Teacher;
use App\Models\Absences;
use App\Models\Grade;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Carbon\Carbon;
// use Maatwebsite\Excel\Facades\Excel;

use App\Exports\TaughtLecturesExport;


class PeriodController extends Controller
{

    public function index()
    {
        return response()->json(Period::all());
    }
    private function timeRangesOverlap($start1, $end1, $start2, $end2): bool
    {
        return Carbon::parse($start1)->lt(Carbon::parse($end2)) &&
               Carbon::parse($end1)->gt(Carbon::parse($start2));
    }

    private function calculateDuration($start, $end): float
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return $start->diffInMinutes($end) / 60;
    }
    

    /**
     * Store a new period.
     */
    public function store(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
        ]);

        $overlappingPeriod = Period::where(function($query) use ($request) {
            $query->whereBetween('startDate', [$request->startDate, $request->endDate])
                  ->orWhereBetween('endDate', [$request->startDate, $request->endDate])
                  ->orWhere(function($q) use ($request) {
                      $q->where('startDate', '<=', $request->startDate)
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
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        return response()->json($period, 201);
    }


public function getRawTaughtLectures(Period $period, Teacher $teacher)
{
    $periodStartDate = Carbon::parse($period->startDate)->startOfDay();
    $periodEndDate = Carbon::parse($period->endDate)->endOfDay();

    $holidays = Holiday::whereBetween('startDate', [$periodStartDate, $periodEndDate])->get();
    $absences = Absences::where('teacher_id', $teacher->id)
                       ->whereBetween('date', [$periodStartDate, $periodEndDate])->get();

    $relevantTimetables = Timetable::where('startDate', '<=', $periodEndDate)
                               ->where('endDate', '>=', $periodStartDate)
                               ->pluck('id');

    // dd(['periodStartDate'=> $periodStartDate, 'periodEndDate'=> $periodEndDate]);
    $allLectures = Lecture::where('teacher_id', $teacher->id)
                           ->whereIn('timetable_id', $relevantTimetables)->get()->keyBy('day');

    $taughtLectures = collect();

    $currentDayIterator = $periodStartDate->copy();
    while ($currentDayIterator->lte($periodEndDate)) {
        $currentDayName = $currentDayIterator->format('l');
        $specificLectureDate = $currentDayIterator->toDateString();

        // dd([
        //     'currentDayName' => $currentDayName,
        //     'specificLectureDate' => $specificLectureDate,
        //     'allLecturesKeys' => array_keys($allLectures->toArray()),
        //     'allLecturesForDay' => $allLectures->get($currentDayName),
        // ]);

        if (isset($allLectures[$currentDayName])) {
            $lecture = $allLectures[$currentDayName];

            $isHoliday = false;
            foreach ($holidays as $holiday) {
                $holidayStart = Carbon::parse($holiday->startDate)->startOfDay();
                $holidayEnd = Carbon::parse($holiday->startDate)->addDays($holiday->duration - 1)->endOfDay();
                if (Carbon::parse($specificLectureDate)->between($holidayStart, $holidayEnd)) {
                    $isHoliday = true;
                    break;
                }
            }

            if (!$isHoliday) {
                $isAbsent = $absences->contains(function ($absence) use ($specificLectureDate, $lecture) {
                    $absenceDate = Carbon::parse($absence->date)->toDateString();
                    return $absenceDate == $specificLectureDate && $absence->lecture_id === $lecture->id;
                });

                if (!$isAbsent) {
                    $taughtLectureInstance = clone $lecture;
                    $taughtLectureInstance->taught_date = $specificLectureDate;
                    $taughtLectures->push($taughtLectureInstance);
                }
            }
        }

        $currentDayIterator->addDay();
    }

    return $taughtLectures;
}



public function show(Period $period, Teacher $teacher)
{
$gradeAssignments = $teacher->grades ?? [];
    if (empty($gradeAssignments)) {
        return response()->json([
            'message' => 'No grade assignments found for this teacher.'
        ], 400);
    }

    // Sort grade assignments by start date DESCENDING
    usort($gradeAssignments, function ($a, $b) {
        return strtotime($b['start_date']) <=> strtotime($a['start_date']);
    });

    

    $result = [];
    $periodStart = \Carbon\Carbon::parse($period->startDate);
    $periodEnd = \Carbon\Carbon::parse($period->endDate);
    // $segmentStartCursor = $periodStart->copy();

    foreach ($gradeAssignments as $index => $assignment) {
        $gradeStart = Carbon::parse($assignment['start_date']);
        
        if ($gradeStart->gt($periodEnd)) {
            continue;
        };

        $segmentEnd = $periodEnd->copy();
        if ( $gradeStart -> lte($periodStart)){
            $segmentStart = $periodStart;
            $grade = Grade::find($assignment['grade_id']);
            $tempInitialPeriod = new Period([
                'startDate' => $periodStart->toDateString(),
                'endDate' => $periodEnd->toDateString()
            ]);
            //get lectures for the period
            $taughtLectures = $this->getRawTaughtLectures($tempInitialPeriod, $teacher);
            $total_supp_hours= $this->calculateTotalHours($taughtLectures);
            $montant_totale = $total_supp_hours * $grade->value;
            $ss = $montant_totale * 0.09;
            $irg = $ss * 0.1;
            $montant_net = $montant_totale - $ss - $irg;
            $segmentData = [
                'period_id' => $period->id,
                'start_date' => $tempInitialPeriod->startDate
                // ->toDateString()
                ,
                'end_date' => $tempInitialPeriod->endDate
                // ->toDateString()
                ,
                'grade' => $grade->name,
                'prix unitaire' => $grade->value,
                'nombre des heurs' => $total_supp_hours,
                'montant totale' =>$montant_totale,
                'sécurité sociale' =>$ss,
                'montant net' => $montant_net,
                'irg' => $irg,

                'taught lectures' => $taughtLectures->toArray(),
            ];

            $result[] = $segmentData;
            
        }
        else{
            if (!isset($gradeAssignments[$index - 1])) return response()->json([
            'message' => 'Grades do not allign with period.'
        ], 400);
            $segmentStart = $gradeStart;
            $nextSegmentStart = $periodStart->copy();
            $nextSegmentEnd = $gradeStart->copy()->subDay();        
            $grade1 = Grade::find($gradeAssignments[$index+1]['grade_id']);
            $grade2 = Grade::find($assignment['grade_id']);
            $tempInitialPeriod1 = new Period([
                'startDate' => $nextSegmentStart->toDateString(),
                'endDate' => $nextSegmentEnd->toDateString()
            ]);
            $tempInitialPeriod2 = new Period([
                'startDate' => $segmentStart->toDateString(),
                'endDate' => $segmentEnd->toDateString()
            ]);
            // for 1st garade
        $taughtLectures = $this->getRawTaughtLectures($tempInitialPeriod1, $teacher);
        $total_supp_hours= $this->calculateTotalHours($taughtLectures);
        $montant_totale = $total_supp_hours * $grade->value;
        $ss = $montant_totale * 0.09;
        $irg = $ss * 0.1;
        $montant_net = $montant_totale - $ss - $irg;
        $segmentData = [
            'period_id' => $period->id,
            'start_date' => $tempInitialPeriod1->startDate->toDateString(),
            'end_date' => $tempInitialPeriod1->endDate->toDateString(),
            'grade' => $grade->name,
            'prix unitaire' => $grade1->value,
            'nombre des heurs' => $total_supp_hours,
            'montant totale' =>$montant_totale,
            'sécurité sociale' =>$ss,
            'montant net' => $montant_net,
            'irg' => $irg,

            'taught lectures' => $taughtLectures->toArray(),
        ];
        $result[] = $segmentData;
            // for 2nd grade
        $taughtLectures = $this->getRawTaughtLectures($tempInitialPeriod2, $teacher);
        $total_supp_hours= $this->calculateTotalHours($taughtLectures);
        $montant_totale = $total_supp_hours * $grade->value;
        $ss = $montant_totale * 0.09;
        $irg = $ss * 0.1;
        $montant_net = $montant_totale - $ss - $irg;
        $segmentData = [
            'period_id' => $period->id,
            'start_date' => $tempInitialPeriod2->startDate->toDateString(),
            'end_date' => $tempInitialPeriod2->endDate->toDateString(),
            'grade' => $grade2->name,
            'prix unitaire' => $grade2->value,
            'nombre des heurs' => $total_supp_hours,
            'montant totale' =>$montant_totale,
            'sécurité sociale' =>$ss,
            'montant net' => $montant_net,
            'irg' => $irg,

            'taught lectures' => $taughtLectures->toArray(),
        ];

        $result[] = $segmentData;
        }

    }
    return response()->json($result);
}

private function calculateTotalHours(\Illuminate\Support\Collection $lectures): float
{
    $totalHours = 0;
    foreach ($lectures as $lecture) {
        $totalHours += $this->calculateDuration($lecture->start, $lecture->end);
    }


    return $totalHours;
}



    public function update(Request $request, Period $period)
    {
        $request->validate([
            'startDate' => 'sometimes|date',
            'endDate' => 'sometimes|date|after:startDate',
        ]);

        if ($request->has('startDate') || $request->has('endDate')) {
            $startDate = $request->has('startDate') ? $request->startDate : $period->startDate;
            $endDate = $request->has('endDate') ? $request->endDate : $period->endDate;

            $overlappingPeriod = Period::where('id', '!=', $period->id)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('startDate', [$startDate, $endDate])
                          ->orWhereBetween('endDate', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('startDate', '<=', $startDate)
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






// private function organizeLecturesByMonthAndWeek(
//         \Illuminate\Support\Collection $lectures,
//         Carbon $periodStart,
//         Carbon $periodEnd
//     ): array {
//         $organizedLectures = [];
//         $currentMonthIterator = $periodStart->copy()->startOfMonth();

//         while ($currentMonthIterator->lte($periodEnd)) {
//             $monthKey = $currentMonthIterator->format('Y-m');
//             $monthData = [
//                 'month_name' => $currentMonthIterator->format('F Y'),
//                 'total_month_supp_hours' => 0,
//                 'weeks' => []
//             ];

//             $firstDayOfMonth = $currentMonthIterator->copy()->startOfMonth();
//             $firstSundayOfMonth = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
//             if ($firstSundayOfMonth->gt($firstDayOfMonth)) {
//                 $firstSundayOfMonth->subWeek();
//             }

//             for ($weekNumber = 0; $weekNumber < 6; $weekNumber++) {
//                 $weekStartDate = $firstSundayOfMonth->copy()->addWeeks($weekNumber);
//                 $weekEndDate = $weekStartDate->copy()->endOfWeek(Carbon::SATURDAY);

//                 if ($weekStartDate->gt($periodEnd)) {
//                     break;
//                 }
//                 if ($weekStartDate->month > $currentMonthIterator->month && $weekStartDate->year >= $currentMonthIterator->year) {
//                     if ($weekStartDate->startOfMonth()->gt($currentMonthIterator->copy()->endOfMonth())) {
//                         break;
//                     }
//                 }

//                 $weekData = [
//                     'week_number' => $weekNumber + 1, 
//                     'start_date' => $weekStartDate->toDateString(),
//                     'end_date' => $weekEndDate->toDateString(),
//                     'total_week_supp_hours' => 0,
//                     'days' => [],
//                 ];

//                 $currentDayInWeek = $weekStartDate->copy();
//                 while ($currentDayInWeek->lte($weekEndDate) && $currentDayInWeek->lte($periodEnd)) {
//                     if ($currentDayInWeek->month === $currentMonthIterator->month ||
//                         ($currentDayInWeek->month === $currentMonthIterator->copy()->addMonth()->month && $weekNumber === 0 && $currentDayInWeek->day <= 7)
//                     ) {
//                         $suppLecturesForDay = $lectures->filter(function ($lecture) use ($currentDayInWeek) {
//                             return Carbon::parse($lecture->taught_date)->isSameDay($currentDayInWeek) && $lecture->type === 'supp';
//                         })->values();

//                         $totalDaySuppHours = 0;
//                         foreach ($suppLecturesForDay as $lecture) {
//                             $totalDaySuppHours += $this->calculateDuration($lecture->start, $lecture->end);
//                         }

//                         if ($currentDayInWeek->between($periodStart, $periodEnd)) {
//                             $weekData['days'][] = [
//                                 'date' => $currentDayInWeek->toDateString(),
//                                 'day_name' => $currentDayInWeek->format('l'),
//                                 'total_day_supp_hours' => round($totalDaySuppHours, 2),
//                                 'supp_lectures' => $suppLecturesForDay->map(function($lecture) {
//                                     return [
//                                         'id' => $lecture->id,
//                                         'start' => $lecture->start,
//                                         'end' => $lecture->end,
//                                         'subject' => $lecture->subject,
//                                         'state' => $lecture->state,
//                                     ];
//                                 })->toArray(),
//                             ];
//                             $weekData['total_week_supp_hours'] += $totalDaySuppHours;
//                         }
//                     }
//                     $currentDayInWeek->addDay();
//                 }

//                 if (!empty($weekData['days'])) {
//                     $weekData['total_week_supp_hours'] = round($weekData['total_week_supp_hours'], 2);
//                     $monthData['weeks'][] = $weekData;
//                     $monthData['total_month_supp_hours'] += $weekData['total_week_supp_hours'];
//                 }
//             }
//             $monthData['total_month_supp_hours'] = round($monthData['total_month_supp_hours'], 2);
//             $organizedLectures[] = $monthData; // Add month data to the main array
//             $currentMonthIterator->addMonth();
//         }

//         return $organizedLectures;
//     }




    // public function getTaughtLecturesByMonth(Teacher $teacher, Period $period)
    // {
    //     $periodStartDate = Carbon::parse($period->startDate)->startOfDay();
    //     $periodEndDate = Carbon::parse($period->endDate)->endOfDay();

    //     $rawTaughtLectures = $this->getRawTaughtLectures($period, $teacher);

    //     $organizedLectures = $this->organizeLecturesByMonthAndWeek(
    //         $rawTaughtLectures,
    //         $periodStartDate,
    //         $periodEndDate
    //     );

    //     return Excel::download(
    //         new TaughtLecturesExport($organizedLectures, $teacher->fullName),
    //         'taught_lectures_' . $teacher->fullName . '_' . $period->startDate . '_to_' . $period->endDate . '.xlsx'
    //     );
    // }
