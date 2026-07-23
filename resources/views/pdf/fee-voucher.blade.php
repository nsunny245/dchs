<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Voucher Invoice - {{ $voucher->voucher_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #fff;
            color: #1E293B;
            font-size: 10px;
            line-height: 1.3;
            padding: 10px;
        }
        .voucher-part {
            height: 31%;
            box-sizing: border-box;
            padding: 12px 16px;
            border-bottom: 2px dashed #CBD5E1;
            position: relative;
            margin-bottom: 10px;
        }
        .voucher-part:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #0A1526;
            padding-bottom: 6px;
        }
        .logo {
            height: 38px;
            width: auto;
            vertical-align: middle;
        }
        .college-title {
            font-size: 13px;
            font-weight: bold;
            color: #0A1526;
            text-transform: uppercase;
        }
        .campus-title {
            font-size: 9px;
            color: #D89A34;
            font-weight: bold;
        }
        .copy-badge {
            background: #0A1526;
            color: #EBB45A;
            font-size: 9px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: right;
            display: inline-block;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
            border-bottom: 1px dashed #F1F5F9;
        }
        .meta-label {
            font-weight: bold;
            color: #64748B;
            width: 18%;
        }
        .meta-value {
            color: #0F172A;
            font-weight: bold;
            width: 32%;
        }
        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .amount-table th, .amount-table td {
            border: 1px solid #E2E8F0;
            padding: 5px 8px;
            font-size: 9.5px;
        }
        .amount-table th {
            background: #F8FAFC;
            color: #0A1526;
            font-weight: bold;
            text-align: left;
        }
        .footer-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .instruction-cell {
            font-size: 8px;
            color: #64748B;
            vertical-align: bottom;
            width: 70%;
        }
        .signature-cell {
            text-align: center;
            vertical-align: bottom;
            width: 30%;
        }
        .sig-line {
            border-top: 1px solid #0A1526;
            margin-top: 15px;
            padding-top: 2px;
            font-size: 8.5px;
            font-weight: bold;
            color: #0A1526;
        }
    </style>
</head>
<body>

    @php
        $copies = ['Bank Copy', 'Accounts Copy', 'Student Copy'];
        $courseId = $voucher->student?->course_id;
        $campusId = $voucher->student?->campus_id;
        $structure = \App\Models\FeeStructure::where('course_id', $courseId)
            ->where(function ($q) use ($campusId) {
                $q->where('campus_id', $campusId)->orWhereNull('campus_id');
            })->first() ?? \App\Models\FeeStructure::first();
        $lateFee = (float)($structure?->late_fee ?? 100);
    @endphp

    @foreach($copies as $copy)
        <div class="voucher-part">
            <table class="header-table">
                <tr>
                    <td style="width: 50px;">
                        <img src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" class="logo">
                    </td>
                    <td>
                        <div class="college-title">Daniyal Group of Colleges</div>
                        <div class="campus-title">{{ strtoupper($voucher->student->campus->name ?? 'DGC CAMPUS') }}</div>
                    </td>
                    <td style="text-align: right;">
                        <div class="copy-badge">{{ $copy }}</div>
                        <div style="font-size: 10px; font-weight: bold; color: #0A1526; margin-top: 3px;">Ref: #{{ $voucher->voucher_number }}</div>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td class="meta-label">Student Name:</td>
                    <td class="meta-value">{{ $voucher->student->full_name }}</td>
                    <td class="meta-label">Enrollment #:</td>
                    <td class="meta-value">{{ $voucher->student->enrollment_number }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Father's Name:</td>
                    <td class="meta-value">{{ $voucher->student->admission->father_name ?? 'N/A' }}</td>
                    <td class="meta-label">Course Program:</td>
                    <td class="meta-value">{{ $voucher->student->course->name }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Voucher Title:</td>
                    <td class="meta-value">{{ $voucher->title }}</td>
                    <td class="meta-label">Due Date:</td>
                    <td class="meta-value" style="color: #C0392B;">{{ $voucher->due_date ? $voucher->due_date->format('d-M-Y') : 'N/A' }}</td>
                </tr>
            </table>

            <table class="amount-table">
                <thead>
                    <tr>
                        <th>Fee Head Description</th>
                        <th style="text-align: right; width: 130px;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $voucher->title }}</strong> (Installment Dues)</td>
                        <td style="text-align: right; font-weight: bold; color: #0A1526;">PKR {{ number_format($voucher->amount, 2) }}</td>
                    </tr>
                    <tr style="background: #FFFBEB;">
                        <td style="font-size: 8.5px; color: #92400E;">* Late Payment Fine (per day after due date)</td>
                        <td style="text-align: right; color: #92400E; font-size: 8.5px; font-weight: bold;">PKR {{ number_format($lateFee, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table">
                <tr>
                    <td class="instruction-cell">
                        <strong>Instructions:</strong> Payable at designated bank branches or campus cashier accounts. Obtain official stamped receipt upon payment.
                    </td>
                    <td class="signature-cell">
                        <div class="sig-line">Authorized Cashier / Stamp</div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

</body>
</html>
