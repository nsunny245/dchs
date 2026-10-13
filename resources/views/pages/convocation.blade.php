@extends('layouts.app')

@section('title', 'Convocation Registration')
@section('meta_description', 'Submit student and attendee information for the Daniyal Group of Colleges convocation.')

@section('content')
<section class="relative overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #ebb45a 0, transparent 28%), radial-gradient(circle at 80% 80%, #2c6fad 0, transparent 30%);"></div>
    <div class="container relative z-10 mx-auto grid gap-10 px-6 py-16 lg:grid-cols-[1.1fr_.9fr] lg:items-center lg:py-24">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-gold-500/40 bg-gold-500/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[.22em] text-gold-400">
                Convocation Registration
            </span>
            <h1 class="mt-6 max-w-3xl font-display text-4xl font-extrabold leading-tight text-white md:text-6xl">Celebrate the milestone. Register your attendee.</h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-navy-200 md:text-base">Submit the student and attendee details required by the college. The registration will be delivered directly to the selected campus administration.</p>
        </div>
        <div class="grid grid-cols-3 gap-3" aria-hidden="true">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center backdrop-blur"><strong class="block text-2xl text-gold-400">01</strong><span class="mt-2 block text-xs text-navy-200">Choose campus</span></div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center backdrop-blur"><strong class="block text-2xl text-gold-400">02</strong><span class="mt-2 block text-xs text-navy-200">Add details</span></div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center backdrop-blur"><strong class="block text-2xl text-gold-400">03</strong><span class="mt-2 block text-xs text-navy-200">Submit entry</span></div>
        </div>
    </div>
</section>

<section class="bg-brand-surface py-16 md:py-24">
    <div class="container mx-auto px-6">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(260px,.58fr)_minmax(0,1.42fr)] lg:items-start">
            <aside class="h-fit rounded-2xl bg-navy-900 p-7 text-white shadow-xl lg:sticky lg:top-32">
                <p class="text-[11px] font-bold uppercase tracking-[.2em] text-gold-400">Before you submit</p>
                <h2 class="mt-3 font-display text-2xl font-bold">Keep the official details ready</h2>
                <div class="mt-7 space-y-5 text-sm text-navy-100">
                    <div class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gold-500 font-bold text-navy-950">1</span><p>Select the campus and academic session connected to the student.</p></div>
                    <div class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gold-500 font-bold text-navy-950">2</span><p>Enter the attendee name exactly as shown on their CNIC.</p></div>
                    <div class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-gold-500 font-bold text-navy-950">3</span><p>Use the CNIC format <strong class="text-white">00000-0000000-0</strong>.</p></div>
                </div>
                <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-4 text-xs leading-6 text-navy-200">All fields marked with an asterisk are required. The selected campus can review the entry in its administration dashboard.</div>
            </aside>

            <div class="overflow-hidden rounded-2xl border border-brand-border bg-white shadow-xl">
                <div class="h-2 bg-gradient-to-r from-navy-900 via-gold-500 to-navy-900"></div>
                <div class="p-6 md:p-10">
                    <div class="mb-8">
                        <p class="text-[11px] font-bold uppercase tracking-[.18em] text-gold-700">Student information collector</p>
                        <h2 class="mt-2 font-display text-2xl font-extrabold text-navy-900 md:text-3xl">Convocation Entry Form</h2>
                        <p class="mt-2 text-sm text-navy-400">Complete the academic and attendee information below.</p>
                    </div>

                    @if(session('success'))
                        <div class="mb-7 rounded-xl border border-success/20 bg-success/10 p-5 text-sm font-semibold text-success" role="status">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="mb-7 rounded-xl border border-danger/20 bg-danger/10 p-5 text-sm text-danger" role="alert">
                            <p class="font-bold">Please correct the following:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('convocation.store') }}"
                          x-data="{
                              campus: '{{ old('campus_id') }}',
                              session: '{{ old('academic_session_id') }}',
                              sessions: {{ Illuminate\Support\Js::from($sessions->map(fn($session) => ['id' => $session->id, 'campus_id' => $session->campus_id, 'name' => $session->name, 'active' => $session->is_active])) }},
                              get availableSessions() { return this.sessions.filter(item => item.campus_id === null || String(item.campus_id) === String(this.campus)); },
                              formatCnic(event) { let value = event.target.value.replace(/\D/g, '').slice(0, 13); event.target.value = value.length > 12 ? value.slice(0,5) + '-' + value.slice(5,12) + '-' + value.slice(12) : value.length > 5 ? value.slice(0,5) + '-' + value.slice(5) : value; }
                          }"
                          class="space-y-8">
                        @csrf

                        <fieldset>
                            <legend class="mb-5 flex w-full items-center gap-3 border-b border-brand-border pb-3 font-display text-sm font-bold uppercase tracking-wider text-navy-900"><span class="grid h-7 w-7 place-items-center rounded-full bg-navy-900 text-xs text-white">1</span>Academic Information</legend>
                            <div class="grid gap-5 md:grid-cols-2">
                                <label class="block text-xs font-semibold text-navy-900">Campus <span class="text-danger">*</span>
                                    <select name="campus_id" x-model="campus" x-on:change="session = ''" required class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                        <option value="">Select campus</option>
                                        @foreach($campuses as $campus)<option value="{{ $campus->id }}">{{ $campus->name }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="block text-xs font-semibold text-navy-900">Session <span class="text-danger">*</span>
                                    <select name="academic_session_id" x-model="session" required x-bind:disabled="!campus" class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition disabled:cursor-not-allowed disabled:opacity-50 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                        <option value="">Select session</option>
                                        <template x-for="item in availableSessions" :key="item.id"><option :value="item.id" x-text="item.name + (item.active ? ' · Active' : '')"></option></template>
                                    </select>
                                </label>
                                <label class="block text-xs font-semibold text-navy-900 md:col-span-2">Program <span class="text-danger">*</span>
                                    <select name="course_id" required class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                        <option value="">Select program</option>
                                        @foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }} ({{ $course->code }})</option>@endforeach
                                    </select>
                                </label>
                                <label class="block text-xs font-semibold text-navy-900 md:col-span-2">Student Name <span class="text-danger">*</span>
                                    <input type="text" name="student_name" value="{{ old('student_name') }}" required maxlength="255" autocomplete="name" placeholder="Enter the student's full name" class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                </label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="mb-5 flex w-full items-center gap-3 border-b border-brand-border pb-3 font-display text-sm font-bold uppercase tracking-wider text-navy-900"><span class="grid h-7 w-7 place-items-center rounded-full bg-gold-500 text-xs text-navy-950">2</span>Attendee Information</legend>
                            <div class="grid gap-5 md:grid-cols-2">
                                <label class="block text-xs font-semibold text-navy-900 md:col-span-2">Attendee Name <span class="text-danger">*</span>
                                    <input type="text" name="attendee_name" value="{{ old('attendee_name') }}" required maxlength="255" autocomplete="name" placeholder="Name as shown on CNIC" class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                </label>
                                <label class="block text-xs font-semibold text-navy-900">Relation with Student <span class="text-danger">*</span>
                                    <select name="attendee_relation" required class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                        <option value="">Select relation</option>
                                        @foreach(\App\Filament\Resources\ConvocationRegistrationResource::relationOptions() as $value => $label)<option value="{{ $value }}" @selected(old('attendee_relation') === $value)>{{ $label }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="block text-xs font-semibold text-navy-900">Attendee CNIC Number <span class="text-danger">*</span>
                                    <input type="text" name="attendee_cnic" value="{{ old('attendee_cnic') }}" x-on:input="formatCnic($event)" required inputmode="numeric" maxlength="15" pattern="[0-9]{5}-[0-9]{7}-[0-9]" placeholder="00000-0000000-0" class="mt-2 w-full rounded-xl border border-brand-border bg-brand-surface/40 px-4 py-3.5 outline-none transition focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20">
                                </label>
                            </div>
                        </fieldset>

                        <div class="rounded-xl border border-gold-500/25 bg-gold-500/5 p-4 text-xs leading-6 text-navy-500">By submitting this form, you confirm that the student and attendee information is accurate and may be used by the college for convocation administration.</div>
                        <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-full bg-gold-500 px-6 py-4 text-sm font-extrabold uppercase tracking-wider text-navy-950 shadow-lg transition hover:-translate-y-0.5 hover:bg-gold-600 focus:outline-none focus:ring-4 focus:ring-gold-500/30">
                            Submit Convocation Entry
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
