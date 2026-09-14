<?php

namespace App\Services\Admissions;

use App\Models\Campus;
use App\Models\Course;
use App\Models\Student;
use App\Support\CampusCode;
use Illuminate\Support\Facades\DB;

class EnrollmentNumberService
{
    public function generate(Campus $campus, Course $course, ?int $year = null): string
    {
        $year ??= now()->year;
        $courseCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $course->code)) ?: 'GEN';
        $prefix = 'DGC-'.CampusCode::for($campus)."-{$courseCode}-{$year}-";
        $sequenceStart = strlen($prefix) + 1;
        $sequenceExpression = DB::getDriverName() === 'sqlite'
            ? "CAST(SUBSTR(enrollment_number, {$sequenceStart}) AS INTEGER)"
            : "CAST(SUBSTRING(enrollment_number, {$sequenceStart}) AS UNSIGNED)";
        $highest = (int) (Student::withoutGlobalScopes()
            ->where('enrollment_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->max(DB::raw($sequenceExpression)) ?? 0);

        return $prefix.str_pad(max(1233, $highest) + 1, 6, '0', STR_PAD_LEFT);
    }
}
