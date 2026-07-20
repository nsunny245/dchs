<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Models\StudentFeeAccount;
use App\Models\Payment;
use App\Models\StudentVoucher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeeCollectionOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = filament()->auth()->user();
        $isSuper = $user && (
            $user->email === 'admin@admin.com' ||
            $user->hasRole('Super Admin') ||
            $user->campus_id === null ||
            filament()->getCurrentPanel()?->getId() === 'admin'
        );
        $campusId = $user?->campus_id;

        // Scoped queries
        $accountQuery = StudentFeeAccount::query();
        $paymentQuery = Payment::query();
        $voucherQuery = StudentVoucher::query();

        if (!$isSuper && $campusId) {
            $accountQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
            $paymentQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
            $voucherQuery->whereHas('student', fn($q) => $q->where('campus_id', $campusId));
        }

        $outstanding = $accountQuery->sum('balance');
        $collectedToday = $paymentQuery->clone()->whereDate('payment_date', now()->toDateString())->sum('amount');
        $collectedMonth = $paymentQuery->clone()->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
        $overdue = $voucherQuery->where('due_date', '<', now()->toDateString())->whereNotIn('status', ['paid', 'waived', 'cancelled'])->sum('balance');

        return [
            Stat::make('Collected Today', 'PKR ' . number_format($collectedToday, 2))
                ->description('Total fees paid today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Collected This Month', 'PKR ' . number_format($collectedMonth, 2))
                ->description('Total fees paid this month')
                ->color('success'),
            Stat::make('Outstanding Balance', 'PKR ' . number_format($outstanding, 2))
                ->description('Remaining contractual dues')
                ->color('warning'),
            Stat::make('Total Overdue Dues', 'PKR ' . number_format($overdue, 2))
                ->description('Vouchers past due date')
                ->color('danger'),
        ];
    }
}
