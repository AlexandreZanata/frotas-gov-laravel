<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gerenciamento de Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- ================================================================== --}}
                    {{-- SEÇÃO DE PESQUISA COM BUSCA AUTOMÁTICA --}}
                    {{-- ================================================================== --}}
                    <form x-data="{
                        searchQuery: '',

                        // 1. A função de busca principal (imediata), chamada pelo Enter.
                        search() {
                            // Se a busca estiver vazia, recarrega a página para ver todos os usuários.
                            if (this.searchQuery.trim() === '') {
                                window.location.href = '{{ route('admin.users.index') }}';
                                return;
                            }
                            // A lógica de fetch que já está funcionando.
                            const url = `{{ route('ajax.search') }}?query=${encodeURIComponent(this.searchQuery)}&model=user`;
                            fetch(url)
                                .then(response => {
                                    if (!response.ok) throw new Error('A resposta da rede não foi OK.');
                                    return response.text();
                                })
                                .then(html => {
                                    const tableBody = document.getElementById('user-table-body');
                                    if (html.trim()) {
                                        tableBody.innerHTML = html;
                                    } else {
                                        tableBody.innerHTML = `<tr><td colspan='5' class='text-center py-4 text-gray-500'>Nenhum usuário encontrado.</td></tr>`;
                                    }
                                    const pagination = document.getElementById('pagination-wrapper');
                                    if (pagination) pagination.style.display = 'none';
                                })
                                .catch(error => {
                                    console.error('Erro na busca AJAX:', error);
                                    document.getElementById('user-table-body').innerHTML = `<tr><td colspan='5' class='text-center py-4 text-red-500'>Ocorreu um erro ao buscar os dados.</td></tr>`;
                                });
                        },

                        // 2. Nova função com 'debounce' (atraso) para a busca automática.
                        debouncedSearch: Alpine.debounce(function() {
                            // Só executa a busca se tiver ao menos 2 caracteres (ou se limpar o campo).
                            if (this.searchQuery.length >= 2 || this.searchQuery.length === 0) {
                                this.search();
                            }
                        }, 500) // Atraso de 500ms (meio segundo).
                    }"
                          @submit.prevent="search()" {{-- Continua funcionando para o "Enter" --}}
                          class="flex justify-between items-end mb-6">

                        {{-- Input de pesquisa --}}
                        <div class="w-full md:w-1/2">
                            <x-input-label for="search" :value="__('Pesquisar Usuário (Nome, Email ou CPF)')" />
                            <x-text-input
                                id="search"
                                class="block mt-1 w-full"
                                type="text"
                                name="q"
                                x-model="searchQuery"
                                @input="debouncedSearch()" {{-- 3. Chama a função com atraso a cada letra digitada --}}
                                placeholder="Digite para pesquisar..." />
                        </div>

                        {{-- Botão de Novo Usuário --}}
                        <a href="{{ route('admin.users.create') }}">
                            <x-primary-button type="button">
                                {{ __('Novo Usuário') }}
                            </x-primary-button>
                        </a>
                    </form>
                    {{-- ================================================================== --}}
                    {{-- FIM DA SEÇÃO ATUALIZADA --}}
                    {{-- ================================================================== --}}


                    {{-- Tabela de Usuários (continua igual) --}}
                    <div class="overflow-x-auto mt-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            {{-- ... Cabeçalhos da tabela ... --}}
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Perfil</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody id="user-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @include('partials.users-table-rows', ['users' => $users])
                            </tbody>
                        </table>
                    </div>

                    {{-- ... Restante do seu código (formulários de exclusão, paginação, modal) ... --}}
                    @foreach ($users as $user)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" id="delete-form-{{ $user->id }}" class="hidden">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="backup" value="true">
                        </form>
                    @endforeach

                    <div class="mt-4" id="pagination-wrapper">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-delete-user-modal />
</x-app-layout>
