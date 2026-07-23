<div class="space-y-4">
    <div class="rounded-lg border border-[#F3D79F] bg-[#FCF5E6] p-3 text-sm text-[#824f04]">
        Preview only. No voucher or ledger record is created until the admission is finalized.
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#082245] text-white">
            <tr>
                <th class="p-3">#</th>
                <th class="p-3">Voucher</th>
                <th class="p-3">Due Date</th>
                <th class="p-3 text-right">Gross</th>
                <th class="p-3 text-right">Concession</th>
                <th class="p-3 text-right">Net Payable</th>
            </tr>
            </thead>
            <tbody>
            @foreach($schedule as $row)
                <tr class="border-b border-[#D9E2EC]">
                    <td class="p-3">{{ $row['number'] }}</td>
                    <td class="p-3 font-semibold">{{ $row['title'] }}</td>
                    <td class="p-3">{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('d M Y') }}</td>
                    <td class="p-3 text-right">PKR {{ number_format($row['gross_paisa'] / 100, 2) }}</td>
                    <td class="p-3 text-right">PKR {{ number_format($row['concession_paisa'] / 100, 2) }}</td>
                    <td class="p-3 text-right font-bold text-[#082245]">PKR {{ number_format($row['net_paisa'] / 100, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="bg-[#F8FAFC] font-bold">
                <td colspan="5" class="p-3 text-right">Total Net Payable</td>
                <td class="p-3 text-right text-[#082245]">PKR {{ number_format(collect($schedule)->sum('net_paisa') / 100, 2) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
