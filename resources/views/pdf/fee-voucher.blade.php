<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Voucher - {{ $voucher->voucher_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            color: #333;
        }
        .voucher-part {
            height: 32%;
            box-sizing: border-box;
            padding: 10px 15px;
            border-bottom: 2px dashed #999;
            position: relative;
        }
        .voucher-part:last-child {
            border-bottom: none;
        }
        .header {
            margin-bottom: 8px;
        }
        .logo-section {
            float: left;
            width: 50px;
        }
        .logo {
            width: 40px;
            height: 40px;
        }
        .title-section {
            float: left;
            margin-left: 10px;
        }
        .college-name {
            font-size: 14px;
            font-weight: bold;
            color: #0A1526;
        }
        .campus-name {
            font-size: 10px;
            color: #b38f00;
            font-weight: bold;
        }
        .copy-tag {
            float: right;
            border: 1px solid #000;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .clear {
            clear: both;
        }
        .meta-grid {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .meta-grid td {
            padding: 3px 0;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 80px;
        }
        .value {
            color: #111;
        }
        .amount-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .amount-table th, .amount-table td {
            border: 1px solid #ddd;
            padding: 4px 8px;
            text-align: left;
        }
        .amount-table th {
            bg-color: #f5f5f5;
            font-weight: bold;
        }
        .footer-note {
            font-size: 8px;
            color: #666;
            margin-top: 6px;
            float: left;
            width: 70%;
        }
        .signatures {
            float: right;
            width: 25%;
            text-align: right;
            margin-top: 15px;
            font-size: 9px;
            border-top: 1px solid #777;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    @php
        $copies = ['Bank Copy', 'Accounts Copy', 'Student Copy'];
    @endphp

    @foreach($copies as $copy)
        <div class="voucher-part">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ public_path('images/logo.png') }}" class="logo" onerror="this.src='https://dchs.edu.pk/wp-content/uploads/2022/05/crest-300x300.png'">
                </div>
                <div class="title-section">
                    <div class="college-name">Daniyal Group of Colleges</div>
                    <div class="campus-name">{{ $voucher->student->campus->name ?? 'DCHS Campus' }}</div>
                </div>
                <div class="copy-tag">{{ $copy }}</div>
                <div class="clear"></div>
            </div>

            <table class="meta-grid">
                <tr>
                    <td class="label">Voucher No:</td>
                    <td class="value"><strong>{{ $voucher->voucher_number }}</strong></td>
                    <td class="label">Enrollment No:</td>
                    <td class="value"><strong>{{ $voucher->student->enrollment_number }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Student Name:</td>
                    <td class="value">{{ $voucher->student->full_name }}</td>
                    <td class="label">Father's Name:</td>
                    <td class="value">{{ $voucher->student->admission->father_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Course Program:</td>
                    <td class="value">{{ $voucher->student->course->name }}</td>
                    <td class="label">Due Date:</td>
                    <td class="value"><strong>{{ $voucher->due_date ? $voucher->due_date->format('d-M-Y') : 'N/A' }}</strong></td>
                </tr>
            </table>

            <table class="amount-table">
                <thead>
                    <tr style="background-color: #f9f9f9;">
                        <th>Description / Installment Details</th>
                        <th style="text-align: right; width: 120px;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $voucher->title }}</td>
                        <td style="text-align: right; font-weight: bold;">PKR {{ number_format($voucher->amount, 2) }}</td>
                    </tr>
                    <tr style="background-color: #fffaf0;">
                        <td style="font-size: 9px; color: #a04000;">* Late Fee Penalty (if paid after due date, per day)</td>
                        <td style="text-align: right; color: #a04000; font-size: 9px;">PKR {{ number_format($voucher->feeAccount->admission->course->feeStructures->first()->late_fee ?? 100, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer-note">
                <strong>Instructions:</strong> Please deposit the amount in any branch of Allied Bank Limited. Mention student details on deposit slip. Contact admin office for queries.
            </div>
            <div class="signatures">
                Authorized Signature
            </div>
            <div class="clear"></div>
        </div>
    @endforeach

</body>
</html>
