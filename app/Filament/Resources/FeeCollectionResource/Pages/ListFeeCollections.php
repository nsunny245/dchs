<?php

namespace App\Filament\Resources\FeeCollectionResource\Pages;

use App\Filament\Resources\FeeCollectionResource;
use Filament\Resources\Pages\ListRecords;

class ListFeeCollections extends ListRecords
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FeeCollectionOverviewWidget::class,
        ];
    }
}
