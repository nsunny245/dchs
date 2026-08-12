<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission Agreement - {{ $admission->applicant_name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            font-size: 11px;
            line-height: 1.4;
            padding: 30px 15px;
        }

        .document-container {
            max-width: 950px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            padding: 30px;
            border: 1px solid #E2E8F0;
        }

        /* Branding Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 3px solid #09264A; /* primary navy */
            position: relative;
            margin-bottom: 20px;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 150px;
            height: 3px;
            background-color: #E9A92F; /* Gold */
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .brand-logo {
            height: 60px;
            width: auto;
        }
        .brand-text h1 {
            font-size: 19px;
            font-weight: 800;
            color: #09264A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .brand-text p {
            font-size: 9.5px;
            color: #64748B;
            font-weight: 600;
        }
        .doc-title-section {
            text-align: right;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .student-photo {
            width: 80px;
            height: 95px;
            border-radius: 6px;
            border: 2px solid #09264A;
            object-fit: cover;
        }
        .student-photo-placeholder {
            width: 80px;
            height: 95px;
            border-radius: 6px;
            border: 2px dashed #09264A;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #64748B;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #09264A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-ref {
            font-size: 11px;
            font-weight: 700;
            color: #E9A92F;
            margin-top: 2px;
        }

        /* 2-Column Grid for Information */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: #F8FAFC;
            border: 1px solid #C4CED8;
            border-left: 4px solid #E9A92F;
            border-radius: 8px;
            padding: 15px;
        }

        .card-header {
            font-size: 12px;
            font-weight: 700;
            color: #09264A;
            margin-bottom: 10px;
            border-bottom: 1px solid #C4CED8;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 5px 0;
            vertical-align: top;
            border-bottom: 1px dashed #E2E8F0;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-label {
            font-weight: 600;
            color: #64748B;
            width: 35%;
            font-size: 10px;
        }
        .data-value {
            font-weight: 700;
            color: #223042;
            width: 65%;
            font-size: 10px;
        }

        /* Full Width Card */
        .card-full {
            background: #F8FAFC;
            border: 1px solid #C4CED8;
            border-left: 4px solid #09264A;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Academic Repeater Styles */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 5px;
        }
        .academic-table th {
            background: #09264A;
            color: #FFFFFF;
            font-weight: 700;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid #C4CED8;
            text-transform: uppercase;
        }
        .academic-table td {
            padding: 6px 10px;
            border: 1px solid #C4CED8;
            color: #223042;
        }

        /* Fee breakdown */
        .fee-flex-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }
        .fee-breakdown-card {
            flex: 1.2;
        }
        .fee-summary-card {
            flex: 0.8;
            background: #09264A;
            color: #FFFFFF;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .fee-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 8px;
            color: #CBD5E1;
        }
        .fee-summary-row.discount {
            color: #E9A92F;
            font-weight: 700;
        }
        .fee-summary-total {
            border-top: 1px solid #334155;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 800;
            color: #E9A92F;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
        }

        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 5px;
        }
        .schedule-item {
            background: #FFF;
            border: 1px solid #C4CED8;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .schedule-title {
            font-weight: 800;
            color: #09264A;
            font-size: 9.5px;
            margin-bottom: 3px;
        }
        .schedule-date {
            font-size: 8.5px;
            color: #64748B;
            margin-bottom: 5px;
        }
        .schedule-amount {
            font-weight: 700;
            color: #E9A92F;
            font-size: 11px;
        }

        /* Regulations */
        .policy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
            margin-top: 5px;
        }
        .policy-item {
            font-size: 9.5px;
            color: #223042;
            line-height: 1.35;
        }

        /* Signatures Section with separate Thumb box */
        .signature-container {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #09264A;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-box {
            width: 22%;
            text-align: center;
        }
        .thumb-box {
            width: 75px;
            height: 80px;
            border: 2px dashed #09264A;
            border-radius: 6px;
            margin: 0 auto;
            background: #F8FAFC;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #64748B;
            font-weight: bold;
        }
        .sig-line {
            border-top: 1px dashed #64748B;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: 700;
            color: #09264A;
            font-size: 10px;
            text-transform: uppercase;
        }
        .sig-date {
            font-size: 9px;
            color: #64748B;
            margin-top: 2px;
        }

        /* Action bar for screen view */
        .action-bar {
            margin-top: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn-print {
            background: #09264A;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-back {
            background: #FFFFFF;
            color: #09264A;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 20px;
            border-radius: 6px;
            border: 1px solid #CBD5E1;
            text-decoration: none;
        }

        @media print {
            body { background: #FFF; padding: 0; font-size: 10px; }
            .document-container { box-shadow: none; border: none; padding: 15px; max-width: 100%; }
            .no-print { display: none !important; }
            .card, .card-full, .schedule-item { border-color: #CBD5E1; }
            .card, .card-full, .signature-container { page-break-inside: avoid; break-inside: avoid; }
            .academic-table th { background: #09264A !important; color: #FFF !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .fee-summary-card { background: #09264A !important; color: #FFF !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="document-container">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <img src="{{ asset('images/branding/daniyal-group-of-colleges-logo.png') }}" class="brand-logo">
                <div class="brand-text">
                    <h1>Daniyal Group of Colleges</h1>
                    <p>DANIYAL INSTITUTE OF HEALTH SCIENCES — {{ strtoupper($admission->campus->name ?? 'OKARA CAMPUS') }}</p>
                </div>
            </div>
            <div class="doc-title-section">
                <div>
                    <div class="doc-title">ADMISSION AGREEMENT</div>
                    <div class="doc-ref">Ref: #{{ $admission->enrollment_no ?? 'ADM-' . date('Y') . '-' . str_pad($admission->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
                @if($studentPhotoDataUri)
                    <img src="{{ $studentPhotoDataUri }}" class="student-photo" alt="Student photo">
                @else
                    <div class="student-photo-placeholder">
                        <span>Affix Photo<br>Here</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2-Column Grid: Student & Parent/Guardian -->
        <div class="grid-2">
            <!-- Student Information -->
            <div class="card">
                <div class="card-header">
                    👤 Student Information
                </div>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Full Name</td>
                        <td class="data-value">{{ $admission->applicant_name }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">CNIC / B-Form #</td>
                        <td class="data-value">{{ $admission->cnic }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Date of Birth</td>
                        <td class="data-value">{{ $admission->dob ? $admission->dob->format('d M Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Gender / Blood</td>
                        <td class="data-value">{{ ucfirst($admission->gender) }} @if($admission->blood_group) ({{ $admission->blood_group }}) @endif</td>
                    </tr>
                    <tr>
                        <td class="data-label">Mobile Number</td>
                        <td class="data-value">{{ $admission->phone }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Email Address</td>
                        <td class="data-value">{{ $admission->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Caste / Domicile</td>
                        <td class="data-value">{{ $admission->caste ?? 'N/A' }} / {{ $admission->domicile_district ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Current Address</td>
                        <td class="data-value">{{ $admission->address }}</td>
                    </tr>
                </table>
            </div>

            <!-- Parent or Guardian Information -->
            <div class="card">
                <div class="card-header">
                    👥 Parent & Guardian Information
                </div>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Father / Guardian</td>
                        <td class="data-value">{{ $admission->father_name }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Father CNIC #</td>
                        <td class="data-value">{{ $admission->father_cnic ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Father Contact</td>
                        <td class="data-value">{{ $admission->father_phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Mother CNIC #</td>
                        <td class="data-value">{{ $admission->mother_cnic ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Mother Contact</td>
                        <td class="data-value">{{ $admission->mother_phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Father Occupation</td>
                        <td class="data-value">{{ $admission->father_occupation ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Emergency Contact</td>
                        <td class="data-value" style="color: #09264A;">{{ $admission->emergency_contact }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Guardian Address</td>
                        <td class="data-value">{{ $admission->father_address ?? $admission->address }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Academic details -->
        <div class="card-full">
            <div class="card-header">
                🎓 Academic Qualifications & Course Details
            </div>
            <div style="font-weight: 700; color: #09264A; font-size: 11px; margin-bottom: 5px;">
                Selected Program: <span style="color: #E9A92F;">{{ $admission->course->name ?? 'N/A' }}</span> &nbsp;|&nbsp; Preferred Shift: <span>{{ ucfirst($admission->shift ?? 'morning') }}</span> &nbsp;|&nbsp; Session: <span>{{ $admission->academicSession->name ?? '2026' }}</span>
            </div>
            <table class="academic-table">
                <thead>
                    <tr>
                        <th>Qualification / SSC / HSSC</th>
                        <th>Degree Title</th>
                        <th>Board / University</th>
                        <th>Passing Year</th>
                        <th>Roll Number</th>
                        <th>Obtained / Total Marks</th>
                        <th>Grade</th>
                        <th>Bio Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $records = $admission->academic_details ?: [];
                    @endphp
                    @if(count($records) > 0)
                        @foreach($records as $rec)
                            <tr>
                                <td style="font-weight: bold; text-transform: uppercase;">{{ $rec['level'] === 'matric' ? 'Matric / SSC' : ($rec['level'] === 'intermediate' ? 'Inter / HSSC' : ucfirst($rec['level'])) }}</td>
                                <td>{{ $rec['degree_title'] ?? 'N/A' }}</td>
                                <td>{{ $rec['board_university'] ?? 'N/A' }}</td>
                                <td>{{ $rec['passing_year'] ?? 'N/A' }}</td>
                                <td>{{ $rec['roll_no'] ?? 'N/A' }}</td>
                                <td>{{ $rec['obtained_marks'] ?? 0 }} / {{ $rec['total_marks'] ?? 0 }}</td>
                                <td>{{ $rec['grade'] ?? 'N/A' }}</td>
                                <td>{{ $rec['biology_marks'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @else
                        @if($admission->matric_degree)
                            <tr>
                                <td style="font-weight: bold;">MATRIC / SSC</td>
                                <td>{{ $admission->matric_degree }}</td>
                                <td>{{ $admission->matric_board }}</td>
                                <td>{{ $admission->matric_year }}</td>
                                <td>{{ $admission->matric_roll_no }}</td>
                                <td>{{ $admission->matric_obtained_marks }} / {{ $admission->matric_total_marks }}</td>
                                <td>{{ $admission->matric_grade ?? 'N/A' }}</td>
                                <td>{{ $admission->matric_biology_marks ?? 'N/A' }}</td>
                            </tr>
                        @endif
                        @if($admission->inter_degree)
                            <tr>
                                <td style="font-weight: bold;">INTER / HSSC</td>
                                <td>{{ $admission->inter_degree }}</td>
                                <td>{{ $admission->inter_board }}</td>
                                <td>{{ $admission->inter_year }}</td>
                                <td>{{ $admission->inter_roll_no }}</td>
                                <td>{{ $admission->inter_obtained_marks }} / {{ $admission->inter_total_marks }}</td>
                                <td>{{ $admission->inter_grade ?? 'N/A' }}</td>
                                <td>N/A</td>
                            </tr>
                        @endif
                        @if(empty($admission->matric_degree) && empty($admission->inter_degree))
                            <tr>
                                <td colspan="8" style="text-align: center; color: #64748B;">No academic qualifications recorded.</td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>

        @php
            // Pull custom or official fee settings
            $isCustom = ($admission->custom_installment_count !== null);
            
            if ($isCustom) {
                $tuitionTotal = (float) $admission->custom_tuition_fee;
                $admissionFee = (float) $admission->custom_admission_fee;
                $enrollmentFee = (float) $admission->custom_enrollment_fee;
                $verificationFee = (float) $admission->custom_verification_fee;
                $examinationFee = (float) $admission->custom_examination_fee;
                $otherMisc = (float) $admission->custom_other_misc;
                $installmentCount = (int) $admission->custom_installment_count;
            } else {
                $structure = \App\Models\FeeStructure::where('course_id', $admission->course_id)->first() 
                    ?? \App\Models\FeeStructure::first();

                $tuitionTotal = (float) ($structure?->total_fee ?? 0);

                $admissionHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('category', 'admission')->first();
                $admissionFee = (float) ($admissionHead?->default_amount ?: 0.00);

                $endowmentHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('category', 'affiliation')->first();
                $enrollmentFee = (float) ($endowmentHead?->default_amount ?: 0.00);

                $verificationHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('code', 'like', 'VERIFICATION_%')->first();
                $verificationFee = (float) ($verificationHead?->default_amount ?: 0.00);

                $examHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('code', 'like', 'EXAM_%')->first();
                $examinationFee = (float) ($examHead?->default_amount ?: 0.00);

                $miscHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('category', 'miscellaneous')->first();
                $hostelHead = \App\Models\FeeHead::where('course_id', $admission->course_id)->where('category', 'hostel')->first();
                $otherMisc = (float) ($miscHead?->default_amount ?: 0.00) + (float) ($hostelHead?->default_amount ?: 0.00);

                $installmentCount = (int) ($structure?->installment_count ?? 12);
            }

            $totalPackage = $tuitionTotal + $admissionFee + $enrollmentFee + $verificationFee + $examinationFee + $otherMisc;
            $concession = (float) $admission->concession_amount;
            $netPayable = max(0, $totalPackage - $concession);
            
            $perInstallment = $installmentCount > 0 ? round(($tuitionTotal - $concession) / $installmentCount, 2) : 0.00;
            if ($perInstallment < 0) $perInstallment = 0.00;
        @endphp

        <!-- Fee Structure and month-by-month installments -->
        <div class="fee-flex-container">
            <!-- Customized fee details breakdown -->
            <div class="card fee-breakdown-card">
                <div class="card-header">
                    💰 Customized Fee Breakdown
                </div>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Admission Fee</td>
                        <td class="data-value">PKR {{ number_format($admissionFee, 2) }}</td>
                        <td class="data-label">Tuition Fee Total</td>
                        <td class="data-value">PKR {{ number_format($tuitionTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Verification Fee</td>
                        <td class="data-value">PKR {{ number_format($verificationFee, 2) }}</td>
                        <td class="data-label">Enrollment / Affiliation</td>
                        <td class="data-value">PKR {{ number_format($enrollmentFee, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Examination Fee</td>
                        <td class="data-value">PKR {{ number_format($examinationFee, 2) }}</td>
                        <td class="data-label">Other / Misc Dues</td>
                        <td class="data-value">PKR {{ number_format($otherMisc, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Summary total box -->
            <div class="fee-summary-card">
                <div class="fee-summary-row">
                    <span>Total Package Dues:</span>
                    <span>PKR {{ number_format($totalPackage, 2) }}</span>
                </div>
                <div class="fee-summary-row discount">
                    <span>Scholarship/Concession:</span>
                    <span>-PKR {{ number_format($concession, 2) }}</span>
                </div>
                <div class="fee-summary-total">
                    <span>Final Net Payable:</span>
                    <span>PKR {{ number_format($netPayable, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Installment Plan -->
        <div class="card-full">
            <div class="card-header">
                📅 Customized Installment Plan ({{ $installmentCount }} Installments)
            </div>
            <div class="schedule-grid">
                @for($i = 1; $i <= $installmentCount; $i++)
                    <div class="schedule-item">
                        <div class="schedule-title">Installment {{ $i }}</div>
                        <div class="schedule-date">{{ now()->addMonths($i - 1)->format('10 M Y') }}</div>
                        <div class="schedule-amount">PKR {{ number_format($perInstallment, 2) }}</div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Institute Regulation Policy -->
        <div class="card-full">
            <div class="card-header">
                📜 Institute Regulation Policy
            </div>
            <div class="policy-grid">
                <div class="policy-item"><strong>1. Attendance:</strong> Minimum 80% attendance required. Absentee penalties apply.</div>
                <div class="policy-item"><strong>2. Discipline:</strong> Professional conduct is mandatory. Zero tolerance for misconduct.</div>
                <div class="policy-item"><strong>3. Payment Schedule:</strong> Installments must be paid by the 10th of every month.</div>
                <div class="policy-item"><strong>4. Late Surcharge:</strong> Overdue payments incur late fees as per policy guidelines.</div>
                <div class="policy-item"><strong>5. Non-Refundable:</strong> Admission, enrollment, and verification charges are non-refundable.</div>
                <div class="policy-item"><strong>6. Integrity Check:</strong> Plagiarism or cheating results in academic suspension.</div>
                <div class="policy-item"><strong>7. Uniform Policy:</strong> Students must adhere to the prescribed college dress code.</div>
                <div class="policy-item"><strong>8. Facility Security:</strong> Students are liable for damage caused to library or labs.</div>
            </div>
        </div>

        <!-- Signature Section with separate Thumb box -->
        <div class="signature-container">
            <div class="sig-box">
                <div class="thumb-box">
                    <span>THUMBPRINT</span>
                </div>
                <div class="sig-date" style="margin-top: 5px;">(Student Left Thumb)</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Student Signature</div>
                <div class="sig-date">Date: {{ date('d M Y') }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Father / Guardian</div>
                <div class="sig-date">Date: {{ date('d M Y') }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Institute Rep</div>
                <div class="sig-date">Date: {{ date('d M Y') }}</div>
            </div>
        </div>

        <!-- Action Bar (Screen Only) -->
        <div class="action-bar no-print">
            <button onclick="window.print()" class="btn-print">Print Agreement</button>
            <a href="javascript:history.back()" class="btn-back">← Back</a>
        </div>
    </div>

</body>
</html>
