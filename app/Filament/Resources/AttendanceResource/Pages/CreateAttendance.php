<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = \App\Models\Student::find($data['student_id']);
        if ($student) {
            $data['campus_id'] = $student->campus_id;
            $data['course_id'] = $student->course_id;
        }
        $data['marked_by'] = filament()->auth()->id();
        return $data;
    }
}
