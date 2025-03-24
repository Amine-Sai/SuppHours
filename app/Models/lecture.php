<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable=['start', 'end','teacher_id', 'subject_id', 'day', 'state', 'type'];
    protected $casts = ['days' => 'array'];
    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function days(){
        return $this->hasMany(Day::class);
    }
}
