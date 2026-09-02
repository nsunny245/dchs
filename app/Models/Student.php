<?php

namespace App\Models;

use App\Traits\ScopedByCampus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use ScopedByCampus;

    protected $table = 'students';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeAccount(): HasOne
    {
        return $this->hasOne(StudentFeeAccount::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function franchisor(): BelongsTo
    {
        return $this->belongsTo(Franchisor::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(StudentInstallment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(StudentLedgerEntry::class);
    }

    public function idCards(): HasMany
    {
        return $this->hasMany(StudentIdCard::class);
    }

    public function activeIdCard(): HasOne
    {
        return $this->hasOne(StudentIdCard::class)->where('status', 'active')->latestOfMany();
    }

    public function getStudentPhotoAttribute()
    {
        return $this->attributes['student_photo'] ?? $this->admission?->student_photo;
    }
}
