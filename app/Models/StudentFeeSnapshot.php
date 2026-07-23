<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeSnapshot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'fee_structure_data' => 'array',
        'installment_schedule' => 'array',
        'concession_approval' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }
}
