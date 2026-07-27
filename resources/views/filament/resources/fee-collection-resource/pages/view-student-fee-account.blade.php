<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Profile Card -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col items-center text-center">
                <img src="{{ $record->student->student_photo ? asset('storage/' . $record->student->student_photo) : url('images/default-avatar.png') }}" 
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
                @endif
            </div>
        </div>

        <!-- Financial Summary & Voucher Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-center">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Net Contractual Package</div>
                    <div class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-200">PKR {{ number_format($record->net_payable, 2) }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Original: PKR {{ number_format($record->original_fee, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-center">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Paid / Collected</div>
                    <div class="mt-1 text-lg font-bold text-emerald-600">PKR {{ number_format($record->amount_paid, 2) }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Concession: PKR {{ number_format($record->concession_amount, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-center col-span-2 md:col-span-1">
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Outstanding Balance</div>
                    <div class="mt-1 text-lg font-bold text-rose-600">PKR {{ number_format($record->balance, 2) }}</div>
                    <div class="text-[10px] text-rose-400 mt-0.5 font-bold">Overdue: PKR {{ number_format($overdueAmount, 2) }}</div>
                </div>
            </div>

            <!-- Voucher Timeline Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">Installments Timeline & Vouchers</h4>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse text-left">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-medium">
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Title</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">Due Date</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Amount</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Paid</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-right">Balance</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Status</th>
                                    <th class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($vouchers as $voucher)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                                            {{ $voucher->title }}
                                            <div class="text-[10px] text-slate-400">{{ $voucher->voucher_number }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                            {{ $voucher->due_date ? $voucher->due_date->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-800 dark:text-slate-200">
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
                                        </td>
                                         <td class="px-4 py-3 text-center space-x-2">
                                             @php
                                                 $isPaid = $voucher->status === 'paid';
                                                 $isOverdue = $voucher->due_date && now()->startOfDay()->greaterThan($voucher->due_date->startOfDay());
                                                 $isDueDays = !$isOverdue;
                                             @endphp

                                             @if($isPaid || $isDueDays)
                                                 <a href="{{ route('fee-vouchers.print.portrait', $voucher->id) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center text-xs font-bold text-primary-600 hover:text-primary-800 hover:underline">
                                                     Print Duplicate
                                                 </a>
                                             @else
                                                 <span class="text-xs text-slate-400 font-semibold cursor-not-allowed" title="Cannot print duplicate after due date. Print Late Fee instead.">
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

                                             @if(auth()->user()?->hasRole('Super Admin'))
                                                 <span class="text-slate-300">|</span>
                                                 @php
                                                     $panelPrefix = auth()->user()?->hasRole('Super Admin') ? 'admin' : 'campus';
                                                 @endphp
                                                 <a href="{{ url("/{$panelPrefix}/fee-vouchers/{$voucher->id}/edit") }}" 
                                                    class="inline-flex items-center text-xs font-bold text-amber-600 hover:text-amber-800 hover:underline">
                                                     Edit
                                                 </a>
                                             @endif
                                         </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse text-left">
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
                                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}
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
</x-filament-panels::page>
