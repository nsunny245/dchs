@extends('layouts.app')

@section('title', 'Allied Health Sciences Programs | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Academic Catalog</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Allied Health Programs</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Filterable Catalog Section -->
<section class="py-24 bg-white font-body" x-data="{ filter: 'all' }">
    <div class="container mx-auto px-6">
        
        <!-- Filter Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-16">
            <button @click="filter = 'all'" 
                    class="px-6 py-2.5 rounded-full text-xs font-bold font-display uppercase tracking-wider transition-all border"
                    :class="filter === 'all' ? 'bg-navy-900 text-white border-navy-900 shadow-md' : 'bg-brand-surface text-navy-400 border-brand-border hover:border-navy-900'">
                All Programs
            </button>
            <button @click="filter = '36'" 
                    class="px-6 py-2.5 rounded-full text-xs font-bold font-display uppercase tracking-wider transition-all border"
                    :class="filter === '36' ? 'bg-navy-900 text-white border-navy-900 shadow-md' : 'bg-brand-surface text-navy-400 border-brand-border hover:border-navy-900'">
                3-Year Diplomas
            </button>
            <button @click="filter = '24'" 
                    class="px-6 py-2.5 rounded-full text-xs font-bold font-display uppercase tracking-wider transition-all border"
                    :class="filter === '24' ? 'bg-navy-900 text-white border-navy-900 shadow-md' : 'bg-brand-surface text-navy-400 border-brand-border hover:border-navy-900'">
                2-Year Diplomas
            </button>
            <button @click="filter = '12'" 
                    class="px-6 py-2.5 rounded-full text-xs font-bold font-display uppercase tracking-wider transition-all border"
                    :class="filter === '12' ? 'bg-navy-900 text-white border-navy-900 shadow-md' : 'bg-brand-surface text-navy-400 border-brand-border hover:border-navy-900'">
                1-Year Certificates
            </button>
        </div>

        <!-- Programs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($courses as $index => $program)
                <div x-show="filter === 'all' || filter === '{{ $program->duration_months }}'"
                     x-transition.scale.origin.top.duration.300ms>
                    <x-program-card :program="$program" :index="$index" />
                </div>
            @empty
                <div class="col-span-3 text-center py-12 text-navy-400 italic">
                    No programs registered at this time.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
