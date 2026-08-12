<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Admission Agreement - {{ $admission->applicant_name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #FFF;
            color: #1E293B;
            font-size: 10px;
            line-height: 1.4;
            padding: 15px;
        }

        .document-container {
            width: 100%;
            background: #FFFFFF;
            padding: 15px;
        }

        /* Header */
        .header {
            width: 100%;
            border-bottom: 2px solid #0A1526;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-text h1 {
            font-size: 16px;
            font-weight: bold;
            color: #0A1526;
            text-transform: uppercase;
        }
        .brand-text p {
            font-size: 9px;
            color: #64748B;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #0A1526;
            text-transform: uppercase;
            text-align: right;
        }
        .doc-ref {
            font-size: 10px;
            font-weight: bold;
            color: #D89A34;
            text-align: right;
        }
        .student-photo-pdf {
            width: 70px;
            height: 85px;
            border: 2px solid #0A1526;
            border-radius: 4px;
            object-fit: cover;
            object-position: center;
        }
        .student-photo-placeholder-pdf {
            width: 70px;
            height: 85px;
            border: 2px dashed #0A1526;
            border-radius: 4px;
            color: #64748B;
            font-size: 8px;
            text-align: center;
            padding-top: 29px;
        }

        /* Grid Cards using tables for DomPDF */
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px 0;
            margin-top: 10px;
        }
        .card-cell {
            width: 50%;
            vertical-align: top;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #EBB45A;
            border-radius: 6px;
            padding: 12px;
        }
        .card-header {
            font-size: 11px;
            font-weight: bold;
            color: #0A1526;
            margin-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 4px 0;
            vertical-align: top;
            border-bottom: 1px dashed #E2E8F0;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-label {
            font-weight: 500;
            color: #64748B;
            width: 40%;
        }
        .data-value {
            font-weight: bold;
            color: #0F172A;
            width: 60%;
        }

        /* Fee Section */
        .fee-container {
            width: 100%;
            margin-top: 15px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #EBB45A;
            border-radius: 6px;
            padding: 12px;
        }
        .fee-table {
            width: 100%;
            border-collapse: collapse;
        }
        .fee-summary-box {
            background: #0A1526;
            color: #FFFFFF;
            border-radius: 6px;
            padding: 12px 15px;
            text-align: right;
        }
        .fee-row {
            font-size: 10px;
            color: #CBD5E1;
            margin-bottom: 4px;
        }
        .fee-total {
            border-top: 1px solid #334155;
            padding-top: 6px;
            margin-top: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #EBB45A;
            text-transform: uppercase;
        }

        /* Schedule Table */
        .schedule-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .schedule-table th {
            background: #0A1526;
            color: #EBB45A;
            padding: 5px 8px;
            text-align: left;
            font-weight: bold;
        }
        .schedule-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #E2E8F0;
        }

        /* Policy Section */
        .policy-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
        }
        .policy-intro {
            font-style: italic;
            color: #64748B;
            margin-bottom: 8px;
            font-size: 9px;
        }
        .policy-table {
            width: 100%;
            border-collapse: collapse;
        }
        .policy-table td {
            width: 50%;
            padding: 2px 5px;
            vertical-align: top;
            font-size: 8.5px;
            color: #334155;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-top: 2px solid #0A1526;
            padding-top: 10px;
        }
        .sig-cell {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
        }
        .thumb-box-pdf {
            width: 65px;
            height: 60px;
            border: 1.5px dashed #0A1526;
            border-radius: 4px;
            margin: 0 auto 5px auto;
            text-align: center;
            font-size: 8px;
            color: #64748B;
            padding-top: 20px;
            background: #F8FAFC;
        }
        .sig-line {
            border-top: 1px dashed #64748B;
            margin: 15px auto 4px auto;
            width: 80%;
            font-weight: bold;
            color: #0A1526;
            font-size: 9.5px;
        }
        .sig-date {
            font-size: 8.5px;
            color: #64748B;
        }
    </style>
</head>
<body>

    <div class="document-container">
        <!-- Header Table -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 56%;">
                        <img src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="Daniyal Group of Colleges" style="width: 58px; height: 58px; object-fit: contain; float: left; margin-right: 12px;">
                        <div class="brand-text">
                            <h1>Daniyal Group of Colleges</h1>
                            <p>WHERE SUCCESS IS A TRADITION — {{ strtoupper($admission->campus->name ?? 'CAMPUS PENDING') }}</p>
                        </div>
                    </td>
                    <td style="width: 31%; text-align: right; white-space: nowrap;">
                        <div class="doc-title">STUDENT AGREEMENT</div>
                        <div class="doc-ref">Ref: #{{ $admission->enrollment_number ?? 'ADM-' . date('Y') . '-' . str_pad($admission->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </td>
                    <td style="width: 13%; text-align: right; padding-left: 10px;">
                        @if($studentPhotoDataUri)
                            <img src="{{ $studentPhotoDataUri }}" class="student-photo-pdf" alt="Student photo">
                        @else
                            <div class="student-photo-placeholder-pdf">AFFIX PHOTO<br>HERE</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2-Column Section Cards -->
        <table class="grid-table">
            <tr>
                <td class="card-cell">
                    <div class="card-header">Student Information</div>
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Full Name:</td>
                            <td class="data-value">{{ $admission->applicant_name }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Email:</td>
                            <td class="data-value">{{ $admission->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Phone:</td>
                            <td class="data-value">{{ $admission->phone }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">CNIC/Passport:</td>
                            <td class="data-value">{{ $admission->cnic }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Date of Birth:</td>
                            <td class="data-value">{{ $admission->dob ? $admission->dob->format('Y-m-d') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Address:</td>
                            <td class="data-value">{{ $admission->address }}</td>
                        </tr>
                    </table>
                </td>

                <td class="card-cell">
                    <div class="card-header">Academic & Course Details</div>
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Enrolled Course:</td>
                            <td class="data-value" style="color: #D89A34;">{{ $admission->course->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Qualification:</td>
                            <td class="data-value">{{ $admission->matric_degree ?? 'Matric / SSC' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Institution:</td>
                            <td class="data-value">{{ $admission->matric_board ?? 'BISE Board' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Shift / Session:</td>
                            <td class="data-value">{{ ucfirst($admission->shift ?? 'morning') }} ({{ $admission->academicSession->name ?? '2026' }})</td>
                        </tr>
                        <tr>
                            <td class="data-label">Emergency Contact:</td>
                            <td class="data-value">{{ $admission->emergency_contact ?? $admission->phone }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Registration Date:</td>
                            <td class="data-value">{{ $admission->created_at ? $admission->created_at->format('d M Y') : date('d M Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @php
            $feeStructure = \App\Models\FeeStructure::where('course_id', $admission->course_id)
                ->where(function ($q) use ($admission) {
                    $q->where('campus_id', $admission->campus_id)
                      ->orWhereNull('campus_id')
                      ->orWhere('campus_id', 0)
                      ->orWhere('campus_id', '');
                })
                ->orderByRaw('campus_id IS NOT NULL AND campus_id != 0 AND campus_id != "" DESC')
                ->first();

            if (!$feeStructure) {
                $feeStructure = \App\Models\FeeStructure::where('course_id', $admission->course_id)->first() ?? \App\Models\FeeStructure::first();
            }

            $totalFee = (float)($feeStructure?->total_fee ?? 0);
            $concession = (float)($admission->concession_amount ?? 0);
            $finalAmount = max(0, $totalFee - $concession);
            $installmentCount = (int)($feeStructure?->installment_count ?? 12);
            $perInstallment = $installmentCount > 0 ? round($finalAmount / $installmentCount, 2) : $finalAmount;
        @endphp

        <!-- Fee Structure Card -->
        <div class="fee-container">
            <table class="fee-table">
                <tr>
                    <td style="vertical-align: middle;">
                        <div class="card-header" style="border: none;">Fee Structure & Payment Plan</div>
                        <table class="data-table" style="width: 90%;">
                            <tr>
                                <td class="data-label">Plan Type:</td>
                                <td class="data-value">{{ $installmentCount > 1 ? 'Monthly Installments' : 'Lumpsum' }}</td>
                            </tr>
                            <tr>
                                <td class="data-label">Installments:</td>
                                <td class="data-value">{{ $installmentCount }} Payments of PKR {{ number_format($perInstallment, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 45%; vertical-align: middle;">
                        <div class="fee-summary-box">
                            <div class="fee-row">Total Course Fee: PKR {{ number_format($totalFee, 2) }}</div>
                            <div class="fee-row" style="color: #EBB45A;">Discount / Concession: -PKR {{ number_format($concession, 2) }}</div>
                            <div class="fee-total">FINAL AMOUNT: PKR {{ number_format($finalAmount, 2) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Installment Schedule Table -->
        <div class="schedule-card">
            <div class="card-header" style="border: none; margin-bottom: 4px;">Month-by-Month Fee Installment Schedule</div>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Installment #</th>
                        <th>Title / Description</th>
                        <th>Estimated Due Date</th>
                        <th style="text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= $installmentCount; $i++)
                        <tr style="{{ $i % 2 === 0 ? 'background: #F8FAFC;' : '' }}">
                            <td>Installment {{ $i }} of {{ $installmentCount }}</td>
                            <td>Monthly Fee Installment {{ $i }}</td>
                            <td>{{ now()->addMonths($i - 1)->format('10 M Y') }}</td>
                            <td style="text-align: right; font-weight: bold;">PKR {{ number_format($perInstallment, 2) }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Institute Policy Card -->
        <div class="policy-card">
            <div class="card-header" style="border: none; margin-bottom: 2px;">Institute Regulation Policy</div>
            <div class="policy-intro">By signing below, I acknowledge that I have read and agree to abide by the following regulations:</div>

            <table class="policy-table">
                <tr>
                    <td><strong>1. Attendance:</strong> Min 80% required. >15 mins late is absent.</td>
                    <td><strong>9. Certification:</strong> Requires project & assessment completion.</td>
                </tr>
                <tr>
                    <td><strong>2. Code of Conduct:</strong> Professionalism expected. Harassment leads to dismissal.</td>
                    <td><strong>10. Grievances:</strong> Written complaints responded in 48 hours.</td>
                </tr>
                <tr>
                    <td><strong>3. Fee Policy:</strong> Late fees incur per-day penalties.</td>
                    <td><strong>11. Privacy:</strong> Data secured & not shared with third parties.</td>
                </tr>
                <tr>
                    <td><strong>4. Refunds:</strong> 100% in 7 days, 50% in 15 days. None after start.</td>
                    <td><strong>12. Dismissal:</strong> Non-payment or misconduct causes dismissal.</td>
                </tr>
                <tr>
                    <td><strong>5. Equipment:</strong> Student liable for property damage.</td>
                    <td><strong>13. Safety:</strong> Follow emergency protocols & report medical issues.</td>
                </tr>
                <tr>
                    <td><strong>6. Integrity:</strong> Plagiarism leads to termination without refund.</td>
                    <td><strong>14. Visitors:</strong> Non-students prohibited in labs.</td>
                </tr>
                <tr>
                    <td><strong>7. Internet:</strong> Educational lab use only. No personal downloads.</td>
                    <td><strong>15. Updates:</strong> Institute reserves right to update policies.</td>
                </tr>
                <tr>
                    <td><strong>8. Attire:</strong> Smart casual code. No offensive slogans.</td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- Signature Table -->
        <table class="signature-table">
            <tr>
                <td class="sig-cell">
                    <div class="thumb-box-pdf">Thumb Impression</div>
                    <div class="sig-line">Student Signature</div>
                    <div class="sig-date">Date: ________________________</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-line" style="margin-top: 65px;">Father / Guardian Signature</div>
                    <div class="sig-date">Date: ________________________</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-line" style="margin-top: 65px;">Institute Representative</div>
                    <div class="sig-date">Date: ________________________</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
