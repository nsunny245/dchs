@extends('layouts.app')

@section('title', 'Admissions')

@section('content')
<!-- Header -->
<div class="bg-navy-900 py-24 text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-4 relative z-10">
        <h1 class="text-5xl font-extrabold font-display mb-6 uppercase tracking-wider leading-tight">Admissions Open <span class="text-gold-500">2026</span></h1>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed font-body font-light">Join the next generation of healthcare leaders. Your journey toward a successful medical career starts here.</p>
        <a href="{{ route('admissions.apply') }}" class="btn btn-accent text-navy-900 px-12 py-5 rounded-full font-bold uppercase tracking-[0.2em] transition-all shadow-xl inline-block transform hover:scale-105">
            Apply Now
        </a>
    </div>
</div>

<!-- Process Section -->
<div class="py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-20">
            <h2 class="text-4xl font-extrabold text-navy-900 font-display mb-4 uppercase">How to Apply</h2>
            <div class="h-1.5 w-24 bg-gold-500 mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 font-body">
            <!-- Step 1 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-brand-surface text-navy-900 border border-navy-100 rounded-xl flex items-center justify-center text-3xl font-extrabold mx-auto mb-8 group-hover:bg-navy-900 group-hover:text-white group-hover:border-navy-900 transition-all duration-300 shadow-sm group-hover:-translate-y-1">
                    01
                </div>
                <h3 class="text-2xl font-bold text-navy-900 mb-4 font-display">Online Registration</h3>
                <p class="text-navy-400 text-sm leading-relaxed">Fill out our simple online application form with your personal and academic details.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-brand-surface text-navy-900 border border-navy-100 rounded-xl flex items-center justify-center text-3xl font-extrabold mx-auto mb-8 group-hover:bg-navy-900 group-hover:text-white group-hover:border-navy-900 transition-all duration-300 shadow-sm group-hover:-translate-y-1">
                    02
                </div>
                <h3 class="text-2xl font-bold text-navy-900 mb-4 font-display">Document Verification</h3>
                <p class="text-navy-400 text-sm leading-relaxed">Visit your nearest campus with original documents for verification and interview.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center group">
                <div class="w-20 h-20 bg-brand-surface text-navy-900 border border-navy-100 rounded-xl flex items-center justify-center text-3xl font-extrabold mx-auto mb-8 group-hover:bg-navy-900 group-hover:text-white group-hover:border-navy-900 transition-all duration-300 shadow-sm group-hover:-translate-y-1">
                    03
                </div>
                <h3 class="text-2xl font-bold text-navy-900 mb-4 font-display">Admission Confirmation</h3>
                <p class="text-navy-400 text-sm leading-relaxed">Secure your seat by paying the admission fee and receiving your student ID card.</p>
            </div>
        </div>
    </div>
</div>

<!-- Requirements Section -->
<div class="py-24 bg-brand-surface border-t border-brand-border">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden border border-brand-border flex flex-col md:flex-row">
            <div class="md:w-1/3 bg-navy-900 p-12 text-white flex flex-col justify-center">
                <h2 class="text-3xl font-extrabold font-display mb-6 leading-tight uppercase tracking-wider">Required Documents</h2>
                <div class="h-1 w-12 bg-gold-500 mb-6 rounded-full"></div>
                <p class="text-navy-200 text-sm italic font-light font-body">Please bring original and photocopies during campus visit.</p>
            </div>
            <div class="md:w-2/3 p-12">
                <ul class="space-y-6 font-body">
                    @foreach([
                        'Metric Degree / Result Card (Science)',
                        'CNIC or Form-B Copy',
                        'Father / Guardian CNIC Copy',
                        '8 Passport Size Photographs (Blue Background)',
                        'Character Certificate from Last Institute',
                        'Domicile Copy'
                    ] as $doc)
                    <li class="flex items-center text-navy-900 font-semibold text-sm">
                        <div class="w-6 h-6 bg-green-50 text-brand-success rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        {{ $doc }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- FAQ CTA -->
<div class="py-20 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-extrabold text-navy-900 font-display mb-8 uppercase tracking-wider">Have more questions?</h2>
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <div class="card max-w-sm flex-1 mx-auto sm:mx-0">
                <h3 class="font-bold text-navy-700 mb-2 uppercase tracking-wider font-display text-sm">Call Admission Office</h3>
                <p class="text-2xl font-extrabold text-gold-700 font-body">+92 321-7729533</p>
            </div>
            <div class="card max-w-sm flex-1 mx-auto sm:mx-0">
                <h3 class="font-bold text-navy-700 mb-2 uppercase tracking-wider font-display text-sm">Email Inquiry</h3>
                <p class="text-xl font-extrabold text-navy-900 font-body break-all">info@daniyalgroupofcolleges.com</p>
            </div>
        </div>
    </div>
</div>
@endsection
