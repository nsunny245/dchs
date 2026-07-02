@props([
    'src',
    'alt' => '',
    'class' => '',
    'glow' => true,
    'badgeText' => null
])

<div class="relative inline-block w-full {{ $class }}">
    <!-- Soft radial glow background behind the image -->
    @if($glow)
        <div class="absolute -inset-4 bg-radial from-gold-500/15 via-gold-500/0 to-transparent blur-2xl z-0 pointer-events-none rounded-full"></div>
    @endif

    <!-- Border Backdrop layout -->
    <div class="double-border-container relative z-10 w-full">
        <div class="double-border-backdrop"></div>
        <img src="{{ $src }}" alt="{{ $alt }}" class="relative rounded-lg shadow-lg w-full z-10 object-cover border border-navy-100/50">
        
        <!-- Frosted-glass floating badge -->
        @if($badgeText)
            <div class="absolute bottom-4 left-4 z-20 backdrop-blur-md bg-navy-950/80 border border-white/10 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2 font-display text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 bg-gold-500 rounded-full animate-pulse"></span>
                <span>{{ $badgeText }}</span>
            </div>
        @endif
    </div>
</div>
