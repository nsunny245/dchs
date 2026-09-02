<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class StudentIdCard extends Model
{
    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'last_printed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StudentIdCard $card): void {
            $card->qr_token ??= Str::random(40);
            $card->barcode_value ??= $card->card_number;
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
