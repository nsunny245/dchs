<?php

use App\Http\Controllers\Admin\AdmissionFeePlanPreviewController;
use App\Http\Controllers\Admin\CampusDashboardAccessController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardImageController;
use App\Http\Controllers\FeeVoucherPrintController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about/chairmans-message', [HomeController::class, 'chairmansMessage'])->name('about.chairmans-message');
Route::get('/about/vision-mission', [HomeController::class, 'visionMission'])->name('about.vision-mission');
Route::get('/about/accreditation', [HomeController::class, 'accreditation'])->name('about.accreditation');
Route::get('/about/leadership', [HomeController::class, 'leadership'])->name('about.leadership');

Route::get('/campuses', [CampusController::class, 'index'])->name('campuses');
Route::get('/campuses/{id}', [CampusController::class, 'show'])->name('campuses.show');

Route::get('/courses', [CourseController::class, 'index'])->name('courses');
Route::get('/courses/{code}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions');
Route::get('/apply', [AdmissionController::class, 'apply'])->name('admissions.apply');
Route::post('/apply', [AdmissionController::class, 'store'])->name('admissions.store');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.store');

Route::middleware('auth:admin,campus')
    ->get('/admin/admission-fee-plan-preview', AdmissionFeePlanPreviewController::class)
    ->name('admin.admissions.fee-plan-preview');

Route::middleware('auth:admin')->prefix('admin/campus-access')->name('admin.campus-access.')->group(function (): void {
    Route::post('/{campus}/enter', [CampusDashboardAccessController::class, 'enter'])->name('enter');
});

Route::middleware('auth:campus')
    ->post('/campus/super-admin-access/exit', [CampusDashboardAccessController::class, 'exit'])
    ->name('campus-access.exit');

Route::middleware(['auth:admin,campus,web', 'signed'])
    ->get('/dashboard/image', DashboardImageController::class)
    ->name('dashboard.images.show');

// ── Public PDF Report ──
Route::get('/project-status-report', [PdfController::class, 'projectStatusReport'])->name('project-status-report');

Route::middleware('auth:admin,campus')->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/admission-letter/{admission}', [PdfController::class, 'admissionLetter'])->name('admission-letter');
    Route::get('/admission-agreement/{admission}', [PdfController::class, 'admissionAgreement'])->name('admission-agreement');
    Route::get('/installment-schedule/{admission}', [PdfController::class, 'installmentSchedule'])->name('installment-schedule');
    Route::get('/fee-receipt/{feePayment}', [PdfController::class, 'feeReceipt'])->name('fee-receipt');
    Route::get('/report-card/{student}', [PdfController::class, 'reportCard'])->name('report-card');
    Route::get('/fee-voucher/{voucher}', [PdfController::class, 'feeVoucher'])->name('fee-voucher');
    Route::get('/payment-receipt/{payment}', [PdfController::class, 'paymentReceipt'])->name('payment-receipt');
    Route::get('/teacher-profile-summary/{staff}', [PdfController::class, 'teacherProfileSummary'])->name('teacher-profile-summary');
    Route::get('/download-documents-zip/{admission}', [PdfController::class, 'downloadDocumentsZip'])->name('download-documents-zip');
    Route::get('/timetable/{timetable}', [PdfController::class, 'timetable'])->name('timetable');
});

Route::middleware('auth:admin,campus')
    ->get('/admissions/{admission}/complete', [AdmissionController::class, 'complete'])
    ->name('admissions.complete');

Route::middleware('auth:admin,campus')->prefix('admin/fee-vouchers')->name('fee-vouchers.')->group(function () {
    Route::get('/campus-monthly/print', [FeeVoucherPrintController::class, 'printCampusMonthly'])->name('print.campus-monthly');
    Route::get('/course-monthly/print', [FeeVoucherPrintController::class, 'printCourseMonthly'])->name('print.course-monthly');
    Route::get('/admission/{admission}/book', [FeeVoucherPrintController::class, 'printBook'])->name('print.book');
    Route::get('/{feeVoucher}/print/horizontal', [FeeVoucherPrintController::class, 'printHorizontal'])->name('print.horizontal');
    Route::get('/{feeVoucher}/print/portrait', [FeeVoucherPrintController::class, 'printPortrait'])->name('print.portrait');
    Route::get('/{feeVoucher}/print/late', [FeeVoucherPrintController::class, 'printLateFee'])->name('print.late');
    Route::get('/{feeVoucher}/pdf/horizontal', [FeeVoucherPrintController::class, 'downloadHorizontal'])->name('pdf.horizontal');
    Route::get('/{feeVoucher}/pdf/portrait', [FeeVoucherPrintController::class, 'downloadPortrait'])->name('pdf.portrait');
    Route::post('/{feeVoucher}/request-edit', [FeeVoucherPrintController::class, 'requestEdit'])->name('request-edit');
    Route::get('/{feeVoucher}/approve-edit', [FeeVoucherPrintController::class, 'approveEdit'])->name('approve-edit');
    Route::get('/{feeVoucher}/reject-edit', [FeeVoucherPrintController::class, 'rejectEdit'])->name('reject-edit');
});
