<?php

namespace App\Filament\Resources\ConvocationRegistrationResource\Pages;

use App\Filament\Resources\ConvocationRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConvocationRegistrations extends ListRecords
{
    protected static string $resource = ConvocationRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
