@extends('layouts.app')

@section('title', 'Admissions')

@section('content')
<!-- Header -->
<div class="bg-[#1e3a5f] py-24 text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-4 relative z-10">
        <h1 class="text-5xl font-bold font-serif mb-6 uppercase tracking-widest leading-tight">Admissions Open <span class="text-[#d4af37]">2026</span></h1>
        <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed font-light">Join the next generation of healthcare leaders. Your journey toward a successful medical career starts here.</p>
        <a href="{{ route('admissions.apply') }}" class="bg-[#d4af37] text-white px-12 py-5 rounded-full font-bold uppercase tracking-[0.2em] hover:bg-white hover:text-[#1e3a5f] transition-all shadow-2xl inline-block transform hover:scale-105">
            Apply Now
        </a>
    </div>
</div>

<!-- Process Section -->
<div class="py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-20">
            <h2 class="text-4xl font-bold text-[#1e3a5f] font-serif mb-4 uppercase">How to Apply</h2>
            <div class="h-1.5 w-24 bg-[#d4af37] mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-gray-50 text-[#1e3a5f] border-2 border-[#1e3a5f]/10 rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 group-hover:bg-[#1e3a5f] group-hover:text-white group-hover:border-[#1e3a5f] transition-all duration-300 shadow-xl group-hover:-translate-y-2">
                    01
                </div>
                <h3 class="text-2xl font-bold text-[#1e3a5f] mb-4">Online Registration</h3>
                <p class="text-gray-600 leading-relaxed">Fill out our simple online application form with your personal and academic details.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-gray-50 text-[#1e3a5f] border-2 border-[#1e3a5f]/10 rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 group-hover:bg-[#1e3a5f] group-hover:text-white group-hover:border-[#1e3a5f] transition-all duration-300 shadow-xl group-hover:-translate-y-2">
                    02
                </div>
                <h3 class="text-2xl font-bold text-[#1e3a5f] mb-4">Document Verification</h3>
                <p class="text-gray-600 leading-relaxed">Visit your nearest campus with original documents for verification and interview.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-gray-50 text-[#1e3a5f] border-2 border-[#1e3a5f]/10 rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 group-hover:bg-[#1e3a5f] group-hover:text-white group-hover:border-[#1e3a5f] transition-all duration-300 shadow-xl group-hover:-translate-y-2">
                    03
                </div>
                <h3 class="text-2xl font-bold text-[#1e3a5f] mb-4">Admission Confirmation</h3>
                <p class="text-gray-600 leading-relaxed">Secure your seat by paying the admission fee and receiving your student ID card.</p>
            </div>
        </div>
    </div>
</div>

<!-- Requirements Section -->
<div class="py-24 bg-gray-50 border-t border-gray-100">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">
            <div class="md:w-1/3 bg-[#1e3a5f] p-12 text-white flex flex-col justify-center">
                <h2 class="text-3xl font-bold font-serif mb-6 leading-tight uppercase tracking-wider">Required Documents</h2>
                <div class="h-1 w-12 bg-[#d4af37] mb-6 rounded-full"></div>
                <p class="text-gray-400 text-sm italic font-light">Please bring original and photocopies during campus visit.</p>
            </div>
            <div class="md:w-2/3 p-12">
                <ul class="space-y-6">
                    @foreach([
                        'Metric Degree / Result Card (Science)',
                        'CNIC or Form-B Copy',
                        'Father / Guardian CNIC Copy',
                        '8 Passport Size Photographs (Blue Background)',
                        'Character Certificate from Last Institute',
                        'Domicile Copy'
                    ] as $doc)
                    <li class="flex items-center text-gray-700 font-medium">
                        <div class="w-6 h-6 bg-green-50 text-green-500 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
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
        <h2 class="text-3xl font-bold text-[#1e3a5f] font-serif mb-8 uppercase tracking-widest">Have more questions?</h2>
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <div class="p-8 bg-gray-50 rounded-2xl border border-gray-100 flex-1 max-w-sm">
                <h3 class="font-bold text-[#1e3a5f] mb-2 uppercase tracking-wider">Call Admission Office</h3>
                <p class="text-2xl font-black text-[#d4af37]">+92 321-7729533</p>
            </div>
            <div class="p-8 bg-gray-50 rounded-2xl border border-gray-100 flex-1 max-w-sm">
                <h3 class="font-bold text-[#1e3a5f] mb-2 uppercase tracking-wider">Email Inquiry</h3>
                <p class="text-2xl font-black text-[#d4af37]">info@daniyalgroupofcolleges.com</p>
            </div>
        </div>
    </div>
</div>
@endsection
