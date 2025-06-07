<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lecture extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
    'teacher_id',
    'timetable_id',
    'start',
    'end',
    'subject',
    'type',
    'state',
    'day',
];

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }
    public function timetable(){
        return $this->belongsTo(Timetable::class);
    }



    // public function subject(){
    //     return $this->belongsTo(subject::class);
    // }
}
