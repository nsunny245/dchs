@props([
    'size' => 'nav' // nav, hero-watermark, footer, mobile-overlay, splash
])

@php
    $class = match($size) {
        'nav' => 'h-16 w-auto md:h-20',
        'hero-watermark' => 'w-[400px] h-[400px] opacity-[0.03] select-none pointer-events-none',
        'footer' => 'h-32 w-auto md:h-40',
        'mobile-overlay' => 'h-24 w-auto',
        'splash' => 'h-32 w-auto animate-pulse',
        default => 'h-16 w-auto'
    };
@endphp

@if($size === 'hero-watermark')
    <img src="{{ asset('images/dchs-logo.png') }}" alt="" class="{{ $class }} absolute z-0">
@else
    <img src="{{ asset('images/dchs-logo.png') }}" alt="Daniyal Group of Colleges Logo" class="{{ $class }} transition-transform duration-300">
@endif
