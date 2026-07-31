<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Filament\Widgets\StaffOverviewWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Teacher')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => StaffResource::getUrl('create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffOverviewWidget::class,
        ];
    }
}
