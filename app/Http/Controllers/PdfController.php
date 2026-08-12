<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentInstallment;
use App\Services\Documents\DocumentImageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PdfController extends Controller
{
    use AuthorizesRequests;

    /**
     * Generate Admission Letter PDF
     */
    public function admissionLetter(Admission $admission, DocumentImageService $documentImages)
    {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession']);

        $pdf = Pdf::loadView('pdf.official_admission_form', [
            'admission' => $admission,
            'studentPhotoDataUri' => $documentImages->fromPublicDisk($admission->student_photo),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("admission-form-{$admission->id}.pdf");
    }

    /**
     * Display Web Printable Student Agreement
     */
    public function admissionAgreement(Admission $admission, DocumentImageService $documentImages)
    {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession']);

        return view('pdf.admission-agreement', [
            'admission' => $admission,
            'studentPhotoDataUri' => $documentImages->fromPublicDisk($admission->student_photo),
        ]);
    }

    /**
     * Download all uploaded student documents in a ZIP archive
     */
    public function downloadDocumentsZip(Admission $admission)
    {
        $this->authorize('view', $admission);

        $files = [
            '01_Student_Photo' => $admission->student_photo,
            '02_Student_CNIC_Front' => $admission->student_cnic_front ?: $admission->cnic_copy,
            '03_Student_CNIC_Back' => $admission->student_cnic_back,
            '04_Father_CNIC_Front' => $admission->father_cnic_front ?: $admission->father_cnic_copy,
            '05_Father_CNIC_Back' => $admission->father_cnic_back,
            '06_Matric_Certificate' => $admission->matric_copy,
            '07_Intermediate_Certificate' => $admission->inter_copy,
            '08_Domicile_Certificate' => $admission->domicile_copy,
            '09_Character_Certificate' => $admission->character_certificate_copy,
            '10_Other_Document' => $admission->other_docs,
        ];

        $validFiles = [];
        foreach ($files as $name => $relativePath) {
            if ($relativePath) {
                $fullPath = storage_path('app/public/' . $relativePath);
                if (file_exists($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $validFiles["{$name}.{$ext}"] = $fullPath;
                }
            }
        }

        if (empty($validFiles)) {
            return back()->with('error', 'No uploaded document files were found for this student.');
        }

        $zipFileName = 'Student_Documents_' . \Illuminate\Support\Str::slug($admission->applicant_name ?: 'Student') . '_' . $admission->id . '.zip';
        $tempZipPath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($validFiles as $zipEntryName => $filePath) {
                $zip->addFile($filePath, $zipEntryName);
            }
            $zip->close();
        }

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function installmentSchedule(Admission $admission)
    {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession', 'student']);
        $installments = StudentInstallment::where('admission_id', $admission->id)
            ->orderBy('installment_number')
            ->get();

        $pdf = Pdf::loadView('pdf.installment-schedule', compact('admission', 'installments'));

        return $pdf->stream("installment-schedule-{$admission->enrollment_no}.pdf");
    }

    /**
     * Generate Fee Receipt PDF
     */
    public function feeReceipt(FeePayment $feePayment)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $feePayment->student?->campus_id, 403);
        $feePayment->load(['student', 'student.admission', 'student.campus', 'student.course', 'feeAccount', 'collectedBy']);

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $feePayment,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("fee-receipt-{$feePayment->id}.pdf");
    }

    /**
     * Generate Student Report Card PDF
     */
    public function reportCard(Student $student)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $student->campus_id, 403);
        $student->load(['campus', 'course', 'marks.exam']);

        $pdf = Pdf::loadView('pdf.report-card', [
            'student' => $student,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("report-card-{$student->enrollment_number}.pdf");
    }

    /**
     * Generate Project Status Report PDF
     */
    public function projectStatusReport()
    {
        $campuses = Campus::all();
        $courses = Course::all();

        $pdf = Pdf::loadView('pdf.project-status-report', [
            'campuses' => $campuses,
            'courses' => $courses,
            'campusCount' => $campuses->count(),
            'courseCount' => $courses->count(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('dgc-project-status-report-'.date('Y-m-d').'.pdf');
    }

    /**
     * Generate Fee Voucher PDF (3-part voucher copy layout)
     */
    public function feeVoucher(FeeVoucher $voucher)
    {
        $this->authorize('print', $voucher);
        $voucher->load(['student', 'student.campus', 'student.course']);

        $pdf = Pdf::loadView('pdf.fee-voucher', [
            'voucher' => $voucher,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("fee-voucher-{$voucher->voucher_number}.pdf");
    }

    /**
     * Generate Payment Receipt PDF
     */
    public function paymentReceipt(FeePayment $payment)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $payment->student?->campus_id, 403);
        $payment->load(['student', 'student.campus', 'student.course', 'collectedBy']);

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("payment-receipt-{$payment->receipt_number}.pdf");
    }

    /**
     * Generate Teacher Profile Summary PDF
     */
    public function teacherProfileSummary(\App\Models\Staff $staff)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $staff->campus_id, 403);
        $staff->load(['campus', 'academics', 'registrations', 'currentEmployment']);

        $pdf = Pdf::loadView('pdf.teacher-profile-summary', [
            'staff' => $staff,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("teacher-summary-{$staff->employee_id}.pdf");
    }

    public function timetable(\App\Models\Timetable $timetable)
    {
        $timetable->load(['campus', 'course', 'academicSession', 'timetableSubjects.subject', 'slots.teacher', 'slots.room']);
        $periods = \App\Models\TimetablePeriod::where('is_active', true)->orderBy('sort_order')->get();

        $pdf = Pdf::loadView('pdf.timetable', compact('timetable', 'periods'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("timetable-{$timetable->id}.pdf");
    }
}
