@extends('layouts.app')

@section('title', 'Our Programs')

@section('content')
<!-- Header -->
<div class="bg-[#1e3a5f] py-16 text-white text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold font-serif mb-4 uppercase tracking-wider">Health Sciences Programs</h1>
        <div class="h-1 w-20 bg-[#d4af37] mx-auto mb-4"></div>
        <p class="text-gray-300 max-w-2xl mx-auto">Discover our diverse range of health sciences programs designed to prepare you for a successful career in healthcare.</p>
    </div>
</div>

<!-- Programs Grid -->
<div class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($courses as $course)
            <div class="border border-gray-100 rounded-2xl p-8 bg-gray-50 hover:bg-white hover:shadow-2xl hover:border-[#d4af37]/30 transition-all duration-300 group">
                <div class="w-16 h-16 bg-[#1e3a5f] rounded-xl flex items-center justify-center text-white mb-6 group-hover:bg-[#d4af37] transition-colors">
                    <span class="text-2xl font-bold uppercase">{{ substr($course->code, 0, 2) }}</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1e3a5f] mb-3">{{ $course->name }}</h2>
                <p class="text-gray-600 mb-6 line-clamp-3">{{ $course->description ?: 'Detailed program curriculum and career outcomes for ' . $course->name }}</p>
                
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div>
                        <span class="block text-xs text-gray-400 uppercase font-bold tracking-widest">Duration</span>
                        <span class="text-[#d4af37] font-bold">{{ $course->duration }}</span>
                    </div>
                    <a href="{{ route('courses.show', $course->code) }}" class="inline-flex items-center bg-[#1e3a5f] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#d4af37] transition shadow-md">
                        Details
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                <p class="text-gray-500 italic">Course catalog is being updated. Please check back later.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- CTA -->
<div class="bg-gray-50 py-20 border-t border-gray-100">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-[#1e3a5f] font-serif mb-6">Unsure which program to choose?</h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto text-lg leading-relaxed">Our career counselors are available to help you navigate your options and find the path that best aligns with your goals.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('admissions.apply') }}" class="bg-[#d4af37] text-white px-8 py-4 rounded-full font-bold uppercase tracking-widest hover:bg-[#1e3a5f] transition shadow-lg">
                Start Application
            </a>
            <a href="{{ route('contact') }}" class="bg-white text-[#1e3a5f] border-2 border-[#1e3a5f]/20 px-8 py-4 rounded-full font-bold uppercase tracking-widest hover:bg-[#1e3a5f] hover:text-white transition shadow-lg">
                Speak to Advisor
            </a>
        </div>
    </div>
</div>
@endsection
