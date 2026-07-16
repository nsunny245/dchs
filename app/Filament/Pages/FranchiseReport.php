<?php

namespace App\Filament\Pages;

use App\Models\Franchisor;
use App\Models\FranchisorStudentPayment;
use App\Models\Admission;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class FranchiseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Franchise Management';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.franchise-report';

    protected static ?string $title = 'Franchise Financial Reporting';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\FranchiseSummaryStats::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Franchisor::query())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('S.No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Franchise Institution')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbound' => 'success',
                        'outbound' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('total_seats')
                    ->label('Enrolled Seats')
                    ->state(fn (Franchisor $record) => Admission::where('franchisor_id', $record->id)->count())
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Dues')
                    ->state(fn (Franchisor $record) => FranchisorStudentPayment::where('franchisor_id', $record->id)->sum('total_amount'))
                    ->money('PKR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Total Paid')
                    ->state(fn (Franchisor $record) => FranchisorStudentPayment::where('franchisor_id', $record->id)->sum('paid_amount'))
                    ->money('PKR')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('total_balance')
                    ->label('Outstanding')
                    ->state(fn (Franchisor $record) => 
                        FranchisorStudentPayment::where('franchisor_id', $record->id)->sum('total_amount') - 
                        FranchisorStudentPayment::where('franchisor_id', $record->id)->sum('paid_amount')
                    )
                    ->money('PKR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->alignRight(),
            ])
            ->paginated(false);
    }
}
