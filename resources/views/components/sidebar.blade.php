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
           class="flex items-center  py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span class="mx-3">Painel</span>
        </a>

        <a href="{{ route('diario.index') }}"
           class="flex items-center  py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('diario.*') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
            <i class="fas fa-book w-6"></i>
            <span class="mx-3">Diário de Bordo</span>
        </a>

        <a href="{{ route('fuel-surveys.index') }}"
           class="flex items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('fuel-surveys.*') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
            <i class="fas fa-gas-pump w-6"></i>
            <span class="mx-3">Cotações</span>
        </a>

        @if(auth()->user() && auth()->user()->role_id <= 2)
            <div x-data="{ open: {{ request()->routeIs('vehicles.*') || request()->routeIs('vehicle-categories.*') || request()->routeIs('vehicles.blocking') ? 'true' : 'false' }} }" class="">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ request()->routeIs('vehicles.*') || request()->routeIs('vehicle-categories') || request()->routeIs('vehicles.blocking') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-car w-6"></i>
                        <span class="mx-3">Veículos</span>
                    </div>
                    <span><i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i></span>
                </button>
                <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                    <a href="{{ route('vehicles.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicles.index', 'vehicles.edit') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Gerenciar Frota
                    </a>
                    <a href="{{ route('vehicles.create') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicles.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Cadastro de Veículos</a>
                    <a href="{{ route('vehicle-categories.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicle-categories.*')  ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Gerenciar Categorias</a>
                    @if(auth()->user()->role_id == 1)
                        <a href="{{ route('vehicles.blocking') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('vehicles.blocking') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Bloquear Veículos</a>
                    @endif
                </div>
            </div>
        @endif

        @if(auth()->user()->role_id == 1)
            <div x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.default-passwords.*') || request()->routeIs('admin.backups.*') ? 'true' : 'false' }} }" class="">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.default-passwords.*') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-users-cog w-6"></i>
                        <span class="mx-3">Usuários</span>
                    </div>
                    <span>
                        <i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                    </span>
                </button>
                <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                    <a href="{{ route('admin.users.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.edit') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Listar Usuários
                    </a>
                    <a href="{{ route('admin.users.create') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('admin.users.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Novo Usuário
                    </a>
                    <a href="{{ route('admin.default-passwords.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('admin.default-passwords.*') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Senhas Padrão
                    </a>
                    <a href="{{ route('admin.backups.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('admin.backups.*') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Backups de Usuários
                    </a>
                </div>
            </div>
        @endif

        @if(auth()->user()->role_id == 1)
            <div x-data="{ open: {{ request()->routeIs('pdf-templates.*') || request()->routeIs('reports.fuel.*') ? 'true' : 'false' }} }" class="">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ request()->routeIs('pdf-templates.*') || request()->routeIs('reports.fuel.*') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-file-alt w-6"></i>
                        <span class="mx-3">Relatórios</span>
                    </div>
                    <span>
                        <i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                    </span>
                </button>
                <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                    <a href="{{ route('pdf-templates.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('pdf-templates.*') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Modelos PDF
                    </a>
                    <a href="{{ route('reports.fuel.index') }}"
                       class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('reports.fuel.*') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">
                        Combustível
                    </a>
                </div>
            </div>
        @endif

        <div x-data="{ open: {{ request()->routeIs('oil.*') || request()->routeIs('oil-products.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ (request()->routeIs('oil.*') || request()->routeIs('oil-products.*')) ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                <div class="flex items-center">
                    <i class="fas fa-oil-can w-6"></i>
                    <span class="mx-3">Óleo</span>
                </div>
                <span><i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i></span>
            </button>
            <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                <a href="{{ route('oil.maintenance') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('oil.maintenance') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Dashboard</a>
                <a href="{{ route('oil.logs') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('oil.logs') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Histórico</a>
                <a href="{{ route('oil-products.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('oil-products.*') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Produtos</a>
            </div>
        </div>

        <div x-data="{ open: {{ (request()->routeIs('tires.*') || request()->routeIs('tire-layouts.*')) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ (request()->routeIs('tires.*') || request()->routeIs('tire-layouts.*')) ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                <div class="flex items-center">
                    <i class="fas fa-circle w-6"></i>
                    <span class="mx-3">Pneus</span>
                </div>
                <span><i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i></span>
            </button>
            <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                <a href="{{ route('tires.dashboard') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('tires.dashboard') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Dashboard</a>
                <a href="{{ route('tires.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('tires.index') || request()->routeIs('tires.edit') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Gerenciar</a>
                <a href="{{ route('tires.attention') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('tires.attention') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Atenção</a>
                @can('create', App\Models\Tire::class)
                    <a href="{{ route('tires.create') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('tires.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Novo Pneu</a>
                @endcan
                <a href="{{ route('tire-layouts.create') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('tire-layouts.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Novo Layout de Pneus</a>
            </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('fines.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none {{ request()->routeIs('fines.*') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                <div class="flex items-center">
                    <i class="fas fa-gavel w-6"></i>
                    <span class="mx-3">Multas</span>
                </div>
                <span><i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i></span>
            </button>
            <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-900">
                <a href="{{ route('fines.index') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('fines.index') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Lista</a>
                @can('create', App\Models\Fine::class)
                    <a href="{{ route('fines.create') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('fines.create') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Nova Multa</a>
                @endcan
                @if(auth()->user()->role_id == 4)
                    <a href="{{ route('fines.pending') }}" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 {{ request()->routeIs('fines.pending') ? 'bg-gray-300 dark:bg-gray-600' : '' }}">Pendentes</a>
                @endif
            </div>
        </div>

        <a href="{{ route('chat.index') }}" class="flex items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ request()->routeIs('chat.index') ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
            <i class="fas fa-comments w-6"></i>
            <span class="mx-3">Chat</span>
        </a>
    </nav>
</aside>
