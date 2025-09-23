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

@if(!request()->routeIs('chat.index'))
<div x-data="chatFab()" x-init="init()" x-cloak class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40">
    <button @click="goChat()" class="relative flex items-center justify-center rounded-full shadow-lg ring-2 ring-white/40 dark:ring-gray-900/40 transition focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-700"
        :class="isMobile ? 'w-11 h-11 bg-blue-600 text-white shadow-md' : 'w-14 h-14 bg-blue-600 text-white'" aria-label="Abrir Chat">
        <i class="fas fa-comments text-lg sm:text-xl"></i>
        <span x-show="unread>0" class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[20px] h-[20px] flex items-center justify-center px-1" x-text="unreadDisplay"></span>
        <span class="sr-only" x-text="unread>0 ? (unread+' mensagens não lidas') : 'Sem novas mensagens'"></span>
    </button>
</div>
@endif

{{-- Scripts extras: section ou variável --}}
@if(View::hasSection('scripts'))
    @yield('scripts')
@elseif(isset($scripts))
    {{ $scripts }}
@endif

@if(auth()->check())
<script>window.APP_USER_ID={{ auth()->id() }};</script>
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
    function chatFab(){
        return {
            unread:0, unreadDisplay:0, timer:null, isMobile:false, echoBound:false,
            init(){
                this.isMobile=window.innerWidth<640; this.fetchUnread(); this.timer=setInterval(()=>this.fetchUnread(),30000);
                window.addEventListener('resize',()=>{ this.isMobile=window.innerWidth<640; });
                this.bindEcho();
            },
            bindEcho(){
                if(this.echoBound || !window.Echo || !window.APP_USER_ID) return; this.echoBound=true;
                window.Echo.private('chat.unread.'+window.APP_USER_ID)
                    .listen('ChatUnreadPing', (e)=>{ this.fetchUnread(true,e); });
            },
            fetchUnread(immediate=false,eventData=null){
                fetch('{{ route('api.chat.unread.summary') }}')
                    .then(r=>r.json())
                    .then(j=>{ this.unread=j.total||0; this.unreadDisplay=this.unread>99?'99+':this.unread; if(eventData){ this.maybeBeep(eventData); } })
                    .catch(()=>{});
            },
            goChat(){ window.location='{{ route('chat.index') }}'; },
            maybeBeep(e){
                try {
                    const muted = JSON.parse(localStorage.getItem('chat.muted')||'[]');
                    if(muted.includes(e.conversation_id)) return; // conversa mutada
                    // gerar beep simples
                    const ctx = new (window.AudioContext||window.webkitAudioContext)();
                    const o = ctx.createOscillator(); const g=ctx.createGain();
                    o.type='sine'; o.frequency.value=880; o.connect(g); g.connect(ctx.destination); g.gain.setValueAtTime(0.001,ctx.currentTime); g.gain.exponentialRampToValueAtTime(0.2,ctx.currentTime+0.01); g.gain.exponentialRampToValueAtTime(0.0001,ctx.currentTime+0.4); o.start(); o.stop(ctx.currentTime+0.42);
                } catch(err){ /* ignore */ }
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
