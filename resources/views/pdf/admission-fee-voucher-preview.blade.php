<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px; }
        body { margin: 0; color: #082245; font-family: DejaVu Sans, sans-serif; }
        .copies { display: table; width: 100%; table-layout: fixed; }
        .copy { display: table-cell; padding: 14px; border-right: 1px dashed #94a3b8; }
        .copy:last-child { border-right: 0; }
        .brand { height: 62px; max-width: 230px; object-fit: contain; }
        .title { margin: 14px 0 4px; padding: 9px; color: white; background: #082245; text-align: center; font-size: 16px; }
        .copy-label { color: #d99a19; text-align: center; font-size: 11px; font-weight: bold; }
        table { width: 100%; margin-top: 14px; border-collapse: collapse; font-size: 11px; }
        td { padding: 8px 4px; border-bottom: 1px solid #dbe3ec; vertical-align: top; }
        td:first-child { width: 38%; color: #64748b; }
        .amount { margin-top: 18px; padding: 15px; border: 2px solid #d99a19; background: #fff8e8; text-align: center; font-size: 19px; font-weight: bold; }
        .note { margin-top: 16px; color: #64748b; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
<div class="copies">
    @foreach(['Bank Copy', 'College Copy', 'Student Copy'] as $copy)
        <section class="copy">
            <div style="text-align:center">
                <img class="brand" src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="Daniyal Group of Colleges">
            </div>
            <div class="title">ADMISSION FEE VOUCHER</div>
            <div class="copy-label">{{ $copy }} · Preview</div>
            <table>
                <tr><td>Student</td><td><strong>{{ $studentName }}</strong></td></tr>
                <tr><td>Campus</td><td>{{ $campusName }}</td></tr>
                <tr><td>Course</td><td>{{ $courseName }}</td></tr>
                <tr><td>Session</td><td>{{ $sessionName }}</td></tr>
                <tr><td>Issue / Due Date</td><td>{{ \Illuminate\Support\Carbon::parse($dueDate)->format('d M Y') }}</td></tr>
                <tr><td>Fee Head</td><td>Admission Fee</td></tr>
            </table>
            <div class="amount">PKR {{ number_format($amount, 2) }}</div>
            <div class="note">Preview only. The official numbered voucher is created when the admission is submitted.</div>
        </section>
    @endforeach
</div>
</body>
</html>
