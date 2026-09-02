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
            @php
                $panelPrefix = auth()->user()?->hasRole('Super Admin') ? 'admin' : 'campus';
                $reviewEditUrl = \App\Filament\Resources\AdmissionResource::getUrl(
                    'edit',
                    ['record' => $admission, 'review' => 1],
                    panel: $panelPrefix,
                );
            @endphp
            <a href="{{ $reviewEditUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-[#082245] px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#10345D] focus:outline-none focus:ring-4 focus:ring-[#E7B65A]/40">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM19.5 7.125L16.875 4.5M18 14.25v4.125A2.625 2.625 0 0115.375 21H5.625A2.625 2.625 0 013 18.375V8.625A2.625 2.625 0 015.625 6H9.75" />
                </svg>
                Review &amp; Edit Admission
            </a>
            <a href="{{ route('pdf.admission-agreement', $admission) }}" target="_blank" class="rounded-lg bg-[#C98D18] px-4 py-3 text-sm font-bold text-[#06192E]">Print Student Agreement</a>
            @if($vouchers->first())
                <a href="{{ route('fee-vouchers.print.book', $admission) }}" target="_blank" class="rounded-lg border border-[#082245] px-4 py-3 text-sm font-bold text-[#082245]">Print Voucher Book</a>
            @endif
            <a href="{{ route('pdf.installment-schedule', $admission) }}" target="_blank" class="rounded-lg border border-[#082245] px-4 py-3 text-sm font-bold text-[#082245]">View Installment Schedule</a>
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
