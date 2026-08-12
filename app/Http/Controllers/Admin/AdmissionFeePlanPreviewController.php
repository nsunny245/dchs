<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Fees\OfficialFeePlanData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdmissionFeePlanPreviewController extends Controller
{
    public function __invoke(Request $request, OfficialFeePlanData $feePlans): JsonResponse
    {
        $courseId = $request->integer('course_id');

        if ($courseId < 1) {
            return response()->json($this->emptyPlan());
        }

        try {
            return response()->json($feePlans->forAdmission([
                'course_id' => $courseId,
                'campus_id' => $request->filled('campus_id') ? $request->integer('campus_id') : null,
                'academic_session_id' => $request->filled('academic_session_id') ? $request->integer('academic_session_id') : null,
                'admission_date' => $request->input('admission_date'),
            ]));
        } catch (ValidationException) {
            return response()->json($this->emptyPlan());
        }
    }

    protected function emptyPlan(): array
    {
        return [
            'custom_tuition_fee' => '0.00',
            'custom_installment_count' => 12,
            'custom_admission_fee' => '0.00',
            'custom_enrollment_fee' => '0.00',
            'custom_verification_fee' => '0.00',
            'custom_examination_fee' => '0.00',
            'custom_other_misc' => '0.00',
        ];
    }
}
