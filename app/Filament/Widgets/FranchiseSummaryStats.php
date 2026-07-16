<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Franchisor;
use App\Models\FranchisorStudentPayment;
use App\Models\Admission;

class FranchiseSummaryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSeats = Admission::whereNotNull('franchisor_id')->count();
        
        $totalDues = FranchisorStudentPayment::sum('total_amount');
        $totalPaid = FranchisorStudentPayment::sum('paid_amount');
        $totalBalance = $totalDues - $totalPaid;

        return [
            Stat::make('Total Franchise Seats', $totalSeats)
                ->description('Registered student seats')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Contracted Revenue', 'PKR ' . number_format($totalDues, 2))
                ->description('Total contractual fees')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make('Verified Collected Payments', 'PKR ' . number_format($totalPaid, 2))
                ->description('Confirmed seat payments')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Outstanding Dues', 'PKR ' . number_format($totalBalance, 2))
                ->description('Unpaid franchise balance')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBalance > 0 ? 'danger' : 'success'),
        ];
    }
}
