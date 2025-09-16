<aside
    class="fixed inset-y-0 z-30 flex flex-col flex-shrink-0 w-60 max-w-full overflow-y-auto bg-white dark:bg-gray-800 shadow-md
           transition-transform duration-300 ease-in-out transform
           right-0 md:left-0"
    :class="{
        'translate-x-0': sidebarOpen,
        'translate-x-full md:-translate-x-full': !sidebarOpen
    }"
>
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Frotas Gov</h2>
        <button @click="sidebarOpen = false" class="p-1 rounded-md md:hidden text-gray-400 hover:text-gray-200 focus:outline-none">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 mt-5">
        <a href="{{ route('dashboard') }}"
           class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span class="mx-3">Painel</span>
        </a>

        <a href="#"
           class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
            <i class="fas fa-book w-6"></i>
            <span class="mx-3">Diário de Bordo</span>
        </a>

        @if(auth()->user() && auth()->user()->role_id <= 2)
            {{-- Dropdown "Veículos" com estado ativo --}}
            <div x-data="{ open: {{ request()->routeIs('vehicles.*') || request()->routeIs('vehicle-categories.*') ? 'true' : 'false' }} }" class="mt-4">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ request()->routeIs('vehicles.*') || request()->routeIs('vehicle-categories') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-car w-6"></i>
                        <span class="mx-3">Veículos</span>
                    </div>
                    <span><i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i></span>
                </button>
                <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                    <a href="{{ route('vehicles.create') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicles.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Cadastro de Veículos</a>
                    <a href="{{ route('vehicle-categories.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicle-categories') ? 'bg-gray-300 dark:hover:bg-gray-600' : '' }}">Gerenciar Categorias</a>
                    <a href="{{ route('vehicles.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicles.index') ? 'bg-gray-300 dark:hover:bg-gray-600' : '' }}">Controle de Status</a>
                </div>
            </div>
        @endif

        @if(auth()->user() && auth()->user()->role_id == 1)
            <a href="#"
               class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-users-cog w-6"></i>
                <span class="mx-3">Usuários</span>
            </a>
        @endif
    </nav>
</aside>
