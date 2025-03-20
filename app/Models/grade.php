<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable=['teacher_id','value','perHour','startDate'];

    function teacher() {
        return $this->belongsTo(Teacher::class);        
    }
}