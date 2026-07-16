<?php

namespace App\Filament\Resources\SuperAdminFranchisorPaymentResource\Pages;

use App\Filament\Resources\SuperAdminFranchisorPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuperAdminFranchisorPayment extends EditRecord
{
    protected static string $resource = SuperAdminFranchisorPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
