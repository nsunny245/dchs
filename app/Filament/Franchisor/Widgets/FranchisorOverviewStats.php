<?php

namespace App\Filament\Franchisor\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Admission;
use App\Models\FranchisorStudentPayment;
use App\Models\FranchisorPaymentInstallment;
use App\Filament\Franchisor\Resources\FranchisorAdmissionResource;

class FranchisorOverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = filament()->auth()->user();
        if (!$user) return [];

        $franchisorId = $user->franchisor?->id;
        $type = FranchisorAdmissionResource::getFranchisorType();

        // 1. Total Students/Admissions Count
        $studentsQuery = Admission::query();
        if ($franchisorId) {
            $studentsQuery->where('franchisor_id', $franchisorId);
        } elseif ($type) {
            $studentsQuery->whereHas('franchisor', fn ($q) => $q->where('type', $type));
        }
        $totalStudents = $studentsQuery->count();

        // 2. Seat Payments Calculations
        $paymentsQuery = FranchisorStudentPayment::query();
        if ($franchisorId) {
            $paymentsQuery->where('franchisor_id', $franchisorId);
        } elseif ($type) {
            $paymentsQuery->whereHas('franchisor', fn ($q) => $q->where('type', $type));
        }

        $totalSeatCost = $paymentsQuery->sum('total_amount');
        $totalPaid = $paymentsQuery->sum('paid_amount');
        $totalBalance = $totalSeatCost - $totalPaid;

        // 3. Installments Verification Counts
        $installmentsQuery = FranchisorPaymentInstallment::query();
        if ($franchisorId) {
            $installmentsQuery->whereHas('payment', fn ($q) => $q->where('franchisor_id', $franchisorId));
        } elseif ($type) {
            $installmentsQuery->whereHas('payment.franchisor', fn ($q) => $q->where('type', $type));
        }

        $pendingVerificationCount = (clone $installmentsQuery)->where('status', 'pending')->count();

        return [
            Stat::make('Registered Student Seats', $totalStudents)
                ->description('Total student admissions')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Total Seat Fees Ledger', 'PKR ' . number_format($totalSeatCost, 2))
                ->description('Overall contractual dues')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make('Verified Paid Amount', 'PKR ' . number_format($totalPaid, 2))
                ->description('Confirmed transactions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Outstanding Balance Due', 'PKR ' . number_format($totalBalance, 2))
                ->description('Unpaid seat amounts')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBalance > 0 ? 'danger' : 'success'),
            Stat::make('Receipts Pending Approval', $pendingVerificationCount)
                ->description('Awaiting Admin review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingVerificationCount > 0 ? 'warning' : 'gray'),
        ];
    }
}
