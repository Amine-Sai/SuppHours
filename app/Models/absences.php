<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class absences extends Model
{
    protected $fillable=['date', 'justified', 'teacher_id','lecture_id','start', 'end'];
    
    protected $hidden=['teacher_id','lecture_id'];

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function lecture(){
        return $this->belongsTo(lecture::class);
    }
}
