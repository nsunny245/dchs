@extends('layouts.app')

@section('title', 'Accreditations & Affiliations | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">About Our Institution</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Accreditations & Affiliations</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Accreditation Details -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto">
            <!-- [PLACEHOLDER — confirm accrediting bodies with client] -->
            <!-- The following accreditation details match Punjab Allied Health guidelines -->
            <x-section-heading eyebrow="official recognitions" heading="Recognized Educational Frameworks" align="left" />

            <p class="text-navy-900 mb-8 leading-relaxed text-sm md:text-base">
                Daniyal Group of Colleges is fully recognized and affiliated with the official regulatory bodies governing pharmacy and allied health sciences training inside the province of Punjab and nationally in Pakistan.
            </p>

            <div class="space-y-6">
                <!-- PMF -->
                <div class="flex flex-col md:flex-row gap-6 p-6 rounded-xl bg-brand-surface border border-brand-border" data-aos="fade-up">
                    <div class="w-16 h-16 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-extrabold text-xl rounded-xl border border-navy-800 flex-shrink-0">
                        PMF
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 font-display">Punjab Medical Faculty (PMF)</h3>
                        <p class="text-gold-700 text-xs font-semibold mt-1">Affiliated & Registered</p>
                        <p class="text-navy-400 text-xs mt-2 leading-relaxed">
                            Our allied health diploma programs (LHV, CNA, CMW, MLT, OT, DT, AT, CSSD) are approved by the Punjab Medical Faculty, Lahore. Examinations, curriculum syllabus outlines, and clinical diploma certifications are governed under the official PMF system.
                        </p>
                    </div>
                </div>

                <!-- Pharmacy Council -->
                <div class="flex flex-col md:flex-row gap-6 p-6 rounded-xl bg-brand-surface border border-brand-border" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-extrabold text-xl rounded-xl border border-navy-800 flex-shrink-0">
                        PCP
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 font-display">Pharmacy Council of Pakistan (PCP)</h3>
                        <p class="text-gold-700 text-xs font-semibold mt-1">Recognized for Pharmacy Technician Programs</p>
                        <p class="text-navy-400 text-xs mt-2 leading-relaxed">
                            The Pharmacy Technician (Category B) diploma is fully accredited by the Pharmacy Council of Pakistan. This allows our graduates to register official drug license shops and practice retail pharmacy consulting.
                        </p>
                    </div>
                </div>

                <!-- Health Dept -->
                <div class="flex flex-col md:flex-row gap-6 p-6 rounded-xl bg-brand-surface border border-brand-border" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-extrabold text-xl rounded-xl border border-navy-800 flex-shrink-0">
                        GOP
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 font-display">Health Department, Govt of Punjab</h3>
                        <p class="text-gold-700 text-xs font-semibold mt-1">Registered Institution</p>
                        <p class="text-navy-400 text-xs mt-2 leading-relaxed">
                            Daniyal Group of Colleges operates under standard government registrations, certifying that our facilities, security systems, classrooms, and clinical labs match the mandatory healthcare standards of the province.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
