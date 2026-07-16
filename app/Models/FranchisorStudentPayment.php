<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FranchisorStudentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchisor_id',
        'admission_id',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function franchisor(): BelongsTo
    {
        return $this->belongsTo(Franchisor::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FranchisorPaymentInstallment::class);
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->installments()->where('status', 'paid')->sum('amount');
        $this->paid_amount = $totalPaid;

        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }
        $this->save();
    }
}
