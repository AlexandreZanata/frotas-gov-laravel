<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Multas" icon="fas fa-gavel">
            @can('create', App\Models\Fine::class)
                <a href="{{ route('fines.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-semibold uppercase tracking-wide">
                    <i class="fas fa-plus"></i> Nova Multa
                </a>
            @endcan
            <a href="{{ route('fines.index',['q'=>request('q'),'status'=>'pago']) }}" class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-500 text-white rounded text-xs font-semibold">
                <i class="fas fa-check-circle"></i> Pagas
            </a>
            <a href="{{ route('fines.index',['q'=>request('q'),'status'=>'aguardando_pagamento']) }}" class="inline-flex items-center gap-2 px-3 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded text-xs font-semibold">
                <i class="fas fa-clock"></i> Aguardando
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form method="get" class="flex gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-72">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar: Auto, Veículo, Condutor" class="w-full pl-9 pr-3 py-2 rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
                </div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-medium">Filtrar</button>
                @if($search)
                    <a href="{{ route('fines.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Limpar</a>
                @endif
            </form>
            <div class="flex flex-wrap gap-2">
                <span class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Total: {{ $fines->total() }}</span>
            </div>
        </div>

        @if(session('success'))
            <x-alert-messages type="success" :message="session('success')" />
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Auto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Veículo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Condutor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Valor</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($fines as $fine)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">#{{ $fine->id }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $fine->auto_number }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fine->vehicle?->plate ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $fine->driver?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><x-fine-status-badge :status="$fine->status" /></td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800 dark:text-gray-100">R$ {{ number_format($fine->total_amount,2,',','.') }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-3">
                                    <a href="{{ route('fines.show',$fine) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Ver</a>
                                    @can('update',$fine)
                                        <a href="{{ route('fines.edit',$fine) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Editar</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma multa encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $fines->links() }}</div>
    </x-page-container>
</x-app-layout>
