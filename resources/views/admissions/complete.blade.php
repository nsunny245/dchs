<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admission Complete — Daniyal Group of Colleges</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#F4F7FB] text-[#102033]">
<main class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
    <section class="overflow-hidden rounded-2xl border border-[#D9E2EC] bg-white shadow-xl">
        <header class="bg-[#082245] px-6 py-8 text-white sm:px-10">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="Daniyal Group of Colleges" class="h-20 w-20 object-contain">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-[#E7B65A]">Where Success Is a Tradition</p>
                    <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Admission created successfully</h1>
                    <p class="mt-2 text-sm text-slate-200">The student, fee plan, installments, vouchers, and opening ledger are ready.</p>
                </div>
            </div>
        </header>

        <div class="grid gap-6 p-6 sm:grid-cols-2 sm:p-10">
            @foreach([
                'Student' => $admission->applicant_name,
                'Admission Number' => $admission->enrollment_no ?: 'Pending',
                'Campus' => $admission->campus?->name ?: 'Pending',
                'Course' => $admission->course?->name ?: 'Pending',
                'Session' => $admission->academicSession?->name ?: 'Pending',
                'Fee Plan' => $feeSnapshot?->feeStructure?->name ?: ('Version ' . ($feeSnapshot?->structure_version ?: 1)),
                'Vouchers Generated' => $vouchers->count(),
                'Document Status' => $admission->is_document_deficient ? 'Documents deficient' : 'Complete',
            ] as $label => $value)
                <div class="rounded-xl border border-[#D9E2EC] bg-[#F8FAFC] p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-[#8290A3]">{{ $label }}</div>
                    <div class="mt-1 font-bold text-[#082245]">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-3 border-t border-[#D9E2EC] p-6 sm:px-10">
            <a href="{{ route('pdf.admission-letter', $admission) }}" target="_blank" class="rounded-lg bg-[#082245] px-4 py-3 text-sm font-bold text-white">Print Admission Form</a>
            <a href="{{ route('pdf.admission-agreement', $admission) }}" target="_blank" class="rounded-lg bg-[#C98D18] px-4 py-3 text-sm font-bold text-[#06192E]">Print Fee Agreement</a>
            @if($vouchers->first())
                <a href="{{ route('fee-vouchers.print.book', $admission) }}" target="_blank" class="rounded-lg border border-[#082245] px-4 py-3 text-sm font-bold text-[#082245]">Print Voucher Book</a>
            @endif
            <a href="{{ route('pdf.installment-schedule', $admission) }}" target="_blank" class="rounded-lg border border-[#082245] px-4 py-3 text-sm font-bold text-[#082245]">View Installment Schedule</a>
            @php
                $panelPrefix = auth()->user()?->hasRole('Super Admin') ? 'admin' : 'campus';
            @endphp
            @if($admission->student)
                <a href="{{ url("/{$panelPrefix}/students/{$admission->student->id}/edit") }}" class="rounded-lg border border-[#D9E2EC] px-4 py-3 text-sm font-bold">View Student Profile</a>
            @endif
            <a href="{{ url("/{$panelPrefix}/fee-collections") }}" class="rounded-lg border border-[#D9E2EC] px-4 py-3 text-sm font-bold">Record Initial Payment</a>
            <a href="{{ url("/{$panelPrefix}/admissions/create") }}" class="rounded-lg border border-[#D9E2EC] px-4 py-3 text-sm font-bold">Create Another Admission</a>
            <a href="{{ url("/{$panelPrefix}/admissions") }}" class="rounded-lg border border-[#D9E2EC] px-4 py-3 text-sm font-bold">Return to Admissions</a>
        </div>
    </section>
</main>
</body>
</html>
