@extends('layouts.app')

@section('title', 'Admissions 2026 | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Join DCHS</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Admissions Process 2026</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Admissions Timeline / Process Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <x-section-heading eyebrow="How to get admitted" heading="Step-by-Step Application Guide" align="center" />

        <!-- Numbered Timeline Component -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-5xl mx-auto relative">
            
            <!-- Step 1 -->
            <div class="flex flex-col items-center text-center relative" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-bold text-lg rounded-full border-2 border-gold-500 shadow-md mb-4 z-10">
                    1
                </div>
                <h3 class="text-sm font-bold text-navy-900 font-display uppercase tracking-wider mb-2">Apply Online</h3>
                <p class="text-navy-400 text-xs leading-relaxed max-w-xs">
                    Fill out our online application form with CNIC, contact, and matriculation score details.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center text-center relative" data-aos="fade-up" data-aos-delay="200">
                <div class="w-12 h-12 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-bold text-lg rounded-full border-2 border-gold-500 shadow-md mb-4 z-10">
                    2
                </div>
                <h3 class="text-sm font-bold text-navy-900 font-display uppercase tracking-wider mb-2">Visit Campus</h3>
                <p class="text-navy-400 text-xs leading-relaxed max-w-xs">
                    Visit your preferred college campus location along with original educational transcripts and CNIC/B-Form.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center text-center relative" data-aos="fade-up" data-aos-delay="300">
                <div class="w-12 h-12 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-bold text-lg rounded-full border-2 border-gold-500 shadow-md mb-4 z-10">
                    3
                </div>
                <h3 class="text-sm font-bold text-navy-900 font-display uppercase tracking-wider mb-2">Document Audit</h3>
                <p class="text-navy-400 text-xs leading-relaxed max-w-xs">
                    The admissions registrar audits your matric marks sheets against PMF eligibility criteria.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="flex flex-col items-center text-center relative" data-aos="fade-up" data-aos-delay="400">
                <div class="w-12 h-12 bg-navy-900 text-gold-500 flex items-center justify-center font-display font-bold text-lg rounded-full border-2 border-gold-500 shadow-md mb-4 z-10">
                    4
                </div>
                <h3 class="text-sm font-bold text-navy-900 font-display uppercase tracking-wider mb-2">Secure Seat</h3>
                <p class="text-navy-400 text-xs leading-relaxed max-w-xs">
                    Deposit the enrollment fee structure to lock your registration for the academic session 2026.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Fee Structure & Important Dates -->
<section class="py-24 bg-brand-surface border-t border-brand-border font-body">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-5xl mx-auto">
            
            <!-- Fee Placeholder info -->
            <div class="bg-white border border-brand-border rounded-xl p-8 shadow-sm">
                <!-- [PLACEHOLDER — confirm fee structure with client] -->
                <h3 class="text-lg font-bold text-navy-900 font-display mb-4 uppercase tracking-wider">Fee Structure</h3>
                <div class="h-0.5 w-8 bg-gold-500 mb-6"></div>
                <p class="text-navy-400 text-xs leading-relaxed mb-6">
                    Daniyal Group of Colleges keeps fees structures affordable. Since actual numbers differ per program (e.g. Pharmacy Tech vs. CSSD) and campus location, please request the exact fee breakdown from your advisor.
                </p>
                <div class="p-4 bg-brand-surface rounded-lg border border-brand-border text-xs text-navy-900 font-semibold space-y-3">
                    <div class="flex justify-between">
                        <span>Admission Registration Fee</span>
                        <span class="text-gold-700 font-bold">[PLACEHOLDER - Confirm Fee]</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Monthly Tuition Installment</span>
                        <span class="text-gold-700 font-bold">[PLACEHOLDER - Confirm Fee]</span>
                    </div>
                </div>
            </div>

            <!-- Dates info -->
            <div class="bg-white border border-brand-border rounded-xl p-8 shadow-sm">
                <h3 class="text-lg font-bold text-navy-900 font-display mb-4 uppercase tracking-wider">Key Admissions Dates</h3>
                <div class="h-0.5 w-8 bg-gold-500 mb-6"></div>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="px-3 py-1 bg-gold-50 border border-gold-100 text-gold-700 font-display font-bold text-xs rounded text-center">
                            July<br>01
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-navy-900">Online Registrations Open</h4>
                            <p class="text-[11px] text-navy-400 mt-0.5">Submit applications through the public DCHS portal.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="px-3 py-1 bg-gold-50 border border-gold-100 text-gold-700 font-display font-bold text-xs rounded text-center">
                            Sep<br>15
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-navy-900">Application Deadline</h4>
                            <p class="text-[11px] text-navy-400 mt-0.5">Final day to present physical transcripts to your registrar.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="px-3 py-1 bg-gold-50 border border-gold-100 text-gold-700 font-display font-bold text-xs rounded text-center">
                            Oct<br>01
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-navy-900">Classes Commencement</h4>
                            <p class="text-[11px] text-navy-400 mt-0.5">Academic sessions officially start at all 4 college campuses.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- FAQs Section -->
<section class="py-24 bg-white border-t border-brand-border font-body">
    <div class="container mx-auto px-6">
        <x-section-heading eyebrow="HAVE QUESTIONS?" heading="Admissions FAQs" align="center" />

        <div class="max-w-3xl mx-auto space-y-4">
            <x-faq-accordion 
                question="What qualification is required for Pharmacy Technician?" 
                answer="To enroll in the Pharmacy Technician program, you must have completed Matric with Science (Biology) with at least 45% marks. Arts students are not eligible under Pharmacy Council rules." 
                index="1" />
            
            <x-faq-accordion 
                question="Is there an age limit for Lady Health Visitor (LHV)?" 
                answer="Yes, the LHV diploma is exclusively open to female applicants, and the age limit is 15-30 years matching Punjab Medical Faculty regulations." 
                index="2" />
            
            <x-faq-accordion 
                question="Are installment plans available for fees payment?" 
                answer="Yes, DCHS supports flexible monthly fee structures to ensure healthcare education is financially accessible for everyone." 
                index="3" />

            <x-faq-accordion 
                question="How do I verify if DCHS is officially accredited?" 
                answer="Daniyal Group of Colleges is fully accredited and affiliated with Punjab Medical Faculty (PMF) and the Pharmacy Council of Pakistan. All diploma certificates are registered and recognized." 
                index="4" />
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
