@extends('layouts.app')

@section('title', 'Leadership Team | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">About Our Institution</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Our Leadership Team</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Leadership Grid -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <!-- [PLACEHOLDER — confirm leadership roster with client] -->
        <x-section-heading eyebrow="guiding our vision" heading="Administrative & Academic Leadership" align="center" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-5xl mx-auto">
            <!-- Dr. Naveed Abbas -->
            <div class="flex flex-col items-center text-center" data-aos="fade-up">
                <x-image-glow 
                    src="{{ asset('images/chairman.png') }}" 
                    alt="Dr. Naveed Abbas - Chairman"
                    class="max-w-xs mb-6" />
                <h3 class="text-lg font-bold text-navy-900 font-display">Dr. Naveed Abbas</h3>
                <p class="text-gold-700 text-xs font-semibold uppercase tracking-wider mt-1">Chairman & Founder</p>
                <p class="text-navy-400 text-xs mt-1">Daniyal Group of Colleges</p>
            </div>

            <!-- Placeholder Director 1 -->
            <div class="flex flex-col items-center text-center opacity-90" data-aos="fade-up" data-aos-delay="100">
                <div class="w-full max-w-xs aspect-square bg-navy-50 rounded-xl mb-6 border border-brand-border flex items-center justify-center text-navy-200">
                    <!-- Placeholder SVG user icon -->
                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 01-7.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <!-- HTML Comment for Client Placeholder -->
                <!-- <!-- [PLACEHOLDER — confirm academic director name with client] --> -->
                <h3 class="text-lg font-bold text-navy-900 font-display">Prof. Dr. M. Iqbal</h3>
                <p class="text-gold-700 text-xs font-semibold uppercase tracking-wider mt-1">Director Academics</p>
                <p class="text-navy-400 text-xs mt-1">DCHS Allied Health Programs</p>
            </div>

            <!-- Placeholder Registrar -->
            <div class="flex flex-col items-center text-center opacity-90" data-aos="fade-up" data-aos-delay="200">
                <div class="w-full max-w-xs aspect-square bg-navy-50 rounded-xl mb-6 border border-brand-border flex items-center justify-center text-navy-200">
                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 01-7.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <!-- <!-- [PLACEHOLDER — confirm registrar name with client] --> -->
                <h3 class="text-lg font-bold text-navy-900 font-display">Muhammad Daniyal</h3>
                <p class="text-gold-700 text-xs font-semibold uppercase tracking-wider mt-1">Registrar & Admin</p>
                <p class="text-navy-400 text-xs mt-1">Daniyal Group of Colleges</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
