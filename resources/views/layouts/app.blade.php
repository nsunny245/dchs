<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Daniyal Group of Colleges') - Where Success is a Tradition</title>
    <meta name="description" content="@yield('meta_description', 'Leading Health Sciences Education in Pakistan')">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Oswald:wght@600;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap" rel="stylesheet">
    
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
<body class="font-body antialiased bg-brand-surface text-navy-900">
    <x-navbar :global-programs="$globalPrograms" />

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <x-footer :global-programs="$globalPrograms" />

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
