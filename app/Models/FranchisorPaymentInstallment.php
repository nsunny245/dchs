<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FranchisorPaymentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchisor_student_payment_id',
        'title',
        'amount',
        'due_date',
        'status',
        'paid_date',
        'payment_method',
        'transaction_id',
        'receipt_path',
        'is_published',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FranchisorStudentPayment::class, 'franchisor_student_payment_id');
    }

    protected static function booted()
    {
        static::saved(function (FranchisorPaymentInstallment $installment) {
            $installment->payment?->updatePaymentStatus();
        });

        static::deleted(function (FranchisorPaymentInstallment $installment) {
            $installment->payment?->updatePaymentStatus();
        });
    }
}
