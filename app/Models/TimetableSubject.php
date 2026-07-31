<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableSubject extends Model
{
    protected $guarded = [];

    protected $casts = [
        'required_periods_per_week' => 'integer',
        'scheduled_periods' => 'integer',
        'is_mandatory' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function defaultTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'default_teacher_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class);
    }
}
