<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\FeeVoucher;
use App\Services\Fees\FeeVoucherPdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeVoucherPrintController extends Controller
{
    use AuthorizesRequests;

    public function printHorizontal(FeeVoucher $feeVoucher)
    {
        $this->authorize('print', $feeVoucher);

        return FeeVoucherPdfService::streamHorizontal($feeVoucher);
    }

    public function printBook(Admission $admission)
    {
        $this->authorize('view', $admission);

        return FeeVoucherPdfService::streamBook($admission);
    }

    public function printPortrait(FeeVoucher $feeVoucher)
    {
        $this->authorize('print', $feeVoucher);

        return FeeVoucherPdfService::streamPortrait($feeVoucher);
    }

    public function printLateFee(FeeVoucher $feeVoucher)
    {
        $this->authorize('print', $feeVoucher);

        $today = now()->startOfDay();
        $dueDate = \Illuminate\Support\Carbon::parse($feeVoucher->due_date)->startOfDay();
        if ($today->greaterThan($dueDate)) {
            $days = $today->diffInDays($dueDate);
            $lateFee = $days * 50;

            $feeVoucher->late_fee_amount = $lateFee;
            $totals = \App\Services\Fees\FeeVoucherCalculator::calculate($feeVoucher);
            $feeVoucher->update($totals);

            if ($feeVoucher->feeAccount) {
                \App\Services\Fees\FeeVoucherService::recalculateAccountTotals($feeVoucher->feeAccount);
            }
        }

        return FeeVoucherPdfService::streamPortrait($feeVoucher);
    }

    public function downloadHorizontal(FeeVoucher $feeVoucher)
    {
        $this->authorize('download', $feeVoucher);

        return FeeVoucherPdfService::downloadHorizontal($feeVoucher);
    }

    public function downloadPortrait(FeeVoucher $feeVoucher)
    {
        $this->authorize('download', $feeVoucher);

        return FeeVoucherPdfService::downloadPortrait($feeVoucher);
    }

    public function printCampusMonthly()
    {
        $user = auth()->user();
        $query = FeeVoucher::query()
            ->with(['student.admission', 'student.campus', 'student.course', 'campus', 'course', 'academicSession', 'items.feeHead'])
            ->whereNotIn('status', ['paid', 'waived', 'cancelled']);

        // Scope by campus if not Super Admin
        if ($user->campus_id && !$user->hasRole('Super Admin')) {
            $query->where('campus_id', $user->campus_id);
        } else {
            $campusId = request('campus_id');
            if ($campusId) {
                $query->where('campus_id', $campusId);
            }
        }

        // Vouchers are issued on 1st of every month and due on 10th.
        // We look ahead up to 15 days (covers last day of month printing).
        $targetDate = now()->addDays(15);
        $start = $targetDate->copy()->startOfMonth()->toDateString();
        $end = $targetDate->copy()->endOfMonth()->toDateString();

        $query->whereBetween('due_date', [$start, $end]);
        $vouchers = $query->orderBy('voucher_number')->get();

        abort_if($vouchers->isEmpty(), 404, 'No upcoming vouchers found for this campus for the next billing cycle.');

        $pdf = Pdf::loadView('fees.vouchers.portrait-three-part', [
            'voucher' => $vouchers->first(),
            'vouchers' => $vouchers,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream("campus-vouchers-" . now()->format('Y-m') . ".pdf");
    }

    public function requestEdit(\Illuminate\Http\Request $request, FeeVoucher $feeVoucher)
    {
        if (auth()->user()->campus_id && $feeVoucher->campus_id !== auth()->user()->campus_id) {
            abort(403);
        }

        $feeVoucher->update([
            'edit_request_status' => 'pending',
            'edit_request_reason' => $request->input('reason', 'Campus requested voucher modification'),
            'edit_requested_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Voucher edit request submitted successfully.');
    }

    public function approveEdit(FeeVoucher $feeVoucher)
    {
        abort_unless(auth()->user()->hasRole('Super Admin'), 403);

        $feeVoucher->update([
            'edit_request_status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Voucher edit request approved.');
    }

    public function rejectEdit(FeeVoucher $feeVoucher)
    {
        abort_unless(auth()->user()->hasRole('Super Admin'), 403);

        $feeVoucher->update([
            'edit_request_status' => null,
            'edit_request_reason' => null,
            'edit_requested_by' => null,
        ]);

        return redirect()->back()->with('success', 'Voucher edit request rejected.');
    }

    public function printCourseMonthly()
    {
        $user = auth()->user();
        $courseId = request('course_id');
        $month = request('month');
        $campusId = request('campus_id');

        abort_unless($courseId && $month, 400, 'Course and Month are required.');

        $query = FeeVoucher::query()
            ->with(['student.admission', 'student.campus', 'student.course', 'campus', 'course', 'academicSession', 'items.feeHead'])
            ->whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->where('course_id', $courseId);

        // Scope by campus if not Super Admin
        if ($user->campus_id && !$user->hasRole('Super Admin')) {
            $query->where('campus_id', $user->campus_id);
        } else if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        // Parse month
        $start = \Illuminate\Support\Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $end = \Illuminate\Support\Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $query->whereBetween('due_date', [$start, $end]);
        $vouchers = $query->orderBy('voucher_number')->get();

        abort_if($vouchers->isEmpty(), 404, 'No upcoming vouchers found for this selection.');

        $pdf = Pdf::loadView('fees.vouchers.portrait-three-part', [
            'voucher' => $vouchers->first(),
            'vouchers' => $vouchers,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream("course-vouchers-{$courseId}-{$month}.pdf");
    }
}
