<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 70% Content Area: Wizard Form -->
        <div class="lg:col-span-8 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-6 flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-4">
                    <button type="button" wire:click="saveDraft" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                        Save as Draft
                    </button>

                    <div class="flex space-x-3">
                        <a href="{{ \App\Filament\Resources\StaffResource::getUrl('index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right 30% Summary Panel -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-navy-950 text-white rounded-xl p-6 shadow-md border border-navy-800 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-gold-500/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-navy-800 border-2 border-gold-500 flex items-center justify-center text-gold-400 overflow-hidden font-bold text-xl flex-shrink-0">
                        @if (!empty($this->data['photo_path']))
                            <img src="{{ Storage::disk('public')->url($this->data['photo_path']) }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($this->data['full_name'] ?? 'T', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white leading-tight">
                            {{ $this->data['full_name'] ?? 'New Teacher Profile' }}
                        </h3>
                        <p class="text-xs text-gold-400 font-mono mt-1">
                            {{ $this->data['employee_id'] ?? 'Generating ID...' }}
                        </p>
                        <span class="inline-block mt-2 px-2.5 py-0.5 text-[10px] uppercase font-extrabold tracking-wider bg-gold-500/20 text-gold-300 rounded-full border border-gold-500/30">
                            {{ ucfirst($this->data['staff_category'] ?? 'Teaching') }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-navy-200 border-t border-navy-800/80 pt-4">
                    <div class="flex justify-between">
                        <span class="text-navy-400">Designation:</span>
                        <span class="font-semibold text-white">{{ $this->data['designation'] ?? 'Not set' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-navy-400">Appointment Status:</span>
                        <span class="font-semibold text-gold-300 uppercase">{{ ucfirst($this->data['appointment_status'] ?? 'Probation') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-navy-400">Employment Type:</span>
                        <span class="font-semibold text-white">{{ ucfirst(str_replace('_', ' ', $this->data['employment_type'] ?? 'Full-Time')) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-navy-400">Joining Date:</span>
                        <span class="font-semibold text-white">{{ $this->data['joining_date'] ?? now()->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>

            <!-- Profile Completion Progress -->
            <div class="bg-white dark:bg-gray-900 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 dark:text-gray-300">Profile Readiness</h4>
                    <span class="text-xs font-bold text-navy-900 dark:text-gold-400">Step {{ $currentStep }} of 5</span>
                </div>

                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Completion Indicator</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                            @php
                                $compPercent = 20 * max(1, $currentStep);
                            @endphp
                            {{ $compPercent }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-gold-500 to-navy-900 h-2 rounded-full transition-all duration-500" style="width: {{ $compPercent }}%"></div>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <h5 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Checklist & Checklist Readiness</h5>
                    <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            CNIC & Contact Identity
                        </li>
                        <li class="flex items-center">
                            @if (!empty($this->data['designation']))
                                <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                            Designation & Campus Assigned
                        </li>
                        <li class="flex items-center">
                            @if (!empty($this->data['gross_salary']))
                                <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                            Remuneration Approved
                        </li>
                        <li class="flex items-center">
                            @if (!empty($this->data['document_cnic']))
                                <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                            CNIC Copy Uploaded
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
