@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<section class="relative h-[600px] flex items-center overflow-hidden" data-aos="fade-up">
    <!-- Background Image with Scrim -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1920" alt="Medical Education" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-950/90 via-navy-900/60 to-transparent"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-2xl">
            <!-- Frosted Glass Content Panel -->
            <div class="backdrop-blur-md bg-navy-950/65 border-l-4 border-gold-500 border-t border-r border-b border-white/10 rounded-r-2xl rounded-l-none p-8 md:p-10 shadow-2xl" data-aos="fade-right" data-aos-delay="100">
                <div class="inline-flex items-center bg-gold-500/20 border border-gold-500/40 rounded-full px-4 py-1.5 mb-6">
                    <span class="w-2 h-2 bg-gold-500 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-gold-500 text-xs font-bold uppercase tracking-wider font-display">Admissions Open 2026</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-extrabold text-white font-display leading-tight mb-4">
                    Excellence in <span class="text-gold-500">Health Sciences</span>
                </h1>
                
                <p class="text-sm md:text-base text-navy-100 mb-8 leading-relaxed font-body">
                    Daniyal Group of Colleges is Pakistan's premier institution for medical education. We shape the next generation of allied healthcare professionals with state-of-the-art labs and expert faculty.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admissions.apply') }}" class="btn btn-accent rounded-full px-8 py-3.5 shadow-lg hover:shadow-gold-500/20 transform hover:-translate-y-0.5 transition-all duration-300 font-display text-sm tracking-wider uppercase">
                        Apply Now
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a href="{{ route('campuses') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white border border-white/20 px-8 py-3.5 rounded-full font-bold text-sm transition-all duration-300 transform hover:-translate-y-0.5 font-display tracking-wider uppercase">
                        Explore Campuses
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Unified Institutional Stats Bar -->
<section class="bg-navy-900 border-t-4 border-gold-500 py-8 relative z-20 shadow-md">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-navy-700/50">
            <div class="py-4 md:py-0" data-aos="fade-up" data-aos-delay="100">
                <div class="text-4xl font-extrabold text-gold-500 font-display mb-1">5,000+</div>
                <div class="text-navy-200 text-xs font-body uppercase tracking-wider font-semibold">Registered Students</div>
            </div>
            <div class="py-4 md:py-0" data-aos="fade-up" data-aos-delay="200">
                <div class="text-4xl font-extrabold text-gold-500 font-display mb-1">{{ $campuses->count() }}</div>
                <div class="text-navy-200 text-xs font-body uppercase tracking-wider font-semibold">State-of-the-Art Campuses</div>
            </div>
            <div class="py-4 md:py-0" data-aos="fade-up" data-aos-delay="300">
                <div class="text-4xl font-extrabold text-gold-500 font-display mb-1">95%</div>
                <div class="text-navy-200 text-xs font-body uppercase tracking-wider font-semibold">Success & Employment Rate</div>
            </div>
        </div>
    </div>
</section>

<!-- Chairman Message -->
<section class="py-20 bg-white relative overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <!-- Left Side: Image with Double Gold Frame -->
            <div class="lg:w-5/12 flex justify-center" data-aos="fade-right">
                <div class="double-border-container max-w-sm w-full">
                    <div class="double-border-backdrop"></div>
                    <img src="{{ asset('images/chairman.png') }}" alt="Dr. Naveed Abbas" class="relative rounded-lg shadow-lg w-full z-10 object-cover border border-navy-100 hover:scale-[1.01] transition-transform duration-300">
                </div>
            </div>
            
            <!-- Right Side: Content with Stylized Quote -->
            <div class="lg:w-7/12" data-aos="fade-left">
                <div class="flex items-center mb-4">
                    <div class="h-px bg-gold-500 w-10"></div>
                    <span class="text-gold-700 font-bold ml-4 text-xs tracking-widest font-display uppercase">CHAIRMAN'S MESSAGE</span>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-extrabold text-navy-900 font-display mb-6">
                    Shaping Healthcare Leaders
                </h2>
                
                <div class="relative bg-brand-surface border-l-4 border-gold-500 rounded-r-xl p-6 mb-6 shadow-sm">
                    <!-- SVG Quotation Mark -->
                    <svg class="absolute top-4 right-4 w-12 h-12 text-navy-100/50 fill-current" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 10.137-9.57v3.457c-2.828 0-4.381 1.708-4.381 4.742h5.187v8.762h-10.943zm-13.174 0v-7.391c0-5.704 3.748-9.57 10.154-9.57v3.457c-2.828 0-4.381 1.708-4.381 4.742h5.199v8.762h-10.972z" />
                    </svg>
                    <p class="text-navy-900 leading-relaxed text-base font-body italic relative z-10">
                        "At Daniyal Group of Colleges, we provide exceptional education in health sciences with state-of-the-art facilities and experienced faculty. With campuses across Punjab, we make quality medical education accessible to students from diverse backgrounds."
                    </p>
                </div>
                
                <p class="text-navy-400 leading-relaxed mb-8 text-sm font-body">
                    We invite you to join us and embark on a rewarding journey of professional growth, service, and excellence in the healthcare ecosystem.
                </p>
                
                <div class="flex items-center border-t border-brand-border pt-6">
                    <div>
                        <div class="font-bold text-navy-900 font-display text-lg">Dr. Naveed Abbas</div>
                        <div class="text-gold-700 text-xs font-semibold font-body uppercase tracking-wider">Chairman, Daniyal Group of Colleges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section (Dynamic from DB) -->
<section class="py-24 bg-brand-surface relative">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
            <div class="inline-block bg-navy-900 text-white px-4 py-1 rounded-full text-xs font-bold mb-3 tracking-widest font-display">OUR SPECIALIZED COURSES</div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-navy-900 font-display mb-3">Allied Health Sciences</h2>
            <div class="h-1 w-16 bg-gold-500 mx-auto mb-4 rounded-full"></div>
            <p class="text-navy-400 text-sm max-w-xl mx-auto font-body">Choose from our widely-accredited diploma programs designed for modern medical careers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($programs as $index => $program)
            @php
                $code = strtoupper($program->code);
            @endphp
            <div class="card-program relative overflow-hidden flex flex-col justify-between" 
                 data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                <div>
                    <!-- Header with Dynamic Allied Health SVG Icon -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 bg-gold-50 rounded-xl flex items-center justify-center text-gold-700 shadow-sm border border-gold-100/50">
                            @if(in_array($code, ['LHV', 'CMW']))
                                <!-- Nursing / Maternity Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v6m-3-3h6" />
                                </svg>
                            @elseif($code === 'CNA')
                                <!-- Stethoscope / Assistant Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-3.75-3.75m11.25-3.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($code === 'PT')
                                <!-- Physiotherapy / Wellness Activity Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            @elseif($code === 'MLT')
                                <!-- Medical Lab Technology / Flask Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif(in_array($code, ['OT', 'OTT', 'AT']))
                                <!-- Anesthesia / Operation Theater Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif($code === 'DT')
                                <!-- Dental Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2-3-3-3-3s-1.5 1-3 1-3-1-3-1-3 1-3 1-1.5-1-3-1-3 1-3 1v6.75c0 3.75 3 4.5 6 6 3-1.5 6-2.25 6-6V8.25z" />
                                </svg>
                            @elseif($code === 'CSSD')
                                <!-- Sterile Supply / Shield Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                                </svg>
                            @else
                                <!-- Generic Medical Cross Icon -->
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold bg-navy-50 text-navy-900 border border-navy-100 px-2.5 py-1 rounded-full font-body">{{ $program->duration }}</span>
                    </div>

                    <h3 class="text-xl font-bold text-navy-900 font-display mb-3 leading-snug group-hover:text-gold-700 transition-colors">{{ $program->name }}</h3>
                    <p class="text-navy-400 text-xs font-body leading-relaxed mb-6">{{ $program->description ?? 'Comprehensive professional healthcare training curriculum matching the PMF standards.' }}</p>
                </div>
                
                <div class="pt-4 border-t border-brand-border mt-auto">
                    <a href="{{ route('courses.show', $program->code) }}" class="inline-flex items-center text-gold-700 font-bold text-xs hover:text-navy-900 transition-colors uppercase tracking-wider font-display group/link">
                        Explore Curriculum
                        <svg class="w-3.5 h-3.5 ml-1.5 transform group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-navy-400 font-body">
                <p>No programs available at the moment. Please check back later.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Campuses Section (Dynamic from DB) -->
<section class="py-24 bg-navy-900 text-white relative">
    <!-- Decorative background elements -->
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-gold-500/5 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16" data-aos="fade-up">
            <div class="inline-block bg-gold-500 text-navy-900 px-4 py-1 rounded-full text-xs font-bold mb-3 tracking-widest font-display">OUR GEOGRAPHIC REACH</div>
            <h2 class="text-3xl md:text-4xl font-extrabold font-display mb-3">Four Locations, One Standard</h2>
            <div class="h-1 w-16 bg-gold-500 mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($campuses as $index => $campus)
            @php
                $campusImages = [
                    0 => 'https://images.unsplash.com/photo-1562774053-701939374585?w=600', // Okara
                    1 => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600', // Depalpur
                    2 => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600', // Chichawatni
                    3 => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600', // Sahiwal
                ];
                $imgUrl = $campusImages[$index % 4] ?? 'https://images.unsplash.com/photo-1562774053-701939374585?w=600';
            @endphp
            <div class="relative overflow-hidden rounded-xl group border border-navy-700/50 shadow-md hover:shadow-lg transition-all duration-300" data-aos="zoom-in" data-aos-delay="{{ ($index % 4) * 100 }}">
                <div class="h-56 relative overflow-hidden">
                    <img src="{{ $imgUrl }}" alt="{{ $campus->city }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-900/40 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-5 transform group-hover:translate-y-[-2px] transition-transform duration-300">
                    <h3 class="text-xl font-bold mb-1 font-display text-white">{{ $campus->city }}</h3>
                    <div class="h-0.5 w-6 bg-gold-500 group-hover:w-16 transition-all duration-300 mb-2"></div>
                    <p class="text-gold-400 text-xs font-body tracking-wider uppercase font-semibold">{{ $campus->name }}</p>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center py-12 text-navy-200 font-body">
                <p>No campuses configured yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Premium CTA Banner Section -->
<section class="py-20 bg-white relative overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto bg-navy-900 border-2 border-gold-500 rounded-2xl p-10 md:p-12 text-center relative overflow-hidden shadow-2xl">
            <!-- Background watermark -->
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-gold-500/5 rounded-full pointer-events-none"></div>
            <div class="absolute -left-16 -top-16 w-64 h-64 bg-white/5 rounded-full pointer-events-none"></div>
            
            <div class="relative z-10 max-w-2xl mx-auto">
                <div class="inline-block bg-gold-500/10 border border-gold-500/30 text-gold-500 px-4 py-1 rounded-full text-xs font-bold mb-6 tracking-widest font-display uppercase">
                    Admissions Process 2026
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white font-display mb-4">Start Your Healthcare Career</h2>
                <p class="text-navy-200 mb-8 max-w-xl mx-auto text-sm md:text-base font-body leading-relaxed">
                    Join thousands of successful graduates who built high-impact healthcare careers at Daniyal Group of Colleges. Secure your seat today.
                </p>
                <a href="{{ route('admissions.apply') }}" class="btn btn-accent rounded-full px-10 py-4 shadow-xl hover:shadow-gold-500/25 transform hover:-translate-y-0.5 transition-all duration-300 font-display text-sm uppercase tracking-widest">
                    Apply Online Now
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
