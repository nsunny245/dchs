<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
