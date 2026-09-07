<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FeeVoucher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'issue_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'scholarship_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (empty($voucher->uuid)) {
                $voucher->uuid = (string) Str::uuid();
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeAccount(): BelongsTo
    {
        return $this->belongsTo(StudentFeeAccount::class, 'student_fee_account_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(StudentInstallment::class, 'installment_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeVoucherItem::class, 'fee_voucher_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'fee_voucher_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'fee_voucher_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(FeeVoucherAudit::class, 'fee_voucher_id');
    }

    public function isAdmissionTuitionInstallment(): bool
    {
        if ($this->voucher_type !== 'monthly_installment') {
            return false;
        }

        $component = data_get($this->metadata, 'fee_component');
        if ($component !== null) {
            return $component === 'tuition_installment';
        }

        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with('feeHead')->get();

        if ($items->contains(fn (FeeVoucherItem $item): bool =>
            $item->feeHead?->code === 'TUITION_REC' || $item->feeHead?->category === 'tuition'
        )) {
            return true;
        }

        if ($items->contains(fn (FeeVoucherItem $item): bool => $item->fee_head_id !== null)) {
            return false;
        }

        return preg_match('/\b(tuition|installment)\b/i', (string) $this->title) === 1
            && preg_match('/\b(exam|examination|miscellaneous|transport|hostel|uniform|library)\b/i', (string) $this->title) !== 1;
    }
}
