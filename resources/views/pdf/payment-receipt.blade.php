<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->receipt_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 30px;
            padding: 0;
            font-size: 12px;
            color: #333;
        }
        .receipt-container {
            border: 2px solid #0A1526;
            padding: 25px;
            border-radius: 10px;
            position: relative;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #b38f00;
            padding-bottom: 12px;
        }
        .logo-section {
            float: left;
            width: 70px;
        }
        .logo {
            width: 60px;
            height: 60px;
        }
        .title-section {
            float: left;
            margin-left: 15px;
        }
        .college-name {
            font-size: 18px;
            font-weight: bold;
            color: #0A1526;
        }
        .campus-name {
            font-size: 12px;
            color: #b38f00;
            font-weight: bold;
            margin-top: 3px;
        }
        .receipt-title {
            float: right;
            text-align: right;
        }
        .receipt-title h2 {
            margin: 0;
            color: #0A1526;
            font-size: 20px;
            text-transform: uppercase;
        }
        .receipt-no {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }
        .clear {
            clear: both;
        }
        .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }
        .value {
            color: #111;
        }
        .amount-box {
            background-color: #f9f9f9;
            border-left: 5px solid #0A1526;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
        }
        .amount-row {
            margin-bottom: 5px;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #0A1526;
        }
        .footer-note {
            font-size: 9px;
            color: #666;
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .signatures-section {
            margin-top: 55px;
        }
        .sig-block {
            float: left;
            width: 45%;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #999;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="header">
            <div class="logo-section">
                <img src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" class="logo">
            </div>
            <div class="title-section">
                <div class="college-name">Daniyal Group of Colleges</div>
                <div class="campus-name">{{ $payment->student->campus->name ?? 'Daniyal Group Campus' }}</div>
            </div>
            <div class="receipt-title">
                <h2>Official Receipt</h2>
                <div class="receipt-no">Receipt No: <strong>{{ $payment->receipt_number }}</strong></div>
            </div>
            <div class="clear"></div>
        </div>

        <table class="details-table">
            <tr>
                <td class="label">Student Name:</td>
                <td class="value">{{ $payment->student->full_name }}</td>
                <td class="label">Enrollment No:</td>
                <td class="value"><strong>{{ $payment->student->enrollment_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Father's Name:</td>
                <td class="value">{{ $payment->student->admission->father_name ?? 'N/A' }}</td>
                <td class="label">Course Program:</td>
                <td class="value">{{ $payment->student->course->name }}</td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td class="value">{{ $payment->payment_date ? $payment->payment_date->format('d F Y') : 'N/A' }}</td>
                <td class="label">Payment Method:</td>
                <td class="value">{{ strtoupper($payment->payment_method) }} @if($payment->transaction_reference) (Ref: {{ $payment->transaction_reference }}) @endif</td>
            </tr>
            @if($payment->notes)
            <tr>
                <td class="label">Notes / Remarks:</td>
                <td class="value" colspan="3">{{ $payment->notes }}</td>
            </tr>
            @endif
        </table>

        <div class="amount-box">
            <div class="amount-row">
                <span>Amount Paid in Words:</span>
                <strong style="text-transform: capitalize; color: #555;">
                    @php
                        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                        echo $f->format($payment->amount) . " Rupees Only";
                    @endphp
                </strong>
            </div>
            <div class="amount-row font-bold mt-2" style="font-size: 16px;">
                Total Received Amount: <span class="grand-total">PKR {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="amount-row text-xs mt-1 text-slate-500">
                Remaining Outstanding Balance: <strong>PKR {{ number_format($payment->feeAccount->balance, 2) }}</strong>
            </div>
        </div>

        <div class="signatures-section">
            <div class="sig-block">
                <div class="sig-line">Cashier / Collected By</div>
                <div style="font-size: 9px; color: #777;">{{ $payment->collectedBy->name ?? 'System Cashier' }}</div>
            </div>
            <div class="sig-block" style="float: right;">
                <div class="sig-line">Director Finance / Principal</div>
                <div style="font-size: 9px; color: #777;">Official Seal Stamp</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer-note">
            This is a system generated computerized receipt and does not require manual signature if printed with official watermark seals. Thank you for your payment.
        </div>
    </div>

</body>
</html>
