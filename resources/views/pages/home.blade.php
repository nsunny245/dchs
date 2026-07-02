@extends('layouts.app')

@section('title', 'Daniyal Group of Colleges | Where Success Is a Tradition')

@section('content')
<!-- Hero Section -->
<section class="relative h-[800px] flex items-center overflow-hidden">
    <!-- Immersive Background Image with Scrim -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1920" alt="Daniyal Group of Colleges Student Lab" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-950 via-navy-900/80 to-transparent"></div>
    </div>

    <!-- Crest Logo Watermark in Background -->
    <div class="absolute right-10 top-1/2 transform -translate-y-1/2 z-0 hidden lg:block">
        <x-logo-mark size="hero-watermark" />
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-2xl">
            <!-- Frosted Glass Text Overlay with Gold Accent Left Border -->
            <div class="backdrop-blur-md bg-navy-950/75 border-l-4 border-gold-500 border-t border-r border-b border-white/10 rounded-r-2xl rounded-l-none p-10 md:p-12 shadow-2xl" 
                 data-aos="fade-right">
                
                <div class="inline-flex items-center bg-gold-500/20 border border-gold-500/40 rounded-full px-4 py-1.5 mb-6">
                    <span class="w-2 h-2 bg-gold-500 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-gold-500 text-[10px] font-bold uppercase tracking-widest font-display">Admissions Open 2026</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-white font-display leading-tight mb-4">
                    Excellence in <br><span class="text-gold-500 relative inline-block">
                        Health Sciences
                        <!-- Stylized gold stroke underline -->
                        <span class="absolute bottom-0 left-0 w-full h-[4px] bg-gold-500/60 rounded-full"></span>
                    </span>
                </h1>
                
                <p class="text-sm md:text-base text-navy-100 mb-8 leading-relaxed font-body">
                    Daniyal Group of Colleges is Pakistan's premier institution for allied healthcare education. We train the next generation of healthcare professionals with modern medical labs and experienced clinical faculty.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <x-button variant="accent" :href="route('admissions.apply')" class="px-8 py-3.5 shadow-lg">
                        Apply Online
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.89 13.5H3m14.89 0l-4.89-4.89m4.89 4.89L13 18.39" />
                        </svg>
                    </x-button>
                    <x-button variant="outline" :href="route('campuses')" class="px-8 py-3.5 border-white/30 text-white hover:bg-white/10">
                        Explore Campuses
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Animated Stats Counter Bar -->
<section class="bg-navy-950 border-t-4 border-gold-500 py-10 relative z-20 shadow-md">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-navy-800/50">
            <x-stat-counter target="5000" suffix="+" label="Registered Students" delay="100" />
            <x-stat-counter target="4" label="State-of-the-Art Campuses" delay="200" />
            <x-stat-counter target="95" suffix="%" label="Success & Employment Rate" delay="300" />
        </div>
    </div>
</section>

<!-- Chairman's Editorial Section -->
<section class="py-24 bg-white relative overflow-hidden font-body">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <!-- Left Column: Double Border Image with Radial Glow -->
            <div class="w-full lg:w-5/12 flex justify-center" data-aos="fade-right">
                <x-image-glow 
                    src="{{ asset('images/chairman.png') }}" 
                    alt="Dr. Naveed Abbas - Chairman"
                    badgeText="Dr. Naveed Abbas"
                    class="max-w-sm" />
            </div>

            <!-- Right Column: Editorial Message -->
            <div class="w-full lg:w-7/12" data-aos="fade-left">
                <div class="flex items-center mb-4">
                    <div class="h-px bg-gold-500 w-10"></div>
                    <span class="text-gold-700 font-bold ml-4 text-xs tracking-widest font-display uppercase">CHAIRMAN'S MESSAGE</span>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-extrabold text-navy-900 font-display mb-6">
                    Shaping Healthcare Leaders
                </h2>

                <div class="relative bg-brand-surface border-l-4 border-gold-500 rounded-r-xl p-8 mb-6 shadow-sm">
                    <!-- Stylized SVG quotation marks -->
                    <svg class="absolute top-4 right-4 w-12 h-12 text-navy-100/50 fill-current" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 10.137-9.57v3.457c-2.828 0-4.381 1.708-4.381 4.742h5.187v8.762h-10.943zm-13.174 0v-7.391c0-5.704 3.748-9.57 10.154-9.57v3.457c-2.828 0-4.381 1.708-4.381 4.742h5.199v8.762h-10.972z" />
                    </svg>
                    <blockquote class="text-navy-900 leading-relaxed text-base italic relative z-10">
                        "At Daniyal Group of Colleges, we provide exceptional education in health sciences with state-of-the-art facilities and experienced faculty. With campuses across Punjab, we make quality medical education accessible to students from diverse backgrounds."
                    </blockquote>
                </div>

                <p class="text-navy-400 text-sm leading-relaxed mb-8">
                    We invite you to join us and embark on a rewarding journey of professional growth, clinical excellence, and service in the healthcare industry. Our dedicated career placement offices work with leading medical laboratories, clinics, and hospitals to secure job opportunities for our graduates.
                </p>

                <x-button variant="outline" :href="route('about.chairmans-message')" class="border-navy-900 text-navy-900 hover:bg-navy-50">
                    Read Full Message
                </x-button>
            </div>
        </div>
    </div>
</section>

<!-- Programs Showcase Section -->
<section class="py-24 bg-brand-surface relative border-t border-brand-border">
    <div class="container mx-auto px-6">
        <x-section-heading 
            eyebrow="OUR ACADEMIC catalog" 
            heading="Allied Health Sciences Programs" 
            align="center" />
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($programs as $index => $program)
                <x-program-card :program="$program" :index="$index" />
            @empty
                <div class="col-span-3 text-center py-12 text-navy-400 italic">
                    No programs are registered at this time.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Campuses section: Four Locations, One Standard -->
<section class="py-24 bg-white relative border-t border-brand-border">
    <div class="container mx-auto px-6">
        <x-section-heading 
            eyebrow="GEOGRAPHIC FOOTPRINT" 
            heading="Four Locations, One Standard" 
            align="center" />
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($campuses as $index => $campus)
                <x-campus-card :campus="$campus" :index="$index" />
            @empty
                <div class="col-span-4 text-center py-12 text-navy-400 italic">
                    No campuses are configured at this time.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner 
    title="Start Your Healthcare Career"
    description="Join thousands of successful graduates who built high-impact healthcare careers at Daniyal Group of Colleges. Secure your seat today."
    buttonText="Apply Online Now" />
@endsection
