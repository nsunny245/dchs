<?php

namespace App\Filament\Resources\TeacherAttendanceResource\Pages;

use App\Filament\Resources\TeacherAttendanceResource;
use App\Models\Staff;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherAttendance extends CreateRecord
{
    protected static string $resource = TeacherAttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['campus_id'] = $data['campus_id'] ?? Staff::find($data['staff_id'])?->campus_id;
        $data['marked_by'] = auth()->id();

        return $data;
    }
}
