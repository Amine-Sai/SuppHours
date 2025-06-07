<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Absences extends Model
{
    protected $fillable = ['date', 'justified', 'teacher_id', 'lecture_id'];
    

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
