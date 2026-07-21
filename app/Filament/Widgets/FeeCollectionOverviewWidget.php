<?php

namespace App\Filament\Widgets;

use App\Models\StudentFeeAccount;
use App\Models\Payment;
use App\Models\StudentVoucher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class FeeCollectionOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            if (!Schema::hasTable('student_fee_accounts') || !Schema::hasTable('payments') || !Schema::hasTable('student_vouchers')) {
                return $this->getEmptyStats();
            }

            $user = filament()->auth()->user();
            $isSuper = false;

            if ($user) {
                $isSuper = $user->email === 'admin@admin.com' ||
                    $user->campus_id === null ||
                    filament()->getCurrentPanel()?->getId() === 'admin';
            }

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

            $outstanding = (float) $accountQuery->sum('balance');
            $collectedToday = (float) $paymentQuery->clone()->whereDate('payment_date', now()->toDateString())->sum('amount');
            $collectedMonth = (float) $paymentQuery->clone()->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
            $overdue = (float) $voucherQuery->where('due_date', '<', now()->toDateString())->whereNotIn('status', ['paid', 'waived', 'cancelled'])->sum('balance');

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
        } catch (\Throwable $e) {
            return $this->getEmptyStats();
        }
    }

    private function getEmptyStats(): array
    {
        return [
            Stat::make('Collected Today', 'PKR 0.00')
                ->description('Total fees paid today')
                ->color('success'),
            Stat::make('Collected This Month', 'PKR 0.00')
                ->description('Total fees paid this month')
                ->color('success'),
            Stat::make('Outstanding Balance', 'PKR 0.00')
                ->description('Remaining contractual dues')
                ->color('warning'),
            Stat::make('Total Overdue Dues', 'PKR 0.00')
                ->description('Vouchers past due date')
                ->color('danger'),
        ];
    }
}
