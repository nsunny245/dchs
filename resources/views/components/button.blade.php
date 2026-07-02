@props([
    'variant' => 'primary', // primary, accent, outline, ghost
    'type' => 'link',      // link, button, submit
    'href' => '#',
    'class' => ''
])

@php
    $baseStyles = 'btn rounded-full text-xs md:text-sm font-bold uppercase tracking-wider transition-all duration-300 transform hover:-translate-y-0.5 inline-flex items-center justify-center';
    
    $variantStyles = match($variant) {
        'primary' => 'bg-navy-900 text-white hover:bg-navy-700 hover:shadow-lg',
        'accent' => 'bg-gold-500 text-navy-900 hover:bg-gold-600 hover:shadow-lg hover:shadow-gold-500/10',
        'outline' => 'bg-transparent text-navy-900 border border-navy-900 hover:bg-navy-50',
        'ghost' => 'bg-transparent text-navy-900 hover:bg-navy-50',
        default => 'bg-navy-900 text-white hover:bg-navy-700'
    };

    $allStyles = $baseStyles . ' ' . $variantStyles . ' ' . $class;
@endphp

@if($type === 'link')
    <a href="{{ $href }}" class="{{ $allStyles }}" {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $allStyles }}" {{ $attributes }}>
        {{ $slot }}
    </button>
@endif
