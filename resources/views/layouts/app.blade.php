<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ init(){ const t=localStorage.getItem('theme'); if(t==='dark'){ document.documentElement.classList.add('dark'); } } }" x-init="init()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Frotas Gov') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Esconde elementos com x-cloak até que o Alpine.js seja inicializado */
        [x-cloak] { display: none !important; }

        #loading-overlay { transition: opacity 0.5s ease-out; }
        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3b82f6;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="font-sans antialiased">
<x-alert-messages />
<div id="loading-overlay" class="fixed inset-0 bg-white dark:bg-gray-900 z-50 flex items-center justify-center">
    <div class="loader"></div>
</div>

<div x-data="layout()" x-cloak class="min-h-screen bg-gray-100 dark:bg-gray-900">

    <x-sidebar />

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black bg-opacity-50 md:hidden" style="display: none;"></div>

    <div class="flex flex-col flex-1 transition-all duration-300 ease-in-out"
         :class="{ 'md:ml-60': sidebarOpen }">

        @include('layouts.navigation')

        {{-- Header: suporta tanto $header (component) quanto section('header') --}}
        @if (isset($header) || View::hasSection('header'))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @if(View::hasSection('header'))
                        @yield('header')
                    @elseif(isset($header))
                        {{ $header }}
                    @endif
                </div>
            </header>
        @endif

        <main>
            {{-- Conteúdo principal: se existir $slot (uso como componente) usa; caso contrário usa section content --}}
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>
</div>

{{-- Scripts extras: section ou variável --}}
@if(View::hasSection('scripts'))
    @yield('scripts')
@elseif(isset($scripts))
    {{ $scripts }}
@endif

<script>
    function layout() {
        return {
            sidebarOpen: window.innerWidth > 768 ? (localStorage.getItem('sidebarOpen') !== 'false') : false,
            theme: (localStorage.getItem('theme') === 'dark') ? 'dark' : 'light',
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                if (window.innerWidth > 768) {
                    localStorage.setItem('sidebarOpen', this.sidebarOpen);
                }
            },
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                if (this.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                localStorage.setItem('theme', this.theme);
            }
        }
    }

    window.addEventListener('load', () => {
        const loader = document.getElementById('loading-overlay');
        loader.style.opacity = '0';
        setTimeout(() => loader.style.display = 'none', 500);
    });
</script>
@stack('scripts')
<x-confirm-modal />
</body>
</html>
