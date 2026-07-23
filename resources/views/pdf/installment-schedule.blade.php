<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Installment Schedule — {{ $admission->enrollment_no }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #102033; font-size: 10px; }
        header { border-bottom: 3px solid #c98d18; padding-bottom: 10px; margin-bottom: 18px; }
        .logo { width: 58px; float: left; margin-right: 12px; }
        h1 { color: #082245; margin: 4px 0; font-size: 20px; }
        .tagline { color: #b97708; font-weight: bold; }
        .meta, table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 8px 4px 0; }
        table.schedule { margin-top: 18px; }
        .schedule th { background: #082245; color: #fff; padding: 8px; text-align: left; }
        .schedule td { border: 1px solid #d9e2ec; padding: 8px; }
        .amount { text-align: right; font-weight: bold; }
        footer { position: fixed; bottom: 0; color: #5e6d7e; font-size: 8px; }
    </style>
</head>
<body>
<header>
    <img class="logo" src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="Daniyal Group of Colleges">
    <h1>Daniyal Group of Colleges</h1>
    <div class="tagline">Where Success Is a Tradition</div>
    <div style="clear: both"></div>
</header>
<h2>Student Installment Schedule</h2>
<table class="meta">
    <tr><td><strong>Student:</strong> {{ $admission->applicant_name }}</td><td><strong>Admission No:</strong> {{ $admission->enrollment_no }}</td></tr>
    <tr><td><strong>Campus:</strong> {{ $admission->campus?->name }}</td><td><strong>Course:</strong> {{ $admission->course?->name }}</td></tr>
    <tr><td><strong>Session:</strong> {{ $admission->academicSession?->name }}</td><td><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</td></tr>
</table>
<table class="schedule">
    <thead><tr><th>#</th><th>Installment</th><th>Due Date</th><th>Status</th><th class="amount">Net Payable</th></tr></thead>
    <tbody>
    @foreach($installments as $installment)
        <tr>
            <td>{{ $installment->installment_number }}</td>
            <td>{{ $installment->title }}</td>
            <td>{{ $installment->due_date->format('d M Y') }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $installment->status)) }}</td>
            <td class="amount">PKR {{ number_format($installment->net_paisa / 100, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><td colspan="4"><strong>Total</strong></td><td class="amount">PKR {{ number_format($installments->sum('net_paisa') / 100, 2) }}</td></tr></tfoot>
</table>
<p style="margin-top: 20px">This schedule is linked to the finalized student fee-plan snapshot. Posted financial records are corrected through reversal entries, not direct overwrites.</p>
<footer>Daniyal Group of Colleges · {{ $admission->enrollment_no }} · Page 1</footer>
</body>
</html>
