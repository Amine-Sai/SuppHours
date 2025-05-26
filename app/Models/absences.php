<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Absence",
 *     title="Absence",
 *     type="object",
 *     required={"date", "teacher_id", "lecture_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="date", type="string", format="date", example="2024-05-01"),
 *     @OA\Property(property="justified", type="boolean", example=true),
 *     @OA\Property(property="start", type="string", example="08:00"),
 *     @OA\Property(property="end", type="string", example="10:00"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-01T12:10:00Z")
 * )
 */
class Absence extends Model
{
    protected $fillable = ['date', 'justified', 'teacher_id', 'lecture_id', 'start', 'end'];
    
    protected $hidden = ['teacher_id', 'lecture_id'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
