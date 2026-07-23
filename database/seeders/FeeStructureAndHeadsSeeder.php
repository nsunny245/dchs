<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\FeeHead;
use Illuminate\Database\Seeder;

class FeeStructureAndHeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the course codes and names exactly as they are in the DB
        $coursesConfig = [
            'LHV' => [
                'total_fee' => 250000.00,
                'admission_fee' => 30000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 32000.00,
                'misc_charges' => 10000.00,
            ],
            'CMW' => [
                'total_fee' => 150000.00,
                'admission_fee' => 25000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 32000.00,
                'misc_charges' => 10000.00,
            ],
            'CNA' => [
                'total_fee' => 150000.00,
                'admission_fee' => 25000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 32000.00,
                'misc_charges' => 10000.00,
            ],
            'PT' => [
                'total_fee' => 100000.00,
                'admission_fee' => 20000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 18000.00,
                'misc_charges' => 10000.00,
            ],
            'MLT' => [
                'total_fee' => 80000.00,
                'admission_fee' => 15000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 14000.00,
                'misc_charges' => 10000.00,
            ],
            'OT' => [
                'total_fee' => 80000.00,
                'admission_fee' => 15000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 14000.00,
                'misc_charges' => 10000.00,
            ],
            'DT' => [
                'total_fee' => 80000.00,
                'admission_fee' => 15000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 14000.00,
                'misc_charges' => 10000.00,
            ],
            'AT' => [
                'total_fee' => 100000.00,
                'admission_fee' => 20000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 14000.00,
                'misc_charges' => 10000.00,
            ],
            'CSSD' => [
                'total_fee' => 100000.00,
                'admission_fee' => 20000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 4500.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 14000.00,
                'misc_charges' => 10000.00,
            ],
            'Post RN' => [
                'total_fee' => 450000.00,
                'admission_fee' => 50000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 9000.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 25000.00,
                'misc_charges' => 15000.00,
            ],
            'BSN' => [
                'total_fee' => 850000.00,
                'admission_fee' => 50000.00,
                'hostel_dues' => 5000.00,
                'late_fee' => 50.00,
                'verification_fee' => 9000.00,
                'endowment_fee' => 1500.00,
                'examination_fee' => 25000.00,
                'misc_charges' => 15000.00,
            ],
        ];

        foreach ($coursesConfig as $code => $config) {
            // Find course
            $course = Course::where('code', $code)->first();
            if (!$course) {
                // Fallback search by code prefix
                $course = Course::where('code', 'like', $code . '%')->first();
            }

            if ($course) {
                // Create unique FeeStructure for this course
                FeeStructure::updateOrCreate(
                    ['course_id' => $course->id],
                    [
                        'total_fee' => $config['total_fee'],
                        'installment_count' => 12,
                    ]
                );

                // Create Fee Heads for this course
                $heads = [
                    [
                        'name' => 'Admission Fee',
                        'code' => 'ADMISSION_' . $course->code,
                        'category' => 'admission',
                        'default_amount' => $config['admission_fee'],
                        'applies_to' => 'new_enrollment',
                    ],
                    [
                        'name' => 'Hostel Dues',
                        'code' => 'HOSTEL_' . $course->code,
                        'category' => 'hostel',
                        'default_amount' => $config['hostel_dues'],
                        'applies_to' => 'both',
                    ],
                    [
                        'name' => 'Late Fee per Day',
                        'code' => 'LATE_FEE_' . $course->code,
                        'category' => 'fine',
                        'default_amount' => $config['late_fee'],
                        'applies_to' => 'both',
                    ],
                    [
                        'name' => 'Verification Fee',
                        'code' => 'VERIFICATION_' . $course->code,
                        'category' => 'examination',
                        'default_amount' => $config['verification_fee'],
                        'applies_to' => 'new_enrollment',
                    ],
                    [
                        'name' => 'Endowment Fee',
                        'code' => 'ENDOWMENT_' . $course->code,
                        'category' => 'affiliation',
                        'default_amount' => $config['endowment_fee'],
                        'applies_to' => 'new_enrollment',
                    ],
                    [
                        'name' => 'Examination Fee',
                        'code' => 'EXAM_' . $course->code,
                        'category' => 'examination',
                        'default_amount' => $config['examination_fee'],
                        'applies_to' => 'both',
                    ],
                    [
                        'name' => 'Miscellaneous Charges',
                        'code' => 'MISC_' . $course->code,
                        'category' => 'miscellaneous',
                        'default_amount' => $config['misc_charges'],
                        'applies_to' => 'new_enrollment',
                    ],
                ];

                foreach ($heads as $h) {
                    FeeHead::updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'code' => $h['code'],
                        ],
                        [
                            'name' => $h['name'],
                            'category' => $h['category'],
                            'default_amount' => $h['default_amount'],
                            'applies_to' => $h['applies_to'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
