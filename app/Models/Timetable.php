<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Timetable extends Model
{
    use ScopedByCampus, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'working_days' => 'array',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'published_at' => 'datetime',
        'default_period_duration' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function timetableSubjects(): HasMany
    {
        return $this->hasMany(TimetableSubject::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
