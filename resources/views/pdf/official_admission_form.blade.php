<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Admission Form - {{ $admission->applicant_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #000;
            font-size: 11px;
            line-height: 1.4;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 20px 30px;
            page-break-after: always;
            position: relative;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .college-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            background: #1e3a5f;
            color: #fff;
            padding: 5px;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .photo-box {
            width: 100px;
            height: 120px;
            border: 1px dashed #444;
            text-align: center;
            font-size: 9px;
            color: #666;
            padding-top: 40px;
        }

        /* Form Tables */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .grid-table th, .grid-table td {
            border: 1px solid #333;
            padding: 5px 8px;
            font-size: 10.5px;
        }

        .grid-table th {
            background: #f0f4f8;
            text-align: left;
            font-weight: bold;
            color: #1e3a5f;
        }

        .section-header {
            background: #2b4c7e;
            color: #fff;
            font-weight: bold;
            padding: 4px 8px;
            font-size: 11px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .rules-list {
            margin-left: 20px;
            margin-top: 10px;
        }

        .rules-list li {
            margin-bottom: 8px;
            text-align: justify;
        }

        .urdu-text {
            direction: rtl;
            text-align: right;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.8;
        }

        .signature-box {
            margin-top: 30px;
            width: 100%;
        }

        .sig-col {
            width: 33%;
            text-align: center;
            display: inline-block;
            vertical-align: top;
        }

        .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 40px auto 5px;
        }

        .thumb-box {
            width: 120px;
            height: 60px;
            border: 1px solid #333;
            margin: 10px auto;
            text-align: center;
            line-height: 60px;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>

<!-- PAGE 1: ADMISSION FORM -->
<div class="page">
    <table class="header-table">
        <tr>
            <td style="width: 80%;">
                <div class="college-title">DANIYAL College Of Health Sciences</div>
                <div style="font-size: 11px; color: #555;">Recognized & Registered Healthcare Education Institution</div>
                <div style="font-size: 12px; font-weight: bold; color: #0f766e; margin-top: 3px;">
                    Campus: {{ $admission->campus->name ?? 'Main Campus' }}
                </div>
            </td>
            <td style="width: 20%; text-align: right;">
                @if($admission->student_photo && file_exists(storage_path('app/public/' . $admission->student_photo)))
                    <img src="{{ storage_path('app/public/' . $admission->student_photo) }}" style="width: 100px; height: 120px; object-fit: cover; border: 1px solid #333;">
                @else
                    <div class="photo-box">Paste Passport<br>Size Photo Here</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="form-title">
        ADMISSION FORM (Session: {{ $admission->academicSession->name ?? '2026-2028' }})
    </div>

    <!-- Office Use Only -->
    <div class="section-header">FOR OFFICE USE ONLY</div>
    <table class="grid-table">
        <tr>
            <td><strong>Roll No:</strong> {{ $admission->roll_no ?? '_________________' }}</td>
            <td><strong>Registration No:</strong> {{ $admission->registration_no ?? '_________________' }}</td>
            <td><strong>GR No:</strong> {{ $admission->gr_no ?? '_________________' }}</td>
            <td><strong>Date:</strong> {{ $admission->admission_date ? $admission->admission_date->format('d-m-Y') : date('d-m-Y') }}</td>
        </tr>
    </table>

    <!-- Personal Details -->
    <div class="section-header">PERSONAL DETAILS (Fill in Capital Letters)</div>
    <table class="grid-table">
        <tr>
            <th style="width: 20%;">Applicant's Name:</th>
            <td style="width: 40%;"><strong>{{ strtoupper($admission->applicant_name) }}</strong></td>
            <th style="width: 15%;">نام (طالب علم):</th>
            <td style="width: 25%; font-weight: bold;">{{ $admission->applicant_name_urdu ?? '—' }}</td>
        </tr>
        <tr>
            <th>Father's / Guardian:</th>
            <td>{{ strtoupper($admission->father_name) }}</td>
            <th>نام (والد/سرپرست):</th>
            <td>{{ $admission->father_name_urdu ?? '—' }}</td>
        </tr>
        <tr>
            <th>Student CNIC / B-Form:</th>
            <td>{{ $admission->cnic }}</td>
            <th>Father CNIC #:</th>
            <td>{{ $admission->father_cnic ?? '—' }}</td>
        </tr>
        <tr>
            <th>Date Of Birth:</th>
            <td>{{ $admission->dob ? $admission->dob->format('d-m-Y') : '—' }}</td>
            <th>Gender:</th>
            <td>{{ ucfirst($admission->gender) }}</td>
        </tr>
        <tr>
            <th>Domicile District:</th>
            <td>{{ $admission->domicile_district ?? '—' }}</td>
            <th>Blood Group:</th>
            <td>{{ $admission->blood_group ?? '—' }}</td>
        </tr>
        <tr>
            <th>Caste:</th>
            <td>{{ $admission->caste ?? '—' }}</td>
            <th>Student Shift:</th>
            <td>{{ ucfirst($admission->shift ?? 'morning') }}</td>
        </tr>
        <tr>
            <th>Student Contact #:</th>
            <td>{{ $admission->phone }}</td>
            <th>Father's Contact #:</th>
            <td>{{ $admission->father_phone ?? $admission->phone }}</td>
        </tr>
        <tr>
            <th>Mother's Contact #:</th>
            <td>{{ $admission->mother_phone ?? '—' }}</td>
            <th>Mother CNIC #:</th>
            <td>{{ $admission->mother_cnic ?? '—' }}</td>
        </tr>
        <tr>
            <th>Postal Address:</th>
            <td colspan="3">{{ $admission->address }}</td>
        </tr>
        <tr>
            <th>Reference:</th>
            <td>{{ $admission->reference ?? 'Direct' }}</td>
            <th>Hostel Status:</th>
            <td>{{ $admission->residence_type == 'boarder' ? 'Boarder' : 'Non-boarder' }}</td>
        </tr>
        <tr>
            <th>Course Applied:</th>
            <td colspan="3"><strong>{{ $admission->course->name ?? '—' }} ({{ $admission->course->code ?? '' }})</strong></td>
        </tr>
    </table>

    <!-- Academic Qualifications -->
    <div class="section-header">PREVIOUS ACADEMIC QUALIFICATIONS</div>
    <table class="grid-table" style="text-align: center;">
        <thead>
            <tr>
                <th>Degree Title</th>
                <th>Passing Year</th>
                <th>Roll Number</th>
                <th>Board / University</th>
                <th>Obtained Marks</th>
                <th>Total Marks</th>
                <th>Div / Grade</th>
                <th>Bio Marks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $admission->matric_degree ?? 'Matric Science' }}</strong></td>
                <td>{{ $admission->matric_year ?? '—' }}</td>
                <td>{{ $admission->matric_roll_no ?? '—' }}</td>
                <td>{{ $admission->matric_board ?? '—' }}</td>
                <td>{{ $admission->matric_obtained_marks ?? '—' }}</td>
                <td>{{ $admission->matric_total_marks ?? '—' }}</td>
                <td>{{ $admission->matric_grade ?? '—' }}</td>
                <td>{{ $admission->matric_biology_marks ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>{{ $admission->inter_degree ?? 'F.A / F.Sc / D.Com' }}</strong></td>
                <td>{{ $admission->inter_year ?? '—' }}</td>
                <td>{{ $admission->inter_roll_no ?? '—' }}</td>
                <td>{{ $admission->inter_board ?? '—' }}</td>
                <td>{{ $admission->inter_obtained_marks ?? '—' }}</td>
                <td>{{ $admission->inter_total_marks ?? '—' }}</td>
                <td>{{ $admission->inter_grade ?? '—' }}</td>
                <td>—</td>
            </tr>
        </tbody>
    </table>

    <!-- Page 1 Footer Signatures -->
    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div class="thumb-box">Thumb Impression</div>
                <div style="font-size: 10px;">Thumb Impression of Applicant</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto 5px;"></div>
                <div style="font-size: 10px; font-weight: bold;">Signature of Applicant / Parent / Guardian</div>
            </td>
        </tr>
    </table>
</div>

<!-- PAGE 2: COLLEGE RULES & REGULATIONS -->
<div class="page">
    <div style="text-align: center; margin-bottom: 15px;">
        <div class="college-title">DANIYAL College Of Health Sciences</div>
        <div class="form-title" style="margin-top: 5px;">COLLEGE ADMISSION RULES & REGULATIONS</div>
    </div>

    <ol class="rules-list">
        <li>All students are expected to abide by this Code of Conduct and assist the administration in maintaining discipline by reporting any violation.</li>
        <li>A minimum of 75% attendance is mandatory for appearing in examinations.</li>
        <li>Every student is responsible for paying monthly fee installments regularly or clearing 80% of the total fee package before enrollment with the concerned examination body.</li>
        <li>College dress code and ID card must be strictly followed and carried by all students during college hours.</li>
        <li>Use of mobile phones is strictly prohibited within the college premises during academic hours.</li>
        <li>Licensed or unlicensed weapons are strictly prohibited inside the college premises.</li>
        <li>Extra-curricular activities including study tours, sports events, functions, or parties will only be allowed after the approval of the Principal/Administration.</li>
        <li>Any misconduct with faculty members, administration staff, or fellow students (especially female students) may lead to disciplinary action, rustication, or expulsion from the college.</li>
        <li>The college will not be responsible for any loss or damage caused by a student’s involvement in illegal activities that harm the reputation or finances of the college.</li>
        <li>Political activities, political gatherings, or student unions are strictly prohibited inside the college.</li>
        <li>The decision of the Discipline Committee shall be final and binding upon the student.</li>
        <li>All college dues must be paid before the 10th of every month. A late fine of Rs.100 per day will be charged after the due date.</li>
        <li>The complete fee package must be cleared before enrollment in Pharmacy Council or any relevant examination authority.</li>
        <li>Ragging, teasing, harassment, intimidation, or abusive language toward junior students, teachers, administration, or female students inside or outside the campus is strictly prohibited and punishable under the law.</li>
        <li>Students must submit authentic documents at the time of admission. Providing fake or misleading information may result in cancellation of admission without refund.</li>
        <li>The college will not be responsible for loss or theft of personal belongings within the campus.</li>
        <li>The college administration reserves the right to modify or update rules and regulations at any time in the best interest of the institution.</li>
    </ol>
</div>

<!-- PAGE 3: IMPORTANT NOTE & DECLARATION -->
<div class="page">
    <div style="text-align: center; margin-bottom: 15px;">
        <div class="college-title">DANIYAL College Of Health Sciences</div>
        <div class="form-title" style="margin-top: 5px; background: #c0392b;">IMPORTANT NOTE / اہم نوٹ</div>
    </div>

    <div style="border: 2px solid #c0392b; padding: 15px; background: #fffaf0; margin-bottom: 20px;">
        <h4 style="color: #c0392b; margin-bottom: 8px;">English Guidelines:</h4>
        <p style="text-align: justify; margin-bottom: 10px;">
            Students must pay the monthly fee starting from the session in which they are admitted, regardless of the date of admission. Even if the admission is taken later in the session, the student will be required to pay the fee from the beginning of that session. Monthly installments must be paid regularly until the complete fee package is cleared. The fee payment is not linked with First Year or Second Year separately. Examination fee and extra-curricular activity charges are NOT included in the fee package and will be paid separately when required.
        </p>

        <hr style="border: 0; border-top: 1px dashed #c0392b; margin: 10px 0;">

        <h4 style="color: #c0392b; margin-bottom: 8px; text-align: right;">اہم نوٹ (Urdu):</h4>
        <p class="urdu-text">
            اگر کوئی طالب علم داخلہ لینے کے بعد اپنی مرضی سے کالج چھوڑ دیتا ہے تو جمع کروائی گئی فیس کسی بھی صورت میں واپس نہیں کی جائےگی۔ اگر طالب علم کا داخلہ متعلقہ ڈیپارٹمنٹ یا امتحانی ادارے میں بھیج دیا گیا ہو تو ایسی صورت میں طالب علم مکمل فیس پیکیج ادا کرنے کا پابند ہوگا۔ چاہے وہ تعلیم حاصل کرنے کا سلسلہ جاری رکھے یا چھوڑ دے۔ طالب علم کو اس سیشن کی فیس اسی وقت سے ادا کرنا ہوگی جس سیشن میں اس نے داخلہ لیا ہے چاہے اس کا داخلہ سیشن کے دوران کسی بھی وقت ہوا ہو۔ طالب علم اپنے فیس پیکج کی مکمل ادائیگی تک ماہانہ بنیاد پر قسطیں ادا کرنے کا پابند ہوگا۔ امتحانی فیس اور اضافی ہم نصابی اور غیر نصابی سرگرمیوں کے اخراجات فیس پیکج میں شامل نہیں ہیں اور یہ علیحدہ ادا کرنا ہوں گے۔
        </p>
    </div>

    <div style="border: 1px solid #333; padding: 10px; margin-bottom: 20px; background: #f9f9f9; font-size: 10px;">
        <strong>FEE PAYMENT SCHEDULE REMINDER:</strong><br>
        • Part 1st Year fee must submit before April 2027.<br>
        • Part 2nd Year fee must clear before April 2028.<br>
        • Examination fee Part 1-2 will be paid in month Dec 2026.
    </div>

    <!-- Final Signatures & Stamps -->
    <table style="width: 100%; margin-top: 40px;">
        <tr>
            <td style="width: 33%; text-align: center;">
                @if($admission->student_signature && file_exists(storage_path('app/public/' . $admission->student_signature)))
                    <div style="height: 60px;"><img src="{{ storage_path('app/public/' . $admission->student_signature) }}" style="max-width: 120px; max-height: 55px;"></div>
                @else
                    <div class="thumb-box">Thumb Impression</div>
                @endif
                <div style="border-top: 1px solid #000; width: 80%; margin: 15px auto 5px;"></div>
                <strong>Applicant's Signatures</strong>
            </td>
            <td style="width: 33%; text-align: center;">
                @if($admission->guardian_signature && file_exists(storage_path('app/public/' . $admission->guardian_signature)))
                    <div style="height: 60px;"><img src="{{ storage_path('app/public/' . $admission->guardian_signature) }}" style="max-width: 120px; max-height: 55px;"></div>
                @else
                    <div class="thumb-box">Thumb Impression</div>
                @endif
                <div style="border-top: 1px solid #000; width: 80%; margin: 15px auto 5px;"></div>
                <strong>Guardian's Signature</strong>
            </td>
            <td style="width: 33%; text-align: center;">
                <div style="width: 100px; height: 60px; border: 1px dashed #666; margin: 10px auto; line-height: 60px; font-size: 9px; color: #888;">Principal's Stamp</div>
                <div style="border-top: 1px solid #000; width: 80%; margin: 15px auto 5px;"></div>
                <strong>Principal's Signatures</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
