@extends('layouts.app')

@section('title', 'Our Campuses | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Regional Footprint</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Our Campuses</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Campuses Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <x-section-heading eyebrow="Four Locations, One Standard" heading="Our Active Campuses across Punjab" align="center" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
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
<x-cta-banner />
@endsection
