@props([
    'globalPrograms' => []
])

<footer class="bg-navy-950 text-white pt-20 pb-10 border-t-4 border-gold-500 relative overflow-hidden font-body">
    <!-- Subtle watermark background -->
    <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-gold-500/5 rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <!-- Logo & Tagline Row (Dedicating top of footer layout) -->
        <div class="mb-12 border-b border-navy-900 pb-8 flex flex-col items-center md:items-start">
            <x-logo-mark size="footer" />
            <h3 class="font-tagline text-gold-500 uppercase tracking-widest text-sm mt-4">Where Success Is a Tradition</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- Brand Description & Socials Column -->
            <div class="space-y-6">
                <p class="text-navy-200 text-xs leading-relaxed max-w-sm">
                    Daniyal Group of Colleges is Punjab's premier allied health sciences institution, preparing medical students for clinical excellence.
                </p>
                <div class="flex space-x-4 pt-2">
                    <!-- Social icons (Placeholder SVGs) -->
                    <a href="#" class="w-8 h-8 rounded-full bg-navy-900 border border-navy-800 flex items-center justify-center text-gold-500 hover:bg-gold-500 hover:text-navy-900 transition-colors">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-navy-900 border border-navy-800 flex items-center justify-center text-gold-500 hover:bg-gold-500 hover:text-navy-900 transition-colors">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="space-y-6">
                <h4 class="font-display font-bold text-sm tracking-wider uppercase text-white relative pb-3 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-8 after:h-0.5 after:bg-gold-500">Quick Links</h4>
                <ul class="space-y-3.5 text-xs text-navy-200">
                    <li><a href="{{ route('home') }}" class="hover:text-gold-500 transition">Home Page</a></li>
                    <li><a href="{{ route('about.chairmans-message') }}" class="hover:text-gold-500 transition">Chairman's Message</a></li>
                    <li><a href="{{ route('about.vision-mission') }}" class="hover:text-gold-500 transition">Vision & Mission</a></li>
                    <li><a href="{{ route('about.accreditation') }}" class="hover:text-gold-500 transition">Accreditations</a></li>
                    <li><a href="{{ route('about.leadership') }}" class="hover:text-gold-500 transition">Leadership Team</a></li>
                    <li><a href="{{ route('admissions') }}" class="hover:text-gold-500 transition">Admissions Process</a></li>
                </ul>
            </div>

            <!-- Programs Links -->
            <div class="space-y-6">
                <h4 class="font-display font-bold text-sm tracking-wider uppercase text-white relative pb-3 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-8 after:h-0.5 after:bg-gold-500">Programs</h4>
                <ul class="space-y-3.5 text-xs text-navy-200">
                    @forelse($globalPrograms->take(6) as $program)
                        <li><a href="{{ route('courses.show', $program->code) }}" class="hover:text-gold-500 transition">{{ $program->name }} ({{ $program->code }})</a></li>
                    @empty
                        <li class="italic text-navy-400">Programs catalog is empty</li>
                    @endforelse
                </ul>
            </div>

            <!-- Campuses & Contact -->
            <div class="space-y-6">
                <h4 class="font-display font-bold text-sm tracking-wider uppercase text-white relative pb-3 after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-8 after:h-0.5 after:bg-gold-500">Get in Touch</h4>
                <ul class="space-y-4 text-xs text-navy-200">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2.5 text-gold-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>Okara · Sahiwal · Depalpur · Chichawatni (Punjab)</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.187-4.165-7-7l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        <span>+92 321-7729533</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2.5 text-gold-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span class="break-all">info@daniyalgroupofcolleges.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-navy-900 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-navy-400 gap-4">
            <p>&copy; {{ date('Y') }} Daniyal Group of Colleges. All rights reserved.</p>
            <div class="flex space-x-6">
                <a href="#" class="hover:text-gold-500 transition">Privacy Policy</a>
                <a href="#" class="hover:text-gold-500 transition">Terms of Service</a>
            </div>
        </div>
    </div>

    <!-- Persistent Mobile Bottom CTA Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-navy-900 border-t-2 border-gold-500 py-3 px-6 flex justify-between items-center z-40 shadow-xl">
        <div class="text-left">
            <span class="block text-[10px] text-navy-300 uppercase tracking-widest font-display font-bold">Admissions 2026</span>
            <span class="text-xs text-white font-semibold">Join Allied Health Programs</span>
        </div>
        <a href="{{ route('admissions.apply') }}" class="btn btn-accent rounded-full text-xs py-2 px-5 font-bold uppercase tracking-wider shadow">
            Apply Now
        </a>
    </div>
</footer>
