<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Frotas Gov') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" x-data="{ sidebarOpen: true }">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">

    <div class="fixed h-full z-30 transition-transform duration-300 ease-in-out"
         :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
        <x-sidebar />
    </div>

    <div class="flex-1 transition-all duration-300 ease-in-out"
         :class="{'lg:ml-60': sidebarOpen, 'lg:ml-0': !sidebarOpen}">

        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
</div>

@isset($scripts)
    {{ $scripts }}
@endisset
</body>
</html>
