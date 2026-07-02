@props([
    'eyebrow' => null,
    'heading',
    'align' => 'center',
    'underline' => true
])

<div class="mb-12 @if($align === 'center') text-center @else text-left @endif" data-aos="fade-up">
    @if($eyebrow)
        <span class="inline-block bg-gold-50 text-gold-700 border border-gold-300 px-4 py-1.5 rounded-full text-[10px] font-bold tracking-widest font-display uppercase mb-3">
            {{ $eyebrow }}
        </span>
    @endif

    <h2 class="text-3xl md:text-4xl font-extrabold text-navy-900 font-display leading-tight mb-3">
        {!! $heading !!}
    </h2>

    @if($underline)
        <div class="h-1 w-16 bg-gold-500 @if($align === 'center') mx-auto @endif rounded-full"></div>
    @endif
</div>
