<?php

namespace App\Filament\Resources\FeeStructureResource\Pages;

use App\Filament\Resources\FeeStructureResource;
use Filament\Resources\Pages\EditRecord;

class EditFeeStructure extends EditRecord
{
    protected static string $resource = FeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = filament()->auth()->id();

        return $data;
    }
}
