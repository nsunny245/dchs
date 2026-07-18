<?php

namespace App\Filament\Resources\FeeStructureResource\Pages;

use App\Filament\Resources\FeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeeStructures extends ListRecords
{
    protected static string $resource = FeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importFromExcel')
                ->label('Import from Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(function () {
                    try {
                        // Ensure campuses and courses are present first
                        $courseSeeder = new \Database\Seeders\CampusAndCourseSeeder();
                        $courseSeeder->run();

                        $seeder = new \Database\Seeders\SeedFeeStructuresFromExcel();
                        $seeder->run();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Fee structures imported successfully!')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Import failed: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
