<?php

namespace App\Filament\Franchisor\Resources\FranchisorAdmissionResource\Pages;

use App\Filament\Franchisor\Resources\FranchisorAdmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFranchisorAdmission extends EditRecord
{
    protected static string $resource = FranchisorAdmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
