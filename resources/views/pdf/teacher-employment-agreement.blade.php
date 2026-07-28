<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Employment Agreement - {{ $agreementNumber }}</title>
    <style>
        @page {
            margin: 110px 50px 80px 50px;
        }
        header {
            position: fixed;
            top: -90px;
            left: 0px;
            right: 0px;
            height: 80px;
            border-bottom: 2px solid #C9963C;
            padding-bottom: 8px;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            border-top: 1px solid #D8E2EC;
            text-align: center;
            font-size: 8pt;
            color: #5F6F80;
            padding-top: 6px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #102033;
        }
        .logo {
            float: left;
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .header-title {
            float: left;
            margin-left: 12px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 14pt;
            color: #071B33;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            color: #C9963C;
            font-weight: bold;
        }
        .header-meta {
            float: right;
            text-align: right;
            font-size: 8pt;
            color: #5F6F80;
        }
        .header-meta strong {
            color: #071B33;
        }
        .clear {
            clear: both;
        }
        .title-block {
            text-align: center;
            margin: 15px 0 20px 0;
            padding: 10px;
            background-color: #F4F7FB;
            border: 1px solid #D8E2EC;
            border-radius: 4px;
        }
        .title-block h2 {
            margin: 0;
            font-size: 13pt;
            color: #071B33;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .title-block p {
            margin: 4px 0 0 0;
            font-size: 8.5pt;
            color: #5F6F80;
        }
        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            color: #071B33;
            text-transform: uppercase;
            border-bottom: 1px solid #C9963C;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table th, .info-table td {
            padding: 5px 8px;
            font-size: 9pt;
            border: 1px solid #E2E8F0;
        }
        .info-table th {
            background-color: #F8FAFC;
            color: #071B33;
            text-align: left;
            width: 32%;
        }
        .clause-text {
            text-align: justify;
            margin-bottom: 8px;
            font-size: 9pt;
        }
        .clause-list {
            margin: 4px 0 10px 18px;
            padding: 0;
        }
        .clause-list li {
            margin-bottom: 4px;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding: 10px 15px;
        }
        .sig-box {
            border-top: 1px solid #102033;
            padding-top: 5px;
            margin-top: 45px;
            text-align: center;
        }
    </style>
</head>
<body>

    <header>
        <img class="logo" src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="DGC Logo">
        <div class="header-title">
            <h1>Daniyal Group of Colleges</h1>
            <p>Where Success Is a Tradition &bull; Central HR Directorate</p>
        </div>
        <div class="header-meta">
            <p><strong>Agreement No:</strong> {{ $agreementNumber }}</p>
            <p><strong>Date:</strong> {{ $date }}</p>
            <p><strong>Version:</strong> V{{ $version }}.0</p>
        </div>
        <div class="clear"></div>
    </header>

    <footer>
        Daniyal Group of Colleges &bull; Central Secretariat, Punjab, Pakistan &bull; Confidential Employment Document &bull; Page <script type="php">if (isset($pdf)) { echo $pdf->get_page_number(); }</script>
    </footer>

    <main>
        <div class="title-block">
            <h2>Teacher Employment Agreement</h2>
            <p>Formal Contract of Academic Appointment & Remuneration Terms</p>
        </div>

        <div class="clause-text">
            This <strong>Teacher Employment Agreement</strong> ("Agreement") is executed on <strong>{{ $date }}</strong> at the Central Administration Office of <strong>Daniyal Group of Colleges</strong>, by and between:
        </div>

        <div class="clause-text">
            <strong>1. DANIYAL GROUP OF COLLEGES</strong> (hereinafter referred to as the "College" or "Employer"), operating through its assigned campus <strong>{{ $staff->campus?->name }}</strong>; AND
        </div>

        <div class="clause-text">
            <strong>2. {{ strtoupper($staff->full_name) }}</strong>, holder of CNIC No. <strong>{{ $staff->cnic }}</strong>, residing at {{ $staff->current_address ?? 'Registered Address' }} (hereinafter referred to as the "Teacher" or "Employee").
        </div>

        <div class="section-heading">1. Personal & Appointment Particulars</div>

        <table class="info-table">
            <tr>
                <th>Employee ID</th>
                <td><strong>{{ $staff->employee_id }}</strong></td>
                <th>Assigned Campus</th>
                <td>{{ $staff->campus?->name }}</td>
            </tr>
            <tr>
                <th>Teacher Full Name</th>
                <td>{{ $staff->full_name }}</td>
                <th>CNIC Number</th>
                <td>{{ $staff->cnic }}</td>
            </tr>
            <tr>
                <th>Academic Designation</th>
                <td><strong>{{ $employment?->designation ?? $staff->designation }}</strong></td>
                <th>Staff Category</th>
                <td>{{ ucfirst($staff->staff_category ?? 'Teaching') }}</td>
            </tr>
            <tr>
                <th>Employment Type</th>
                <td>{{ ucfirst(str_replace('_', ' ', $employment?->employment_type ?? 'Full-Time')) }}</td>
                <th>Appointment Status</th>
                <td><strong>{{ ucfirst($employment?->appointment_status ?? 'Probation') }}</strong></td>
            </tr>
            <tr>
                <th>Date of Joining</th>
                <td>{{ $employment?->joining_date?->format('d-M-Y') ?? $staff->joining_date?->format('d-M-Y') }}</td>
                <th>Notice Period</th>
                <td>{{ $salary?->employee_notice_days ?? 30 }} Days</td>
            </tr>
        </table>

        <div class="section-heading">2. Terms of Remuneration & Benefits</div>
        <div class="clause-text">
            The Teacher shall receive a monthly gross salary of <strong>PKR {{ number_format($salary?->gross_salary ?? 0, 2) }}</strong> payable in accordance with the standard monthly college payroll cycle. The detailed breakdown of salary components is specified below:
        </div>

        <table class="info-table">
            <tr>
                <th>Basic Salary</th>
                <td>PKR {{ number_format($salary?->basic_salary ?? 0, 2) }}</td>
                <th>House Allowance</th>
                <td>PKR {{ number_format($salary?->house_allowance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Transport Allowance</th>
                <td>PKR {{ number_format($salary?->transport_allowance ?? 0, 2) }}</td>
                <th>Medical Allowance</th>
                <td>PKR {{ number_format($salary?->medical_allowance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Other Allowances</th>
                <td>PKR {{ number_format($salary?->other_allowance ?? 0, 2) }}</td>
                <th>Gross Monthly Salary</th>
                <td><strong>PKR {{ number_format($salary?->gross_salary ?? 0, 2) }}</strong></td>
            </tr>
        </table>

        <div class="section-heading">3. Key Terms & Operating Policies</div>
        
        <div class="clause-text"><strong>3.1 Probation & Permanent Confirmation:</strong></div>
        <div class="clause-text">
            If appointed under probation status, the probation period shall be six (6) months starting from the Joining Date. Confirmation of permanent employment is subject to satisfactory performance appraisal by the Campus Principal and Super Admin HR.
        </div>

        <div class="clause-text"><strong>3.2 Teaching Workload & Responsibilities:</strong></div>
        <div class="clause-text">
            The Teacher agrees to conduct lectures, laboratory practicals, clinical demonstrations, periodic assessments, semester examinations, and institutional academic duties as assigned by the Head of Department or Principal. Minimum weekly teaching workload is {{ $employment?->weekly_teaching_hours ?? 20 }} hours.
        </div>

        <div class="clause-text"><strong>3.3 Attendance & Punctuality:</strong></div>
        <div class="clause-text">
            The Teacher shall adhere to college working hours and register check-in and check-out daily via the biometric attendance system. Leave requests must be submitted through the Campus Administration in advance.
        </div>

        <div class="clause-text"><strong>3.4 Confidentiality & Conduct:</strong></div>
        <div class="clause-text">
            The Teacher shall maintain strict confidentiality regarding student records, examination papers, institutional documents, and proprietary curriculum materials.
        </div>

        <div class="section-heading">4. Execution & Signatures</div>

        <table class="signature-table">
            <tr>
                <td>
                    <div class="sig-box">
                        <strong>TEACHER SIGNATURE</strong><br>
                        Name: {{ $staff->full_name }}<br>
                        CNIC: {{ $staff->cnic }}
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <strong>SUPER ADMIN / COLLEGE AUTHORITY</strong><br>
                        Daniyal Group of Colleges<br>
                        Official Stamp & Signature
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="sig-box">
                        <strong>CAMPUS PRINCIPAL / WITNESS 1</strong><br>
                        Campus: {{ $staff->campus?->name }}
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <strong>HR DIRECTORATE / WITNESS 2</strong><br>
                        Central HR Division
                    </div>
                </td>
            </tr>
        </table>

    </main>

</body>
</html>
