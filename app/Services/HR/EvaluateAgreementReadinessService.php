<?php

namespace App\Services\HR;

use App\Models\Staff;

class EvaluateAgreementReadinessService
{
    public static function check(Staff $staff): array
    {
        $blockingReasons = [];

        if (empty($staff->full_name)) {
            $blockingReasons[] = "Teacher full name is required.";
        }

        if (empty($staff->cnic)) {
            $blockingReasons[] = "CNIC is required.";
        }

        if (empty($staff->campus_id)) {
            $blockingReasons[] = "Assigned campus is required.";
        }

        $employment = $staff->currentEmployment ?? $staff->employmentRecords()->first();
        if (!$employment) {
            $blockingReasons[] = "Current employment record is missing.";
        } else {
            if (empty($employment->designation)) {
                $blockingReasons[] = "Designation is required.";
            }
            if (empty($employment->appointment_status)) {
                $blockingReasons[] = "Appointment status (Probation / Permanent / Contract) is required.";
            }
            if (empty($employment->joining_date)) {
                $blockingReasons[] = "Joining date is required.";
            }
        }

        $salary = $staff->currentSalary ?? $staff->salaryRecords()->first();
        if (!$salary || (float)$salary->gross_salary <= 0) {
            $blockingReasons[] = "Approved salary record with positive gross salary is required.";
        }

        $documents = $staff->documents;
        if ($documents->where('document_type', 'cnic')->count() === 0) {
            $blockingReasons[] = "Mandatory document missing: CNIC copy.";
        }

        return [
            'is_ready' => count($blockingReasons) === 0,
            'reasons' => $blockingReasons,
        ];
    }
}
