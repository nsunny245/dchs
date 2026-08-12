<div
    x-data="{
        tuition: $wire.entangle('data.custom_tuition_fee'),
        admissionFee: $wire.entangle('data.custom_admission_fee'),
        examinationFee: $wire.entangle('data.custom_examination_fee'),
        concession: $wire.entangle('data.concession_amount'),
        installments: $wire.entangle('data.custom_installment_count'),
        amount(value) {
            return Number(value || 0)
        },
        money(value) {
            return this.amount(value).toLocaleString('en-PK', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        },
        netPayable() {
            return Math.max(
                0,
                this.amount(this.tuition) + this.amount(this.admissionFee) + this.amount(this.examinationFee) - this.amount(this.concession),
            )
        },
        perInstallment() {
            const count = Math.max(1, Number.parseInt(this.installments || 5, 10))

            return Math.max(0, (this.amount(this.tuition) - this.amount(this.concession)) / count)
        },
    }"
    class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="border-b pb-2 text-sm font-bold text-slate-800">Fee Package Summary</div>
    <div class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
        <div class="space-y-1">
            <div><strong>Tuition Fee Total:</strong> PKR <span x-text="money(tuition)"></span></div>
            <div><strong>Admission/Exam Fee:</strong> PKR <span x-text="money(amount(admissionFee) + amount(examinationFee))"></span></div>
            <div><strong>Discount Waived:</strong> -PKR <span x-text="money(concession)"></span></div>
            <div class="pt-1 text-sm font-bold text-emerald-700">Net Total Payable: PKR <span x-text="money(netPayable())"></span></div>
        </div>
        <div class="space-y-1">
            <div><strong>Installment Plan:</strong> <span x-text="Number.parseInt(installments || 5, 10)"></span> Custom Installments</div>
            <div><strong>Approx per Installment:</strong> PKR <span x-text="money(perInstallment())"></span></div>
            <div class="pt-1 text-xs font-semibold text-amber-700">Late Fee Rule: PKR 50/day after 10-day grace period</div>
        </div>
    </div>
</div>
