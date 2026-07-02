@extends('layouts.app')

@section('title', $course->name . ' (' . $course->code . ') | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center space-x-3">
                <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Program Details</span>
                <span class="bg-gold-500 text-navy-900 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $course->code }}</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">{{ $course->name }}</h1>
        </div>
        <x-button variant="accent" :href="route('admissions.apply') . '?program=' . $course->code" class="px-8 py-3.5 shadow-lg">
            Apply For This Course
        </x-button>
    </div>
</section>

<!-- Details Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            
            <!-- Left Side: Curriculum, Career, Info -->
            <div class="w-full lg:w-8/12 space-y-12">
                
                <!-- Description -->
                <div>
                    <h2 class="text-2xl font-bold text-navy-900 font-display mb-4">Program Overview</h2>
                    <p class="text-navy-400 leading-relaxed text-sm md:text-base">
                        {{ $course->description ?? 'Comprehensive professional healthcare training curriculum matching the PMF standards.' }}
                        Students will undergo intensive classroom training alongside practical clinical sessions in our affiliated hospitals. This course provides key foundation blocks in anatomy, biochemistry, clinical practice, and diagnostic procedures.
                    </p>
                </div>

                <!-- Eligibility & Duration -->
                <div class="border border-brand-border rounded-xl bg-brand-surface p-8">
                    <h3 class="text-lg font-bold text-navy-900 font-display mb-6">Program Standards</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <span class="block text-[10px] font-bold text-gold-700 tracking-wider uppercase font-display mb-2">Academic Duration</span>
                            <p class="text-sm font-semibold text-navy-900">
                                {{ $course->duration_months >= 12 ? ($course->duration_months / 12) . ' Years' : $course->duration_months . ' Months' }}
                                (Full Time)
                            </p>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gold-700 tracking-wider uppercase font-display mb-2">Eligibility Criteria</span>
                            <p class="text-sm text-navy-900 leading-relaxed">
                                {{ $course->eligibility ?? 'Matric Science with Physics, Chemistry & Biology with minimum 45% marks.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Curriculum Highlights -->
                <div>
                    <h3 class="text-lg font-bold text-navy-900 font-display mb-4">Curriculum Highlights</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <span class="w-5 h-5 rounded-full bg-gold-50 border border-gold-100 flex items-center justify-center text-gold-700 mr-3 mt-0.5 flex-shrink-0">✓</span>
                            <div>
                                <h4 class="text-sm font-bold text-navy-900 font-display">Foundation Sciences</h4>
                                <p class="text-navy-400 text-xs mt-1">Study of anatomy, physiology, hygiene, and microbiology.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="w-5 h-5 rounded-full bg-gold-50 border border-gold-100 flex items-center justify-center text-gold-700 mr-3 mt-0.5 flex-shrink-0">✓</span>
                            <div>
                                <h4 class="text-sm font-bold text-navy-900 font-display">Clinical Instrumentation</h4>
                                <p class="text-navy-400 text-xs mt-1">Hands-on calibration and management of modern diagnostic medical tools.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="w-5 h-5 rounded-full bg-gold-50 border border-gold-100 flex items-center justify-center text-gold-700 mr-3 mt-0.5 flex-shrink-0">✓</span>
                            <div>
                                <h4 class="text-sm font-bold text-navy-900 font-display">Hospital Internship</h4>
                                <p class="text-navy-400 text-xs mt-1">Practical clinical internship rotations at affiliated medical centers in Punjab.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Career Prospects -->
                <div>
                    <h3 class="text-lg font-bold text-navy-900 font-display mb-4">Career Prospects</h3>
                    <p class="text-navy-400 leading-relaxed text-sm mb-6">
                        Graduates of this program have extensive career tracks in both government and private health departments:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-navy-900">
                        <div class="p-3 bg-brand-surface border border-brand-border rounded-lg">Clinical Lab / Ward Supervisor</div>
                        <div class="p-3 bg-brand-surface border border-brand-border rounded-lg">Community Healthcare Officer</div>
                        <div class="p-3 bg-brand-surface border border-brand-border rounded-lg">Private Clinic Assistant</div>
                        <div class="p-3 bg-brand-surface border border-brand-border rounded-lg">Technician at Government District HQ Hospitals</div>
                    </div>
                </div>

            </div>

            <!-- Right Side: Apply Info card -->
            <div class="w-full lg:w-4/12 bg-brand-surface border border-brand-border rounded-2xl p-8 sticky top-28">
                <h3 class="text-lg font-bold text-navy-900 font-display mb-4">Admissions Guide</h3>
                <p class="text-navy-400 text-xs leading-relaxed mb-6">
                    Ready to take the next step? Applications for Session 2026 are currently active. Verify the eligibility requirements before filling out the registration form.
                </p>

                <div class="space-y-4 mb-8 text-xs text-navy-950 font-semibold">
                    <div class="flex justify-between border-b border-brand-border pb-2.5">
                        <span>Intake Period</span>
                        <span class="text-gold-700">Open (Fall 2026)</span>
                    </div>
                    <div class="flex justify-between border-b border-brand-border pb-2.5">
                        <span>Affiliation Body</span>
                        <span class="text-gold-700">PMF / Pharmacy Council</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Required Education</span>
                        <span class="text-gold-700">Matric (Science/Arts)</span>
                    </div>
                </div>

                <x-button variant="accent" :href="route('admissions.apply') . '?program=' . $course->code" class="w-full py-4 text-center">
                    Apply Online Now
                </x-button>
            </div>
            
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
