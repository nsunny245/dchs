<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'course_id',
        'code',
        'name',
        'semester_year',
        'credit_hours',
        'weekly_periods',
        'subject_type',
        'default_class_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_hours' => 'integer',
        'weekly_periods' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
