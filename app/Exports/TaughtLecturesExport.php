<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaughtLecturesExport implements WithMultipleSheets
{
    protected $organizedLectures;
    protected $teacherName;

    public function __construct(array $organizedLectures, string $teacherName)
    {
        $this->organizedLectures = $organizedLectures;
        $this->teacherName = $teacherName;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->organizedLectures as $month => $monthData) {
            $sheets[] = new MonthTaughtLecturesSheet($month, $monthData, $this->teacherName);
        }
        return $sheets;
    }
}

class MonthTaughtLecturesSheet implements FromCollection, WithTitle, ShouldAutoSize
{
    protected $month;
    protected $monthData;
    protected $teacherName;

    public function __construct(string $month, array $monthData, string $teacherName)
    {
        $this->month = $month;
        $this->monthData = $monthData;
        $this->teacherName = $teacherName;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        $data->push([$this->teacherName . ' - ' . $this->month]);
        $data->push(['Week Start', 'Week End', 'Date', 'Day', 'Start Time', 'End Time', 'Subject', 'Type']);

        if (isset($this->monthData['weeks'])) {
            foreach ($this->monthData['weeks'] as $week) {
                foreach ($week['days'] as $day) {
                    foreach ($day['lectures'] as $lecture) {
                        $data->push([
                            $week['start_date'],
                            $week['end_date'],
                            $day['date'],
                            Carbon::parse($lecture->day)->format('l'),
                            $lecture->start,
                            $lecture->end,
                            $lecture->subject,
                            $lecture->type,
                        ]);
                    }
                }
                $data->push([]); // Add an empty row between weeks
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->month;
    }
}