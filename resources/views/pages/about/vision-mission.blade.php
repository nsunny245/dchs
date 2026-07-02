@extends('layouts.app')

@section('title', 'Vision, Mission & Core Values | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">About Our Institution</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Vision, Mission & Values</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Vision & Mission Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Vision Box -->
            <div class="bg-navy-950 text-white rounded-2xl p-10 relative overflow-hidden shadow-xl" data-aos="fade-right">
                <div class="absolute -right-16 -bottom-16 w-48 h-48 bg-gold-500/5 rounded-full pointer-events-none"></div>
                <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display block mb-3">Our Aspiration</span>
                <h2 class="text-2xl font-extrabold font-display mb-6">The Vision Statement</h2>
                <p class="text-navy-100 leading-relaxed text-sm md:text-base">
                    To be the leading allied health sciences educational provider in Punjab, recognized for clinical excellence, state-of-the-art facilities, and producing skilled healthcare professionals who meet international clinical standards and serve communities with dedication.
                </p>
            </div>

            <!-- Mission Box -->
            <div class="bg-brand-surface border border-brand-border rounded-2xl p-10 relative overflow-hidden shadow-sm" data-aos="fade-left">
                <span class="text-gold-700 font-bold text-xs uppercase tracking-widest font-display block mb-3">Our Objective</span>
                <h2 class="text-2xl font-extrabold font-display text-navy-900 mb-6">The Mission Statement</h2>
                <p class="text-navy-900 leading-relaxed text-sm md:text-base">
                    To provide accessible, affordable, and high-quality medical training to students from diverse backgrounds. We achieve this by establishing advanced labs, partnering with top healthcare institutions, and adhering to strict national regulatory guidelines.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Grid -->
<section class="py-24 bg-brand-surface border-t border-brand-border font-body">
    <div class="container mx-auto px-6">
        <x-section-heading eyebrow="THE DANIYAL WAY" heading="Our Core Values" align="center" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Value 1: Islamic-Values-Rooted Education -->
            <div class="bg-white rounded-xl p-8 border border-brand-border shadow-sm hover:shadow-md transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 bg-gold-50 rounded-xl flex items-center justify-center text-gold-700 mb-6 border border-gold-100">
                    <!-- Arabic/Islamic Star SVG -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.173-.439.782-.439.954 0l1.838 4.673a.5.5 0 00.375.334l4.908.775c.485.077.68.665.342.998l-3.5 3.454a.5.5 0 00-.142.441l.889 5.013c.09.507-.442.894-.889.65L12 17.5a.5.5 0 00-.472 0l-4.402 2.338c-.447.237-.978-.149-.889-.649l.889-5.014a.5.5 0 00-.142-.441l-3.5-3.454c-.339-.333-.144-.921.342-.998l4.908-.775a.5.5 0 00.375-.334l1.838-4.673z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-navy-900 font-display mb-3">Islamic-Values-Rooted</h3>
                <p class="text-navy-400 text-xs leading-relaxed">
                    We foster ethical healthcare training rooted in Islamic values of empathy, integrity, service, and respect for human life.
                </p>
            </div>

            <!-- Value 2: Clinical Excellence -->
            <div class="bg-white rounded-xl p-8 border border-brand-border shadow-sm hover:shadow-md transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="w-12 h-12 bg-gold-50 rounded-xl flex items-center justify-center text-gold-700 mb-6 border border-gold-100">
                    <!-- Stethoscope SVG -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-3.75-3.75m11.25-3.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-navy-900 font-display mb-3">Clinical Excellence</h3>
                <p class="text-navy-400 text-xs leading-relaxed">
                    We adhere to standard operating procedures, clean laboratory diagnostic practices, and updated clinical workflows.
                </p>
            </div>

            <!-- Value 3: Accessibility Across Punjab -->
            <div class="bg-white rounded-xl p-8 border border-brand-border shadow-sm hover:shadow-md transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="w-12 h-12 bg-gold-50 rounded-xl flex items-center justify-center text-gold-700 mb-6 border border-gold-100">
                    <!-- Punjab/Map/Globe SVG -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-navy-900 font-display mb-3">Geographic Accessibility</h3>
                <p class="text-navy-400 text-xs leading-relaxed">
                    Establishing state-of-the-art campuses across Punjab (Okara, Sahiwal, Depalpur, Chichawatni) to keep education close to home.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
