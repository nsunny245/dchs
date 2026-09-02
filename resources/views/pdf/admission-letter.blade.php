<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Letter – {{ $admission->applicant_name }}</title>
    <style>
        /* ── Reset & Base ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            color: #1a1a2e;
            font-size: 12px;
            line-height: 1.6;
        }

        /* ── Outer border ── */
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
            padding-bottom: 18px;
            margin-bottom: 22px;
        }
        .header h1 {
            font-size: 26px;
            color: #0f3460;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .header .tagline {
            font-size: 11px;
            color: #555;
            letter-spacing: 1px;
        }
        .header .campus-name {
            font-size: 13px;
            color: #e94560;
            font-weight: bold;
            margin-top: 4px;
        }

        /* ── Reference bar ── */
        .ref-bar {
            display: flex;
            justify-content: space-between;
            background: #f0f4ff;
            border: 1px solid #d0d9f0;
            border-radius: 4px;
            padding: 8px 14px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .ref-bar table { width: 100%; }
        .ref-bar td { padding: 2px 6px; }
        .ref-bar td:last-child { text-align: right; }

        /* ── Title ── */
        .doc-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #0f3460;
            text-decoration: underline;
            margin-bottom: 18px;
        }

        /* ── Body text ── */
        .body-text {
            text-align: justify;
            margin-bottom: 14px;
            font-size: 12.5px;
        }
        .body-text .name-highlight {
            font-weight: bold;
            color: #0f3460;
        }

        /* ── Details Table ── */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0;
        }
        .details-table th,
        .details-table td {
            border: 1px solid #c0c8e0;
            padding: 8px 14px;
            text-align: left;
            font-size: 12px;
        }
        .details-table th {
            background: #0f3460;
            color: #ffffff;
            font-weight: 600;
            width: 35%;
        }
        .details-table td {
            background: #fafbff;
        }

        /* ── Status Badge ── */
        .status-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-approved,
        .status-enrolled { background: #d4edda; color: #155724; }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-waitlisted { background: #e2e3e5; color: #383d41; }

        /* ── Instructions ── */
        .instructions {
            background: #fffbe6;
            border-left: 4px solid #e94560;
            padding: 12px 16px;
            margin: 18px 0;
            font-size: 11.5px;
        }
        .instructions h4 {
            color: #e94560;
            margin-bottom: 6px;
        }
        .instructions ul {
            margin-left: 18px;
        }
        .instructions li {
            margin-bottom: 4px;
        }

        /* ── Signature ── */
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-area table { width: 100%; }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #333;
            width: 180px;
            margin: 0 auto 4px;
        }
        .sig-label {
            font-size: 11px;
            color: #555;
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

        /* ── Watermark ── */
        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            font-size: 80px;
            color: rgba(15, 52, 96, 0.04);
            transform: rotate(-30deg);
            font-weight: bold;
            letter-spacing: 10px;
            z-index: -1;
        }
    </style>
</head>
<body>

<div class="watermark">DGC</div>

<div class="page-border">
    <!-- Header -->
    <div class="header">
        <h1>Daniyal Group of Colleges</h1>
        <div class="tagline">Excellence in Healthcare Education</div>
        <div class="campus-name">{{ $admission->campus->name ?? 'Main Campus' }}</div>
    </div>

    <!-- Reference Bar -->
    <div class="ref-bar">
        <table>
            <tr>
                <td><strong>Ref No:</strong> DGC/ADM/{{ str_pad($admission->id, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</td>
                <td><strong>Date:</strong> {{ now()->format('d M, Y') }}</td>
            </tr>
            <tr>
                <td><strong>Enrollment No:</strong> {{ $admission->enrollment_no ?? 'Pending' }}</td>
                <td><strong>Applied:</strong> {{ $admission->applied_at ? $admission->applied_at->format('d M, Y') : $admission->created_at->format('d M, Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Title -->
    <div class="doc-title">ADMISSION LETTER</div>

    <!-- Body -->
    <div class="body-text">
        Dear <span class="name-highlight">{{ $admission->applicant_name }}</span>,
    </div>

    <div class="body-text">
        We are pleased to confirm that you have been <strong>enrolled</strong> at <strong>Daniyal Group of Colleges</strong>.
        Congratulations on your admission. We look forward to welcoming you and supporting you throughout your academic journey.
    </div>

    <!-- Applicant Details -->
    <table class="details-table">
        <tr>
            <th>Applicant Name</th>
            <td>{{ $admission->applicant_name }}</td>
        </tr>
        <tr>
            <th>Father's Name</th>
            <td>{{ $admission->father_name }}</td>
        </tr>
        <tr>
            <th>CNIC</th>
            <td>{{ $admission->cnic }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $admission->dob ? $admission->dob->format('d M, Y') : '—' }}</td>
        </tr>
        <tr>
            <th>Gender</th>
            <td>{{ ucfirst($admission->gender) }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $admission->phone }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $admission->email ?? '—' }}</td>
        </tr>
        <tr>
            <th>Programme Applied</th>
            <td>{{ $admission->course->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Campus</th>
            <td>{{ $admission->campus->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Admission</th>
            <td>
                <span class="status-badge status-enrolled">
                    Enrolled
                </span>
            </td>
        </tr>
        @if($admission->merit_score)
        <tr>
            <th>Merit Score</th>
            <td>{{ $admission->merit_score }}%</td>
        </tr>
        @endif
    </table>

    <!-- Instructions -->
    <div class="instructions">
        <h4>Important Instructions:</h4>
        <ul>
            <li>Report to the campus within <strong>15 working days</strong> with the original documents for verification.</li>
            <li>Submit the required fee payment to the accounts office to confirm your seat.</li>
            <li>Bring original and photocopies of CNIC, educational certificates, and passport-size photographs.</li>
            <li>Failure to report within the given timeframe may result in cancellation of your admission.</li>
        </ul>
    </div>

    <!-- Signature Area -->
    <div class="signature-area">
        <table>
            <tr>
                <td class="sig-block" style="width:50%;">
                    <br><br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Registrar</div>
                    <div class="sig-label">Daniyal Group of Colleges</div>
                </td>
                <td class="sig-block" style="width:50%;">
                    <br><br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Principal / Director</div>
                    <div class="sig-label">{{ $admission->campus->name ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a computer-generated document. &bull; Daniyal Group of Colleges &bull;
        Generated on {{ now()->format('d M, Y \\a\\t h:i A') }}
    </div>
</div>

</body>
</html>
