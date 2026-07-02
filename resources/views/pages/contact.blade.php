@extends('layouts.app')

@section('title', 'Contact Us | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Get in Touch</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Contact DCHS</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Contact Form and Details -->
<section class="py-24 bg-white font-body">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-16">
            
            <!-- Left Column: Form -->
            <div class="w-full lg:w-7/12 bg-brand-surface border border-brand-border rounded-2xl p-8 md:p-10 shadow-sm">
                <h2 class="text-2xl font-bold text-navy-900 font-display mb-2">Send us a Message</h2>
                <p class="text-navy-400 text-xs mb-8">Do you have questions about programs, fees, or campus visits? Submit this form and we'll reply shortly.</p>

                <!-- Success Alert -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-success/10 border border-success/20 text-success text-xs font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Errors Alert -->
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-danger/10 border border-danger/20 text-danger text-xs font-semibold">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-6 text-xs text-navy-900">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-2">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" placeholder="e.g. 0321-7729533" value="{{ old('phone') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Subject</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Message <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" required 
                                      class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-white">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full bg-navy-900 text-white hover:bg-navy-700 py-3.5 rounded-lg font-bold uppercase tracking-wider shadow transition-transform transform hover:-translate-y-0.5">
                            Submit Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Campus Addresses & Info -->
            <div class="w-full lg:w-5/12 space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-navy-900 font-display mb-4">Contact Information</h2>
                    <p class="text-navy-400 text-xs leading-relaxed">
                        For institutional inquiries, franchisor discussions, or general info, contact our central admissions office. Or visit one of our 4 locations.
                    </p>
                </div>

                <div class="space-y-4 text-xs text-navy-900">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gold-50 flex items-center justify-center text-gold-700 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1.3 1.3 0 01-.36 1.15L8 9.38a10.02 10.02 0 005 5l1.62-1.62c.3-.3.73-.42 1.15-.362l2.2.547a1 1 0 01.725.94V19a2 2 0 01-2 2h-3C7.119 21 3 16.881 3 12V5z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold">Phone Support</p>
                            <p class="text-navy-400">+92 321-7729533</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gold-50 flex items-center justify-center text-gold-700 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold">Email Admissions</p>
                            <p class="text-navy-400">info@daniyalgroupofcolleges.com</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gold-50 flex items-center justify-center text-gold-700 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold">College Hours</p>
                            <p class="text-navy-400">Monday - Saturday (8:00 AM - 2:00 PM)</p>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-brand-border"></div>

                <!-- Addresses for all 4 campuses -->
                <div>
                    <h3 class="text-sm font-bold text-navy-900 font-display mb-4 uppercase tracking-wider">Campus Locations</h3>
                    <div class="space-y-4">
                        @foreach($campuses as $campus)
                            <div class="p-4 rounded-lg bg-brand-surface border border-brand-border text-xs text-navy-900">
                                <h4 class="font-bold text-gold-700">{{ $campus->name }}</h4>
                                <p class="text-navy-400 mt-1">{{ $campus->address }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
