<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Timetable extends Model
{
    use HasFactory;
    protected $fillable=['startDate','endDate'];

    public function lectures()
{
    return $this->hasMany(Lecture::class);
}

}
