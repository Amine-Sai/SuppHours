<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable=['timeTable_id'];
    protected $casts = ['hours' => 'array'];

    function timeTable() {
        return $this->belongsTo(TimeTable::class);
    }

    function hours() {
        return $this->hasMany(Hour::class);        
    }
}
