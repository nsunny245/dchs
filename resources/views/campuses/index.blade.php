@extends('layouts.app')

@section('title', 'Our Campuses')

@section('content')
<!-- Header -->
<div class="bg-navy-900 py-16 text-white text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-extrabold font-display mb-4 uppercase tracking-wider">Our Campuses</h1>
        <div class="h-1 w-20 bg-gold-500 mx-auto mb-4"></div>
        <p class="text-gray-300 max-w-2xl mx-auto font-body text-sm">Providing quality health sciences education across multiple locations in Punjab with state-of-the-art facilities.</p>
    </div>
</div>

<!-- Campuses Grid -->
<div class="py-20 bg-brand-surface">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($campuses as $campus)
            <div class="bg-white border border-brand-border rounded-xl shadow-sm overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                <div class="h-64 relative overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=800" alt="{{ $campus->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 right-4 bg-gold-500 text-navy-900 px-4 py-1 rounded-full text-xs font-bold uppercase shadow-md font-body">
                        {{ $campus->city }}
                    </div>
                </div>
                <div class="p-8">
                    <h2 class="text-2xl font-bold text-navy-900 mb-4 font-display">{{ $campus->name }}</h2>
                    <div class="space-y-4 mb-8 font-body">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gold-700 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-navy-400 text-sm">{{ $campus->address ?: 'Location details coming soon' }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gold-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-navy-400 text-sm">{{ $campus->phone ?: '+92 300-1234567' }}</span>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-brand-border flex justify-between items-center font-body">
                        <span class="text-gold-700 font-bold text-xs uppercase tracking-wider">Active Campus</span>
                        <a href="{{ route('admissions.apply') }}" class="inline-flex items-center text-navy-900 font-bold hover:text-gold-700 transition text-sm">
                            Apply for this Campus
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 bg-white rounded-xl border border-brand-border">
                <p class="text-navy-400 italic font-body text-sm">No campuses currently available.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- CTA -->
<div class="bg-gold-500 py-16 text-navy-900 text-center">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-extrabold font-display mb-6">Want to Visit Our Campus?</h2>
        <p class="mb-8 max-w-xl mx-auto font-body text-sm text-navy-950/80">Schedule a campus tour today and explore our state-of-the-art labs and clinical training facilities.</p>
        <a href="{{ route('contact') }}" class="btn btn-primary rounded-full hover:bg-navy-700 text-white font-display uppercase tracking-wider text-sm shadow-md hover:shadow-lg">
            Book a Tour
        </a>
    </div>
</div>
@endsection
