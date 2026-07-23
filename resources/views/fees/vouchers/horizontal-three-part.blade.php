<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Voucher - {{ $voucher->voucher_number }}</title>
    <style>
        /* CSS reset & base settings */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #ffffff;
            color: #223042;
            font-size: 8.5pt;
            line-height: 1.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Voucher wrapper & layouts */
        .voucher-wrapper {
            width: 100%;
            height: 94mm; /* exactly 1/3 of A4 Portrait page height minus margins */
            position: relative;
            overflow: hidden;
            display: block;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .voucher-inner {
            border: 1px solid #C4CED8;
            border-radius: 4px;
            padding: 3mm 4mm;
            height: 89mm;
            background: #ffffff;
        }

        .cut-indicator {
            height: 5mm;
            text-align: center;
            font-size: 7.5pt;
            color: #718096;
            vertical-align: middle;
            line-height: 5mm;
            user-select: none;
        }

        /* Header design matching brand colors */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }

        .logo-cell {
            width: 12%;
            vertical-align: middle;
        }

        .college-logo {
            height: 12mm;
            width: auto;
            display: block;
        }

        .title-cell {
            width: 48%;
            vertical-align: middle;
            padding-left: 2mm;
        }

        .college-name {
            font-size: 13.5pt;
            font-weight: 800;
            color: #09264A;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            line-height: 1.1;
        }

        .tagline {
            font-size: 7.5pt;
            color: #E9A92F;
            font-weight: 700;
            font-style: italic;
            margin-top: 0.5mm;
        }

        .badge-cell {
            width: 40%;
            text-align: right;
            vertical-align: middle;
        }

        .copy-badge {
            background-color: #09264A;
            color: #E9A92F;
            font-size: 8pt;
            font-weight: 800;
            padding: 1mm 3mm;
            border-radius: 3px;
            display: inline-block;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .voucher-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: #09264A;
        }

        .voucher-type-label {
            font-size: 7.5pt;
            color: #718096;
            font-weight: 700;
        }

        /* Student info table */
        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }

        .student-info-table td {
            padding: 0.8mm 1mm;
            vertical-align: middle;
            font-size: 8pt;
            border-bottom: 1px solid #F4F8FC;
        }

        .info-label {
            width: 15%;
            font-weight: 700;
            color: #718096;
        }

        .info-value {
            width: 35%;
            color: #223042;
        }

        .text-danger {
            color: #C0392B !important;
        }

        /* Fee Table styling */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
            table-layout: fixed;
        }

        .fee-table th, .fee-table td {
            border: 1px solid #C4CED8;
            padding: 1mm 1.5mm;
            font-size: 8pt;
            line-height: 1.1;
        }

        .fee-table th {
            background-color: #09264A;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .fee-description {
            overflow-wrap: anywhere;
        }

        .summary-row td {
            background-color: #F4F8FC;
            font-size: 7.5pt;
            font-weight: 600;
            border-top: 1px dashed #C4CED8;
        }

        .total-row td {
            background-color: #FFF4DA;
            border-top: 2px solid #09264A;
            font-size: 9pt;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        /* Footer signatures and notes */
        .footer-layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm;
        }

        .notes-cell {
            width: 50%;
            vertical-align: top;
        }

        .sig-cell {
            width: 25%;
            vertical-align: bottom;
            text-align: center;
        }

        .legal-note {
            font-size: 6.8pt;
            color: #718096;
            line-height: 1.2;
            padding-right: 2mm;
        }

        .sig-wrapper {
            padding: 0 2mm;
        }

        .sig-line {
            border-top: 1.2px solid #09264A;
            font-size: 7.5pt;
            font-weight: 700;
            color: #09264A;
            padding-top: 1mm;
            margin-top: 5mm;
        }

        /* Dynamic Row Fitting Styles */
        .density-compact .voucher-inner {
            padding: 2mm 3mm;
        }
        .density-compact .student-info-table td {
            padding: 0.5mm 0.8mm;
            font-size: 7.5pt;
        }
        .density-compact .fee-table th, .density-compact .fee-table td {
            padding: 0.8mm 1mm;
            font-size: 7.5pt;
        }

        .density-dense .voucher-inner {
            padding: 1.5mm 2.5mm;
        }
        .density-dense .student-info-table td {
            padding: 0.3mm 0.6mm;
            font-size: 7pt;
        }
        .density-dense .fee-table th, .density-dense .fee-table td {
            padding: 0.6mm 0.8mm;
            font-size: 7pt;
        }
        .density-dense .legal-note {
            font-size: 6.2pt;
        }
        .density-dense .sig-line {
            font-size: 7pt;
            margin-top: 3.5mm;
        }
        .voucher-page {
            page-break-after: always;
        }
        .voucher-page:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>

    @php
        $copies = ['Bank Copy', 'Accounts Office Copy', 'Student Copy'];
        $printVouchers = $vouchers ?? collect([$voucher]);
    @endphp

    @foreach($printVouchers as $printVoucher)
        <div class="voucher-page">
            @foreach($copies as $copy)
                @include('fees.vouchers.partials.voucher-content', [
                    'copyLabel' => $copy,
                    'voucher' => $printVoucher,
                    'layout' => 'horizontal'
                ])
            @endforeach
        </div>
    @endforeach

</body>
</html>
