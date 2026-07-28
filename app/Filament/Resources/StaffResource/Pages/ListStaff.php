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
                ->visible(fn () => filament()->auth()->user()?->hasRole('Super Admin')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffOverviewWidget::class,
        ];
    }
}
