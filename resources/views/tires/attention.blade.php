<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pneus em Atenção / Críticos" icon="fas fa-triangle-exclamation">
            <a href="{{ route('tires.dashboard') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="max-w-6xl mx-auto p-4">
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-2">Série</th>
                            <th class="px-4 py-2">Marca</th>
                            <th class="px-4 py-2">Modelo</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Uso (%)</th>
                            <th class="px-4 py-2">Veículo</th>
                            <th class="px-4 py-2 w-20">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-100">
                        @forelse($tires as $t)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 py-2 font-medium">{{ $t->serial_number }}</td>
                                <td class="px-4 py-2">{{ $t->brand }}</td>
                                <td class="px-4 py-2">{{ $t->model }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium @class([
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'=> $t->status==='attention',
                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'=> $t->status==='critical',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'=> $t->status==='recap_out'
                                    ])">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                                </td>
                                <td class="px-4 py-2">{{ $t->life_usage_percent !== null ? $t->life_usage_percent.'%' : '—' }}</td>
                                <td class="px-4 py-2">{{ $t->vehicle?->plate ?? $t->vehicle?->prefix }}</td>
                                <td class="px-4 py-2 flex gap-2">
                                    @can('update',$t)
                                        <a href="{{ route('tires.edit',$t) }}" class="text-blue-600 dark:text-blue-400" title="Editar"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('delete',$t)
                                        <form method="POST" action="{{ route('tires.destroy',$t) }}" onsubmit="return confirm('Excluir pneu?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Nenhum pneu em atenção.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $tires->links() }}</div>
        </div>
    </x-page-container>
</x-app-layout>
