<div class="space-y-4">
    <div class="grid grid-cols-3 gap-3 rounded-xl bg-slate-50 p-4 text-sm dark:bg-slate-800">
        <div><div class="text-xs text-slate-500">Student</div><div class="font-bold">{{ $student->full_name }}</div></div>
        <div><div class="text-xs text-slate-500">Course</div><div class="font-bold">{{ $student->course?->name ?? 'N/A' }}</div></div>
        <div><div class="text-xs text-slate-500">Enrollment</div><div class="font-bold">{{ $student->enrollment_number }}</div></div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
        <table class="w-full min-w-[760px] text-sm"><thead class="bg-[#08264b] text-left text-white"><tr><th class="px-3 py-3">Voucher</th><th class="px-3 py-3">Due</th><th class="px-3 py-3 text-right">Payable</th><th class="px-3 py-3 text-right">Paid</th><th class="px-3 py-3 text-right">Balance</th><th class="px-3 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">@forelse($vouchers as $voucher)<tr><td class="px-3 py-3 font-semibold">{{ $voucher->title }}<div class="text-xs text-slate-400">{{ $voucher->voucher_number }}</div></td><td class="px-3 py-3">{{ optional($voucher->due_date)->format('d M Y') ?? 'N/A' }}</td><td class="px-3 py-3 text-right font-semibold">PKR {{ number_format((float)$voucher->total_amount, 2) }}</td><td class="px-3 py-3 text-right text-emerald-600">PKR {{ number_format((float)$voucher->paid_amount, 2) }}</td><td class="px-3 py-3 text-right text-rose-600">PKR {{ number_format((float)$voucher->balance_amount, 2) }}</td><td class="px-3 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold dark:bg-slate-700">{{ ucfirst(str_replace('_',' ', $voucher->status)) }}</span></td></tr>@empty<tr><td colspan="6" class="px-3 py-8 text-center text-slate-500">No payment schedule is available.</td></tr>@endforelse</tbody></table>
    </div>
</div>
