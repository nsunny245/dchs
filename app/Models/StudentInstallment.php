<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentInstallment extends Model
{
    protected $guarded = [];

    protected $casts = ['due_date' => 'date', 'breakdown' => 'array'];

    public function feeAccount(): BelongsTo
    {
        return $this->belongsTo(StudentFeeAccount::class, 'student_fee_account_id');
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(FeeVoucher::class, 'installment_id');
    }
}
