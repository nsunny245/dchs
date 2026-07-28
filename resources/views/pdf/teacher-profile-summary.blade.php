<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Profile Summary - {{ $staff->employee_id }}</title>
    <style>
        @page { margin: 80px 40px 60px 40px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #102033; line-height: 1.4; }
        header { position: fixed; top: -60px; left: 0; right: 0; height: 50px; border-bottom: 2px solid #C9963C; }
        .logo { float: left; width: 45px; height: 45px; object-fit: contain; }
        .title { float: left; margin-left: 10px; }
        .title h1 { margin: 0; font-size: 13pt; color: #071B33; }
        .title p { margin: 2px 0 0 0; font-size: 8pt; color: #C9963C; font-weight: bold; }
        .clear { clear: both; }
        .info-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; }
        .info-table th, .info-table td { border: 1px solid #E2E8F0; padding: 6px 8px; font-size: 8.5pt; }
        .info-table th { background: #F8FAFC; text-align: left; color: #071B33; width: 30%; }
        .section-header { font-size: 10pt; font-weight: bold; color: #071B33; border-bottom: 1px solid #C9963C; margin-top: 15px; padding-bottom: 3px; }
    </style>
</head>
<body>
    <header>
        <img class="logo" src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="DGC Logo">
        <div class="title">
            <h1>Daniyal Group of Colleges</h1>
            <p>Teacher Profile Summary & HR Record Sheet</p>
        </div>
        <div class="clear"></div>
    </header>

    <main>
        <div class="section-header">1. Personal Details</div>
        <table class="info-table">
            <tr><th>Employee ID</th><td><strong>{{ $staff->employee_id }}</strong></td></tr>
            <tr><th>Teacher Full Name</th><td>{{ $staff->full_name }}</td></tr>
            <tr><th>CNIC Number</th><td>{{ $staff->cnic }}</td></tr>
            <tr><th>Father / Spouse Name</th><td>{{ $staff->father_or_spouse_name ?? 'N/A' }}</td></tr>
            <tr><th>Gender / DOB</th><td>{{ ucfirst($staff->gender ?? 'N/A') }} ({{ $staff->date_of_birth?->format('d M Y') ?? 'N/A' }})</td></tr>
            <tr><th>Mobile / WhatsApp</th><td>{{ $staff->phone }} / {{ $staff->whatsapp ?? 'N/A' }}</td></tr>
            <tr><th>Emergency Contact</th><td>{{ $staff->emergency_contact_name }} ({{ $staff->emergency_contact_relationship }}) - {{ $staff->emergency_contact_phone }}</td></tr>
        </table>

        <div class="section-header">2. Academic & Experience</div>
        <table class="info-table">
            <tr><th>Highest Qualification</th><td>{{ $staff->academics?->highest_qualification ?? 'N/A' }}</td></tr>
            <tr><th>Degree Title</th><td>{{ $staff->academics?->degree_title ?? 'N/A' }}</td></tr>
            <tr><th>University / Institute</th><td>{{ $staff->academics?->institution ?? 'N/A' }}</td></tr>
            <tr><th>Teaching / Clinical Exp.</th><td>{{ $staff->academics?->teaching_experience_years ?? 0 }} Yrs Teaching / {{ $staff->academics?->clinical_experience_years ?? 0 }} Yrs Clinical</td></tr>
            <tr><th>Licence / Registration</th><td>{{ $staff->registrations?->first()?->registration_body ?? 'N/A' }} #{{ $staff->registrations?->first()?->registration_number ?? 'N/A' }}</td></tr>
        </table>

        <div class="section-header">3. Employment & Campus Assignment</div>
        <table class="info-table">
            <tr><th>Assigned Campus</th><td><strong>{{ $staff->campus?->name }}</strong></td></tr>
            <tr><th>Designation</th><td><strong>{{ $staff->designation }}</strong></td></tr>
            <tr><th>Appointment Status</th><td>{{ ucfirst($staff->currentEmployment?->appointment_status ?? 'Probation') }}</td></tr>
            <tr><th>Employment Type</th><td>{{ ucfirst(str_replace('_', ' ', $staff->currentEmployment?->employment_type ?? 'Full-Time')) }}</td></tr>
            <tr><th>Date of Joining</th><td>{{ $staff->joining_date?->format('d M Y') }}</td></tr>
        </table>
    </main>
</body>
</html>
