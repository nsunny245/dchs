<x-filament-panels::page>
    @php
        $user = filament()->auth()->user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $completion = \App\Services\HR\CalculateProfileCompletionService::evaluate($record);
        $readiness = \App\Services\HR\EvaluateAgreementReadinessService::check($record);
        $currentEmployment = $record->currentEmployment ?? $record->employmentRecords->first();
        $currentSalary = $record->currentSalary ?? $record->salaryRecords->first();
    @endphp

    <!-- Top Profile Header Card -->
    <div class="bg-navy-950 text-white rounded-2xl p-6 shadow-xl border border-navy-800 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-gold-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center space-x-5">
                <div class="w-20 h-20 rounded-2xl bg-navy-800 border-2 border-gold-500/80 flex items-center justify-center text-gold-400 overflow-hidden font-bold text-2xl flex-shrink-0 shadow-lg">
                    <img
                        src="{{ \App\Support\DashboardImage::url($record->photo_path) ?? \App\Support\DashboardImage::avatar($record->full_name) }}"
                        alt="{{ $record->full_name }} profile photo"
                        class="w-full h-full object-cover"
                    >
                </div>

                <div class="space-y-1">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-2xl font-bold text-white tracking-tight">{{ $record->full_name }}</h2>
                        <span class="px-2.5 py-0.5 text-xs font-mono font-bold bg-gold-500/20 text-gold-300 rounded-md border border-gold-500/40">
                            {{ $record->employee_id }}
                        </span>
                    </div>

                    <p class="text-sm text-navy-200 font-medium">
                        {{ $record->designation }} &bull; <span class="text-gold-400 font-semibold">{{ $record->campus?->name }}</span>
                    </p>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            {{ ucfirst($currentEmployment?->appointment_status ?? 'Probation') }}
                        </span>
                        <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full bg-navy-800 text-navy-200 border border-navy-700">
                            {{ ucfirst(str_replace('_', ' ', $currentEmployment?->employment_type ?? 'Full-Time')) }}
                        </span>
                        <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            Joined: {{ $record->joining_date?->format('d M Y') ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons & Readiness Gauge -->
            <div class="flex flex-col items-end space-y-3 w-full md:w-auto">
                <div class="flex items-center space-x-4 bg-navy-900/80 px-4 py-2 rounded-xl border border-navy-800">
                    <div class="text-right">
                        <p class="text-[10px] text-navy-300 uppercase tracking-wider font-bold">Profile Completion</p>
                        <p class="text-lg font-bold text-gold-400">{{ $completion['percentage'] }}%</p>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-gold-500/60 flex items-center justify-center text-xs font-bold text-white bg-navy-950">
                        {{ $completion['percentage'] }}%
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('pdf.teacher-profile-summary', $record->id) }}" target="_blank" class="px-3.5 py-1.5 text-xs font-bold bg-navy-800 hover:bg-navy-700 text-white rounded-lg border border-navy-700 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Summary
                    </a>

                    @if ($isSuperAdmin)
                        <button type="button" wire:click="generateAgreement" class="px-3.5 py-1.5 text-xs font-bold bg-gold-600 hover:bg-gold-500 text-navy-950 rounded-lg transition-colors flex items-center shadow-md">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Generate Agreement
                        </button>

                        @if ($currentEmployment?->appointment_status === 'probation')
                            <button type="button" wire:click="confirmEmployment" class="px-3.5 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors flex items-center">
                                Confirm Employment
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-xl shadow-sm px-4 pt-2">
        <nav class="flex space-x-6 overflow-x-auto text-xs font-bold">
            @foreach (['overview' => 'Overview', 'personal' => 'Personal', 'academic' => 'Academic & Qualifications', 'employment' => 'Employment History', 'attendance' => 'Attendance Log', 'leave' => 'Leave Requests', 'payroll' => 'Payroll & Salary', 'documents' => 'Documents', 'agreements' => 'Agreements & Versions'] as $key => $label)
                @if ($key !== 'payroll' || $isSuperAdmin)
                    <button type="button" wire:click="setActiveTab('{{ $key }}')" class="py-3.5 border-b-2 transition-colors flex items-center whitespace-nowrap {{ $activeTab === $key ? 'border-navy-900 text-navy-900 dark:border-gold-400 dark:text-gold-400 font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                        {{ $label }}
                    </button>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- TAB CONTENTS -->
    <div class="mt-6">
        @if ($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Academic & Registration -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-navy-900 dark:text-gold-400 uppercase tracking-wider">Academic Credentials</h3>
                    <div class="space-y-2 text-xs text-gray-700 dark:text-gray-300">
                        <p><span class="font-semibold text-gray-500">Degree:</span> {{ $record->academics?->degree_title ?? 'Not specified' }}</p>
                        <p><span class="font-semibold text-gray-500">University:</span> {{ $record->academics?->institution ?? 'Not specified' }}</p>
                        <p><span class="font-semibold text-gray-500">Teaching Experience:</span> {{ $record->academics?->teaching_experience_years ?? 0 }} Years</p>
                        <p><span class="font-semibold text-gray-500">Clinical Experience:</span> {{ $record->academics?->clinical_experience_years ?? 0 }} Years</p>
                    </div>
                </div>

                <!-- Employment & Posting -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-navy-900 dark:text-gold-400 uppercase tracking-wider">Posting Information</h3>
                    <div class="space-y-2 text-xs text-gray-700 dark:text-gray-300">
                        <p><span class="font-semibold text-gray-500">Department:</span> {{ $record->department ?? 'N/A' }}</p>
                        <p><span class="font-semibold text-gray-500">Shift:</span> {{ $currentEmployment?->shift ?? 'Morning' }}</p>
                        <p><span class="font-semibold text-gray-500">Weekly Hours:</span> {{ $currentEmployment?->weekly_working_hours ?? 40 }} Hours</p>
                        <p><span class="font-semibold text-gray-500">Biometric Device ID:</span> {{ $currentEmployment?->biometric_id ?? 'Not assigned' }}</p>
                    </div>
                </div>

                <!-- Remuneration (Protected) -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-sm text-navy-900 dark:text-gold-400 uppercase tracking-wider">Salary Summary</h3>
                    @if ($isSuperAdmin && $currentSalary)
                        <div class="space-y-2 text-xs text-gray-700 dark:text-gray-300">
                            <p><span class="font-semibold text-gray-500">Gross Salary:</span> <strong class="text-emerald-600 text-sm font-mono">PKR {{ number_format($currentSalary->gross_salary, 2) }}</strong></p>
                            <p><span class="font-semibold text-gray-500">Basic Salary:</span> PKR {{ number_format($currentSalary->basic_salary, 2) }}</p>
                            <p><span class="font-semibold text-gray-500">Payment Method:</span> {{ ucfirst($currentSalary->payment_method) }}</p>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">Protected remuneration details. Accessible by Super Admin HR only.</p>
                    @endif
                </div>
            </div>
        @elseif ($activeTab === 'personal')
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-700 dark:text-gray-300">
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-gold-400 mb-3 uppercase tracking-wider">Personal Identity</h4>
                    <p class="py-1"><span class="font-semibold text-gray-500">Full Name:</span> {{ $record->full_name }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">Father/Spouse Name:</span> {{ $record->father_or_spouse_name ?? 'N/A' }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">CNIC:</span> {{ $record->cnic }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">Date of Birth:</span> {{ $record->date_of_birth?->format('d M Y') ?? 'N/A' }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">Gender:</span> {{ ucfirst($record->gender ?? 'N/A') }}</p>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-navy-900 dark:text-gold-400 mb-3 uppercase tracking-wider">Contact & Emergency</h4>
                    <p class="py-1"><span class="font-semibold text-gray-500">Primary Phone:</span> {{ $record->phone }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">WhatsApp:</span> {{ $record->whatsapp ?? 'N/A' }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">Emergency Contact:</span> {{ $record->emergency_contact_name }} ({{ $record->emergency_contact_relationship }}) - {{ $record->emergency_contact_phone }}</p>
                    <p class="py-1"><span class="font-semibold text-gray-500">Current Address:</span> {{ $record->current_address ?? 'N/A' }}</p>
                </div>
            </div>
        @elseif ($activeTab === 'agreements')
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-6">
                <h3 class="font-bold text-sm text-navy-900 dark:text-gold-400 uppercase tracking-wider">Employment Agreements & Versions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-500 uppercase tracking-wider font-bold">
                                <th class="py-2.5">Agreement #</th>
                                <th class="py-2.5">Version</th>
                                <th class="py-2.5">Status</th>
                                <th class="py-2.5">Generated At</th>
                                <th class="py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($record->agreementVersions as $ver)
                                <tr>
                                    <td class="py-3 font-mono font-bold text-navy-900 dark:text-gold-400">{{ $ver->agreement_number }}</td>
                                    <td class="py-3 font-bold">V{{ $ver->version }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full {{ $ver->status === 'signed' ? 'bg-emerald-100 text-emerald-800' : ($ver->status === 'generated' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ ucfirst($ver->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-gray-500">{{ $ver->generated_at?->format('d M Y H:i') ?? 'N/A' }}</td>
                                    <td class="py-3 text-right">
                                        @if ($ver->generated_pdf_path)
                                            <a href="{{ Storage::disk('public')->url($ver->generated_pdf_path) }}" target="_blank" class="px-2.5 py-1 text-xs font-bold bg-navy-900 text-white rounded hover:bg-navy-800">
                                                Download PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400 italic">No agreements generated yet. Click "Generate Agreement" to create one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-xs text-gray-500">
                <p>Showing records for <strong>{{ ucfirst($activeTab) }}</strong> tab.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
