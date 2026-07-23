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
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #ffffff;
            color: #223042;
            font-size: 7.5pt;
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Voucher wrapper & layouts */
        .voucher-wrapper.portrait {
            width: 100%;
            height: 198mm; /* exactly fits within A4 Landscape page height */
            position: relative;
            overflow: hidden;
            display: block;
        }

        .voucher-inner {
            border: 1px solid #C4CED8;
            border-radius: 4px;
            padding: 2.5mm 3.5mm;
            height: 196mm;
            background: #ffffff;
        }

        /* Header design */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }

        .logo-cell {
            width: 18%;
            vertical-align: middle;
        }

        .college-logo {
            height: 11mm;
            width: auto;
            display: block;
        }

        .title-cell {
            width: 82%;
            vertical-align: middle;
            padding-left: 1.5mm;
        }

        .college-name {
            font-size: 11.5pt;
            font-weight: 800;
            color: #09264A;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            line-height: 1.1;
        }

        .tagline {
            font-size: 7pt;
            color: #E9A92F;
            font-weight: 700;
            font-style: italic;
        }

        .badge-cell {
            text-align: right;
            padding-top: 1mm;
            border-bottom: 2px solid #09264A;
            padding-bottom: 1.5mm;
            margin-bottom: 2mm;
        }

        .copy-badge {
            background-color: #09264A;
            color: #E9A92F;
            font-size: 7.5pt;
            font-weight: 800;
            padding: 0.8mm 2.5mm;
            border-radius: 2px;
            display: inline-block;
            text-transform: uppercase;
        }

        .voucher-title {
            font-size: 8.5pt;
            font-weight: 800;
            color: #09264A;
            margin-top: 1mm;
        }

        .voucher-type-label {
            font-size: 7pt;
            color: #718096;
            font-weight: 700;
        }

        /* Student info table */
        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }

        .student-info-table td {
            padding: 0.8mm 0.5mm;
            vertical-align: middle;
            font-size: 7.5pt;
            border-bottom: 1px solid #F4F8FC;
        }

        .info-label {
            width: 30%;
            font-weight: 700;
            color: #718096;
        }

        .info-value {
            width: 70%;
            color: #223042;
        }

        /* Portrait column student info custom style */
        .student-info-table tr {
            display: table-row;
        }
        
        /* Flatten 2-column info layout into 1-column layout for portrait vertical voucher copy */
        .student-info-table td:nth-child(3) {
            border-top: 1px solid #F4F8FC;
        }

        .text-danger {
            color: #C0392B !important;
        }

        /* Fee Table styling */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            table-layout: fixed;
        }

        .fee-table th, .fee-table td {
            border: 1px solid #C4CED8;
            padding: 0.8mm 1.2mm;
            font-size: 7.5pt;
            line-height: 1.1;
        }

        .fee-table th {
            background-color: #09264A;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.2pt;
        }

        .fee-description {
            overflow-wrap: anywhere;
        }

        .summary-row td {
            background-color: #F4F8FC;
            font-size: 7pt;
            font-weight: 600;
            border-top: 1px dashed #C4CED8;
        }

        .total-row td {
            background-color: #FFF4DA;
            border-top: 2px solid #09264A;
            font-size: 8.5pt;
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
            margin-top: 2.5mm;
        }

        .footer-layout-table td {
            display: block;
            width: 100% !important;
            text-align: left;
            padding-bottom: 2mm;
        }

        .legal-note {
            font-size: 6.8pt;
            color: #718096;
            line-height: 1.2;
            margin-bottom: 2mm;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 2mm;
        }

        .sig-wrapper {
            margin-top: 3mm;
        }

        .sig-line {
            border-top: 1.2px solid #09264A;
            font-size: 7.2pt;
            font-weight: 700;
            color: #09264A;
            padding-top: 0.8mm;
            display: inline-block;
            width: 60%;
        }

        /* Dynamic Row Fitting Styles */
        .density-compact .voucher-inner {
            padding: 1.8mm 2.5mm;
        }
        .density-compact .student-info-table td {
            padding: 0.5mm 0.4mm;
            font-size: 7pt;
        }
        .density-compact .fee-table th, .density-compact .fee-table td {
            padding: 0.6mm 0.8mm;
            font-size: 7pt;
        }

        .density-dense .voucher-inner {
            padding: 1.2mm 2mm;
        }
        .density-dense .student-info-table td {
            padding: 0.3mm 0.3mm;
            font-size: 6.8pt;
        }
        .density-dense .fee-table th, .density-dense .fee-table td {
            padding: 0.5mm 0.6mm;
            font-size: 6.8pt;
        }
        .density-dense .legal-note {
            font-size: 6pt;
        }
    </style>
</head>
<body>

    @php
        $copies = ['Bank Copy', 'Accounts Office Copy', 'Student Copy'];
    @endphp

    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
        <tr>
            <td style="width: 32.5%; padding-right: 2mm; vertical-align: top;">
                @include('fees.vouchers.partials.voucher-content', [
                    'copyLabel' => 'Bank Copy',
                    'voucher' => $voucher,
                    'layout' => 'portrait'
                ])
            </td>
            <td style="width: 32.5%; padding-left: 1mm; padding-right: 1mm; border-left: 1.5px dashed #CBD5E1; vertical-align: top;">
                @include('fees.vouchers.partials.voucher-content', [
                    'copyLabel' => 'Accounts Office Copy',
                    'voucher' => $voucher,
                    'layout' => 'portrait'
                ])
            </td>
            <td style="width: 32.5%; padding-left: 2mm; border-left: 1.5px dashed #CBD5E1; vertical-align: top;">
                @include('fees.vouchers.partials.voucher-content', [
                    'copyLabel' => 'Student Copy',
                    'voucher' => $voucher,
                    'layout' => 'portrait'
                ])
            </td>
        </tr>
    </table>

</body>
</html>
