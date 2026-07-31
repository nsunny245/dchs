@php
    use App\Models\Campus;
    use App\Models\Course;
    use App\Models\AcademicSession;
    use App\Models\Staff;
    use App\Models\Room;
    use App\Models\TimetablePeriod;
    use App\Models\Timetable;
    use App\Models\TimetableSlot;

    $campuses = Campus::all();
    $courses = Course::all();
    $sessions = AcademicSession::all();
    $teachers = Staff::where(function ($q) {
        $q->where('status', 'active')->orWhereNull('status');
    })->get();
    if ($teachers->count() === 0) {
        $teachers = Staff::all();
    }
    $rooms = Room::where('campus_id', $campus_id)->get();
    if ($rooms->count() === 0) {
        $rooms = Room::all();
    }

    $periods = TimetablePeriod::where('is_active', true)->orderBy('sort_order')->get();
    if ($periods->count() === 0) {
        $periods = collect([
            (object)['id' => 1, 'name' => 'Period 1', 'start_time' => '08:30:00', 'end_time' => '09:15:00', 'is_break' => false],
            (object)['id' => 2, 'name' => 'Period 2', 'start_time' => '09:15:00', 'end_time' => '10:00:00', 'is_break' => false],
            (object)['id' => 3, 'name' => 'Period 3', 'start_time' => '10:00:00', 'end_time' => '10:45:00', 'is_break' => false],
            (object)['id' => 4, 'name' => 'Morning Break', 'start_time' => '10:45:00', 'end_time' => '11:15:00', 'is_break' => true],
            (object)['id' => 5, 'name' => 'Period 4', 'start_time' => '11:15:00', 'end_time' => '12:00:00', 'is_break' => false],
            (object)['id' => 6, 'name' => 'Period 5', 'start_time' => '12:00:00', 'end_time' => '12:45:00', 'is_break' => false],
            (object)['id' => 7, 'name' => 'Period 6', 'start_time' => '12:45:00', 'end_time' => '13:30:00', 'is_break' => false],
        ]);
    }

    $timetableModel = $recordId ? Timetable::with(['timetableSubjects.subject', 'slots.teacher', 'slots.room'])->find($recordId) : null;
    $slots = $timetableModel ? $timetableModel->slots : collect();
    $timetableSubjects = $timetableModel ? $timetableModel->timetableSubjects : collect();
@endphp

<x-filament-panels::page>
    <style>
        .dgc-wizard-wrap select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.25em 1.25em !important;
            padding-right: 2.5rem !important;
        }
        .dgc-wizard-steps-grid {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            width: 100% !important;
        }
        .dgc-wizard-step-item {
            flex: 1 !important;
            min-width: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 10px 8px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
        }
        .dgc-form-grid-3 {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 20px !important;
        }
        .dgc-span-2 {
            grid-column: span 2 / span 2 !important;
        }
        @media (max-width: 768px) {
            .dgc-form-grid-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            }
            .dgc-span-2 {
                grid-column: span 1 / span 1 !important;
            }
            .dgc-wizard-steps-grid {
                flex-wrap: wrap !important;
            }
        }
    </style>

    <div class="dgc-wizard-wrap space-y-6">
        <!-- Brand Header & Steps Progress Bar -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="DGC Logo" class="h-12 w-auto object-contain" />
                    <div>
                        <h2 class="text-xl font-bold font-display text-navy-900">Program Timetable Workspace</h2>
                        <p class="text-xs text-slate-500">Daniyal Group of Colleges — Visual Multi-Subject Scheduling System</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full uppercase tracking-wider 
                        {{ $status === 'published' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        ● {{ ucfirst($status) }}
                    </span>
                    @if($recordId)
                        <a href="{{ route('pdf.timetable', $recordId) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </a>
                    @endif
                </div>
            </div>

            <!-- Steps Progress Bar -->
            <div class="pt-5">
                <div class="dgc-wizard-steps-grid">
                    <button wire:click="goToStep(1)" class="dgc-wizard-step-item {{ $currentStep === 1 ? 'bg-navy-900 text-white font-bold shadow' : ($currentStep > 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-400') }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $currentStep === 1 ? 'bg-gold-500 text-navy-950 font-bold' : ($currentStep > 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200') }}">1</span>
                        <span>1. Setup</span>
                    </button>
                    <button wire:click="goToStep(2)" class="dgc-wizard-step-item {{ $currentStep === 2 ? 'bg-navy-900 text-white font-bold shadow' : ($currentStep > 2 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-400') }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $currentStep === 2 ? 'bg-gold-500 text-navy-950 font-bold' : ($currentStep > 2 ? 'bg-emerald-600 text-white' : 'bg-slate-200') }}">2</span>
                        <span>2. Subjects</span>
                    </button>
                    <button wire:click="goToStep(3)" class="dgc-wizard-step-item {{ $currentStep === 3 ? 'bg-navy-900 text-white font-bold shadow' : ($currentStep > 3 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-400') }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $currentStep === 3 ? 'bg-gold-500 text-navy-950 font-bold' : ($currentStep > 3 ? 'bg-emerald-600 text-white' : 'bg-slate-200') }}">3</span>
                        <span>3. Visual Grid</span>
                    </button>
                    <button wire:click="goToStep(4)" class="dgc-wizard-step-item {{ $currentStep === 4 ? 'bg-navy-900 text-white font-bold shadow' : ($currentStep > 4 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-400') }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $currentStep === 4 ? 'bg-gold-500 text-navy-950 font-bold' : ($currentStep > 4 ? 'bg-emerald-600 text-white' : 'bg-slate-200') }}">4</span>
                        <span>4. Preview</span>
                    </button>
                    <button wire:click="goToStep(5)" class="dgc-wizard-step-item {{ $currentStep === 5 ? 'bg-navy-900 text-white font-bold shadow' : 'bg-slate-50 text-slate-400' }}">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $currentStep === 5 ? 'bg-gold-500 text-navy-950 font-bold' : 'bg-slate-200' }}">5</span>
                        <span>5. Publish</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 1: SETUP FORM -->
        @if($currentStep === 1)
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">
                <h3 class="text-base font-bold font-display text-navy-900 border-b pb-3">Step 1: Program & Academic Context Setup</h3>
                
                <div class="dgc-form-grid-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Campus <span class="text-red-500">*</span></label>
                        <select wire:model.live="campus_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900">
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->city }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Program / Course <span class="text-red-500">*</span></label>
                        <select wire:model.live="course_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Academic Session</label>
                        <select wire:model.live="academic_session_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900">
                            @foreach($sessions as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Batch / Intake</label>
                        <input type="text" wire:model.blur="batch_name" placeholder="e.g. Batch 2025-2027" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Semester / Year <span class="text-red-500">*</span></label>
                        <select wire:model.live="semester_name" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900">
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Semester 1">Semester 1</option>
                            <option value="Semester 2">Semester 2</option>
                            <option value="Semester 3">Semester 3</option>
                            <option value="Semester 4">Semester 4</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Section / Group <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.blur="section_name" placeholder="e.g. Section A" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>

                    <div class="dgc-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Timetable Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.blur="timetable_title" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Shift</label>
                        <select wire:model.live="shift" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900">
                            <option value="morning">Morning Shift</option>
                            <option value="evening">Evening Shift</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Effective From <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.blur="effective_from" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Effective To (Optional)</label>
                        <input type="date" wire:model.blur="effective_to" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Default Period Duration (Mins)</label>
                        <input type="number" wire:model.blur="default_period_duration" class="w-full text-xs rounded-lg border-slate-300 focus:border-navy-900 focus:ring-navy-900" />
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <button type="button" wire:click="nextToSubjects" class="px-5 py-2.5 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-lg shadow flex items-center gap-2">
                        <span>Next: Load & Select Subjects</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 2: LOAD & SELECT SUBJECTS -->
        @if($currentStep === 2)
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b pb-3">
                    <div>
                        <h3 class="text-base font-bold font-display text-navy-900">Step 2: Program Subjects & Teacher Assignments</h3>
                        <p class="text-xs text-slate-500">Auto-loaded subjects for <strong>{{ Course::find($course_id)?->name }}</strong> ({{ $semester_name }})</p>
                    </div>
                    <span class="text-xs bg-navy-50 text-navy-900 px-3 py-1 rounded-full font-semibold border border-navy-200">
                        {{ count($selectedSubjectIds) }} Subjects Selected
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 uppercase font-semibold">
                                <th class="p-3 w-10 text-center">Include</th>
                                <th class="p-3">Code</th>
                                <th class="p-3">Subject Name</th>
                                <th class="p-3 text-center">Credit Hours</th>
                                <th class="p-3 text-center">Required Periods / Week</th>
                                <th class="p-3">Class Type</th>
                                <th class="p-3">Default Teacher</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($availableSubjects as $sub)
                                @php $subId = $sub['id']; @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" wire:model.live="selectedSubjectIds" value="{{ $subId }}" class="rounded text-navy-900 focus:ring-navy-900" />
                                    </td>
                                    <td class="p-3 font-mono font-bold text-navy-900">{{ $sub['code'] ?? 'SUB' }}</td>
                                    <td class="p-3 font-semibold text-slate-800">{{ $sub['name'] }}</td>
                                    <td class="p-3 text-center font-semibold">{{ $sub['credit_hours'] ?? 3 }}</td>
                                    <td class="p-3 text-center">
                                        <input type="number" wire:model.blur="subjectPeriods.{{ $subId }}" min="1" max="15" class="w-16 text-center text-xs rounded border-slate-300 focus:ring-navy-900" />
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border">
                                            {{ $sub['default_class_type'] ?? 'Theory' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <select wire:model.live="subjectTeachers.{{ $subId }}" class="w-full text-xs rounded border-slate-300 focus:ring-navy-900">
                                            <option value="">-- Select Teacher --</option>
                                            @foreach($teachers as $t)
                                                <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->designation ?? 'Faculty' }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-500 italic">No subjects configured for this course. You can proceed to add slots manually.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between pt-4 border-t">
                    <button type="button" wire:click="goToStep(1)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                        Back to Setup
                    </button>
                    <button type="button" wire:click="nextToBuilder" class="px-5 py-2.5 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-lg shadow flex items-center gap-2">
                        <span>Next: Open Visual Grid Builder</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- STEP 3: VISUAL WEEKLY GRID BUILDER -->
        @if($currentStep === 3)
            <div class="grid grid-cols-12 gap-6">
                <!-- Collapsible Subject Panel (3 Columns) -->
                <div class="col-span-12 lg:col-span-3 space-y-4">
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-center justify-between border-b pb-2 mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-navy-900">Subject Requirements</h4>
                            <span class="text-[11px] font-semibold text-slate-500">{{ $timetableSubjects->count() }} Subjects</span>
                        </div>

                        <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                            @forelse($timetableSubjects as $ts)
                                @php
                                    $req = $ts->required_periods_per_week;
                                    $sched = $ts->scheduled_periods;
                                    $rem = max(0, $req - $sched);
                                    $statusColor = $sched >= $req ? 'bg-emerald-500' : ($sched > 0 ? 'bg-amber-500' : 'bg-rose-500');
                                @endphp
                                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-sm transition-all space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-xs font-bold text-navy-900">{{ $ts->subject_code ?? 'SUB' }}</span>
                                        <span class="w-2.5 h-2.5 rounded-full {{ $statusColor }}"></span>
                                    </div>
                                    <h5 class="text-xs font-semibold text-slate-800 line-clamp-1">{{ $ts->subject_name }}</h5>
                                    <p class="text-[11px] text-slate-500 flex items-center justify-between">
                                        <span>Teacher: <strong>{{ $ts->defaultTeacher?->full_name ?? 'Unassigned' }}</strong></span>
                                    </p>
                                    <div class="flex items-center justify-between text-[11px] font-mono pt-1">
                                        <span class="text-slate-600">Req: <strong>{{ $req }}</strong></span>
                                        <span class="text-emerald-700">Sched: <strong>{{ $sched }}</strong></span>
                                        <span class="{{ $rem > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">Rem: <strong>{{ $rem }}</strong></span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic text-center py-4">No subjects synced yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Main Visual Timetable Grid (9 Columns) -->
                <div class="col-span-12 lg:col-span-9 space-y-4">
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b pb-3">
                            <div>
                                <h3 class="text-base font-bold text-navy-900">{{ $timetable_title }}</h3>
                                <p class="text-xs text-slate-500">Click any empty cell to schedule a class. Click scheduled cards to edit.</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" wire:click="goToStep(4)" class="px-4 py-2 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-lg shadow">
                                    Preview & Export
                                </button>
                            </div>
                        </div>

                        <!-- Weekly Grid Table -->
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="w-full text-left text-xs border-collapse min-w-[750px]">
                                <thead>
                                    <tr class="bg-navy-900 text-white text-center font-bold">
                                        <th class="p-3 w-28 border-r border-navy-800">Time / Period</th>
                                        <th class="p-3 border-r border-navy-800">Monday</th>
                                        <th class="p-3 border-r border-navy-800">Tuesday</th>
                                        <th class="p-3 border-r border-navy-800">Wednesday</th>
                                        <th class="p-3 border-r border-navy-800">Thursday</th>
                                        <th class="p-3 border-r border-navy-800">Friday</th>
                                        <th class="p-3">Saturday</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($periods as $p)
                                        @php
                                            $st = date('H:i', strtotime($p->start_time));
                                            $et = date('H:i', strtotime($p->end_time));
                                        @endphp
                                        @if($p->is_break)
                                            <tr class="bg-amber-50 text-amber-900 text-center font-bold">
                                                <td class="p-2 border-r text-[11px] font-mono bg-amber-100/70">{{ $p->name }}<br><span class="text-[10px] text-amber-700 font-normal">{{ $st }} - {{ $et }}</span></td>
                                                <td colspan="6" class="p-2 text-xs uppercase tracking-wider text-amber-800 italic">● {{ $p->name }} Break Period ●</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="p-2.5 border-r font-mono text-[11px] text-center bg-slate-50 font-semibold text-slate-700">
                                                    {{ $p->name }}<br>
                                                    <span class="text-[10px] text-slate-500 font-normal">{{ $st }} - {{ $et }}</span>
                                                </td>

                                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                                    @php
                                                        $cellSlots = $slots->filter(function ($s) use ($day, $st, $et) {
                                                            $sDay = strtolower($s->day_of_week);
                                                            $sSt = date('H:i', strtotime($s->start_time));
                                                            return $sDay === $day && $sSt === $st;
                                                        });
                                                    @endphp
                                                    <td class="p-1.5 border-r vertical-top h-20 w-1/6 transition-all hover:bg-slate-50/80 relative">
                                                        @if($cellSlots->count() > 0)
                                                            @foreach($cellSlots as $slot)
                                                                <div wire:click="editSlot({{ $slot->id }})" class="cursor-pointer p-2 rounded-lg bg-navy-50 border border-navy-200 hover:border-gold-500 hover:shadow-md transition-all space-y-1 group">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="font-bold text-navy-950 text-[11px] line-clamp-1">{{ $slot->subject_name }}</span>
                                                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase {{ $slot->class_type === 'Lab' ? 'bg-purple-100 text-purple-800' : ($slot->class_type === 'Practical' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                                                            {{ substr($slot->class_type ?? 'Theory', 0, 4) }}
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-[10px] text-slate-600 flex items-center gap-1 line-clamp-1">
                                                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                                        <span>{{ $slot->teacher?->full_name ?? 'Faculty' }}</span>
                                                                    </p>
                                                                    @if($slot->room)
                                                                        <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                                            <span>{{ $slot->room->name }}</span>
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <button type="button" wire:click="openAddSlotModal('{{ $day }}', '{{ $st }}', '{{ $et }}')" class="w-full h-full min-h-[60px] rounded border border-dashed border-slate-200 hover:border-navy-400 hover:bg-navy-50/50 flex flex-col items-center justify-center text-slate-400 hover:text-navy-900 transition-all">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                                <span class="text-[9px] mt-0.5">Add Slot</span>
                                                            </button>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- STEP 4: PREVIEW & EXPORT -->
        @if($currentStep === 4)
            <div class="bg-white rounded-xl border border-slate-200 p-8 shadow-sm space-y-6">
                <!-- Printable Institution Header -->
                <div class="flex items-center justify-between border-b pb-6">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="DGC Logo" class="h-16 w-auto object-contain" />
                        <div>
                            <h1 class="text-2xl font-bold font-display text-navy-900 uppercase">Daniyal Group of Colleges</h1>
                            <p class="text-xs font-semibold text-slate-600">{{ Campus::find($campus_id)?->name }} — Academic Timetable</p>
                        </div>
                    </div>
                    <div class="text-right text-xs text-slate-600 space-y-1">
                        <p><strong>Program:</strong> {{ Course::find($course_id)?->name }}</p>
                        <p><strong>Session / Batch:</strong> {{ AcademicSession::find($academic_session_id)?->name }} ({{ $batch_name }})</p>
                        <p><strong>Semester / Section:</strong> {{ $semester_name }} — {{ $section_name }}</p>
                        <p><strong>Effective Date:</strong> {{ $effective_from }}</p>
                    </div>
                </div>

                <!-- Timetable Grid Preview -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse border border-slate-300">
                        <thead>
                            <tr class="bg-navy-900 text-white text-center font-bold">
                                <th class="p-2 border border-navy-800">Time</th>
                                <th class="p-2 border border-navy-800">Monday</th>
                                <th class="p-2 border border-navy-800">Tuesday</th>
                                <th class="p-2 border border-navy-800">Wednesday</th>
                                <th class="p-2 border border-navy-800">Thursday</th>
                                <th class="p-2 border border-navy-800">Friday</th>
                                <th class="p-2 border border-navy-800">Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periods as $p)
                                @php
                                    $st = date('H:i', strtotime($p->start_time));
                                    $et = date('H:i', strtotime($p->end_time));
                                @endphp
                                @if($p->is_break)
                                    <tr class="bg-amber-50 text-center font-bold text-amber-900">
                                        <td class="p-2 border font-mono text-[10px]">{{ $st }}-{{ $et }}</td>
                                        <td colspan="6" class="p-2 uppercase tracking-wider text-[11px]">● {{ $p->name }} ●</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="p-2 border font-mono text-[10px] text-center font-bold bg-slate-50">{{ $st }}-{{ $et }}</td>
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                            @php
                                                $cellSlots = $slots->filter(function ($s) use ($day, $st) {
                                                    return strtolower($s->day_of_week) === $day && date('H:i', strtotime($s->start_time)) === $st;
                                                });
                                            @endphp
                                            <td class="p-2 border text-center vertical-top">
                                                @foreach($cellSlots as $slot)
                                                    <div class="font-bold text-navy-950 text-xs">{{ $slot->subject_name }}</div>
                                                    <div class="text-[10px] text-slate-600">{{ $slot->teacher?->full_name ?? 'Faculty' }}</div>
                                                    @if($slot->room)<div class="text-[9px] text-slate-400">[{{ $slot->room->name }}]</div>@endif
                                                @endforeach
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Signature & Action Bar -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <button type="button" wire:click="goToStep(3)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                        Back to Grid Builder
                    </button>
                    <div class="flex items-center space-x-3">
                        <button type="button" wire:click="saveDraft" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow">
                            Save as Draft
                        </button>
                        <button type="button" wire:click="publishTimetable" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Publish Timetable Now</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- STEP 5: PUBLISHED / CONFIRMATION -->
        @if($currentStep === 5)
            <div class="bg-white rounded-xl border border-slate-200 p-10 shadow-sm text-center max-w-xl mx-auto space-y-6">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-bold font-display text-navy-900">Timetable Ready & Saved</h3>
                    <p class="text-xs text-slate-600">The timetable <strong>{{ $timetable_title }}</strong> has been configured with status <strong>{{ strtoupper($status) }}</strong>.</p>
                </div>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                    @if($recordId)
                        <a href="{{ route('pdf.timetable', $recordId) }}" target="_blank" class="px-5 py-2.5 bg-navy-900 text-white text-xs font-bold rounded-lg shadow hover:bg-navy-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Download PDF Report</span>
                        </a>
                        <button type="button" wire:click="duplicateTimetable" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-200">
                            Duplicate for Section B
                        </button>
                    @endif
                    <a href="{{ \App\Filament\Resources\TimetableResource::getUrl('index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-200">
                        Return to Timetables List
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- SLOT ASSIGNMENT MODAL -->
    @if($isSlotModalOpen)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="bg-navy-900 text-white px-5 py-4 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold font-display uppercase tracking-wider">Assign Class Slot</h4>
                        <p class="text-[11px] text-slate-300 font-mono">{{ ucfirst($modalDay) }} ({{ $modalStartTime }} - {{ $modalEndTime }})</p>
                    </div>
                    <button type="button" wire:click="resetSlotModal" class="text-slate-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Conflict Alerts -->
                    @if(!empty($modalConflictErrors))
                        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
                            <strong class="font-bold flex items-center gap-1">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Conflict Detected:
                            </strong>
                            @foreach($modalConflictErrors as $err)
                                <p class="pl-5 text-[11px]">• {{ $err }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Subject <span class="text-red-500">*</span></label>
                        <select wire:model.live="modalSubjectId" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900">
                            <option value="">-- Select Subject --</option>
                            @foreach($availableSubjects as $s)
                                <option value="{{ $s['id'] }}">{{ $s['code'] }} — {{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned Teacher</label>
                            <select wire:model.live="modalTeacherId" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900">
                                <option value="">-- Select Teacher --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Room / Lab</label>
                            <select wire:model.live="modalRoomId" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900">
                                <option value="">-- Select Room --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} (Cap: {{ $r->capacity }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Class Type</label>
                            <select wire:model.live="modalClassType" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900">
                                <option value="Theory">Theory</option>
                                <option value="Practical">Practical</option>
                                <option value="Lab">Laboratory</option>
                                <option value="Clinical">Clinical</option>
                                <option value="Tutorial">Tutorial</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Day of Week</label>
                            <select wire:model.live="modalDay" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Start Time</label>
                            <input type="time" wire:model.blur="modalStartTime" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">End Time</label>
                            <input type="time" wire:model.blur="modalEndTime" class="w-full text-xs rounded-lg border-slate-300 focus:ring-navy-900" />
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-5 py-3 border-t flex items-center justify-between">
                    <div>
                        @if($editingSlotId)
                            <button type="button" wire:click="deleteSlot({{ $editingSlotId }})" class="text-xs text-red-600 hover:underline font-semibold">
                                Delete Slot
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" wire:click="resetSlotModal" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg">
                            Cancel
                        </button>
                        <button type="button" wire:click="saveSlot" class="px-5 py-2 bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold rounded-lg shadow">
                            Save Class Slot
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>
</x-filament-panels::page>
