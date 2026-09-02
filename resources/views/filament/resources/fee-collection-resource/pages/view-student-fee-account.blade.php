<x-filament-panels::page>
    @php
        $panelPrefix = auth()->user()?->hasRole('Super Admin') ? 'admin' : 'campus';
    @endphp

    @if(session('success'))
        <div class="mb-4 p-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#06192e] via-[#0b315d] to-[#104b79] p-5 text-white shadow-lg sm:p-7">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-amber-300">
                    <span>Student Fee Account</span>
                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-white/80">{{ $installmentCount }} installment vouchers</span>
                </div>
                <h2 class="text-2xl font-black sm:text-3xl">{{ $record->student->full_name }}</h2>
                <p class="mt-2 max-w-2xl text-sm text-blue-100">
                    {{ $planMatches
                        ? 'Admission installments are shown below with the same titles, dates and scheduled amounts entered in the admission form.'
                        : 'This legacy account needs review because its saved admission schedule and generated vouchers do not fully match.' }}
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button"
                        wire:click="mountAction('collectPayment')"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-400 px-5 py-3 text-sm font-black text-[#06192e] shadow-md transition hover:bg-amber-300">
                    Collect Custom / Partial Amount
                </button>
                <a href="{{ url("/{$panelPrefix}/fee-vouchers/create?student_id=" . $record->student->id) }}"
                   class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/30 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                    Create Additional Fee Voucher
                </a>
                @if((float) $record->amount_paid <= 0)
                    <button type="button"
                            wire:click="mountAction('syncAdmissionPlan')"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-amber-300/70 bg-amber-300/10 px-5 py-3 text-sm font-bold text-amber-200 transition hover:bg-amber-300/20">
                        Sync Admission Plan
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(! $planMatches)
        <div class="rounded-2xl border border-amber-300 bg-gradient-to-r from-amber-50 to-orange-50 p-5 shadow-sm dark:border-amber-800 dark:from-amber-950/40 dark:to-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-base font-black text-amber-900 dark:text-amber-200">Legacy fee plan mismatch detected</h3>
                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                        Saved admission rows total PKR {{ number_format($savedScheduleTotal, 2) }}, while active vouchers total PKR {{ number_format($scheduledAmount, 2) }}.
                        @if($hasPaymentHistory)
                            Because payment history exists, the system has protected this account from automatic rewriting.
                        @else
                            Use <strong>Sync Admission Plan</strong> to repair this unpaid account safely.
                        @endif
                    </p>
                </div>
                @if($record->admission)
                    <a href="{{ url("/{$panelPrefix}/admissions/{$record->admission->id}/edit?review=1") }}"
                       class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-amber-400 bg-white px-5 py-3 text-sm font-black text-amber-900 shadow-sm transition hover:bg-amber-100 dark:bg-slate-900 dark:text-amber-200">
                        Review Admission Fee Plan
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        <!-- Student Profile Card -->
        <div class="xl:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col items-center text-center">
                <img src="{{ \App\Support\DashboardImage::url($record->student->student_photo) ?? \App\Support\DashboardImage::avatar($record->student->full_name) }}"
                     alt="Profile photo" 
                     class="w-32 h-32 rounded-full border-4 border-slate-100 dark:border-slate-800 object-cover shadow">
                <h3 class="mt-4 text-xl font-bold text-slate-800 dark:text-slate-200">{{ $record->student->full_name }}</h3>
                <span class="px-3 py-1 mt-1 text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 rounded-full">
                    {{ $record->student->enrollment_number }}
                </span>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Campus:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->campus->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Course / Program:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->course->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Shift:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ ucfirst($record->student->admission->shift ?? 'morning') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Academic Session:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->admission->academicSession->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Student Phone:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->admission->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Father / Guardian:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->admission->father_name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Guardian Contact:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $record->student->admission->emergency_contact ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Status:</span>
                    <span class="px-2 py-0.5 text-xs font-bold rounded {{ $record->status === 'paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                        {{ strtoupper($record->status) }}
                    </span>
                </div>
            </div>

            <!-- Student Detail Actions -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex flex-col gap-2">
                @if($record->student && $record->student->admission)
                    <a href="{{ route('fee-vouchers.print.book', $record->student->admission) }}" 
                       target="_blank" 
                       class="w-full text-center px-4 py-2 text-xs font-bold text-white bg-[#082245] hover:bg-[#10345D] rounded-lg shadow-sm transition">
                        Print Voucher Book
                    </a>
                    <a href="{{ route('pdf.installment-schedule', $record->student->admission) }}" 
                       target="_blank" 
                       class="w-full text-center px-4 py-2 text-xs font-bold text-[#082245] hover:text-white border border-[#082245] hover:bg-[#082245] rounded-lg shadow-sm transition">
                        View Installment Schedule
                    </a>
                    <a href="{{ route('pdf.admission-agreement', $record->student->admission) }}" 
                       target="_blank" 
                       class="w-full text-center px-4 py-2 text-xs font-bold text-[#06192E] bg-[#C98D18] hover:bg-[#B97708] rounded-lg shadow-sm transition">
                        Print Student Agreement
                    </a>
                    <a href="{{ url("/{$panelPrefix}/fee-vouchers/create?student_id=" . $record->student->id) }}" 
                       class="w-full text-center px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-white border border-slate-300 dark:border-slate-800 hover:bg-[#082245] hover:border-[#082245] rounded-lg shadow-sm transition">
                        Create Additional Fee Voucher
                    </a>
                @endif
            </div>
        </div>

        <!-- Financial Summary & Voucher Timeline -->
        <div class="space-y-6 xl:col-span-3">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-blue-500">Scheduled Tuition</div>
                    <div class="mt-2 text-xl font-black text-[#082245] dark:text-blue-200">PKR {{ number_format($scheduledAmount, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-500">Admission fee included</div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-600">Concession</div>
                    <div class="mt-2 text-xl font-black text-amber-700 dark:text-amber-300">PKR {{ number_format($voucherConcession, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-500">Deducted from tuition</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Paid</div>
                    <div class="mt-2 text-xl font-black text-emerald-700 dark:text-emerald-300">PKR {{ number_format($record->amount_paid, 2) }}</div>
                    <div class="mt-1 text-xs text-slate-500">Receipts collected</div>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-white to-rose-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-rose-600">Remaining</div>
                    <div class="mt-2 text-xl font-black text-rose-700 dark:text-rose-300">PKR {{ number_format($record->balance, 2) }}</div>
                    <div class="mt-1 text-xs font-semibold text-rose-500">Overdue: PKR {{ number_format($overdueAmount, 2) }}</div>
                </div>
            </div>

            <!-- Voucher Timeline Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Admission Installments & Vouchers</h4>
                        <p class="mt-1 text-xs text-slate-500">Every row mirrors the installment schedule saved during admission.</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="fee-account-table-frame w-full rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="fee-voucher-schedule-table w-full border-collapse text-left">
                            <colgroup>
                                <col style="width: 16%">
                                <col style="width: 9%">
                                <col style="width: 10%">
                                <col style="width: 10%">
                                <col style="width: 10%">
                                <col style="width: 9%">
                                <col style="width: 10%">
                                <col style="width: 8%">
                                <col style="width: 10%">
                                <col style="width: 8%">
                            </colgroup>
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-medium">
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Title</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Due Date</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Scheduled</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Concession</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Payable</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Paid</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Balance</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Status</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Actions</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Collect Fee</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($vouchers as $voucher)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                                            {{ $voucher->title }}
                                            <div class="text-[10px] text-slate-400">{{ $voucher->voucher_number }}</div>
                                        </td>
                                         <td class="px-4 py-3 text-slate-500 dark:text-slate-400 font-medium">
                                             {{ $voucher->due_date ? $voucher->due_date->format('d-M-Y') : 'N/A' }}
                                         </td>
                                         <td class="px-4 py-3 text-right font-medium text-slate-800 dark:text-slate-200">
                                             PKR {{ number_format($voucher->subtotal, 2) }}
                                         </td>
                                         <td class="px-4 py-3 text-right font-semibold text-amber-600">
                                             {{ ((float) $voucher->discount_amount + (float) $voucher->scholarship_amount) > 0 ? '-PKR '.number_format((float) $voucher->discount_amount + (float) $voucher->scholarship_amount, 2) : '—' }}
                                         </td>
                                         <td class="px-4 py-3 text-right font-bold text-[#082245] dark:text-blue-200">
                                             PKR {{ number_format($voucher->total_amount, 2) }}
                                         </td>
                                         <td class="px-4 py-3 text-right text-emerald-600 font-semibold">
                                             PKR {{ number_format($voucher->paid_amount, 2) }}
                                         </td>
                                         <td class="px-4 py-3 text-right text-rose-600 font-semibold">
                                             PKR {{ number_format($voucher->balance_amount, 2) }}
                                         </td>
                                         <td class="px-4 py-3 text-center">
                                             @php
                                                 $statusColor = match($voucher->status) {
                                                     'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                                     'partially_paid' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                                     'due', 'issued' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                                     'overdue' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                                     'waived' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300',
                                                     default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
                                                 };
                                             @endphp
                                             <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $statusColor }}">
                                                 {{ ucfirst($voucher->status) }}
                                             </span>
                                             @if(auth()->user()?->hasRole('Super Admin') && $voucher->edit_request_status === 'pending')
                                                 <div class="mt-2 p-1.5 text-[10px] bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 rounded border border-amber-200 dark:border-amber-900 max-w-[150px] mx-auto text-left">
                                                     <span class="font-bold block text-slate-700 dark:text-slate-300">Edit Requested:</span> 
                                                     <span class="italic block mb-1">"{{ $voucher->edit_request_reason }}"</span>
                                                     <div class="flex gap-2 justify-end border-t border-amber-200/50 pt-1 font-bold">
                                                         <a href="{{ route('fee-vouchers.approve-edit', $voucher->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">Approve</a>
                                                         <a href="{{ route('fee-vouchers.reject-edit', $voucher->id) }}" class="text-rose-600 dark:text-rose-400 hover:underline">Reject</a>
                                                     </div>
                                                 </div>
                                             @endif
                                         </td>
                                         <td class="px-4 py-3 text-center space-x-2">
                                             @php
                                                 $isPaid = $voucher->status === 'paid';
                                                 $isOverdue = $voucher->due_date && now()->startOfDay()->greaterThan($voucher->due_date->startOfDay());
                                                 $isDueDays = !$isOverdue;
                                                 $canPrintDuplicate = ($isPaid || $isDueDays) && !(auth()->user()?->campus_id && $isPaid);
                                             @endphp

                                             @if($canPrintDuplicate)
                                                 <a href="{{ route('fee-vouchers.print.portrait', $voucher->id) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-800 hover:underline">
                                                     Print Duplicate
                                                 </a>
                                             @else
                                                 <span class="text-xs text-slate-400 font-semibold cursor-not-allowed" title="{{ $isPaid ? 'Voucher is already paid.' : 'Cannot print duplicate after due date. Print Late Fee instead.' }}">
                                                     Print Duplicate
                                                 </span>
                                             @endif

                                             @if($isOverdue && !$isPaid)
                                                 <span class="text-slate-300">|</span>
                                                 <a href="{{ route('fee-vouchers.print.late', $voucher->id) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                                     Print Late Fee
                                                 </a>
                                             @endif

                                             <span class="text-slate-300">|</span>
                                             <a href="{{ auth()->user()?->hasRole('Super Admin') ? url("/admin/fee-vouchers/{$voucher->id}/edit") : url("/campus/fee-vouchers/{$voucher->id}/edit") }}" 
                                                class="inline-flex items-center text-xs font-bold text-amber-600 hover:text-amber-800 hover:underline">
                                                 Edit
                                             </a>
                                         </td>
                                         <td class="px-4 py-3 text-center">
                                             @if($voucher->status !== 'paid' && $voucher->status !== 'waived' && $voucher->status !== 'cancelled')
                                                 <button type="button" 
                                                         wire:click="mountAction('collectPayment', { voucher_id: {{ $voucher->id }} })" 
                                                         class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-800 hover:underline border-none bg-transparent p-0 cursor-pointer">
                                                     Collect Now
                                                 </button>
                                             @else
                                                 <span class="text-xs text-slate-400 font-semibold cursor-not-allowed">
                                                     Paid
                                                 </span>
                                             @endif
                                         </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-6 text-center text-slate-400">
                                            No vouchers generated for this account.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Collection History Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Payments Collection & Receipt Log</h4>
                </div>
                <div class="p-6">
                    <div class="fee-account-table-frame w-full">
                        <table class="fee-payment-history-table w-full border-collapse text-left">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-medium">
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Receipt No</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Payment Date</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Amount</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Method</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Cashier</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($payments as $payment)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $payment->receipt_number }}
                                            @if($payment->notes)
                                                <div class="text-[10px] text-slate-400 font-normal">Notes: {{ $payment->notes }}</div>
                                            @endif
                                        </td>
                                         <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                             {{ $payment->payment_date ? $payment->payment_date->format('d-M-Y') : 'N/A' }}
                                         </td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-600">
                                            PKR {{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200 font-medium">
                                            {{ strtoupper($payment->payment_method) }}
                                            @if($payment->transaction_reference)
                                                <div class="text-[10px] text-slate-400">Ref: {{ $payment->transaction_reference }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                            {{ $payment->collectedBy->name ?? 'System' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('pdf.payment-receipt', $payment->id) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-800 hover:underline">
                                                Print Receipt
                                            </a>
                                            @if($payment->office_copy)
                                                <span class="text-slate-300">|</span>
                                                <a href="{{ asset('storage/' . $payment->office_copy) }}" 
                                                   target="_blank" 
                                                   class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-800 hover:underline">
                                                    View Copy
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                                            No payments collected yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function requestVoucherEdit(voucherId) {
            const reason = prompt("Please enter the reason for editing this voucher:");
            if (reason === null) return;
            if (reason.trim() === "") {
                alert("Reason is required to submit an edit request.");
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/fee-vouchers/${voucherId}/request-edit`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || '{{ csrf_token() }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
    <x-filament-actions::modals />
</x-filament-panels::page>
