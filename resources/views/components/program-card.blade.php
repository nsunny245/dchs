@props([
    'program',
    'index' => 0
])

@php
    $code = strtoupper($program->code);
@endphp

<div class="card-program relative overflow-hidden flex flex-col justify-between" 
     data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
    <div>
        <!-- Header with Allied Health Dynamic SVG Icon -->
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-gold-50 rounded-xl flex items-center justify-center text-gold-700 shadow-sm border border-gold-100/50">
                @if(in_array($code, ['LHV', 'CMW']))
                    <!-- Nursing / Midwife Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v6m-3-3h6" />
                    </svg>
                @elseif($code === 'CNA')
                    <!-- Certified Nursing Assistant Stethoscope -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-3.75-3.75m11.25-3.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif($code === 'PT')
                    <!-- Pharmacy Tech / Medicine Capsule Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                @elseif($code === 'MLT')
                    <!-- Lab Tech Microscope/Flask -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif(in_array($code, ['OT', 'OTT', 'AT']))
                    <!-- Operation Theater / Anesthesia Board -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                @elseif($code === 'DT')
                    <!-- Dental Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2-3-3-3-3s-1.5 1-3 1-3-1-3-1-3 1-3 1-1.5-1-3-1-3 1-3 1v6.75c0 3.75 3 4.5 6 6 3-1.5 6-2.25 6-6V8.25z" />
                    </svg>
                @elseif($code === 'CSSD')
                    <!-- Sterile Supply Shield -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                    </svg>
                @else
                    <!-- Generic Health Cross -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            
            <x-badge variant="info">
                {{ $program->duration_months >= 12 ? ($program->duration_months / 12) . ' Year' : $program->duration_months . ' Months' }}
            </x-badge>
        </div>

        <h3 class="text-xl font-bold text-navy-900 font-display mb-3 leading-snug hover:text-gold-700 transition-colors">
            {{ $program->name }}
        </h3>
        <p class="text-navy-400 text-xs font-body leading-relaxed mb-6">
            {{ $program->description ?? 'Comprehensive professional healthcare training curriculum matching the PMF standards.' }}
        </p>
    </div>
    
    <div class="pt-4 border-t border-brand-border mt-auto">
        <a href="{{ route('courses.show', $program->code) }}" class="inline-flex items-center text-gold-700 font-bold text-xs hover:text-navy-900 transition-colors uppercase tracking-wider font-display group/link">
            Explore Curriculum
            <svg class="w-3.5 h-3.5 ml-1.5 transform group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</div>
