<div class="fi-wi-widget bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
            <x-heroicon-o-printer class="w-5 h-5 text-primary-500" />
            Course-wise Bulk Voucher Printing
        </h3>
    </div>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($courses as $course)
            <div class="flex flex-col justify-between p-4 bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100/70 dark:hover:bg-slate-850 rounded-lg border border-slate-100 dark:border-slate-800 transition">
                <div class="mb-3">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 line-clamp-2" title="{{ $course->name }}">
                        {{ $course->name }}
                    </h4>
                </div>
                <div>
                    <button type="button" 
                            wire:click="openPrintModal({{ $course->id }})" 
                            class="w-full text-center px-3 py-1.5 text-xs font-bold text-white bg-[#082245] hover:bg-[#10345D] rounded-lg shadow-sm transition cursor-pointer">
                        Print Vouchers
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Dialog / Modal overlay -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md p-6 overflow-hidden transform transition-all">
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
                            <select wire:model="selectedCampusId" class="w-full text-sm rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-primary-500 focus:border-primary-500">
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
                        <select wire:model="selectedMonth" class="w-full text-sm rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-primary-500 focus:border-primary-500">
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
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-new-tab', (event) => {
                const url = event[0].url;
                window.open(url, '_blank');
            });
        });
    </script>
</div>
