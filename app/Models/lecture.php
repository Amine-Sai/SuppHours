<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lecture extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=['start','end','teacher_id', 'subject', 'day', 'state', 'type'];

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }

    public function subject(){
        return $this->belongsTo(subject::class);
    }
}
