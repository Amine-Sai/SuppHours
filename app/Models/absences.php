<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class absences extends Model
{
    protected $fillable=['teacher_id', 'justified','lecture_id'];
    


    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function lecture(){
        return $this->belongsTo(lecture::class);
    }
}
