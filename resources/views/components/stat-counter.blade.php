@props([
    'target',
    'prefix' => '',
    'suffix' => '',
    'label',
    'delay' => 100
])

@php
    // Clean target to a pure number for Alpine count animation
    $cleanTarget = preg_replace('/[^0-9]/', '', $target);
@endphp

<div class="py-6 md:py-0" 
     data-aos="fade-up" 
     data-aos-delay="{{ $delay }}"
     x-data="{ 
         current: 0, 
         target: {{ $cleanTarget }},
         time: 1500, // duration in ms
         start() {
             let startTimestamp = null;
             const step = (timestamp) => {
                 if (!startTimestamp) startTimestamp = timestamp;
                 const progress = Math.min((timestamp - startTimestamp) / this.time, 1);
                 this.current = Math.floor(progress * this.target);
                 if (progress < 1) {
                     window.requestAnimationFrame(step);
                 } else {
                     this.current = this.target;
                 }
             };
             window.requestAnimationFrame(step);
         }
     }"
     x-intersect.once="start()">
    <div class="text-4xl md:text-5xl font-extrabold text-gold-500 font-display mb-1 flex justify-center items-center">
        <span>{{ $prefix }}</span>
        <span x-text="current">0</span>
        <span>{{ $suffix }}</span>
    </div>
    <div class="text-navy-200 text-xs font-body uppercase tracking-wider font-semibold">
        {{ $label }}
    </div>
</div>
