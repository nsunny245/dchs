<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\Student;
use App\Models\StudentInstallment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PdfController extends Controller
{
    use AuthorizesRequests;

    /**
     * Generate Admission Letter PDF
     */
    public function admissionLetter(Admission $admission)
    {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession']);

        $pdf = Pdf::loadView('pdf.official_admission_form', [
            'admission' => $admission,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("admission-form-{$admission->id}.pdf");
    }

    /**
     * Display Web Printable Student Agreement
     */
    public function admissionAgreement(Admission $admission)
    {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession']);

        return view('pdf.admission-agreement', [
            'admission' => $admission,
        ]);
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
}
