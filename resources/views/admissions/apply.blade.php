@extends('layouts.app')

@section('title', 'Apply Online')

@section('content')
<div class="py-24 bg-brand-surface min-h-screen">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Form Header -->
            <div class="bg-white rounded-t-2xl p-10 border-b border-brand-border text-center shadow-sm">
                <h1 class="text-3xl font-extrabold text-navy-900 font-display mb-4 uppercase tracking-wider leading-tight">Online Application Form</h1>
                <p class="text-navy-400 font-medium font-body text-sm">Please fill in the details accurately. Fields marked with <span class="text-brand-danger">*</span> are mandatory.</p>
            </div>

            <!-- Form Content -->
            <div class="bg-white p-10 rounded-b-2xl border-x border-b border-brand-border shadow-sm">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg font-medium font-body text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg font-medium font-body text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg font-medium font-body text-sm">
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
                            <div class="w-10 h-10 bg-navy-900 text-white rounded-lg flex items-center justify-center font-bold mr-4 shadow-md font-display">1</div>
                            <h2 class="text-xl font-bold text-navy-900 uppercase tracking-wider font-display">Personal Information</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-body">
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Full Name <span class="text-brand-danger">*</span></label>
                                <input type="text" name="name" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="Enter your full name">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Father's Name <span class="text-brand-danger">*</span></label>
                                <input type="text" name="father_name" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="Enter father's name">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Phone Number <span class="text-brand-danger">*</span></label>
                                <input type="tel" name="phone" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="+92 3XX XXXXXXX">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Email Address</label>
                                <input type="email" name="email" class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="email@example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">CNIC / B-Form <span class="text-brand-danger">*</span></label>
                                <input type="text" name="cnic" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="35302-XXXXXXX-X">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Date of Birth <span class="text-brand-danger">*</span></label>
                                <input type="date" name="dob" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Gender <span class="text-brand-danger">*</span></label>
                                <select name="gender" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none appearance-none text-navy-900 text-sm">
                                    <option value="">Choose Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Residential Address <span class="text-brand-danger">*</span></label>
                                <textarea name="address" required rows="3" class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="Enter your complete home address"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Program Selection -->
                    <div class="mb-12">
                        <div class="flex items-center mb-8">
                            <div class="w-10 h-10 bg-navy-900 text-white rounded-lg flex items-center justify-center font-bold mr-4 shadow-md font-display">2</div>
                            <h2 class="text-xl font-bold text-navy-900 uppercase tracking-wider font-display">Program & Campus Selection</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-body">
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Select Program <span class="text-brand-danger">*</span></label>
                                <select name="program" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none appearance-none text-navy-900 text-sm">
                                    <option value="">Choose a Program</option>
                                    @foreach(['LHV', 'CMW', 'CNA', 'PT', 'MLT', 'OT', 'DT', 'AT', 'CSSD'] as $code)
                                        <option value="{{ $code }}">{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Preferred Campus <span class="text-brand-danger">*</span></label>
                                <select name="campus" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none appearance-none text-navy-900 text-sm">
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
                            <div class="w-10 h-10 bg-navy-900 text-white rounded-lg flex items-center justify-center font-bold mr-4 shadow-md font-display">3</div>
                            <h2 class="text-xl font-bold text-navy-900 uppercase tracking-wider font-display">Academic Background</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 font-body">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Metric Board Name <span class="text-brand-danger">*</span></label>
                                <input type="text" name="metric_board" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="e.g. BISE Sahiwal">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-navy-700 mb-2 uppercase tracking-wider">Metric Marks <span class="text-brand-danger">*</span></label>
                                <input type="number" name="metric_marks" required class="w-full bg-brand-surface border border-navy-100 rounded-md px-4 py-3.5 focus:ring-2 focus:ring-gold-500 focus:border-gold-500 transition-all outline-none text-navy-900 text-sm" placeholder="Total Marks Obtained">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-10">
                        <button type="submit" class="w-full bg-navy-900 text-white py-5 rounded-lg font-bold uppercase tracking-widest text-lg hover:bg-gold-500 hover:text-navy-900 transition-all shadow-md flex items-center justify-center group font-display">
                            Submit Application
                            <svg class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </button>
                        <p class="text-center text-navy-200 text-xs mt-6 font-medium font-body">By submitting, you agree to the Daniyal Group of Colleges admission policies and terms.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
