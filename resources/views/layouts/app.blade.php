<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-sans antialiased">
    @include('components.toast')

    <div class="min-h-screen bg-background flex flex-col gap-5 p-5 md:flex-row" x-data="{ mobileSidebarOpen: false }">
        <!-- Mobile sidebar backdrop -->
        <div x-show="mobileSidebarOpen" x-transition.opacity x-cloak @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

        <!-- Page Content -->
        @include('components.sidebar')
        <main class="flex-1 min-w-0">
            @include('components.navbar')
            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>

</html>
