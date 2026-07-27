<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Admission;
use App\Filament\Resources\AdmissionResource;

class RecentAdmissionsTable extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Recent Applications';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn () => filament()->auth()->user()?->campus_id === null
                    ? Admission::query()->latest()
                    : Admission::query()->where('campus_id', filament()->auth()->user()->campus_id)->latest()
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => AdmissionResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([10])
            ->defaultSort('created_at', 'desc');
    }
}
