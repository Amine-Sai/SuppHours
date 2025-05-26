<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable=['teacher_id','startDate', 'endDate'];

    protected $hidden=['teacher_id',];

    public function holidays(){
        return $this->hasMany(Holidays::class);
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class);
    }
}

