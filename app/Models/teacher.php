<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable=['fullName'];
    protected $casts = ['grades' => 'array'];

    function grades(){
        return $this->hasMany(Grade::class);
    }

    function timeTable(){
        return $this->hasMany(TimeTable::class);
    }

}
