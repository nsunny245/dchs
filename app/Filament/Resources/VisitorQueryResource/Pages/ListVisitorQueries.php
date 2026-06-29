<?php

namespace App\Filament\Resources\VisitorQueryResource\Pages;

use App\Filament\Resources\VisitorQueryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisitorQueries extends ListRecords
{
    protected static string $resource = VisitorQueryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
