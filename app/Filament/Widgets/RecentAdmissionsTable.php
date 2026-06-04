<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Admission;

class RecentAdmissionsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Applications';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn () => auth()->user()->hasRole('Super Admin')
                    ? Admission::query()->latest()
                    : Admission::query()->where('campus_id', auth()->user()->campus_id)->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('applicant_name')->label('Applicant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.name')->label('Program')->searchable(),
                Tables\Columns\TextColumn::make('campus.city')->label('Campus'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Applied')->sortable(),
            ])
            ->paginated([10])
            ->defaultSort('created_at', 'desc');
    }
}
