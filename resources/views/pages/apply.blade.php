@extends('layouts.app')

@section('title', 'Online Admission Form | Daniyal Group of Colleges')

@section('content')
<!-- Header Banner Section -->
<section class="bg-navy-900 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="container mx-auto px-6 relative z-10">
        <span class="text-gold-500 font-bold text-xs uppercase tracking-widest font-display">Enrollment Portal</span>
        <h1 class="text-3xl md:text-4xl font-extrabold font-display text-white mt-2">Online Application Form</h1>
        <div class="h-1 w-12 bg-gold-500 mt-4 rounded-full"></div>
    </div>
</section>

<!-- Application Form -->
<section class="py-24 bg-brand-surface font-body">
    <div class="container mx-auto px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl border border-brand-border shadow-md p-8 md:p-12 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-navy-900 via-gold-500 to-navy-900"></div>

            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-navy-900 font-display">Student Registration Form</h2>
                <p class="text-navy-400 text-xs mt-2">Please complete all fields carefully. All entries must match your official matriculation transcripts.</p>
            </div>

            <!-- Success Alert -->
            @if(session('success'))
                <div class="mb-8 p-6 rounded-xl bg-success/10 border border-success/20 text-success text-xs font-semibold leading-relaxed">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Errors Alert -->
            @if($errors->any())
                <div class="mb-8 p-6 rounded-xl bg-danger/10 border border-danger/20 text-danger text-xs font-semibold">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admissions.store') }}" method="POST" class="space-y-6 text-xs text-navy-900">
                @csrf

                <!-- Section 1: Personal Info -->
                <div>
                    <h3 class="text-sm font-bold font-display uppercase tracking-wider text-navy-900 pb-2 border-b border-brand-border mb-4">1. Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-2">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Father's Name <span class="text-danger">*</span></label>
                            <input type="text" name="father_name" value="{{ old('father_name') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">CNIC / B-Form Number <span class="text-danger">*</span></label>
                            <input type="text" name="cnic" placeholder="e.g. 35201-1234567-8" value="{{ old('cnic') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="dob" value="{{ old('dob') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Gender <span class="text-danger">*</span></label>
                            <select name="gender" required 
                                    class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" placeholder="e.g. 0321-7729533" value="{{ old('phone') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Residential Address <span class="text-danger">*</span></label>
                            <textarea name="address" rows="3" required 
                                      class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Program Preference -->
                <div>
                    <h3 class="text-sm font-bold font-display uppercase tracking-wider text-navy-900 pb-2 border-b border-brand-border mb-4">2. Program & Campus Preference</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-2">Preferred Program <span class="text-danger">*</span></label>
                            <select name="program" required 
                                    class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                                <option value="" disabled selected>Select a Program</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->code }}" 
                                        {{ (request('program') === $course->code || old('program') === $course->code) ? 'selected' : '' }}>
                                        {{ $course->name }} ({{ $course->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Preferred Campus <span class="text-danger">*</span></label>
                            <select name="campus" required 
                                    class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                                <option value="" disabled selected>Select a Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->city }}" 
                                        {{ (request('campus') === $campus->city || old('campus') === $campus->city) ? 'selected' : '' }}>
                                        Daniyal College {{ $campus->city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Academic Credentials -->
                <div>
                    <h3 class="text-sm font-bold font-display uppercase tracking-wider text-navy-900 pb-2 border-b border-brand-border mb-4">3. Academic History (Matriculation)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-2">Matric Board <span class="text-danger">*</span></label>
                            <input type="text" name="metric_board" placeholder="e.g. BISE Sahiwal, BISE Lahore" value="{{ old('metric_board') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                        <div>
                            <label class="block font-semibold mb-2">Matric Marks / Percentage <span class="text-danger">*</span></label>
                            <input type="text" name="metric_marks" placeholder="e.g. 850 or 75%" value="{{ old('metric_marks') }}" required 
                                   class="w-full px-4 py-3 rounded-lg border border-brand-border focus:border-gold-500 focus:outline-none bg-brand-surface/40">
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-gold-500 text-navy-900 hover:bg-gold-600 py-4 rounded-full font-bold uppercase tracking-wider shadow-lg transition-transform duration-200 transform hover:-translate-y-0.5">
                        Submit Application
                    </button>
                </div>
            </form>

        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<x-cta-banner />
@endsection
