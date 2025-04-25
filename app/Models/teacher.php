<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Teacher extends Model
{
    use HasFactory;
    protected $fillable=['fullName','email'];
    protected $casts = ['grades' => 'array'];
    function grades(){
        return $this->hasMany(Grade::class);
    }

    public function absences()
    {
        return $this->hasMany(absences::class);
    }

    public function lectures()
{
    return $this->hasMany(Lecture::class);
}

    function timeTable(){
        return $this->hasMany(TimeTable::class);
    }

}
