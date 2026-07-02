@props([
    'title' => 'Start Your Healthcare Career',
    'description' => 'Join thousands of successful graduates who built high-impact healthcare careers at Daniyal Group of Colleges. Secure your seat today.',
    'buttonText' => 'Apply Online Now',
    'buttonLink' => null
])

@php
    $link = $buttonLink ?? route('admissions.apply');
@endphp

<section class="py-20 bg-white relative overflow-hidden font-body">
    <div class="container mx-auto px-6">
        <div class="max-w-5xl mx-auto bg-navy-900 border-2 border-gold-500 rounded-2xl p-10 md:p-12 text-center relative overflow-hidden shadow-2xl" data-aos="fade-up">
            <!-- Background watermark decoration -->
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-gold-500/5 rounded-full pointer-events-none"></div>
            <div class="absolute -left-16 -top-16 w-64 h-64 bg-white/5 rounded-full pointer-events-none"></div>
            
            <div class="relative z-10 max-w-2xl mx-auto">
                <div class="inline-block bg-gold-500/10 border border-gold-500/30 text-gold-500 px-4 py-1.5 rounded-full text-[10px] font-bold mb-6 tracking-widest font-display uppercase">
                    Admissions Process 2026
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-white font-display mb-4 leading-tight">
                    {{ $title }}
                </h2>
                <p class="text-navy-200 mb-8 text-xs md:text-sm leading-relaxed max-w-xl mx-auto">
                    {{ $description }}
                </p>
                <x-button variant="accent" :href="$link" class="px-10 py-4 shadow-xl hover:shadow-gold-500/25">
                    {{ $buttonText }}
                </x-button>
            </div>
        </div>
    </div>
</section>
