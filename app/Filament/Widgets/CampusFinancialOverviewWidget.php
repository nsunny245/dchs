<?php

namespace App\Filament\Widgets;

use App\Models\Campus;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CampusFinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = filament()->auth()->user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('Super Admin') || $user->hasRole('Campus Principal') || $user->hasRole('Finance');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = filament()->auth()->user();
                return $user->hasRole('Super Admin')
                    ? Campus::query()
                    : Campus::query()->where('id', $user->campus_id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Campus Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('collected_fee')
                    ->label('Collected Fee')
                    ->state(fn (Campus $record) => FeePayment::whereHas('student', fn ($q) => $q->where('campus_id', $record->id))
                        ->sum('amount'))
                    ->money('PKR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('pending_fee')
                    ->label('Pending Fee')
                    ->state(fn (Campus $record) => FeeVoucher::where('campus_id', $record->id)
                        ->whereNotIn('status', ['paid', 'cancelled', 'void'])
                        ->sum('balance_amount'))
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('expenses')
                    ->label('Total Expenses')
                    ->state(fn (Campus $record) => Expense::where('campus_id', $record->id)->sum('college_revenue_amount'))
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignRight(),
            ])
            ->paginated(false);
    }
}
