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
        $user = filament()->auth()->user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('Super Admin') || $user->hasRole('Campus Principal') || $user->hasRole('Finance');
    }

    protected function getStats(): array
    {
        $user = filament()->auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $totalPaidFee = $isSuperAdmin 
            ? FeePayment::sum('amount')
            : FeePayment::whereHas('student', fn ($q) => $q->where('campus_id', $user->campus_id))->sum('amount');
            
        $totalPendingFee = $isSuperAdmin 
            ? \App\Models\FeeVoucher::whereNotIn('status', ['paid', 'cancelled', 'void'])->sum('balance_amount')
            : \App\Models\FeeVoucher::where('campus_id', $user->campus_id)->whereNotIn('status', ['paid', 'cancelled', 'void'])->sum('balance_amount');
            
        $totalExpenses = $isSuperAdmin 
            ? Expense::sum('college_revenue_amount')
            : Expense::where('campus_id', $user->campus_id)->sum('college_revenue_amount');
            
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
                ->description($isSuperAdmin ? 'Across all campuses & head office' : 'For this campus location')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Net Net Earnings (Profit/Loss)', 'PKR ' . number_format($netEarnings, 2))
                ->description($netEarnings >= 0 ? 'Positive net cash flow' : 'Deficit')
                ->descriptionIcon($netEarnings >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netEarnings >= 0 ? 'success' : 'danger'),
        ];
    }
}
