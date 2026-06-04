<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Receipt – #{{ $payment->id }}</title>
    <style>
        /* ── Reset & Base ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            color: #1a1a2e;
            font-size: 12px;
            line-height: 1.6;
        }

        .page-border {
            border: 3px double #0f3460;
            padding: 25px 35px;
            min-height: 95vh;
            position: relative;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            border-bottom: 2px solid #0f3460;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #0f3460;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header .tagline {
            font-size: 11px;
            color: #555;
            letter-spacing: 1px;
        }
        .header .campus-name {
            font-size: 12px;
            color: #e94560;
            font-weight: bold;
            margin-top: 3px;
        }

        /* ── Receipt Title ── */
        .receipt-title-bar {
            background: #0f3460;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* ── Meta Info ── */
        .meta-row {
            margin-bottom: 18px;
        }
        .meta-row table {
            width: 100%;
        }
        .meta-row td {
            padding: 4px 8px;
            font-size: 11.5px;
        }
        .meta-row td:last-child {
            text-align: right;
        }
        .meta-row .label {
            color: #555;
            font-weight: normal;
        }
        .meta-row .value {
            font-weight: bold;
            color: #1a1a2e;
        }

        /* ── Student Info ── */
        .info-section {
            margin-bottom: 18px;
        }
        .info-section h3 {
            font-size: 13px;
            color: #0f3460;
            border-bottom: 1px solid #c0c8e0;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #dde2f0;
            padding: 7px 12px;
            font-size: 11.5px;
            text-align: left;
        }
        .info-table th {
            background: #eef1fb;
            color: #333;
            font-weight: 600;
            width: 35%;
        }

        /* ── Payment Breakdown ── */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .payment-table th,
        .payment-table td {
            border: 1px solid #c0c8e0;
            padding: 10px 14px;
            font-size: 12px;
        }
        .payment-table thead th {
            background: #0f3460;
            color: #fff;
            font-weight: 600;
            text-align: left;
        }
        .payment-table tbody td {
            background: #fafbff;
        }
        .payment-table tfoot td {
            background: #eef1fb;
            font-weight: bold;
            font-size: 13px;
        }
        .text-right { text-align: right; }

        /* ── Amount Box ── */
        .amount-box {
            background: linear-gradient(135deg, #0f3460, #16213e);
            color: #fff;
            text-align: center;
            padding: 18px;
            margin: 18px 0;
            border-radius: 6px;
        }
        .amount-box .label {
            font-size: 12px;
            color: #a0b4e0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .amount-box .amount {
            font-size: 28px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-paid   { background: #d4edda; color: #155724; }
        .status-unpaid  { background: #f8d7da; color: #721c24; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-overdue { background: #f8d7da; color: #721c24; }

        /* ── Notes ── */
        .notes {
            background: #f9fafb;
            border: 1px dashed #c0c8e0;
            padding: 12px 16px;
            margin: 18px 0;
            font-size: 11px;
            color: #555;
        }
        .notes h4 {
            color: #0f3460;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .notes ul {
            margin-left: 16px;
        }
        .notes li {
            margin-bottom: 3px;
        }

        /* ── Signature ── */
        .signature-area {
            margin-top: 45px;
        }
        .signature-area table { width: 100%; }
        .sig-block { text-align: center; }
        .sig-line {
            border-top: 1px solid #333;
            width: 160px;
            margin: 0 auto 4px;
        }
        .sig-label {
            font-size: 10px;
            color: #666;
        }

        /* ── Footer ── */
        .footer {
            position: absolute;
            bottom: 20px;
            left: 35px;
            right: 35px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
            color: #888;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 22%;
            font-size: 70px;
            color: rgba(15, 52, 96, 0.04);
            transform: rotate(-30deg);
            font-weight: bold;
            letter-spacing: 10px;
            z-index: -1;
        }
    </style>
</head>
<body>

<div class="watermark">DCHS</div>

<div class="page-border">
    <!-- Header -->
    <div class="header">
        <h1>Daniyal College of Health Sciences</h1>
        <div class="tagline">Excellence in Healthcare Education</div>
        <div class="campus-name">{{ $payment->student->campus->name ?? 'Main Campus' }}</div>
    </div>

    <!-- Title Bar -->
    <div class="receipt-title-bar">
        FEE PAYMENT RECEIPT
    </div>

    <!-- Meta Row -->
    <div class="meta-row">
        <table>
            <tr>
                <td><span class="label">Receipt No:</span> <span class="value">DCHS/FEE/{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                <td><span class="label">Date:</span> <span class="value">{{ $payment->paid_date ? $payment->paid_date->format('d M, Y') : now()->format('d M, Y') }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Transaction ID:</span> <span class="value">{{ $payment->transaction_id ?? '—' }}</span></td>
                <td>
                    <span class="label">Status:</span>
                    <span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Student Details -->
    <div class="info-section">
        <h3>Student Information</h3>
        <table class="info-table">
            <tr>
                <th>Student Name</th>
                <td>{{ $payment->student->full_name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Enrollment No.</th>
                <td>{{ $payment->student->enrollment_number ?? '—' }}</td>
            </tr>
            <tr>
                <th>Programme</th>
                <td>{{ $payment->student->course->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Campus</th>
                <td>{{ $payment->student->campus->name ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <!-- Payment Breakdown -->
    <div class="info-section">
        <h3>Payment Details</h3>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Details</th>
                    <th class="text-right">Amount (PKR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->feeStructure->fee_type ?? 'Tuition Fee' }}</td>
                    <td>Installment {{ $payment->installment_no ?? '—' }} of {{ $payment->feeStructure->installment_count ?? '—' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Amount Received</td>
                    <td class="text-right">PKR {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Amount Highlight Box -->
    <div class="amount-box">
        <div class="label">Amount Paid</div>
        <div class="amount">PKR {{ number_format($payment->amount, 2) }}</div>
    </div>

    <!-- Payment Info -->
    <div class="meta-row">
        <table>
            <tr>
                <td><span class="label">Payment Method:</span> <span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</span></td>
                <td><span class="label">Due Date:</span> <span class="value">{{ $payment->due_date ? $payment->due_date->format('d M, Y') : '—' }}</span></td>
            </tr>
        </table>
    </div>

    <!-- Notes -->
    <div class="notes">
        <h4>Terms & Conditions:</h4>
        <ul>
            <li>This receipt is valid only upon clearance of payment.</li>
            <li>Fee once paid is non-refundable and non-transferable.</li>
            <li>Late fee charges apply as per the fee structure.</li>
            <li>Please retain this receipt for your records and future reference.</li>
        </ul>
    </div>

    <!-- Signature Area -->
    <div class="signature-area">
        <table>
            <tr>
                <td class="sig-block" style="width:33%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Student Signature</div>
                </td>
                <td class="sig-block" style="width:34%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Accounts Officer</div>
                </td>
                <td class="sig-block" style="width:33%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Authorized Signatory</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a computer-generated receipt. &bull; Daniyal College of Health Sciences &bull;
        Generated on {{ now()->format('d M, Y \\a\\t h:i A') }}
    </div>
</div>

</body>
</html>
