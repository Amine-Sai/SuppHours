<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{

    protected $fillale=['type','state','day_id'];

    function day() : Returntype {
        $this->belongsTo(Day::class);
    }

}
