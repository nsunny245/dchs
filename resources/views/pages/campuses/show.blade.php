@extends('layouts.app')

@section('title', $campus->name . ' | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Campus Profile</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">{{ $campus->name }}</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Detail Section -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            <!-- Left Side: Detail list & map info -->
            <div class="w-full lg:w-7/12 space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-navy-900 font-display mb-4">Welcome to our {{ $campus->city }} Campus</h2>
                    <p class="text-navy-400 leading-relaxed text-sm md:text-base">
                        Daniyal College {{ $campus->city }} offers exceptional facilities designed specifically to meet clinical standards. Students receive hands-on training with advanced machinery, patient monitors, simulation skeletons, and diagnostic equipment.
                    </p>
                </div>

                <!-- Facilities List -->
                <div>
                    <h3 class="text-lg font-bold text-navy-900 font-display mb-4">Key Campus Facilities</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-4 rounded-lg bg-brand-surface border border-brand-border">
                            <span class="text-gold-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold text-navy-900">Modern Diagnostic Labs</span>
                        </div>
                        <div class="flex items-center space-x-3 p-4 rounded-lg bg-brand-surface border border-brand-border">
                            <span class="text-gold-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </span>
                            <span class="text-xs font-semibold text-navy-900">Resource Library</span>
                        </div>
                        <div class="flex items-center space-x-3 p-4 rounded-lg bg-brand-surface border border-brand-border">
                            <span class="text-gold-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </span>
                            <span class="text-xs font-semibold text-navy-900">Surgical & OT Practice Labs</span>
                        </div>
                        <div class="flex items-center space-x-3 p-4 rounded-lg bg-brand-surface border border-brand-border">
                            <span class="text-gold-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold text-navy-900">Fully Certified Instructors</span>
                        </div>
                    </div>
                </div>

                <!-- Google Maps Placeholders / पंजाब Context -->
                <div>
                    <h3 class="text-lg font-bold text-navy-900 font-display mb-4">Location Map</h3>
                    <div class="w-full h-80 rounded-xl border border-brand-border bg-brand-surface overflow-hidden relative flex items-center justify-center text-navy-400">
                        <!-- Embedded mock map visual -->
                        <div class="absolute inset-0 bg-[url('https://maps.googleapis.com/maps/api/staticmap?center=' . urlencode($campus->city) . ',Punjab,Pakistan&zoom=14&size=600x400&sensor=false&key=')] bg-cover bg-center filter grayscale opacity-20"></div>
                        <div class="relative z-10 text-center p-6">
                            <svg class="w-12 h-12 text-gold-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="font-bold text-navy-900 text-sm font-display">{{ $campus->address }}</p>
                            <p class="text-xs text-navy-400 mt-1">Visit during college hours: 8:00 AM - 2:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Contact Box & Quick Apply -->
            <div class="w-full lg:w-5/12 bg-brand-surface border border-brand-border rounded-2xl p-8 sticky top-28">
                <h3 class="text-xl font-bold text-navy-900 font-display mb-6">Campus Contact Info</h3>
                
                <div class="space-y-4 mb-8 text-xs text-navy-900">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 mr-3 text-gold-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $campus->address }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-3 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1.3 1.3 0 01-.36 1.15L8 9.38a10.02 10.02 0 005 5l1.62-1.62c.3-.3.73-.42 1.15-.362l2.2.547a1 1 0 01.725.94V19a2 2 0 01-2 2h-3C7.119 21 3 16.881 3 12V5z"/></svg>
                        <span>{{ $campus->phone }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-3 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ $campus->email }}</span>
                    </div>
                </div>

                <div class="h-px bg-brand-border mb-6"></div>

                <h4 class="text-sm font-bold text-navy-900 font-display mb-3">Available Programs</h4>
                <div class="flex flex-wrap gap-2 mb-8">
                    @foreach($courses as $c)
                        <a href="{{ route('courses.show', $c->code) }}" class="text-[10px] font-bold bg-white text-navy-900 border border-brand-border px-3 py-1 rounded-full hover:border-gold-500 hover:text-gold-700 transition">
                            {{ $c->code }}
                        </a>
                    @endforeach
                </div>

                <x-button variant="accent" :href="route('admissions.apply') . '?campus=' . $campus->city" class="w-full py-4 text-center">
                    Apply to this Campus
                </x-button>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
