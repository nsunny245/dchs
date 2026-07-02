@props([
    'campus',
    'index' => 0
])

@php
    $campusImages = [
        0 => 'https://images.unsplash.com/photo-1562774053-701939374585?w=600', // Okara (Entrance/Campus)
        1 => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600', // Depalpur (Institution)
        2 => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600', // Chichawatni (Medical Labs)
        3 => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600', // Sahiwal (Modern Classroom)
    ];
    $imgUrl = $campusImages[$index % 4] ?? 'https://images.unsplash.com/photo-1562774053-701939374585?w=600';
@endphp

<div class="relative overflow-hidden rounded-xl group border border-navy-700/50 shadow-md hover:shadow-lg transition-all duration-300" 
     data-aos="zoom-in" data-aos-delay="{{ ($index % 4) * 100 }}">
    <div class="h-64 relative overflow-hidden">
        <img src="{{ $imgUrl }}" alt="{{ $campus->city }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
        <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-900/40 to-transparent"></div>
    </div>
    <div class="absolute bottom-0 left-0 right-0 p-5 transform group-hover:translate-y-[-2px] transition-transform duration-300">
        <h3 class="text-xl font-bold mb-1 font-display text-white">{{ $campus->city }}</h3>
        <div class="h-0.5 w-6 bg-gold-500 group-hover:w-16 transition-all duration-300 mb-2"></div>
        <p class="text-gold-400 text-xs font-body tracking-wider uppercase font-semibold mb-3">{{ $campus->name }}</p>
        
        <a href="{{ route('campuses.show', $campus->id) }}" class="inline-flex items-center text-white text-xs font-bold font-display hover:text-gold-500 transition-colors uppercase tracking-wider">
            Explore Campus
            <svg class="w-3.5 h-3.5 ml-1.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
</div>
