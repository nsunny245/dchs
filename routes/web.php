<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\PdfController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/campuses', [CampusController::class, 'index'])->name('campuses');
Route::get('/courses', [CourseController::class, 'index'])->name('courses');
Route::get('/courses/{code}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions');
Route::get('/apply', [AdmissionController::class, 'apply'])->name('admissions.apply');
Route::post('/apply', [AdmissionController::class, 'store'])->name('admissions.store');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// ── Public PDF Report ──
Route::get('/project-status-report', [PdfController::class, 'projectStatusReport'])->name('project-status-report');

Route::middleware('auth:admin,campus')->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/admission-letter/{admission}', [PdfController::class, 'admissionLetter'])->name('admission-letter');
    Route::get('/fee-receipt/{feePayment}', [PdfController::class, 'feeReceipt'])->name('fee-receipt');
    Route::get('/report-card/{student}', [PdfController::class, 'reportCard'])->name('report-card');
});
