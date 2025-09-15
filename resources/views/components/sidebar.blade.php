<aside class="w-60 bg-white shadow-md fixed h-full z-10 dark:bg-gray-800"> <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Frotas Gov</h2>
    </div>

    <nav class="mt-5">
        <a href="{{ route('dashboard') }}"
           class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span class="mx-3">Painel</span>
        </a>

        <a href="#"
           class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
            <i class="fas fa-book w-6"></i>
            <span class="mx-3">Diário de Bordo</span>
        </a>

        @if(auth()->user()->role_id <= 2)
            <div x-data="{ open: false }" class="mt-4">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none">
                    <div class="flex items-center">
                        <i class="fas fa-car w-6"></i>
                        <span class="mx-3">Veículos</span>
                    </div>
                    <span>
                    <i class="fas" :class="{ 'fa-chevron-down': !open, 'fa-chevron-up': open }"></i>
                </span>
                </button>
                <div x-show="open" x-transition class="bg-gray-100 dark:bg-gray-700">
                    <a href="#" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">Cadastro de Veículos</a>
                    <a href="#" class="py-2 px-12 block text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">Controle de Status</a>
                </div>
            </div>
        @endif

        @if(auth()->user()->role_id == 1)
            <a href="#"
               class="flex items-center mt-4 py-2 px-6 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-users-cog w-6"></i>
                <span class="mx-3">Usuários</span>
            </a>
        @endif

    </nav>
</aside>
