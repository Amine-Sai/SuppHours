<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable=['startDate','teacher_id'];
    protected $casts = ['days' => 'array'];
    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function days(){
        return $this->hasMany(Day::class);
    }
}
