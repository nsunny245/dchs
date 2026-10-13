<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocationRegistration extends Model
{
    use ScopedByCampus;

    protected $fillable = [
        'campus_id',
        'academic_session_id',
        'course_id',
        'student_name',
        'attendee_name',
        'attendee_relation',
        'attendee_cnic',
        'status',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
