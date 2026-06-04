@extends('layouts.app')

@section('title', $course->name)

@section('content')
<!-- Hero Section -->
<div class="relative py-24 bg-[#1e3a5f] text-white overflow-hidden">
    <!-- Decorative Circle -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#d4af37]/10 rounded-full blur-3xl"></div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-block bg-[#d4af37] text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-widest mb-6 shadow-lg">
                {{ $course->code }}
            </div>
            <h1 class="text-4xl md:text-6xl font-bold font-serif mb-6 leading-tight">{{ $course->name }}</h1>
            <div class="flex flex-wrap items-center gap-8 text-gray-300">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-[#d4af37] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-lg font-semibold">{{ $course->duration }} Program</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-[#d4af37] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-lg font-semibold">Recognized by Punjab Medical Faculty</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
            <!-- Main Details -->
            <div class="lg:col-span-2">
                <div class="prose prose-lg max-w-none prose-headings:text-[#1e3a5f] prose-headings:font-serif">
                    <h2 class="text-3xl font-bold mb-8">Program Overview</h2>
                    <p class="text-gray-600 leading-relaxed text-xl mb-12">
                        {{ $course->description ?: 'The ' . $course->name . ' program at Daniyal Group of Colleges provides students with a comprehensive foundation in health sciences. This curriculum is designed to combine rigorous academic theory with intensive practical training, ensuring our graduates are ready for the healthcare workforce.' }}
                    </p>

                    <h3 class="text-2xl font-bold mb-6 mt-12">Core Curriculum</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 not-prose mb-12">
                        @foreach(['Clinical Foundations', 'Medical Ethics', 'Patient Care Management', 'Laboratory Sciences', 'Pharmacology Basics', 'Anatomy & Physiology'] as $subject)
                        <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-2 h-2 bg-[#d4af37] rounded-full mr-4"></div>
                            <span class="font-bold text-[#1e3a5f]">{{ $subject }}</span>
                        </div>
                        @endforeach
                    </div>

                    <h3 class="text-2xl font-bold mb-6 mt-12">Career Opportunities</h3>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Graduates of this program can find rewarding careers in:
                    </p>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 list-none p-0 not-prose">
                        @foreach(['Public Hospitals', 'Private Clinics', 'Community Health Centers', 'Research Laboratories', 'Diagnostic Centers', 'Nursing Homes'] as $job)
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $job }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Apply Card -->
                <div class="bg-[#1e3a5f] text-white p-8 rounded-2xl shadow-2xl relative overflow-hidden">
                    <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
                    <h3 class="text-2xl font-bold font-serif mb-6 relative z-10">Ready to Join?</h3>
                    <p class="text-gray-300 mb-8 relative z-10">Admissions are currently open for the 2026 session. Secure your future today.</p>
                    <a href="{{ route('admissions.apply') }}" class="block w-full bg-[#d4af37] text-white text-center py-4 rounded-xl font-bold uppercase tracking-widest hover:bg-white hover:text-[#1e3a5f] transition-all shadow-xl relative z-10">
                        Apply Online
                    </a>
                </div>

                <!-- Info Card -->
                <div class="bg-gray-50 border border-gray-100 p-8 rounded-2xl">
                    <h3 class="text-xl font-bold text-[#1e3a5f] mb-6">Program Snapshot</h3>
                    <div class="space-y-6">
                        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                            <span class="text-gray-500">Qualification</span>
                            <span class="font-bold text-[#1e3a5f]">Diploma</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                            <span class="text-gray-500">Sessions</span>
                            <span class="font-bold text-[#1e3a5f]">Morning / Evening</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Eligibility</span>
                            <span class="font-bold text-[#1e3a5f]">Metric (Science)</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Sidebar -->
                <div class="p-8 border-2 border-dashed border-gray-200 rounded-2xl text-center">
                    <h4 class="font-bold text-[#1e3a5f] mb-2">Need Guidance?</h4>
                    <p class="text-gray-500 text-sm mb-4">Talk to our program coordinators for specific details.</p>
                    <a href="tel:+923001234567" class="text-[#d4af37] font-bold text-lg hover:underline">+92 300-1234567</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
