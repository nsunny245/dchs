@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<section class="relative h-[550px] flex items-center overflow-hidden" data-aos="fade-up">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1920" alt="Medical Education" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-900/95 via-navy-900/90 to-navy-900/70"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-2xl">
            <div class="inline-flex items-center bg-gold-500/20 border border-gold-500/50 rounded-full px-4 py-1.5 mb-4 backdrop-blur-sm">
                <span class="w-2 h-2 bg-gold-500 rounded-full mr-2 animate-pulse"></span>
                <span class="text-gold-500 text-xs font-bold">Admissions Open 2026</span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-white font-display leading-tight mb-4" data-aos="fade-up" data-aos-delay="100">
                Excellence in <span class="text-gold-500">Health Sciences</span>
            </h1>
            
            <p class="text-base text-gray-200 mb-6 leading-relaxed font-body" data-aos="fade-up" data-aos-delay="200">
                Pakistan's premier institution for medical education. Shaping healthcare professionals with world-class facilities.
            </p>
            
            <div class="flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="300">
                <a href="{{ route('admissions.apply') }}" class="btn btn-accent rounded-full text-sm hover:shadow-lg">
                    Apply Now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('campuses') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white border border-white/30 px-6 py-3 rounded-full font-bold text-sm transition">
                    Explore Campuses
                </a>
            </div>

            <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-white/20" data-aos="fade-up" data-aos-delay="400">
                <div>
                    <div class="text-3xl font-extrabold text-gold-500 font-display">5000+</div>
                    <div class="text-gray-300 text-xs font-body">Students</div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-gold-500 font-display">{{ $campuses->count() }}</div>
                    <div class="text-gray-300 text-xs font-body">Campuses</div>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-gold-500 font-display">95%</div>
                    <div class="text-gray-300 text-xs font-body">Success Rate</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chairman Message -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center gap-8">
            <div class="lg:w-1/2" data-aos="fade-right">
                <div class="relative">
                    <img src="{{ asset('images/chairman.png') }}" alt="Dr. Naveed Abbas" class="relative rounded-xl shadow-xl w-full">
                </div>
            </div>
            
            <div class="lg:w-1/2" data-aos="fade-left">
                <div class="flex items-center mb-3">
                    <div class="h-px bg-gold-500 w-8"></div>
                    <span class="text-gold-700 font-bold ml-3 text-xs tracking-wider font-display">CHAIRMAN'S MESSAGE</span>
                </div>
                
                <h2 class="text-3xl font-extrabold text-navy-900 font-display mb-4">
                    Shaping Healthcare Leaders
                </h2>
                
                <p class="text-navy-400 leading-relaxed mb-4 text-sm font-body">
                    At Daniyal Group of Colleges, we provide exceptional education in health sciences with state-of-the-art facilities and experienced faculty.
                </p>
                
                <p class="text-navy-400 leading-relaxed mb-6 text-sm font-body">
                    With campuses across Punjab, we make quality medical education accessible to students from diverse backgrounds.
                </p>
                
                <div class="flex items-center">
                    <div>
                        <div class="font-bold text-navy-900 font-display">Dr. Naveed Abbas</div>
                        <div class="text-gold-700 text-xs font-semibold font-body">Chairman, Daniyal Group of Colleges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Programs Section (Dynamic from DB) -->
<section class="py-16 bg-brand-surface">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-block bg-navy-900 text-white px-4 py-1 rounded-full text-xs font-bold mb-3 tracking-wider font-display">OUR PROGRAMS</div>
            <h2 class="text-3xl font-extrabold text-navy-900 font-display mb-3">Choose Your Path in Healthcare</h2>
            <p class="text-navy-400 text-sm max-w-xl mx-auto font-body">{{ $programs->count() }} specialized programs designed for modern healthcare</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($programs as $index => $program)
            <div class="group bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-brand-border hover:border-gold-500" 
                 data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-navy-900 font-display">{{ $program->name }}</h3>
                    <span class="text-xs font-bold bg-gold-500 text-navy-900 px-3 py-1 rounded-full font-body">{{ $program->duration }}</span>
                </div>
                <p class="text-navy-400 text-xs mb-4 font-body leading-relaxed">{{ $program->description ?? 'Comprehensive healthcare training program' }}</p>
                <a href="{{ route('courses.show', $program->code) }}" class="inline-flex items-center text-gold-700 font-semibold text-sm hover:text-navy-900 transition font-body">
                    Learn More
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-gray-500">
                <p>No programs available at the moment. Please check back later.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Campuses Section (Dynamic from DB) -->
<section class="py-16 bg-navy-900 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-block bg-gold-500 text-navy-900 px-4 py-1 rounded-full text-xs font-bold mb-3 tracking-wider font-display">OUR CAMPUSES</div>
            <h2 class="text-3xl font-extrabold font-display mb-3">Four Locations, One Standard</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($campuses as $index => $campus)
            <div class="relative overflow-hidden rounded-xl group" data-aos="zoom-in" data-aos-delay="{{ ($index % 4) * 100 }}">
                <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=400" alt="{{ $campus->city }}" class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-900/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <h3 class="text-lg font-bold mb-1 font-display">{{ $campus->city }}</h3>
                    <p class="text-gold-500 text-xs font-body">{{ $campus->name }}</p>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center py-12 text-gray-300 font-body">
                <p>No campuses configured yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gold-500" data-aos="fade-up">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-extrabold text-navy-900 font-display mb-4">Start Your Healthcare Career</h2>
        <p class="text-navy-950/90 mb-6 max-w-xl mx-auto text-sm font-body">Join thousands of successful graduates at Daniyal Group of Colleges.</p>
        <a href="{{ route('admissions.apply') }}" class="inline-flex items-center bg-white text-gold-700 px-6 py-3 rounded-full font-bold text-sm hover:bg-gray-100 transition shadow-lg font-body">
            Apply Online Now
        </a>
    </div>
</section>
@endsection
