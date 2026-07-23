<?php

namespace App\Http\Controllers;

use App\Models\FeeVoucher;
use App\Services\Fees\FeeVoucherPdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FeeVoucherPrintController extends Controller
{
    use AuthorizesRequests;

    public function printHorizontal(FeeVoucher $feeVoucher)
    {
        $this->authorize('print', $feeVoucher);
        return FeeVoucherPdfService::streamHorizontal($feeVoucher);
    }

    public function printPortrait(FeeVoucher $feeVoucher)
    {
        $this->authorize('print', $feeVoucher);
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
}
