<?php

namespace App\Filament\Franchisor\Resources\FranchisorAdmissionResource\Pages;

use App\Filament\Franchisor\Resources\FranchisorAdmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFranchisorAdmissions extends ListRecords
{
    protected static string $resource = FranchisorAdmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
