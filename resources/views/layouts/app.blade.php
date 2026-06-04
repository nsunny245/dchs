<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Daniyal Group of Colleges') - Where Success is a Tradition</title>
    <meta name="description" content="@yield('meta_description', 'Leading Health Sciences Education in Pakistan')">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:600,700" rel="stylesheet" />
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .dropdown-menu { transform: translateY(10px); opacity: 0; transition: all 0.2s ease; }
        .group:hover .dropdown-menu { transform: translateY(0); opacity: 1; }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Top Bar - Navy Blue -->
    <div class="bg-[#1e3a5f] text-white text-xs py-2">
        <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-4 flex-wrap justify-center">
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    +92 321-7729533
                </span>
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    info@daniyalgroupofcolleges.com
                </span>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <a href="{{ route('filament.admin.auth.login') }}" class="text-[#d4af37] hover:text-white transition font-medium">Staff Login</a>
                <span class="text-gray-400">|</span>
                <a href="#" class="text-[#d4af37] hover:text-white transition font-medium">Student Login</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo Only - Bigger Size -->
                <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                    <img src="{{ asset('images/dchs-logo.png') }}" alt="Daniyal Group of Colleges" class="h-20 w-auto hover:scale-105 transition-transform duration-300">
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2">Home</a>
                    
                    <div class="relative group" x-data="{ open: false }">
                        <button @mouseenter="open = true" @mouseleave="open = false" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2 flex items-center">
                            About Us
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" class="dropdown-menu absolute top-full left-0 mt-0 w-56 bg-white rounded-lg shadow-xl border-t-4 border-[#d4af37] overflow-hidden">
                            <div class="py-1">
                                <a href="#" class="block px-4 py-2.5 text-sm text-[#1e3a5f] hover:bg-[#f8f9fa] hover:text-[#d4af37]">Chairman's Message</a>
                                <a href="#" class="block px-4 py-2.5 text-sm text-[#1e3a5f] hover:bg-[#f8f9fa] hover:text-[#d4af37]">Vision & Mission</a>
                                <a href="#" class="block px-4 py-2.5 text-sm text-[#1e3a5f] hover:bg-[#f8f9fa] hover:text-[#d4af37]">Accreditation</a>
                                <a href="#" class="block px-4 py-2.5 text-sm text-[#1e3a5f] hover:bg-[#f8f9fa] hover:text-[#d4af37]">Leadership</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('campuses') }}" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2">Campuses</a>
                    
                    <div class="relative group" x-data="{ open: false }">
                        <button @mouseenter="open = true" @mouseleave="open = false" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2 flex items-center">
                            Programs
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" class="dropdown-menu absolute top-full left-0 mt-0 w-72 bg-white rounded-lg shadow-xl border-t-4 border-[#d4af37] overflow-hidden">
                            <div class="py-1 max-h-96 overflow-y-auto">
                                @forelse($globalPrograms as $program)
                                    <a href="{{ route('courses.show', $program->code) }}" class="block px-4 py-2.5 hover:bg-[#f8f9fa] border-b border-gray-50 last:border-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-[#d4af37] text-xs">{{ $program->code }}</span>
                                            <span class="text-[#1e3a5f] text-xs">{{ $program->name }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <span class="block px-4 py-2 text-xs text-gray-500 italic">No programs available</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('admissions') }}" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2">Admissions</a>
                    <a href="{{ route('contact') }}" class="text-[#1e3a5f] hover:text-[#d4af37] font-semibold text-sm transition px-3 py-2">Contact</a>

                    <a href="{{ route('admissions.apply') }}" class="bg-[#d4af37] hover:bg-[#c49b2e] text-white px-6 py-2 rounded-full font-bold text-sm transition shadow-md hover:shadow-lg">
                        Apply Now
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-[#1e3a5f] p-2">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak @click.away="mobileMenuOpen = false" class="lg:hidden bg-white border-t shadow-lg">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 text-[#1e3a5f] hover:bg-gray-50 hover:text-[#d4af37] rounded font-medium">Home</a>
                <a href="{{ route('campuses') }}" class="block px-3 py-2.5 text-[#1e3a5f] hover:bg-gray-50 hover:text-[#d4af37] rounded font-medium">Campuses</a>
                <a href="{{ route('courses') }}" class="block px-3 py-2.5 text-[#1e3a5f] hover:bg-gray-50 hover:text-[#d4af37] rounded font-medium">Programs</a>
                <a href="{{ route('admissions') }}" class="block px-3 py-2.5 text-[#1e3a5f] hover:bg-gray-50 hover:text-[#d4af37] rounded font-medium">Admissions</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2.5 text-[#1e3a5f] hover:bg-gray-50 hover:text-[#d4af37] rounded font-medium">Contact</a>
                <a href="{{ route('admissions.apply') }}" class="block mt-3 text-center bg-[#d4af37] text-white px-4 py-3 rounded-lg font-bold">Apply Now</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer - Navy Blue with Gold Accents -->
    <footer class="bg-[#1e3a5f] text-white pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <img src="{{ asset('images/dchs-logo.png') }}" alt="Daniyal Group" class="h-24 mb-4 bg-white p-3 rounded-lg">
                    <p class="text-gray-300 text-sm leading-relaxed mb-4">
                        Excellence in Health Sciences Education since inception. Shaping healthcare professionals with state-of-the-art facilities across Punjab.
                    </p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-8 h-8 rounded-full bg-[#2c5282] flex items-center justify-center hover:bg-[#d4af37] transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-[#2c5282] flex items-center justify-center hover:bg-[#d4af37] transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4 text-[#d4af37]">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-300 hover:text-[#d4af37] transition">About Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#d4af37] transition">Programs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#d4af37] transition">Admissions</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#d4af37] transition">Fee Structure</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#d4af37] transition">Student Portal</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4 text-[#d4af37]">Programs</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($globalPrograms->take(5) as $program)
                            <li><a href="{{ route('courses.show', $program->code) }}" class="text-gray-300 hover:text-[#d4af37] transition">{{ $program->code }} - {{ $program->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4 text-[#d4af37]">Contact</h3>
                    <ul class="space-y-3 text-sm text-gray-300">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 text-[#d4af37] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            @foreach($globalCampuses as $campus)
                                {{ $campus->city }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            +92 321-7729533
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#d4af37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            info@daniyalgroupofcolleges.com
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-[#2c5282] pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} Daniyal Group of Colleges. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0 text-sm text-gray-400">
                    <a href="#" class="hover:text-[#d4af37] transition">Privacy Policy</a>
                    <a href="#" class="hover:text-[#d4af37] transition">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- AOS Initialization -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-cubic',
            offset: 120
        });
    </script>
    @stack('scripts')
</body>
</html>
