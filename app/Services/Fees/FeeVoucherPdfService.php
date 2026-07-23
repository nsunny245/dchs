<?php

namespace App\Services\Fees;

use App\Models\Admission;
use App\Models\FeeVoucher;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeVoucherPdfService
{
    public static function streamBook(Admission $admission)
    {
        $vouchers = FeeVoucher::query()
            ->where('admission_id', $admission->id)
            ->with(['student.admission', 'student.campus', 'student.course', 'campus', 'course', 'academicSession', 'items.feeHead'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        abort_if($vouchers->isEmpty(), 404, 'No vouchers have been generated for this admission.');

        $pdf = Pdf::loadView('fees.vouchers.horizontal-three-part', [
            'voucher' => $vouchers->first(),
            'vouchers' => $vouchers,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("voucher-book-{$admission->enrollment_no}.pdf");
    }

    public static function streamHorizontal(FeeVoucher $voucher)
    {
        $voucher->load(['student', 'student.campus', 'student.course', 'items.feeHead']);

        $pdf = Pdf::loadView('fees.vouchers.horizontal-three-part', [
            'voucher' => $voucher,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("fee-voucher-horizontal-{$voucher->voucher_number}.pdf");
    }

    public static function downloadHorizontal(FeeVoucher $voucher)
    {
        $voucher->load(['student', 'student.campus', 'student.course', 'items.feeHead']);

        $pdf = Pdf::loadView('fees.vouchers.horizontal-three-part', [
            'voucher' => $voucher,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("fee-voucher-horizontal-{$voucher->voucher_number}.pdf");
    }

    public static function streamPortrait(FeeVoucher $voucher)
    {
        $voucher->load(['student', 'student.campus', 'student.course', 'items.feeHead']);

        $pdf = Pdf::loadView('fees.vouchers.portrait-three-part', [
            'voucher' => $voucher,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("fee-voucher-portrait-{$voucher->voucher_number}.pdf");
    }

    public static function downloadPortrait(FeeVoucher $voucher)
    {
        $voucher->load(['student', 'student.campus', 'student.course', 'items.feeHead']);

        $pdf = Pdf::loadView('fees.vouchers.portrait-three-part', [
            'voucher' => $voucher,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("fee-voucher-portrait-{$voucher->voucher_number}.pdf");
    }
}
