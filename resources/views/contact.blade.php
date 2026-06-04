@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="py-20">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-primary-800 font-serif mb-8 text-center">Contact Us</h1>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg">
            <p class="text-gray-600 mb-6 text-center">Have questions? We're here to help. Reach out to any of our campuses.</p>
            <!-- Basic contact info placeholder -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <span class="font-bold w-32">Main Phone:</span>
                    <span>+92 321-7729533</span>
                </div>
                <div class="flex items-center">
                    <span class="font-bold w-32">Email:</span>
                    <span>info@daniyalgroupofcolleges.com</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
