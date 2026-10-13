<?php

namespace App\Filament\Resources\TimetableResource\Pages;

use App\Filament\Resources\TimetableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimetables extends ListRecords
{
    protected static string $resource = TimetableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('teacher_layout')
                ->label('Create Teacher Timetable')
                ->icon('heroicon-o-table-cells')
                ->url(fn (): string => TimetableResource::getUrl('teacher-create')),
            Actions\CreateAction::make()
                ->label('Create Program Timetable')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => TimetableResource::getUrl('create')),
        ];
    }
}
