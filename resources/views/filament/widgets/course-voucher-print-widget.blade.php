<div class="fi-wi-widget bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 shadow-sm mb-6">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-3">
            <x-heroicon-o-printer class="w-7 h-7 text-primary-500" />
            Course-wise Bulk Voucher Printing
        </h3>
    </div>

    <!-- Courses Grid (Spacious 3-in-a-row, large cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($courses as $course)
            <div wire:key="course-card-{{ $course->id }}" class="flex flex-col justify-between p-8 bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100/70 dark:hover:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-800 transition-all duration-200 shadow-sm hover:shadow-md">
                <div class="mb-6 flex items-start gap-4">
                    <div class="p-4 bg-primary-50 dark:bg-primary-950 rounded-xl text-primary-600 dark:text-primary-400 shrink-0 shadow-sm">
                        <x-heroicon-o-academic-cap class="w-8 h-8" />
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-lg font-bold text-slate-800 dark:text-slate-200 leading-snug line-clamp-2" title="{{ $course->name }}">
                            {{ $course->name }}
                        </h4>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block">DCHS Academic Program</span>
                    </div>
                </div>
                <div>
                    <button type="button" 
                            wire:click="openPrintModal({{ $course->id }})" 
                            class="w-full text-center px-4 py-3 text-sm font-bold text-white bg-[#082245] hover:bg-[#10345D] rounded-xl shadow-md transition-all duration-150 cursor-pointer hover:-translate-y-0.5 active:translate-y-0">
                        Print Vouchers
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Dialog / Modal overlay (Prevent Duplication with unique wire:keys) -->
    @if($showModal)
        <div wire:key="print-course-modal-overlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div wire:key="print-course-modal-content" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md p-6 overflow-hidden transform transition-all">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-2">
                    <x-heroicon-o-document-arrow-down class="w-6 h-6 text-primary-500" />
                    Print Course Vouchers
                </h3>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                    Select details to download monthly vouchers for <span class="font-bold text-slate-800 dark:text-slate-200">"{{ $selectedCourseName }}"</span>.
                </p>

                <div class="space-y-4">
                    <!-- Campus Selection (Super Admin only) -->
                    @if($isSuperAdmin)
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wider">Campus</label>
                            <select wire:model="selectedCampusId" 
                                    class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                                    style="appearance: auto !important; -webkit-appearance: auto !important; -moz-appearance: auto !important; background-image: none !important; padding-right: 1.5rem !important;">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Month Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wider">Select Month</label>
                        <select wire:model="selectedMonth" 
                                class="w-full text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-2 px-3 focus:ring-primary-500 focus:border-primary-500"
                                style="appearance: auto !important; -webkit-appearance: auto !important; -moz-appearance: auto !important; background-image: none !important; padding-right: 1.5rem !important;">
                            @foreach($months as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" 
                            wire:click="generatePdf" 
                            class="px-4 py-2 text-xs font-bold text-white bg-[#082245] hover:bg-[#10345D] rounded-lg transition cursor-pointer">
                        Generate PDF
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('open-new-tab', event => {
            const url = event.detail.url || (event.detail && event.detail[0] ? event.detail[0].url : null) || event.detail;
            if (url && typeof url === 'string') {
                window.open(url, '_blank');
            } else if (event.detail && event.detail.url) {
                window.open(event.detail.url, '_blank');
            }
        });
    </script>
</div>
