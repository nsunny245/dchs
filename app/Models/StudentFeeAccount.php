<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'original_fee' => 'decimal:2',
        'concession_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'opening_paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'opening_balance_recorded_at' => 'datetime',
    ];

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
