<?php

namespace App\Services\HR;

use App\Models\Staff;
use App\Models\AgreementVersion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateTeacherAgreementAction
{
    public static function execute(Staff $staff, ?int $generatedById = null): AgreementVersion
    {
        $readiness = EvaluateAgreementReadinessService::check($staff);
        if (!$readiness['is_ready']) {
            throw new \Exception("Cannot generate agreement: " . implode(" ", $readiness['reasons']));
        }

        return DB::transaction(function () use ($staff, $generatedById) {
            $employment = $staff->currentEmployment ?? $staff->employmentRecords()->first();
            $salary = $staff->currentSalary ?? $staff->salaryRecords()->first();

            // Next agreement version number
            $latestVer = AgreementVersion::where('staff_id', $staff->id)->max('version') ?? 0;
            $newVer = $latestVer + 1;

            $agreementNumber = "AGR-{$staff->employee_id}-V{$newVer}";

            $pdf = Pdf::loadView('pdf.teacher-employment-agreement', [
                'staff' => $staff,
                'employment' => $employment,
                'salary' => $salary,
                'agreementNumber' => $agreementNumber,
                'version' => $newVer,
                'date' => now()->format('d-M-Y'),
            ])->setPaper('a4', 'portrait');

            $fileName = "agreements/{$staff->employee_id}_agreement_v{$newVer}.pdf";
            Storage::disk('public')->put($fileName, $pdf->output());

            $fileHash = md5_file(Storage::disk('public')->path($fileName));

            // Supersede existing active agreement
            AgreementVersion::where('staff_id', $staff->id)
                ->where('status', 'generated')
                ->update(['status' => 'superseded']);

            $agreement = AgreementVersion::create([
                'staff_id' => $staff->id,
                'agreement_number' => $agreementNumber,
                'version' => $newVer,
                'appointment_status' => $employment->appointment_status,
                'salary_record_id' => $salary ? $salary->id : null,
                'employment_record_id' => $employment ? $employment->id : null,
                'generated_pdf_path' => $fileName,
                'status' => 'generated',
                'generated_by' => $generatedById ?? auth()->id(),
                'generated_at' => now(),
                'file_hash' => $fileHash,
            ]);

            return $agreement;
        });
    }
}
