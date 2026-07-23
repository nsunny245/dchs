<?php

namespace App\Filament\Resources\FeeStructureResource\Pages;

use App\Filament\Resources\FeeStructureResource;
use App\Models\FeeStructure;
use Filament\Resources\Pages\CreateRecord;

class CreateFeeStructure extends CreateRecord
{
    protected static string $resource = FeeStructureResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = filament()->auth()->id();
        $data['updated_by'] = filament()->auth()->id();
        $data['version'] = FeeStructure::query()
            ->where('course_id', $data['course_id'])
            ->where('campus_id', $data['campus_id'] ?? null)
            ->where('academic_session_id', $data['academic_session_id'] ?? null)
            ->max('version') + 1;

        return $data;
    }
}
