@props([
    'question',
    'answer',
    'index' => 0
])

<div class="border border-brand-border rounded-lg bg-white overflow-hidden shadow-sm transition-all"
     x-data="{ active: false }"
     :class="{ 'border-gold-500 shadow-md': active }">
    <!-- Trigger Header -->
    <button class="w-full flex justify-between items-center px-6 py-4 text-left font-display font-bold text-[14px] md:text-[15px] text-navy-900 focus:outline-none"
            @click="active = !active">
        <span>{{ $question }}</span>
        <span class="w-6 h-6 rounded-full bg-brand-surface flex items-center justify-center text-navy-400 group-hover:text-navy-900 transition-transform duration-300"
              :class="{ 'rotate-180 bg-gold-50 text-gold-700': active }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </span>
    </button>

    <!-- Collapsible Body -->
    <div x-show="active"
         x-collapse
         x-cloak
         class="font-body text-navy-400 text-[13px] md:text-[14px] px-6 pb-5 leading-relaxed bg-brand-surface/40">
        {{ $answer }}
    </div>
</div>
