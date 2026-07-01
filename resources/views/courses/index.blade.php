@extends('layouts.app')

@section('title', 'Our Programs')

@section('content')
<!-- Header -->
<div class="bg-navy-900 py-16 text-white text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-extrabold font-display mb-4 uppercase tracking-wider">Health Sciences Programs</h1>
        <div class="h-1 w-20 bg-gold-500 mx-auto mb-4"></div>
        <p class="text-gray-300 max-w-2xl mx-auto font-body text-sm">Discover our diverse range of health sciences programs designed to prepare you for a successful career in healthcare.</p>
    </div>
</div>

<!-- Programs Grid -->
<div class="py-20 bg-brand-surface">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($courses as $course)
            <div class="bg-white border border-brand-border rounded-xl p-8 hover:-translate-y-1 hover:border-gold-500 transition-all duration-300 group shadow-sm hover:shadow-md">
                <div class="w-16 h-16 bg-navy-900 rounded-xl flex items-center justify-center text-white mb-6 group-hover:bg-gold-500 group-hover:text-navy-900 transition-colors duration-300 shadow-md">
                    <span class="text-2xl font-bold uppercase font-display">{{ substr($course->code, 0, 2) }}</span>
                </div>
                <h2 class="text-2xl font-bold text-navy-900 mb-3 font-display">{{ $course->name }}</h2>
                <p class="text-navy-400 mb-6 line-clamp-3 font-body text-sm leading-relaxed">{{ $course->description ?: 'Detailed program curriculum and career outcomes for ' . $course->name }}</p>
                
                <div class="flex items-center justify-between pt-6 border-t border-brand-border font-body">
                    <div>
                        <span class="block text-[10px] text-navy-200 uppercase font-bold tracking-wider">Duration</span>
                        <span class="text-gold-700 font-bold text-sm">{{ $course->duration }}</span>
                    </div>
                    <a href="{{ route('courses.show', $course->code) }}" class="btn btn-primary px-4 py-2 text-xs rounded shadow-sm hover:shadow">
                        Details
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-xl border border-brand-border shadow-sm">
                <p class="text-navy-400 italic font-body text-sm">Course catalog is being updated. Please check back later.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- CTA -->
<div class="bg-brand-surface py-20 border-t border-brand-border">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-extrabold text-navy-900 font-display mb-6">Unsure which program to choose?</h2>
        <p class="text-navy-400 mb-8 max-w-2xl mx-auto text-base leading-relaxed font-body">Our career counselors are available to help you navigate your options and find the path that best aligns with your goals.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('admissions.apply') }}" class="btn btn-accent px-8 py-4 rounded-full text-sm font-bold uppercase tracking-wider hover:shadow-lg">
                Start Application
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline px-8 py-4 rounded-full text-sm font-bold uppercase tracking-wider">
                Speak to Advisor
            </a>
        </div>
    </div>
</div>
@endsection
