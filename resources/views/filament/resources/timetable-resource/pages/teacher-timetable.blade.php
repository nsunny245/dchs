<x-filament-panels::page>
    <div class="teacher-workspace" x-data="{ dirty: $wire.entangle('dirty') }"
         x-on:input="dirty = true" x-on:change="dirty = true"
         x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">
        <div class="tt-toolbar">
            <div>
                <strong>Weekly teacher schedule</strong>
                <p>Edit any teacher, subject, class/batch, time or heading. Each schedule has its own days and periods.</p>
                <span role="status" x-text="dirty ? 'Unsaved changes' : '{{ $recordId ? ucfirst($status).' · Saved changes' : 'New timetable · Not saved' }}'" class="tt-save-status"></span>
            </div>
            <div class="tt-actions">
                <x-filament::button color="gray" wire:click="$toggle('preview')">{{ $preview ? 'Continue editing' : 'Preview' }}</x-filament::button>
                <x-filament::button color="gray" wire:click="save" wire:loading.attr="disabled">Save draft</x-filament::button>
                <x-filament::button wire:click="save(true)" wire:loading.attr="disabled">Save &amp; publish</x-filament::button>
                @if($recordId && !$dirty)
                    <x-filament::button tag="a" color="gray" :href="route('pdf.timetable', $recordId)" target="_blank">Print / PDF</x-filament::button>
                @endif
            </div>
        </div>
        @if($errors->any())
            <div class="tt-errors" role="alert" tabindex="-1">
                <strong>Please correct these items:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if(!$recordId)
            <p class="tt-note">Starting from the supplied college timetable. Review the sample teachers and classes before publishing. Tuesday-only entries are marked explicitly.</p>
        @endif
        @if(!$preview)
            <section class="tt-settings" aria-label="Timetable details">
                <label>Timetable name<input wire:model="timetableTitle" maxlength="191" required></label>
                <label>Campus<select wire:model="campusId" required><option value="">Choose campus</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}">{{ $campus->name }}</option>@endforeach</select></label>
                <label>Filing program<select wire:model="courseId" required><option value="">Choose program</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select><small>Classes in the grid can cover multiple programs.</small></label>
                <label>Effective date<input type="date" wire:model="effectiveFrom" required></label>
                <label>Institution heading<input wire:model="sheet.heading" maxlength="191" required></label>
                <label>Print subtitle<input wire:model="sheet.subtitle" maxlength="191" required></label>
            </section>
        @else
            <div class="tt-print-heading"><h2>{{ $sheet['heading'] }}</h2><p>{{ $sheet['subtitle'] }}</p><p>{{ $timetableTitle }} · Effective {{ $effectiveFrom }}</p></div>
        @endif

        @foreach($sheet['groups'] as $g => $group)
            <section class="tt-schedule" wire:key="schedule-{{ $g }}-{{ count($sheet['groups']) }}" aria-label="{{ $group['label'] }}">
                <div class="tt-schedule-heading">
                    @if(!$preview)
                        <label class="tt-group-name">Schedule heading<input wire:model="sheet.groups.{{ $g }}.label" aria-label="Schedule {{ $g + 1 }} heading" maxlength="100"></label>
                        <fieldset class="tt-days"><legend>Days using this schedule</legend>
                            @foreach($days as $day)<label><input type="checkbox" wire:model.live="sheet.groups.{{ $g }}.days" value="{{ $day }}">{{ ucfirst(substr($day, 0, 3)) }}</label>@endforeach
                        </fieldset>
                        <button class="tt-remove" wire:click="removeGroup({{ $g }})" wire:confirm="Remove this schedule and all its classes?" @disabled(count($sheet['groups']) === 1)>Remove schedule</button>
                    @else
                        <h3>{{ $group['label'] }}</h3><p>{{ implode(', ', array_map('ucfirst', $group['days'])) }}</p>
                    @endif
                </div>
                <div class="tt-scroll" tabindex="0" role="region" aria-label="{{ $group['label'] }} editable timetable; scroll horizontally for more periods">
                    <table class="tt-grid">
                        <caption class="sr-only">{{ $group['label'] }} — teachers by period</caption>
                        <thead><tr><th scope="col" class="tt-teacher">Teacher name / designation</th>
                            @foreach($group['periods'] as $p => $period)
                                <th scope="col" class="{{ $period['is_break'] ? 'tt-break' : '' }}">
                                    @if($preview)
                                        {{ $period['label'] }}<br>{{ $period['start'] }}–{{ $period['end'] }}
                                    @else
                                        <input wire:model="sheet.groups.{{ $g }}.periods.{{ $p }}.label" aria-label="Schedule {{ $g + 1 }} period {{ $p + 1 }} label" maxlength="50">
                                        <div class="tt-time"><input type="time" wire:model="sheet.groups.{{ $g }}.periods.{{ $p }}.start" aria-label="Period {{ $p + 1 }} start"><span>to</span><input type="time" wire:model="sheet.groups.{{ $g }}.periods.{{ $p }}.end" aria-label="Period {{ $p + 1 }} end"></div>
                                        <label class="tt-break-toggle"><input type="checkbox" wire:model.live="sheet.groups.{{ $g }}.periods.{{ $p }}.is_break">Break</label>
                                        <button class="tt-remove" wire:click="removePeriod({{ $g }}, {{ $p }})" wire:confirm="Remove this period and all classes in its column?" @disabled(count($group['periods']) === 1)>Remove period</button>
                                    @endif
                                </th>
                            @endforeach
                        </tr></thead>
                        <tbody>
                            @foreach($group['teachers'] as $r => $teacher)
                                <tr wire:key="teacher-{{ $g }}-{{ $r }}-{{ count($group['teachers']) }}-{{ count($group['periods']) }}">
                                    <th scope="row" class="tt-teacher">
                                        @if($preview){{ $teacher['name'] }}@else
                                            <textarea wire:model="sheet.groups.{{ $g }}.teachers.{{ $r }}.name" aria-label="Teacher {{ $r + 1 }} name and designation in {{ $group['label'] }}" rows="3" maxlength="191"></textarea>
                                            <div class="tt-row-actions">
                                                <button wire:click="moveTeacher({{ $g }}, {{ $r }}, -1)" aria-label="Move teacher {{ $r + 1 }} up" @disabled($r === 0)>↑</button>
                                                <button wire:click="moveTeacher({{ $g }}, {{ $r }}, 1)" aria-label="Move teacher {{ $r + 1 }} down" @disabled($r === count($group['teachers']) - 1)>↓</button>
                                                <button class="tt-remove" wire:click="removeTeacher({{ $g }}, {{ $r }})" wire:confirm="Remove this teacher row and its classes?" @disabled(count($group['teachers']) === 1)>Remove</button>
                                            </div>
                                        @endif
                                    </th>
                                    @foreach($group['periods'] as $p => $period)
                                        <td class="{{ $period['is_break'] ? 'tt-break' : '' }}">
                                            @if($period['is_break'] && blank($teacher['cells'][$p]['text']))
                                                <span class="tt-break-label">{{ $period['label'] }}</span>
                                            @elseif($preview)
                                                <div class="tt-cell-copy">{{ $teacher['cells'][$p]['text'] }}</div>
                                                @if($teacher['cells'][$p]['days'])<small>{{ implode(', ', array_map('ucfirst', $teacher['cells'][$p]['days'])) }} only</small>@endif
                                            @else
                                                <textarea wire:model="sheet.groups.{{ $g }}.teachers.{{ $r }}.cells.{{ $p }}.text" rows="4" maxlength="500" placeholder="Subject, class / batch…" aria-label="{{ $teacher['name'] ?: 'Teacher '.($r + 1) }}, {{ $group['label'] }}, {{ $period['start'] }} class"></textarea>
                                                <details class="tt-cell-days"><summary>Specific days{{ $teacher['cells'][$p]['days'] ? ' ('.count($teacher['cells'][$p]['days']).')' : '' }}</summary>
                                                    <small>Leave unchecked to use all schedule days.</small>
                                                    @foreach($group['days'] as $day)<label><input type="checkbox" wire:model="sheet.groups.{{ $g }}.teachers.{{ $r }}.cells.{{ $p }}.days" value="{{ $day }}">{{ ucfirst($day) }}</label>@endforeach
                                                </details>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(!$preview)<div class="tt-actions tt-schedule-footer"><x-filament::button color="gray" wire:click="addTeacher({{ $g }})">Add teacher</x-filament::button><x-filament::button color="gray" wire:click="addPeriod({{ $g }})">Add period</x-filament::button></div>@endif
            </section>
        @endforeach
        @if(!$preview)
            <div class="tt-actions"><x-filament::button color="gray" wire:click="addGroup">Add day schedule</x-filament::button></div>
            <label class="tt-notes">Timetable notes<textarea wire:model="notes" rows="3" maxlength="5000"></textarea></label>
        @elseif($notes)<p class="tt-cell-copy">{{ $notes }}</p>@endif
        <p class="tt-note">The timetable checks overlapping classes for the same teacher name within this sheet. Keep teacher names consistent across schedules.</p>
    </div>
</x-filament-panels::page>
