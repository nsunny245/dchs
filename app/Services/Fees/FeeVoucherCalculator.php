<?php

namespace App\Services\Fees;

use App\Models\FeeVoucher;
use App\Models\FeeVoucherItem;

class FeeVoucherCalculator
{
    /**
     * Calculate voucher totals based on its items and additional fields.
     * Returns an array with exact decimal calculations.
     */
    public static function calculate(FeeVoucher $voucher): array
    {
        $items = $voucher->items;

        $subtotal = 0.00;
        $discountAmount = (float) $voucher->discount_amount;
        $scholarshipAmount = (float) $voucher->scholarship_amount;
        $fineAmount = (float) $voucher->fine_amount;
        $lateFeeAmount = (float) $voucher->late_fee_amount;
        $previousBalance = (float) $voucher->previous_balance;

        foreach ($items as $item) {
            $amt = (float) $item->amount;
            if ($item->feeHead?->is_discount || $item->adjustment_type === 'discount' || $item->adjustment_type === 'credit') {
                // If it's stored as positive in amount but is a discount, we track it
                if ($item->feeHead?->category === 'discount') {
                    $discountAmount += $amt;
                } elseif ($item->feeHead?->category === 'scholarship') {
                    $scholarshipAmount += $amt;
                } else {
                    $discountAmount += $amt;
                }
            } else {
                $subtotal += $amt;
            }
        }

        // Net payable calculation
        // subtotal + previous_balance + fine + late_fee - discount - scholarship
        $totalPayable = ($subtotal + $previousBalance + $fineAmount + $lateFeeAmount) - ($discountAmount + $scholarshipAmount);
        
        // Prevent negative total payable
        if ($totalPayable < 0) {
            $totalPayable = 0.00;
        }

        $paidAmount = (float) $voucher->payments()->where('status', 'paid')->sum('amount');
        $balanceAmount = $totalPayable - $paidAmount;
        if ($balanceAmount < 0) {
            $balanceAmount = 0.00;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'scholarship_amount' => round($scholarshipAmount, 2),
            'fine_amount' => round($fineAmount, 2),
            'late_fee_amount' => round($lateFeeAmount, 2),
            'previous_balance' => round($previousBalance, 2),
            'total_amount' => round($totalPayable, 2),
            'paid_amount' => round($paidAmount, 2),
            'balance_amount' => round($balanceAmount, 2),
        ];
    }
}
