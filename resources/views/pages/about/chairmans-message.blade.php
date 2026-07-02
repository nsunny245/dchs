@extends('layouts.app')

@section('title', "Chairman's Message | Daniyal Group of Colleges")

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">About Our Institution</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Chairman's Message</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Content Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            <!-- Left Side: Chairman Image & Card -->
            <div class="w-full lg:w-4/12 flex flex-col items-center">
                <x-image-glow 
                    src="{{ asset('images/chairman.png') }}" 
                    alt="Dr. Naveed Abbas - Chairman"
                    class="max-w-xs mb-6" />
                
                <div class="text-center">
                    <h3 class="text-xl font-bold text-navy-900 font-display">Dr. Naveed Abbas</h3>
                    <p class="text-gold-700 text-xs font-semibold uppercase tracking-wider mt-1">Chairman & Founder</p>
                    <p class="text-navy-400 text-xs mt-1">Daniyal Group of Colleges</p>
                </div>
            </div>

            <!-- Right Side: Full Message -->
            <div class="w-full lg:w-8/12 text-navy-900 space-y-6 leading-relaxed text-sm md:text-base">
                <div class="relative bg-brand-surface border-l-4 border-gold-500 rounded-r-xl p-8 mb-6 shadow-sm">
                    <h4 class="font-display font-bold text-navy-900 text-lg mb-2">Dear Prospective Students & Parents,</h4>
                    <p class="italic font-semibold text-navy-900/95">
                        "Welcome to Daniyal Group of Colleges. We believe that accessibility to quality healthcare education is key to empowering local communities and raising standard of medical services across Pakistan."
                    </p>
                </div>

                <p>
                    Our mission is to establish institutions that deliver world-class training in allied health sciences. The healthcare landscape is growing rapidly, creating a critical demand for certified medical lab technologists, nurses, community midwives, dental technicians, and pharmacy professionals.
                </p>

                <p>
                    At Daniyal Group of Colleges, we provide students with hands-on practice, cutting-edge clinical laboratories, and direct simulation exposure to medical environments. Our campuses in Okara, Sahiwal, Depalpur, and Chichawatni serve as centers of educational excellence, supporting students from diverse social and financial backgrounds.
                </p>

                <p>
                    We have curated our academic programs to strictly match the Punjab Medical Faculty (PMF) and Pharmacy Council standards, securing certifications that have strong local and international value. Our job placement cell works round-the-clock to link graduates with hospitals, healthcare centers, and pharmacies across Punjab.
                </p>

                <p class="pt-6 font-display font-bold text-navy-900">
                    Warm Regards,<br>
                    <span class="text-gold-700 text-lg font-tagline uppercase tracking-widest mt-1 block">Dr. Naveed Abbas</span>
                    <span class="text-xs font-normal text-navy-400 block font-body">Chairman, Daniyal Group of Colleges</span>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
