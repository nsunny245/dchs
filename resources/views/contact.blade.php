@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="py-20 bg-brand-surface">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-extrabold text-navy-900 font-display mb-8 text-center">Contact Us</h1>
        <div class="max-w-2xl mx-auto card">
            <p class="text-navy-400 mb-6 text-center font-body">Have questions? We're here to help. Reach out to any of our campuses.</p>
            <!-- Basic contact info placeholder -->
            <div class="space-y-4 font-body">
                <div class="flex items-center">
                    <span class="font-bold w-32 text-navy-900">Main Phone:</span>
                    <span class="text-navy-400">+92 321-7729533</span>
                </div>
                <div class="flex items-center">
                    <span class="font-bold w-32 text-navy-900">Email:</span>
                    <span class="text-navy-400">info@daniyalgroupofcolleges.com</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
