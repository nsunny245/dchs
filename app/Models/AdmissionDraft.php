<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'last_saved_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }
}
