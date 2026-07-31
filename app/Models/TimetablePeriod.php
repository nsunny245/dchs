<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'campus_id',
        'name',
        'start_time',
        'end_time',
        'sort_order',
        'is_break',
        'is_active',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
