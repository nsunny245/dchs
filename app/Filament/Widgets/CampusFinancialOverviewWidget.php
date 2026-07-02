<?php

namespace App\Filament\Widgets;

use App\Models\Campus;
use App\Models\FeePayment;
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
        return auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Finance');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Campus::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Campus Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('collected_fee')
                    ->label('Collected Fee')
                    ->state(fn (Campus $record) => FeePayment::where('campus_id', $record->id)->where('status', 'paid')->sum('amount'))
                    ->money('PKR')
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pending_fee')
                    ->label('Pending Fee')
                    ->state(fn (Campus $record) => FeePayment::where('campus_id', $record->id)->whereIn('status', ['unpaid', 'overdue', 'partial'])->sum('amount'))
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenses')
                    ->label('Total Expenses')
                    ->state(fn (Campus $record) => Expense::where('campus_id', $record->id)->sum('amount'))
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignRight()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
