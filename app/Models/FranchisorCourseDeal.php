<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FranchisorCourseDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchisor_id',
        'course_id',
        'total_seats',
        'per_seat_cost',
    ];

    protected $casts = [
        'per_seat_cost' => 'decimal:2',
        'total_seats' => 'integer',
    ];

    public function franchisor(): BelongsTo
    {
        return $this->belongsTo(Franchisor::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
