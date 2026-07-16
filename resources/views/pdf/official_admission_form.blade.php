<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Admission Form - {{ $admission->applicant_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1A2238;
            font-size: 11px;
            line-height: 1.4;
            background: #fff;
        }

        .page {
            width: 100%;
            padding: 30px 40px;
            page-break-after: always;
            position: relative;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #C5A85A; /* Golden Border */
            padding-bottom: 15px;
        }

        .college-title {
            font-size: 24px;
            font-weight: bold;
            color: #0A1526; /* Dark Navy */
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .college-subtitle {
            font-size: 11px;
            color: #556B82;
            margin-top: 2px;
            font-weight: 500;
        }

        .campus-badge {
            font-size: 12px;
            font-weight: bold;
            color: #C5A85A; /* Gold */
            margin-top: 5px;
            text-transform: uppercase;
        }

        .form-title {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            background: #0A1526; /* Navy */
            color: #fff;
            padding: 6px;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 5px solid #C5A85A; /* Gold Accent */
        }

        .photo-box {
            width: 100px;
            height: 120px;
            border: 2px dashed #C5A85A;
            text-align: center;
            font-size: 9px;
            color: #556B82;
            padding-top: 45px;
            background: #F8FAFC;
        }

        /* Tables & Grid styling */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .grid-table th, .grid-table td {
            border: 1px solid #E2E8F0;
            padding: 6px 10px;
            font-size: 10.5px;
            vertical-align: middle;
        }

        .grid-table th {
            background: #0A1526;
            color: #fff;
            text-align: left;
            font-weight: bold;
            width: 25%;
        }

        .section-header {
            background: #1A2E4F; /* Secondary Navy */
            color: #fff;
            font-weight: bold;
            padding: 5px 10px;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 4px solid #C5A85A; /* Gold highlight */
        }

        .rules-list {
            margin-left: 20px;
            margin-top: 15px;
        }

        .rules-list li {
            margin-bottom: 8px;
            text-align: justify;
            color: #334155;
            font-size: 10.5px;
        }

        .signature-box {
            margin-top: 40px;
            width: 100%;
        }

        .sig-col {
            width: 33%;
            text-align: center;
            display: inline-block;
            vertical-align: top;
        }

        .sig-line {
            border-top: 1.5px solid #0A1526;
            width: 80%;
            margin: 45px auto 8px;
        }

        .note-container {
            border: 2px solid #C5A85A;
            padding: 15px;
            background: #FFFDF9; /* Cream tint */
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .note-title {
            color: #0A1526;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .note-text {
            text-align: justify;
            color: #334155;
            font-size: 11px;
            line-height: 1.5;
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
                <div class="college-subtitle">Approved & Affiliated Allied Health Education Institution</div>
                <div class="campus-badge">
                    Campus: {{ $admission->campus->name ?? 'Main Campus' }}
                </div>
            </td>
            <td style="width: 20%; text-align: right;">
                @if($admission->student_photo && file_exists(storage_path('app/public/' . $admission->student_photo)))
                    <img src="{{ storage_path('app/public/' . $admission->student_photo) }}" style="width: 100px; height: 120px; object-fit: cover; border: 2px solid #0A1526; border-radius: 4px;">
                @else
                    <div class="photo-box">Affix Passport<br>Size Photo Here</div>
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
            <th>Applicant's Full Name:</th>
            <td colspan="3"><strong>{{ strtoupper($admission->applicant_name ?? '') }}</strong></td>
        </tr>
        <tr>
            <th>Father's / Guardian Name:</th>
            <td colspan="3">{{ strtoupper($admission->father_name ?? '') }}</td>
        </tr>
        <tr>
            <th>Student CNIC / B-Form #:</th>
            <td>{{ $admission->cnic }}</td>
            <th style="width: 20%;">Father CNIC #:</th>
            <td>{{ $admission->father_cnic ?? '—' }}</td>
        </tr>
        <tr>
            <th>Date Of Birth:</th>
            <td>{{ $admission->dob ? $admission->dob->format('d-m-Y') : '—' }}</td>
            <th>Gender:</th>
            <td>{{ ucfirst($admission->gender ?? '') }}</td>
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
            <th>Reference Details:</th>
            <td>{{ $admission->reference ?? 'Direct' }}</td>
            <th>Hostel Status:</th>
            <td>{{ $admission->residence_type == 'boarder' ? 'Boarder (Hostel)' : 'Non-boarder' }}</td>
        </tr>
        <tr>
            <th>Course Enrolled:</th>
            <td colspan="3"><strong>{{ $admission->course->name ?? '—' }} ({{ $admission->course->code ?? '' }})</strong></td>
        </tr>
    </table>

    <!-- Academic Qualifications -->
    <div class="section-header">PREVIOUS ACADEMIC QUALIFICATIONS</div>
    <table class="grid-table" style="text-align: center;">
        <thead>
            <tr style="background: #F1F5F9;">
                <th style="color: #0A1526; width: auto; text-align: center;">Degree Title</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Passing Year</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Roll Number</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Board / University</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Obtained Marks</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Total Marks</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Div / Grade</th>
                <th style="color: #0A1526; width: auto; text-align: center;">Bio Marks</th>
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
                <td><strong>{{ $admission->inter_degree ?? 'F.A / F.Sc' }}</strong></td>
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
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1.5px solid #0A1526; width: 80%; margin: 50px auto 5px;"></div>
                <div style="font-size: 10px; font-weight: bold; color: #0A1526;">Applicant's Signature</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1.5px solid #0A1526; width: 80%; margin: 50px auto 5px;"></div>
                <div style="font-size: 10px; font-weight: bold; color: #0A1526;">Parent / Guardian's Signature</div>
            </td>
        </tr>
    </table>
</div>

<!-- PAGE 2: COLLEGE RULES & REGULATIONS -->
<div class="page">
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="college-title">DANIYAL College Of Health Sciences</div>
        <div class="form-title" style="margin-top: 10px;">COLLEGE ADMISSION RULES & REGULATIONS</div>
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
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="college-title">DANIYAL College Of Health Sciences</div>
        <div class="form-title" style="margin-top: 10px; background: #C5A85A;">FEE REGULATION & DECLARATION</div>
    </div>

    <div class="note-container">
        <div class="note-title">Academic Fee Guidelines</div>
        <p class="note-text">
            Students must pay the monthly fee starting from the session in which they are admitted, regardless of the date of admission. Even if the admission is taken later in the session, the student will be required to pay the fee from the beginning of that session. Monthly installments must be paid regularly until the complete fee package is cleared. The fee payment is not linked with First Year or Second Year separately. Examination fee and extra-curricular activity charges are NOT included in the fee package and will be paid separately when required.
        </p>
        <p class="note-text" style="margin-top: 12px; font-weight: bold; color: #0A1526;">
            Fee Refund Policy:
            Under no circumstances will any fee amount paid at the time of admission or during academic semesters be refunded if the student decides to leave the college on their own accord. If the student registration has already been sent to the pharmacy council or related departments, the student remains liable to clear their full contractual fee package.
        </p>
    </div>

    <div style="border: 1px solid #CBD5E1; padding: 12px; margin-bottom: 25px; background: #F8FAFC; font-size: 10.5px; line-height: 1.5; color: #334155;">
        <strong>FEE PAYMENT SCHEDULE REMINDER:</strong><br>
        • Part 1st Year fee must be submitted before April 2027.<br>
        • Part 2nd Year fee must be cleared before April 2028.<br>
        • Examination fee Part 1-2 will be paid in the month of December 2026.
    </div>

    <!-- Final Signatures & Stamps -->
    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1.5px solid #0A1526; width: 80%; margin: 60px auto 5px;"></div>
                <strong style="color: #0A1526;">Applicant's Signature</strong>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1.5px solid #0A1526; width: 80%; margin: 60px auto 5px;"></div>
                <strong style="color: #0A1526;">Guardian's Signature</strong>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="width: 100px; height: 60px; border: 2px dashed #C5A85A; margin: 0 auto 5px; background: #F8FAFC; text-align: center; line-height: 60px; font-size: 9px; color: #556B82;">Principal's Stamp</div>
                <div style="border-top: 1.5px solid #0A1526; width: 80%; margin: 15px auto 5px;"></div>
                <strong style="color: #0A1526;">Principal's Signature</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
