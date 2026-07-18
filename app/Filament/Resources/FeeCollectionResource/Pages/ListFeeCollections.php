<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use App\Models\StudentFeeAccount;
use App\Models\Payment;
use App\Models\StudentVoucher;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListFeeCollections extends ListRecords
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderWidgets(): array
    {
        $user = filament()->auth()->user();
        $isSuper = $user->hasRole('Super Admin');
        $campusId = $user->campus_id;

        // Scoped queries
        $accountQuery = StudentFeeAccount::query();
        $paymentQuery = Payment::query();
        $voucherQuery = StudentVoucher::query();

        if (!$isSuper) {
            $accountQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
            $paymentQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
            $voucherQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
        }

        $totalReceivable = $accountQuery->sum('net_payable');
        $outstanding = $accountQuery->sum('balance');
        $collectedToday = $paymentQuery->whereDate('payment_date', now()->toDateString())->sum('amount');
        $collectedMonth = $paymentQuery->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
        $overdue = $voucherQuery->where('due_date', '<', now()->toDateString())->whereNotIn('status', ['paid', 'waived', 'cancelled'])->sum('balance');

        return [
            \App\Filament\Resources\FeeCollectionResource\Pages\FeeCollectionOverviewWidget::class,
        ];
    }
}
