<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lecture extends Model
{
    protected $fillable=['start', 'end','teacher_id', 'subject_id', 'day', 'state', 'type'];

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function subject(){
        return $this->belongsTo(subject::class);
    }
}
