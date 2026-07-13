<?php

namespace App\Filament\Resources\AdmissionInquiryResource\Pages;

use App\Filament\Resources\AdmissionInquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmissionInquiries extends ListRecords
{
    protected static string $resource = AdmissionInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
