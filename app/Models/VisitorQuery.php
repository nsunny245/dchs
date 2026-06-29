<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'visitor_name',
        'phone',
        'relation_to_student',
        'came_by',
        'desired_course_id',
        'status',
        'follow_up_date',
        'notes',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function desiredCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'desired_course_id');
    }
}
