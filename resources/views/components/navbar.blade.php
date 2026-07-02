@props([
    'globalPrograms' => []
])

<nav class="bg-white border-b border-brand-border sticky top-0 z-50 transition-all duration-300 shadow-sm"
     x-data="{ mobileMenuOpen: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.pageYOffset > 20 })"
     :class="{ 'py-1 shadow-md': scrolled, 'py-3': !scrolled }">
    <!-- Top Utility Bar (Solid navy bg-navy-950, visible) -->
    <div class="bg-navy-950 text-white text-[11px] py-2 transition-all duration-300 font-body">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center space-x-6">
                <span class="flex items-center text-navy-200">
                    <svg class="w-3.5 h-3.5 mr-2 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.187-4.165-7-7l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    +92 321-7729533
                </span>
                <span class="flex items-center text-navy-200">
                    <svg class="w-3.5 h-3.5 mr-2 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    info@daniyalgroupofcolleges.com
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('filament.admin.auth.login') }}" class="text-gold-500 hover:text-white transition font-semibold">Staff Login</a>
                <span class="text-navy-700">|</span>
                <a href="#" class="text-gold-500 hover:text-white transition font-semibold">Student Login</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-center">
            <!-- Logo Mark with Size prop -->
            <a href="{{ route('home') }}" class="flex items-center py-2">
                <x-logo-mark size="nav" />
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-4 font-display">
                <a href="{{ route('home') }}" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 nav-link-hover">Home</a>
                
                <!-- About Us Dropdown -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                    <button @click="open = !open" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 flex items-center nav-link-hover">
                        About Us
                        <svg class="w-3 h-3 ml-1 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="absolute top-full left-0 w-56 bg-white rounded-md shadow-lg border-t-4 border-gold-500 overflow-hidden z-50 font-body">
                        <div class="py-1">
                            <a href="{{ route('about.chairmans-message') }}" class="block px-4 py-3.5 text-[13px] font-semibold text-navy-900 hover:bg-navy-50 hover:text-gold-700">Chairman's Message</a>
                            <a href="{{ route('about.vision-mission') }}" class="block px-4 py-3.5 text-[13px] font-semibold text-navy-900 hover:bg-navy-50 hover:text-gold-700">Vision & Mission</a>
                            <a href="{{ route('about.accreditation') }}" class="block px-4 py-3.5 text-[13px] font-semibold text-navy-900 hover:bg-navy-50 hover:text-gold-700">Accreditation</a>
                            <a href="{{ route('about.leadership') }}" class="block px-4 py-3.5 text-[13px] font-semibold text-navy-900 hover:bg-navy-50 hover:text-gold-700">Leadership</a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('campuses') }}" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 nav-link-hover">Campuses</a>
                
                <!-- Programs Dropdown -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                    <button @click="open = !open" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 flex items-center nav-link-hover">
                        Programs
                        <svg class="w-3 h-3 ml-1 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="absolute top-full left-1/2 transform -translate-x-1/2 w-80 bg-white rounded-md shadow-lg border-t-4 border-gold-500 overflow-hidden z-50 font-body">
                        <div class="py-1 max-h-[350px] overflow-y-auto">
                            @forelse($globalPrograms as $program)
                                <a href="{{ route('courses.show', $program->code) }}" class="block px-4 py-3 hover:bg-navy-50 border-b border-navy-50 last:border-0">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gold-700 text-xs tracking-wider">{{ $program->code }}</span>
                                        <span class="text-navy-900 text-xs font-semibold">{{ $program->name }}</span>
                                    </div>
                                </a>
                            @empty
                                <span class="block px-4 py-3 text-xs text-navy-400 italic">No programs loaded</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <a href="{{ route('admissions') }}" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 nav-link-hover">Admissions</a>
                <a href="{{ route('contact') }}" class="text-navy-900 hover:text-gold-700 font-bold text-[13px] px-3 py-2 nav-link-hover">Contact</a>

                <a href="{{ route('admissions.apply') }}" class="btn btn-accent rounded-full text-xs uppercase tracking-wider font-semibold py-2.5 px-6 shadow-sm hover:shadow hover:scale-[1.03] transition-all ml-4">
                    Apply Now
                </a>
            </div>

            <!-- Hamburger Button on Mobile -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-navy-900 p-2 focus:outline-none">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Full-screen Overlay Menu -->
    <div x-show="mobileMenuOpen"
         x-transition.opacity.duration.300ms
         class="fixed inset-0 bg-navy-950/98 z-40 lg:hidden flex flex-col justify-between pt-24 pb-8 px-8 text-white font-display"
         style="display: none;">
        <!-- Large centered crest logo at the top of overlay -->
        <div class="flex flex-col items-center mt-4">
            <x-logo-mark size="mobile-overlay" />
            <h2 class="text-gold-500 font-tagline uppercase tracking-widest text-sm mt-3">Where Success Is a Tradition</h2>
        </div>

        <div class="flex-1 flex flex-col justify-center space-y-6 text-center text-lg mt-6">
            <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="hover:text-gold-500 transition font-bold">Home</a>
            <a href="{{ route('campuses') }}" @click="mobileMenuOpen = false" class="hover:text-gold-500 transition font-bold">Campuses</a>
            <a href="{{ route('courses') }}" @click="mobileMenuOpen = false" class="hover:text-gold-500 transition font-bold">Programs</a>
            <a href="{{ route('admissions') }}" @click="mobileMenuOpen = false" class="hover:text-gold-500 transition font-bold">Admissions</a>
            <a href="{{ route('contact') }}" @click="mobileMenuOpen = false" class="hover:text-gold-500 transition font-bold">Contact Us</a>
        </div>

        <div class="space-y-4">
            <a href="{{ route('admissions.apply') }}" @click="mobileMenuOpen = false" class="block w-full bg-gold-500 text-navy-900 py-4 rounded-full text-center font-bold text-sm uppercase tracking-wider shadow-lg">
                Apply Online Now
            </a>
            <div class="flex justify-center space-x-6 text-xs text-navy-300 font-body">
                <a href="{{ route('filament.admin.auth.login') }}">Staff Area</a>
                <span>·</span>
                <a href="#">Student Portal</a>
            </div>
        </div>
    </div>
</nav>
