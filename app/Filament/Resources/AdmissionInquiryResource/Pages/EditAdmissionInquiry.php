<?php

namespace App\Filament\Resources\AdmissionInquiryResource\Pages;

use App\Filament\Resources\AdmissionInquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmissionInquiry extends EditRecord
{
    protected static string $resource = AdmissionInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
