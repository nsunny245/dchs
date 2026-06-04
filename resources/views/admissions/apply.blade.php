@extends('layouts.app')

@section('title', 'Apply Online')

@section('content')
<div class="py-24 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Form Header -->
            <div class="bg-white rounded-t-3xl p-10 border-b border-gray-100 text-center shadow-sm">
                <h1 class="text-4xl font-bold text-[#1e3a5f] font-serif mb-4 uppercase tracking-widest leading-tight">Online Application Form</h1>
                <p class="text-gray-500 font-medium">Please fill in the details accurately. Fields marked with <span class="text-red-500">*</span> are mandatory.</p>
            </div>

            <!-- Form Content -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl font-medium">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('admissions.store') }}" method="POST" class="relative z-10">
                    @csrf
                    
                    <!-- Section: Personal Information -->
                    <div class="mb-12">
                        <div class="flex items-center mb-8">
                            <div class="w-10 h-10 bg-[#1e3a5f] text-white rounded-xl flex items-center justify-center font-bold mr-4 shadow-lg">1</div>
                            <h2 class="text-2xl font-bold text-[#1e3a5f] uppercase tracking-wider font-serif">Personal Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="Enter your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Father's Name <span class="text-red-500">*</span></label>
                                <input type="text" name="father_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="Enter father's name">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="+92 3XX XXXXXXX">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Email Address</label>
                                <input type="email" name="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="email@example.com">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">CNIC / B-Form <span class="text-red-500">*</span></label>
                                <input type="text" name="cnic" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="35302-XXXXXXX-X">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Date of Birth <span class="text-red-500">*</span></label>
                                <input type="date" name="dob" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Gender <span class="text-red-500">*</span></label>
                                <select name="gender" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none appearance-none">
                                    <option value="">Choose Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Residential Address <span class="text-red-500">*</span></label>
                                <textarea name="address" required rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="Enter your complete home address"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Program Selection -->
                    <div class="mb-12">
                        <div class="flex items-center mb-8">
                            <div class="w-10 h-10 bg-[#1e3a5f] text-white rounded-xl flex items-center justify-center font-bold mr-4 shadow-lg">2</div>
                            <h2 class="text-2xl font-bold text-[#1e3a5f] uppercase tracking-wider font-serif">Program & Campus Selection</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Select Program <span class="text-red-500">*</span></label>
                                <select name="program" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none appearance-none">
                                    <option value="">Choose a Program</option>
                                    @foreach(['LHV', 'CMW', 'CNA', 'PT', 'MLT', 'OT', 'DT', 'AT', 'CSSD'] as $code)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Preferred Campus <span class="text-red-500">*</span></label>
                                <select name="campus" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none appearance-none">
                                    <option value="">Choose a Campus</option>
                                    <option value="Okara">Okara Campus</option>
                                    <option value="Depalpur">Depalpur Campus</option>
                                    <option value="Chichawatni">Chichawatni Campus</option>
                                    <option value="Sahiwal">Sahiwal Campus</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Academic Background -->
                    <div class="mb-12">
                        <div class="flex items-center mb-8">
                            <div class="w-10 h-10 bg-[#1e3a5f] text-white rounded-xl flex items-center justify-center font-bold mr-4 shadow-lg">3</div>
                            <h2 class="text-2xl font-bold text-[#1e3a5f] uppercase tracking-wider font-serif">Academic Background</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Metric Board Name <span class="text-red-500">*</span></label>
                                <input type="text" name="metric_board" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="e.g. BISE Sahiwal">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[#1e3a5f] mb-2 uppercase tracking-widest">Metric Marks <span class="text-red-500">*</span></label>
                                <input type="number" name="metric_marks" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#d4af37] focus:border-[#d4af37] transition-all outline-none" placeholder="Total Marks Obtained">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-10">
                        <button type="submit" class="w-full bg-[#1e3a5f] text-white py-5 rounded-2xl font-bold uppercase tracking-widest text-xl hover:bg-[#d4af37] transition-all shadow-2xl flex items-center justify-center group">
                            Submit Application
                            <svg class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </button>
                        <p class="text-center text-gray-400 text-xs mt-6 font-medium">By submitting, you agree to the Daniyal Group of Colleges admission policies and terms.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
