<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Agreement - {{ $admission->applicant_name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F1F5F9;
            color: #1E293B;
            font-size: 11px;
            line-height: 1.5;
            padding: 20px 10px;
        }

        .document-container {
            max-width: 900px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            padding: 40px;
            border: 1px solid #E2E8F0;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #0A1526;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 120px;
            height: 2px;
            background-color: #EBB45A;
        }
        .brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .brand-logo {
            height: 55px;
            width: auto;
        }
        .brand-text h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0A1526;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .brand-text p {
            font-size: 10px;
            color: #64748B;
            font-weight: 500;
        }
        .doc-title-section {
            text-align: right;
        }
        .doc-title {
            font-size: 20px;
            font-weight: 800;
            color: #0A1526;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-ref {
            font-size: 12px;
            font-weight: 700;
            color: #D89A34;
            margin-top: 2px;
        }

        /* Section Cards */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 25px;
        }

        .card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #EBB45A;
            border-radius: 8px;
            padding: 20px;
        }

        .card-header {
            font-size: 13px;
            font-weight: 700;
            color: #0A1526;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 6px 0;
            vertical-align: top;
            border-bottom: 1px dashed #E2E8F0;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-label {
            font-weight: 500;
            color: #64748B;
            width: 38%;
        }
        .data-value {
            font-weight: 600;
            color: #0F172A;
            width: 62%;
        }

        /* Fee Section */
        .fee-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #EBB45A;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .fee-details {
            flex: 1;
        }
        .fee-summary-box {
            background: #0A1526;
            color: #FFFFFF;
            border-radius: 8px;
            padding: 20px 25px;
            text-align: right;
            min-width: 280px;
            box-shadow: 0 4px 12px rgba(10, 21, 38, 0.15);
        }
        .fee-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 6px;
            color: #CBD5E1;
        }
        .fee-row.discount {
            color: #EBB45A;
            font-weight: 600;
        }
        .fee-total {
            border-top: 1px solid #334155;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 15px;
            font-weight: 800;
            color: #EBB45A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Policy Section */
        .policy-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .policy-intro {
            font-style: italic;
            color: #64748B;
            margin-bottom: 12px;
            font-size: 10.5px;
        }
        .policy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
        }
        .policy-item {
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }
        .policy-item strong {
            color: #0A1526;
        }

        /* Signatures */
        .signature-section {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #0A1526;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 42%;
            text-align: center;
        }
        .sig-line {
            border-top: 1px dashed #64748B;
            margin-top: 45px;
            padding-top: 6px;
            font-weight: 700;
            color: #0A1526;
            font-size: 11px;
        }
        .sig-date {
            font-size: 10px;
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
            background: #0A1526;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 12px;
            padding: 10px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #1E293B;
        }
        .btn-back {
            background: #FFFFFF;
            color: #0A1526;
            font-weight: 700;
            font-size: 12px;
            padding: 10px 24px;
            border-radius: 6px;
            border: 1px solid #CBD5E1;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background: #F8FAFC;
        }

        @media print {
            body { background: #FFF; padding: 0; }
            .document-container { box-shadow: none; border: none; padding: 20px; max-width: 100%; }
            .no-print { display: none !important; }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

    <div class="document-container">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <img src="{{ public_path('images/logo.png') }}" class="brand-logo" onerror="this.src='https://dchs.edu.pk/wp-content/uploads/2022/05/crest-300x300.png'">
                <div class="brand-text">
                    <h1>Daniyal Group of Colleges</h1>
                    <p>DANIYAL INSTITUTE OF HEALTH SCIENCES — {{ strtoupper($admission->campus->name ?? 'OKARA CAMPUS') }}</p>
                </div>
            </div>
            <div class="doc-title-section">
                <div class="doc-title">STUDENT AGREEMENT</div>
                <div class="doc-ref">Ref: #{{ $admission->enrollment_number ?? 'ADM-' . date('Y') . '-' . str_pad($admission->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- 2-Column Grid -->
        <div class="grid-2">
            <!-- Student Information -->
            <div class="card">
                <div class="card-header">
                    <span>👤</span> Student Information
                </div>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Full Name</td>
                        <td class="data-value">{{ $admission->applicant_name }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Email</td>
                        <td class="data-value">{{ $admission->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Phone</td>
                        <td class="data-value">{{ $admission->phone }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">CNIC/Passport</td>
                        <td class="data-value">{{ $admission->cnic }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Date of Birth</td>
                        <td class="data-value">{{ $admission->dob ? $admission->dob->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Address</td>
                        <td class="data-value">{{ $admission->address }}</td>
                    </tr>
                </table>
            </div>

            <!-- Academic & Course Details -->
            <div class="card">
                <div class="card-header">
                    <span>🎓</span> Academic & Course Details
                </div>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Enrolled Course</td>
                        <td class="data-value" style="color: #D89A34;">{{ $admission->course->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Qualification</td>
                        <td class="data-value">{{ $admission->matric_degree ?? 'Matric / SSC' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Institution</td>
                        <td class="data-value">{{ $admission->matric_board ?? 'BISE Board' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Shift / Session</td>
                        <td class="data-value">{{ ucfirst($admission->shift ?? 'morning') }} ({{ $admission->academicSession->name ?? '2026' }})</td>
                    </tr>
                    <tr>
                        <td class="data-label">Emergency Contact</td>
                        <td class="data-value">{{ $admission->emergency_contact ?? $admission->phone }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Registration Date</td>
                        <td class="data-value">{{ $admission->created_at ? $admission->created_at->format('d M Y') : date('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

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

        <!-- Fee Structure & Payment Plan -->
        <div class="fee-card">
            <div class="fee-details">
                <div class="card-header" style="margin-bottom: 10px;">
                    <span>💰</span> Fee Structure & Payment Plan
                </div>
                <table class="data-table" style="width: 85%;">
                    <tr>
                        <td class="data-label">Plan Type</td>
                        <td class="data-value">{{ $installmentCount > 1 ? 'Monthly Installments' : 'Lumpsum' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Installments</td>
                        <td class="data-value">{{ $installmentCount }} Payments of PKR {{ number_format($perInstallment, 2) }}</td>
                    </tr>
                </table>
            </div>

            <div class="fee-summary-box">
                <div class="fee-row">
                    <span>Total Course Fee:</span>
                    <span>PKR {{ number_format($totalFee, 2) }}</span>
                </div>
                <div class="fee-row discount">
                    <span>Discount / Concession:</span>
                    <span>-PKR {{ number_format($concession, 2) }}</span>
                </div>
                <div class="fee-total">
                    FINAL AMOUNT: PKR {{ number_format($finalAmount, 2) }}
                </div>
            </div>
        </div>

        <!-- Institute Regulation Policy -->
        <div class="policy-card">
            <div class="card-header" style="margin-bottom: 5px;">
                <span>📜</span> Institute Regulation Policy
            </div>
            <div class="policy-intro">
                By signing below, I acknowledge that I have read and agree to abide by the following regulations:
            </div>

            <div class="policy-grid">
                <div class="policy-item"><strong>1. Attendance:</strong> Minimum 80% attendance required. Late arrivals >15 mins marked absent.</div>
                <div class="policy-item"><strong>2. Code of Conduct:</strong> Professional behavior expected. Harassment leads to immediate dismissal.</div>
                <div class="policy-item"><strong>3. Fee Policy:</strong> Payments must follow the selected plan. Late fees incur penalty per day.</div>
                <div class="policy-item"><strong>4. Refunds:</strong> 100% within 7 days. 50% within 15 days. No refunds after course start.</div>
                <div class="policy-item"><strong>5. Equipment:</strong> Students are responsible for damage to institute property caused by negligence.</div>
                <div class="policy-item"><strong>6. Integrity:</strong> Plagiarism or cheating results in termination without refund.</div>
                <div class="policy-item"><strong>7. Internet:</strong> Lab internet is for educational use only. No downloading personal files.</div>
                <div class="policy-item"><strong>8. Attire:</strong> Smart casual dress code. No offensive graphics or slogans.</div>
                <div class="policy-item"><strong>9. Certification:</strong> Requires completion of all projects and passing final assessments.</div>
                <div class="policy-item"><strong>10. Grievances:</strong> Written complaints to admin; response within 48 hours guaranteed.</div>
                <div class="policy-item"><strong>11. Privacy:</strong> Student data is secure and not shared with third parties.</div>
                <div class="policy-item"><strong>12. Dismissal:</strong> Non-payment or misconduct leads to permanent dismissal.</div>
                <div class="policy-item"><strong>13. Safety:</strong> Follow emergency protocols. Report medical issues to staff immediately.</div>
                <div class="policy-item"><strong>14. Visitors:</strong> Non-students prohibited in labs without permission.</div>
                <div class="policy-item"><strong>15. Updates:</strong> Institute reserves the right to update policies with notice.</div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-line">Student Signature</div>
                <div class="sig-date">Date: ________________________</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Institute Representative</div>
                <div class="sig-date">Date: ________________________</div>
            </div>
        </div>

        <!-- Action Bar (Screen Only) -->
        <div class="action-bar no-print">
            <button onclick="window.print()" class="btn-print">Print / Save PDF</button>
            <a href="javascript:history.back()" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

</body>
</html>
