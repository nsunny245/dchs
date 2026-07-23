<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductionFeeCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'LHV' => ['total_fee' => '250000.00', 'admission' => '30000.00', 'verification' => '4500.00', 'examination' => '32000.00', 'miscellaneous' => '10000.00'],
            'CMW' => ['total_fee' => '150000.00', 'admission' => '25000.00', 'verification' => '4500.00', 'examination' => '32000.00', 'miscellaneous' => '10000.00'],
            'CNA' => ['total_fee' => '150000.00', 'admission' => '25000.00', 'verification' => '4500.00', 'examination' => '32000.00', 'miscellaneous' => '10000.00'],
            'PT' => ['total_fee' => '100000.00', 'admission' => '20000.00', 'verification' => '4500.00', 'examination' => '18000.00', 'miscellaneous' => '10000.00'],
            'MLT' => ['total_fee' => '80000.00', 'admission' => '15000.00', 'verification' => '4500.00', 'examination' => '14000.00', 'miscellaneous' => '10000.00'],
            'OT' => ['total_fee' => '80000.00', 'admission' => '15000.00', 'verification' => '4500.00', 'examination' => '14000.00', 'miscellaneous' => '10000.00'],
            'DT' => ['total_fee' => '80000.00', 'admission' => '15000.00', 'verification' => '4500.00', 'examination' => '14000.00', 'miscellaneous' => '10000.00'],
            'AT' => ['total_fee' => '100000.00', 'admission' => '20000.00', 'verification' => '4500.00', 'examination' => '14000.00', 'miscellaneous' => '10000.00'],
            'CSSD' => ['total_fee' => '100000.00', 'admission' => '20000.00', 'verification' => '4500.00', 'examination' => '14000.00', 'miscellaneous' => '10000.00'],
            'Post RN' => ['total_fee' => '450000.00', 'admission' => '50000.00', 'verification' => '9000.00', 'examination' => '25000.00', 'miscellaneous' => '15000.00'],
            'BSN' => ['total_fee' => '850000.00', 'admission' => '50000.00', 'verification' => '9000.00', 'examination' => '25000.00', 'miscellaneous' => '15000.00'],
        ];

        $courses = Course::query()
            ->whereIn('code', array_keys($catalog))
            ->get()
            ->keyBy('code');

        $missingCourses = array_values(array_diff(array_keys($catalog), $courses->keys()->all()));

        if ($missingCourses !== []) {
            throw new RuntimeException(
                'Fee catalog synchronization stopped because these course codes are missing: '.implode(', ', $missingCourses),
            );
        }

        DB::transaction(function () use ($catalog, $courses): void {
            foreach ($catalog as $courseCode => $fees) {
                $course = $courses->get($courseCode);

                FeeStructure::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'campus_id' => null,
                        'academic_session_id' => null,
                        'shift' => null,
                        'version' => 1,
                    ],
                    [
                        'name' => null,
                        'total_fee' => $fees['total_fee'],
                        'installment_count' => 12,
                        'effective_date' => null,
                        'expiry_date' => null,
                        'status' => 'active',
                        'notes' => null,
                    ],
                );

                foreach ($this->courseFeeHeads($courseCode, $fees) as $head) {
                    FeeHead::query()->updateOrCreate(
                        ['code' => $head['code']],
                        $head + ['course_id' => $course->id],
                    );
                }
            }

            foreach ($this->globalFeeHeads() as $head) {
                FeeHead::query()->updateOrCreate(
                    ['code' => $head['code']],
                    $head + ['course_id' => null],
                );
            }
        });

        $this->command?->info('Production fee catalog synchronized: 11 structures and 84 fee heads.');
    }

    /**
     * @param  array{total_fee: string, admission: string, verification: string, examination: string, miscellaneous: string}  $fees
     * @return array<int, array<string, bool|int|string>>
     */
    private function courseFeeHeads(string $courseCode, array $fees): array
    {
        return [
            $this->feeHead('Admission Fee', "ADMISSION_{$courseCode}", 'admission', $fees['admission'], 'new_enrollment'),
            $this->feeHead('Hostel Dues', "HOSTEL_{$courseCode}", 'hostel', '5000.00', 'both'),
            $this->feeHead('Late Fee per Day', "LATE_FEE_{$courseCode}", 'fine', '50.00', 'both'),
            $this->feeHead('Verification Fee', "VERIFICATION_{$courseCode}", 'examination', $fees['verification'], 'new_enrollment'),
            $this->feeHead('Endowment Fee', "ENDOWMENT_{$courseCode}", 'affiliation', '1500.00', 'new_enrollment'),
            $this->feeHead('Examination Fee', "EXAM_{$courseCode}", 'examination', $fees['examination'], 'both'),
            $this->feeHead('Miscellaneous Charges', "MISC_{$courseCode}", 'miscellaneous', $fees['miscellaneous'], 'new_enrollment'),
        ];
    }

    /**
     * @return array<int, array<string, bool|int|string>>
     */
    private function globalFeeHeads(): array
    {
        return [
            $this->feeHead('Admission Fee', 'ADMISSION', 'admission', '20000.00', 'new_enrollment', 1),
            $this->feeHead('Tuition Fee / First Installment', 'TUITION_1', 'tuition', '5882.35', 'new_enrollment', 2),
            $this->feeHead('Enrollment Fee', 'ENROLLMENT', 'affiliation', '1500.00', 'new_enrollment', 3),
            $this->feeHead('Verification Fee', 'VERIFICATION', 'examination', '4500.00', 'new_enrollment', 4),
            $this->feeHead('Miscellaneous / Other Charges', 'MISC', 'miscellaneous', '15000.00', 'new_enrollment', 5),
            $this->feeHead('Monthly Fee / Installment', 'TUITION_REC', 'tuition', '5882.35', 'monthly_installment', 1),
            $this->feeHead('Examination Fee', 'EXAM', 'examination', '18000.00', 'both', 1),
        ];
    }

    /**
     * @return array<string, bool|int|string>
     */
    private function feeHead(
        string $name,
        string $code,
        string $category,
        string $amount,
        string $appliesTo,
        int $sortOrder = 0,
    ): array {
        return [
            'name' => $name,
            'code' => $code,
            'category' => $category,
            'default_amount' => $amount,
            'applies_to' => $appliesTo,
            'is_discount' => false,
            'is_refundable' => false,
            'is_active' => true,
            'sort_order' => $sortOrder,
        ];
    }
}
