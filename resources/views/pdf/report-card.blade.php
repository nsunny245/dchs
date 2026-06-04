<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card – {{ $student->full_name }}</title>
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
            border-bottom: 3px solid #0f3460;
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

        /* ── Report Card Title ── */
        .report-title {
            background: linear-gradient(135deg, #0f3460, #16213e);
            color: #fff;
            text-align: center;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* ── Student Profile ── */
        .profile-section {
            margin-bottom: 20px;
        }
        .profile-section h3 {
            font-size: 13px;
            color: #0f3460;
            border-bottom: 1px solid #c0c8e0;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
        }
        .profile-table th,
        .profile-table td {
            border: 1px solid #dde2f0;
            padding: 7px 12px;
            font-size: 11.5px;
            text-align: left;
        }
        .profile-table th {
            background: #eef1fb;
            color: #333;
            font-weight: 600;
            width: 30%;
        }

        /* ── Marks Table ── */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .marks-table th,
        .marks-table td {
            border: 1px solid #c0c8e0;
            padding: 9px 14px;
            font-size: 12px;
        }
        .marks-table thead th {
            background: #0f3460;
            color: #fff;
            font-weight: 600;
            text-align: left;
        }
        .marks-table tbody td {
            background: #fafbff;
        }
        .marks-table tbody tr:nth-child(even) td {
            background: #f0f3fc;
        }
        .marks-table tfoot td {
            background: #0f3460;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        /* ── Status badges ── */
        .grade-pass {
            color: #155724;
            font-weight: bold;
        }
        .grade-fail {
            color: #721c24;
            font-weight: bold;
        }

        /* ── Summary Box ── */
        .summary-row {
            margin: 18px 0;
        }
        .summary-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box {
            border: 2px solid #0f3460;
            text-align: center;
            padding: 14px;
            width: 32%;
        }
        .summary-box .label {
            font-size: 10px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .summary-box .value {
            font-size: 22px;
            font-weight: bold;
            color: #0f3460;
            margin-top: 4px;
        }
        .summary-box .value.pass { color: #155724; }
        .summary-box .value.fail { color: #721c24; }

        /* ── Grading Key ── */
        .grading-key {
            background: #f9fafb;
            border: 1px solid #dde2f0;
            padding: 12px 16px;
            margin: 18px 0;
            font-size: 10.5px;
        }
        .grading-key h4 {
            color: #0f3460;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .grading-key table {
            width: 100%;
            border-collapse: collapse;
        }
        .grading-key td {
            padding: 3px 10px;
            border: 1px solid #e0e0e0;
            font-size: 10px;
        }
        .grading-key td:first-child {
            font-weight: bold;
            width: 15%;
            text-align: center;
        }

        /* ── Remarks ── */
        .remarks {
            background: #fffbe6;
            border-left: 4px solid #e94560;
            padding: 10px 14px;
            margin: 14px 0;
            font-size: 11px;
        }
        .remarks h4 {
            color: #e94560;
            margin-bottom: 4px;
        }

        /* ── Signature ── */
        .signature-area {
            margin-top: 40px;
        }
        .signature-area table { width: 100%; }
        .sig-block { text-align: center; }
        .sig-line {
            border-top: 1px solid #333;
            width: 150px;
            margin: 0 auto 3px;
        }
        .sig-label {
            font-size: 10px;
            color: #666;
        }

        /* ── Footer ── */
        .footer {
            position: absolute;
            bottom: 18px;
            left: 35px;
            right: 35px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 6px;
            font-size: 9px;
            color: #888;
        }

        .watermark {
            position: fixed;
            top: 38%;
            left: 18%;
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

<div class="watermark">DCHS</div>

<div class="page-border">
    <!-- Header -->
    <div class="header">
        <h1>Daniyal College of Health Sciences</h1>
        <div class="tagline">Excellence in Healthcare Education</div>
        <div class="campus-name">{{ $student->campus->name ?? 'Main Campus' }}</div>
    </div>

    <!-- Title -->
    <div class="report-title">Student Report Card</div>

    <!-- Student Profile -->
    <div class="profile-section">
        <h3>Student Information</h3>
        <table class="profile-table">
            <tr>
                <th>Student Name</th>
                <td>{{ $student->full_name }}</td>
                <th>Enrollment No.</th>
                <td>{{ $student->enrollment_number }}</td>
            </tr>
            <tr>
                <th>Programme</th>
                <td>{{ $student->course->name ?? '—' }}</td>
                <th>Batch Year</th>
                <td>{{ $student->batch_year ?? '—' }}</td>
            </tr>
            <tr>
                <th>Campus</th>
                <td>{{ $student->campus->name ?? '—' }}</td>
                <th>Status</th>
                <td>{{ ucfirst($student->status) }}</td>
            </tr>
        </table>
    </div>

    <!-- Marks Table -->
    @php
        $marks = $student->marks ?? collect();
        $totalObtained = 0;
        $totalMax = 0;
    @endphp

    <div class="profile-section">
        <h3>Examination Results</h3>

        @if($marks->count() > 0)
        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th>Examination</th>
                    <th style="width:12%;">Type</th>
                    <th class="text-center" style="width:12%;">Total Marks</th>
                    <th class="text-center" style="width:12%;">Obtained</th>
                    <th class="text-center" style="width:12%;">Percentage</th>
                    <th class="text-center" style="width:10%;">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marks as $index => $mark)
                @php
                    $maxMarks = $mark->exam->total_marks ?? 100;
                    $obtained = $mark->obtained_marks ?? 0;
                    $percentage = $maxMarks > 0 ? round(($obtained / $maxMarks) * 100, 1) : 0;
                    $isPassing = $percentage >= 50;
                    $totalObtained += $obtained;
                    $totalMax += $maxMarks;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $mark->exam->name ?? '—' }}</td>
                    <td>{{ ucfirst($mark->exam->exam_type ?? '—') }}</td>
                    <td class="text-center">{{ $maxMarks }}</td>
                    <td class="text-center">{{ $obtained }}</td>
                    <td class="text-center">{{ $percentage }}%</td>
                    <td class="text-center">
                        <span class="{{ $isPassing ? 'grade-pass' : 'grade-fail' }}">
                            {{ $isPassing ? 'PASS' : 'FAIL' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Grand Total</td>
                    <td class="text-center">{{ $totalMax }}</td>
                    <td class="text-center">{{ $totalObtained }}</td>
                    <td class="text-center">{{ $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0 }}%</td>
                    <td class="text-center">
                        @php $overallPass = $totalMax > 0 && (($totalObtained / $totalMax) * 100) >= 50; @endphp
                        {{ $overallPass ? 'PASS' : 'FAIL' }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @else
        <div class="remarks">
            <h4>No Results Available</h4>
            <p>No examination results have been recorded for this student yet.</p>
        </div>
        @endif
    </div>

    <!-- Summary Boxes -->
    @if($marks->count() > 0)
    <div class="summary-row">
        <table>
            <tr>
                <td class="summary-box">
                    <div class="label">Total Marks</div>
                    <div class="value">{{ $totalObtained }} / {{ $totalMax }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="summary-box">
                    <div class="label">Overall Percentage</div>
                    <div class="value {{ $overallPass ? 'pass' : 'fail' }}">
                        {{ $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 1) : 0 }}%
                    </div>
                </td>
                <td style="width:2%;"></td>
                <td class="summary-box">
                    <div class="label">Overall Result</div>
                    <div class="value {{ $overallPass ? 'pass' : 'fail' }}">
                        {{ $overallPass ? 'PASSED' : 'FAILED' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Grading Key -->
    <div class="grading-key">
        <h4>Grading Scale</h4>
        <table>
            <tr>
                <td>A+</td><td>90% and above</td>
                <td>A</td><td>80% – 89%</td>
                <td>B+</td><td>70% – 79%</td>
                <td>B</td><td>60% – 69%</td>
            </tr>
            <tr>
                <td>C</td><td>50% – 59%</td>
                <td>D</td><td>40% – 49%</td>
                <td>F</td><td>Below 40%</td>
                <td></td><td></td>
            </tr>
        </table>
    </div>

    <!-- Signature Area -->
    <div class="signature-area">
        <table>
            <tr>
                <td class="sig-block" style="width:33%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Class Instructor</div>
                </td>
                <td class="sig-block" style="width:34%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Controller of Examinations</div>
                </td>
                <td class="sig-block" style="width:33%;">
                    <br><br>
                    <div class="sig-line"></div>
                    <div class="sig-label">Principal / Director</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a computer-generated report card. &bull; Daniyal College of Health Sciences &bull;
        Generated on {{ now()->format('d M, Y \\a\\t h:i A') }}
    </div>
</div>

</body>
</html>
