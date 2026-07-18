<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use App\Models\StudentVoucher;
use App\Models\Payment;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentFeeAccount extends ViewRecord
{
    protected static string $resource = FeeCollectionResource::class;

    protected static string $view = 'filament.resources.fee-collection-resource.pages.view-student-fee-account';

    public function getTitle(): string
    {
        return "Fee Account: " . $this->record->student->full_name;
    }

    protected function getViewData(): array
    {
        $vouchers = StudentVoucher::where('student_fee_account_id', $this->record->id)
            ->orderBy('sequence_no', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        $payments = Payment::where('student_fee_account_id', $this->record->id)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $nextVoucher = StudentVoucher::where('student_fee_account_id', $this->record->id)
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->orderBy('due_date', 'asc')
            ->first();

        $overdue = StudentVoucher::where('student_fee_account_id', $this->record->id)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->sum('balance');

        return [
            'vouchers' => $vouchers,
            'payments' => $payments,
            'nextVoucher' => $nextVoucher,
            'overdueAmount' => $overdue,
        ];
    }
}
