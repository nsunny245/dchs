<?php

namespace App\Services\HR;

use App\Models\Staff;

class CalculateProfileCompletionService
{
    public static function evaluate(Staff $staff): array
    {
        $missing = [];
        $totalWeight = 0;
        $earnedWeight = 0;

        // 1. Personal Information (30%)
        $personalFields = [
            'full_name' => 'Full Name',
            'cnic' => 'CNIC',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'phone' => 'Primary Phone',
            'current_address' => 'Current Address',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
        ];

        foreach ($personalFields as $field => $label) {
            $totalWeight += 3.75;
            if (! empty($staff->$field)) {
                $earnedWeight += 3.75;
            } else {
                $missing[] = "Personal: {$label}";
            }
        }

        // 2. Academic Profile (20%)
        $academic = $staff->academics;
        $totalWeight += 10;
        if ($academic && ! empty($academic->highest_qualification)) {
            $earnedWeight += 10;
        } else {
            $missing[] = 'Academic: Highest Qualification';
        }

        $totalWeight += 10;
        if ($academic && ! empty($academic->degree_title)) {
            $earnedWeight += 10;
        } else {
            $missing[] = 'Academic: Degree Title';
        }

        // 3. Employment & Posting (25%)
        $employment = $staff->currentEmployment ?? $staff->employmentRecords()->first();
        $totalWeight += 8.33;
        if ($employment && ! empty($employment->designation)) {
            $earnedWeight += 8.33;
        } else {
            $missing[] = 'Employment: Designation';
        }

        $totalWeight += 8.33;
        if ($employment && ! empty($employment->appointment_status)) {
            $earnedWeight += 8.33;
        } else {
            $missing[] = 'Employment: Appointment Status';
        }

        $totalWeight += 8.34;
        if ($employment && ! empty($employment->joining_date)) {
            $earnedWeight += 8.34;
        } else {
            $missing[] = 'Employment: Joining Date';
        }

        // 4. Salary & Payroll (15%)
        $salary = $staff->currentSalary ?? $staff->salaryRecords()->first();
        $totalWeight += 15;
        if ($salary && (float) $salary->gross_salary > 0) {
            $earnedWeight += 15;
        } else {
            $missing[] = 'Salary: Approved Gross Salary';
        }

        // 5. Mandatory Documents (10%)
        $documents = $staff->documents;
        $hasCnic = $documents->whereIn('document_type', ['cnic', 'cnic_front', 'cnic_back'])->isNotEmpty();
        $hasDegree = $documents->whereIn('document_type', ['degree', 'transcript'])->isNotEmpty();

        $totalWeight += 5;
        if ($hasCnic) {
            $earnedWeight += 5;
        } else {
            $missing[] = 'Document: CNIC Copy';
        }

        $totalWeight += 5;
        if ($hasDegree) {
            $earnedWeight += 5;
        } else {
            $missing[] = 'Document: Degree Certificate';
        }

        $percentage = round(($earnedWeight / max(1, $totalWeight)) * 100);

        return [
            'percentage' => (int) $percentage,
            'missing' => $missing,
            'is_complete' => count($missing) === 0,
        ];
    }
}
