<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentIdCard;
use App\Models\StudentInstallment;
use App\Models\Timetable;
use App\Models\TimetablePeriod;
use App\Services\Documents\DocumentImageService;
use App\Services\Fees\AdmissionFeeAgreementData;
use App\Services\StudentIdCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function admissionAgreement(
        Admission $admission,
        DocumentImageService $documentImages,
        AdmissionFeeAgreementData $agreementFees,
    ) {
        $this->authorize('view', $admission);
        $admission->load(['campus', 'course', 'academicSession']);

        return view('pdf.admission-agreement', [
            'admission' => $admission,
            'studentPhotoDataUri' => $documentImages->fromPublicDisk($admission->student_photo),
            'feePlan' => $agreementFees->build($admission),
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
                $fullPath = storage_path('app/public/'.$relativePath);
                if (file_exists($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $validFiles["{$name}.{$ext}"] = $fullPath;
                }
            }
        }

        if (empty($validFiles)) {
            return back()->with('error', 'No uploaded document files were found for this student.');
        }

        $zipFileName = 'Student_Documents_'.Str::slug($admission->applicant_name ?: 'Student').'_'.$admission->id.'.zip';
        $tempZipPath = storage_path('app/'.$zipFileName);

        $zip = new \ZipArchive;
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

    /** Generate a high-resolution PNG of the branded Concept B student identity card. */
    public function studentIdCard(Student $student, StudentIdCardService $cards)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $student->campus_id, 403);
        $student->load(['campus', 'course', 'admission.academicSession']);
        $card = $cards->activeFor($student);

        $width = 1600;
        // 1600x1010 preserves the CR80 85.60:53.98 mm aspect ratio at print quality.
        $height = 1010;
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, true);
        $navy = imagecolorallocate($canvas, 8, 38, 75);
        $gold = imagecolorallocate($canvas, 216, 154, 25);
        $ink = imagecolorallocate($canvas, 16, 39, 70);
        $muted = imagecolorallocate($canvas, 113, 128, 150);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $pale = imagecolorallocate($canvas, 247, 249, 252);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        imagefilledrectangle($canvas, 0, 0, 500, $height, $navy);
        imagefilledrectangle($canvas, 490, 0, 500, $height, $gold);

        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $fontBold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $text = static function ($image, string $value, int $size, int $x, int $y, int $color, string $fontPath) {
            if (function_exists('imagettftext') && is_file($fontPath)) {
                imagettftext($image, $size, 0, $x, $y, $color, $fontPath, $value);
            } else {
                imagestring($image, max(1, (int) ($size / 2)), $x, $y - $size, $value, $color);
            }
        };
        $loadImage = static function (mixed $path) {
            if (is_array($path)) {
                $path = $path[0] ?? null;
            }
            if (! $path) {
                return null;
            }
            $path = ltrim((string) parse_url($path, PHP_URL_PATH), '/');
            $path = preg_replace('#^storage/#', '', $path);
            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                return null;
            }

            return @imagecreatefromstring($disk->get($path)) ?: null;
        };

        $logoPath = public_path('images/branding/daniyal-group-of-colleges-logo.png');
        $logo = is_file($logoPath) ? @imagecreatefromstring(file_get_contents($logoPath)) : null;
        if ($logo) {
            imagecopyresampled($canvas, $logo, 55, 45, 0, 0, 390, 110, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
        }
        $photo = $loadImage($student->student_photo);
        if ($photo) {
            imagecopyresampled($canvas, $photo, 155, 205, 0, 0, 190, 225, imagesx($photo), imagesy($photo));
            imagedestroy($photo);
        } else {
            imagefilledrectangle($canvas, 155, 205, 345, 430, $white);
            imagerectangle($canvas, 155, 205, 345, 430, $gold);
            $text($canvas, 'PHOTO', 20, 210, 325, $gold, $fontBold);
        }
        $text($canvas, $student->full_name, 28, 55, 500, $white, $fontBold);
        $text($canvas, $student->enrollment_number, 18, 55, 540, $gold, $fontBold);
        $text($canvas, $student->course?->name ?? 'Student', 16, 55, 575, $white, $font);
        $text($canvas, 'DANIYAL GROUP OF COLLEGES', 18, 560, 115, $gold, $fontBold);
        $text($canvas, 'STUDENT IDENTITY CARD', 42, 560, 180, $ink, $fontBold);
        $text($canvas, $student->campus?->name ?? 'Daniyal Group of Colleges', 20, 560, 220, $muted, $font);
        imagefilledrectangle($canvas, 560, 250, 1510, 258, $gold);
        $rows = [
            ['STUDENT ID', $card->card_number, 'ROLL NO.', $student->admission?->roll_no ?: $student->enrollment_number],
            ['NAME', strtoupper($student->full_name), 'FATHER NAME', strtoupper($student->admission?->father_name ?: '—')],
            ['PROGRAM', $student->course?->name ?? '—', 'SESSION', $student->admission?->academicSession?->name ?? '—'],
            ['CAMPUS', $student->campus?->name ?? '—', 'CNIC / B-FORM', $student->admission?->cnic ?: '—'],
            ['BLOOD GROUP', $student->admission?->blood_group ?: '—', 'STATUS', strtoupper($card->status)],
        ];
        foreach ($rows as $i => $row) {
            $y = 320 + ($i * 92);
            $text($canvas, $row[0], 16, 560, $y, $muted, $fontBold);
            $text($canvas, $row[1], 22, 560, $y + 35, $ink, $font);
            $text($canvas, $row[2], 16, 1030, $y, $muted, $fontBold);
            $text($canvas, $row[3], 22, 1030, $y + 35, $ink, $font);
            imageline($canvas, 560, $y + 58, 1490, $y + 58, imagecolorallocate($canvas, 230, 235, 242));
        }
        $text($canvas, 'PRINCIPAL SIGNATURE', 14, 560, 815, $muted, $fontBold);
        imageline($canvas, 560, 850, 820, 850, $ink);
        for ($bar = 0; $bar < 34; $bar++) {
            $barWidth = (($bar * 7) % 4) + 2;
            imagefilledrectangle($canvas, 930 + ($bar * 9), 795, 930 + ($bar * 9) + $barWidth, 855, $ink);
        }
        $text($canvas, $card->barcode_value, 12, 930, 880, $muted, $font);
        imagefilledrectangle($canvas, 500, 910, $width, $height, $navy);
        $text($canvas, 'VALID UPTO: '.optional($card->valid_until)->format('d-m-Y'), 18, 560, 968, $white, $fontBold);
        $text($canvas, 'VERIFY: '.route('student-id.verify', $card->qr_token), 11, 920, 968, $gold, $font);

        ob_start();
        imagepng($canvas, null, 6);
        $png = ob_get_clean();
        imagedestroy($canvas);

        $filename = 'student-id-card-'.$student->enrollment_number.'-v'.$card->version.'-'.now()->format('YmdHis').'.png';

        return response($png, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'attachment; filename="'.$filename.'"', 'Cache-Control' => 'no-store, no-cache, must-revalidate']);
    }

    public function verifyStudentIdCard(string $token)
    {
        $card = StudentIdCard::with(['student.course', 'student.campus'])
            ->where('qr_token', $token)->firstOrFail();

        return view('students.id-card.verify', compact('card'));
    }

    public function studentIdCardBack(Student $student)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $student->campus_id, 403);
        $width = 1600;
        $height = 1010;
        $canvas = imagecreatetruecolor($width, $height);
        $navy = imagecolorallocate($canvas, 8, 38, 75);
        $gold = imagecolorallocate($canvas, 216, 154, 25);
        $ivory = imagecolorallocate($canvas, 251, 249, 244);
        $muted = imagecolorallocate($canvas, 113, 128, 150);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $ivory);
        imagefilledrectangle($canvas, 0, 760, $width, $height, $navy);
        $logoPath = public_path('images/branding/daniyal-group-of-colleges-logo.png');
        if (is_file($logoPath) && ($logo = @imagecreatefrompng($logoPath))) {
            imagecopyresampled($canvas, $logo, 540, 100, 0, 0, 520, 150, imagesx($logo), imagesy($logo));
            imagedestroy($logo);
        }
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $bold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $text = static function ($image, string $value, int $size, int $x, int $y, int $color, string $fontPath): void {
            if (function_exists('imagettftext') && is_file($fontPath)) {
                imagettftext($image, $size, 0, $x, $y, $color, $fontPath, $value);
            } else {
                imagestring($image, max(1, (int) ($size / 2)), $x, $y - $size, $value, $color);
            }
        };
        $text($canvas, 'DANIYAL GROUP OF COLLEGES', 34, 475, 370, $navy, $bold);
        $text($canvas, 'WHERE SUCCESS IS A TRADITION', 20, 600, 415, $gold, $bold);
        $text($canvas, 'Helpline: '.config('app.college_helpline', 'Contact your nearest campus office'), 18, 70, 825, imagecolorallocate($canvas, 255, 255, 255), $font);
        $text($canvas, 'Website: '.config('app.url'), 18, 70, 865, imagecolorallocate($canvas, 255, 255, 255), $font);
        $text($canvas, 'This card is the property of Daniyal Group of Colleges.', 17, 860, 825, imagecolorallocate($canvas, 255, 255, 255), $font);
        $text($canvas, 'If found, please return it to the nearest campus office.', 17, 860, 865, imagecolorallocate($canvas, 255, 255, 255), $font);
        ob_start();
        imagepng($canvas, null, 6);
        $png = ob_get_clean();
        imagedestroy($canvas);

        return response($png, 200, ['Content-Type' => 'image/png', 'Content-Disposition' => 'attachment; filename="student-id-card-back-'.$student->enrollment_number.'.png"']);
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
    public function teacherProfileSummary(Staff $staff)
    {
        abort_unless(auth()->user()?->hasRole('Super Admin') || auth()->user()?->campus_id === $staff->campus_id, 403);
        $staff->load(['campus', 'academics', 'registrations', 'currentEmployment']);

        $pdf = Pdf::loadView('pdf.teacher-profile-summary', [
            'staff' => $staff,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("teacher-summary-{$staff->employee_id}.pdf");
    }

    public function timetable(Timetable $timetable)
    {
        $timetable->load(['campus', 'course', 'academicSession', 'timetableSubjects.subject', 'slots.teacher', 'slots.room']);
        $periods = TimetablePeriod::where('is_active', true)->orderBy('sort_order')->get();

        $pdf = Pdf::loadView('pdf.timetable', compact('timetable', 'periods'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("timetable-{$timetable->id}.pdf");
    }
}
