<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentIdCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentIdCardService
{
    public function generate(Student $student, ?Carbon $validUntil = null): StudentIdCard
    {
        return DB::transaction(function () use ($student, $validUntil): StudentIdCard {
            StudentIdCard::where('student_id', $student->id)->where('status', 'active')->update(['status' => 'reissued']);
            $year = now()->format('Y');
            $sequence = (StudentIdCard::whereYear('created_at', $year)->lockForUpdate()->max('id') ?? 0) + 1;
            $card = StudentIdCard::create([
                'student_id' => $student->id,
                'card_number' => sprintf('DGC-%s-%06d', $year, $sequence),
                'issue_date' => now()->toDateString(),
                'valid_until' => ($validUntil ?? now()->addYears(2))->toDateString(),
                'status' => 'active',
                'issued_by' => auth()->id(),
                'version' => ((int) StudentIdCard::where('student_id', $student->id)->max('version')) + 1,
            ]);

            return $card->fresh();
        });
    }

    public function activeFor(Student $student): StudentIdCard
    {
        return $student->activeIdCard ?? $this->generate($student);
    }
}
