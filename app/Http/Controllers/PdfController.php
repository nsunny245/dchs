<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\FeePayment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Generate Admission Letter PDF
     */
    public function admissionLetter(Admission $admission)
    {
        $admission->load(['campus', 'course', 'academicSession']);

        $pdf = Pdf::loadView('pdf.official_admission_form', [
            'admission' => $admission,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("admission-form-{$admission->id}.pdf");
    }

    /**
     * Generate Fee Receipt PDF
     */
    public function feeReceipt(FeePayment $feePayment)
    {
        $feePayment->load(['student', 'feeStructure', 'student.campus', 'student.course']);

        $pdf = Pdf::loadView('pdf.fee-receipt', [
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
        $campuses = \App\Models\Campus::all();
        $courses = \App\Models\Course::all();

        $pdf = Pdf::loadView('pdf.project-status-report', [
            'campuses' => $campuses,
            'courses' => $courses,
            'campusCount' => $campuses->count(),
            'courseCount' => $courses->count(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("dchs-project-status-report-" . date('Y-m-d') . ".pdf");
    }
}
