<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeAccount extends Model
{
    protected $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(FeeVoucher::class, 'student_fee_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'student_fee_account_id');
    }
}
