<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Histórico de Alterações (Auditoria)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-6">
                        <div class="flex items-end">
                            <div class="w-full md:w-1/3">
                                <x-input-label for="search" :value="__('Pesquisar por Ação, Tabela ou Usuário')" />
                                <x-text-input id="search" name="search" class="block mt-1 w-full" type="text"
                                              :value="request('search')"
                                              placeholder="Digite para pesquisar..." />
                            </div>
                            <x-primary-button class="ml-3">
                                Pesquisar
                            </x-primary-button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Usuário (Ator)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Ação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Tabela</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">ID do Registro</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->actor->name ?? 'Sistema' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->action }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->table_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $log->record_id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhum log encontrado.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
