<div
    x-data="{
        tuition: $wire.entangle('data.custom_tuition_fee'),
        examinationFee: $wire.entangle('data.custom_examination_fee'),
        verificationFee: $wire.entangle('data.custom_verification_fee'),
        otherFees: $wire.entangle('data.custom_other_misc'),
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
        netTuition() {
            return Math.max(0, this.amount(this.tuition) - this.amount(this.concession))
        },
        netPayable() {
            return this.netTuition() + this.amount(this.examinationFee) + this.amount(this.verificationFee) + this.amount(this.otherFees)
        },
        perInstallment() {
            const count = Math.max(1, Number.parseInt(this.installments || 5, 10))

            return this.netTuition() / count
        },
    }"
    class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="border-b pb-2 text-sm font-bold text-slate-800">Fee Package Summary</div>
    <div class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
        <div class="space-y-1">
            <div><strong>Program Tuition Package:</strong> PKR <span x-text="money(tuition)"></span></div>
            <div><strong>Discount / Concession:</strong> -PKR <span x-text="money(concession)"></span></div>
            <div class="pt-1 text-sm font-bold text-emerald-700">Net Tuition: PKR <span x-text="money(netTuition())"></span></div>
            <div class="pt-2 text-slate-600"><strong>Additional fee breakdown:</strong> Exam PKR <span x-text="money(examinationFee)"></span>, Verification PKR <span x-text="money(verificationFee)"></span>, Other PKR <span x-text="money(otherFees)"></span></div>
            <div class="pt-1 text-sm font-bold text-slate-900">Total Payable: PKR <span x-text="money(netPayable())"></span></div>
        </div>
        <div class="space-y-1">
            <div><strong>Installment Plan:</strong> <span x-text="Number.parseInt(installments || 5, 10)"></span> Custom Installments</div>
            <div><strong>Approx per Installment:</strong> PKR <span x-text="money(perInstallment())"></span></div>
            <div class="pt-1 text-xs font-semibold text-amber-700">Late Fee Rule: PKR 50/day after 10-day grace period</div>
        </div>
    </div>
</div>
