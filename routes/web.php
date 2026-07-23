<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\PdfController;

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

// ── Public PDF Report ──
Route::get('/project-status-report', [PdfController::class, 'projectStatusReport'])->name('project-status-report');

Route::middleware('auth:admin,campus')->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/admission-letter/{admission}', [PdfController::class, 'admissionLetter'])->name('admission-letter');
    Route::get('/admission-agreement/{admission}', [PdfController::class, 'admissionAgreement'])->name('admission-agreement');
    Route::get('/fee-receipt/{feePayment}', [PdfController::class, 'feeReceipt'])->name('fee-receipt');
    Route::get('/report-card/{student}', [PdfController::class, 'reportCard'])->name('report-card');
    Route::get('/fee-voucher/{voucher}', [PdfController::class, 'feeVoucher'])->name('fee-voucher');
    Route::get('/payment-receipt/{payment}', [PdfController::class, 'paymentReceipt'])->name('payment-receipt');
});

Route::middleware('auth:admin,campus')->prefix('admin/fee-vouchers')->name('fee-vouchers.')->group(function () {
    Route::get('/{feeVoucher}/print/horizontal', [\App\Http\Controllers\FeeVoucherPrintController::class, 'printHorizontal'])->name('print.horizontal');
    Route::get('/{feeVoucher}/print/portrait', [\App\Http\Controllers\FeeVoucherPrintController::class, 'printPortrait'])->name('print.portrait');
    Route::get('/{feeVoucher}/pdf/horizontal', [\App\Http\Controllers\FeeVoucherPrintController::class, 'downloadHorizontal'])->name('pdf.horizontal');
    Route::get('/{feeVoucher}/pdf/portrait', [\App\Http\Controllers\FeeVoucherPrintController::class, 'downloadPortrait'])->name('pdf.portrait');
});
