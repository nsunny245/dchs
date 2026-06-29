<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\FeePayment;
use App\Models\Expense;

class FinancialSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Finance');
    }

    protected function getStats(): array
    {
        $totalPaidFee = FeePayment::where('status', 'paid')->sum('amount');
        $totalPendingFee = FeePayment::whereIn('status', ['unpaid', 'overdue', 'partial'])->sum('amount');
        $totalExpenses = Expense::sum('amount');
        $netEarnings = $totalPaidFee - $totalExpenses;

        return [
            Stat::make('Total Fee Revenue Collected', 'PKR ' . number_format($totalPaidFee, 2))
                ->description('Received fee payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Total Pending Fee Dues', 'PKR ' . number_format($totalPendingFee, 2))
                ->description('Outstanding student fees')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make('Total College Expenses', 'PKR ' . number_format($totalExpenses, 2))
                ->description('Across all campuses & head office')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Net Net Earnings (Profit/Loss)', 'PKR ' . number_format($netEarnings, 2))
                ->description($netEarnings >= 0 ? 'Positive net cash flow' : 'Deficit')
                ->descriptionIcon($netEarnings >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netEarnings >= 0 ? 'success' : 'danger'),
        ];
    }
}
