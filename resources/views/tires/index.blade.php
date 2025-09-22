<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pneus" icon="fas fa-circle">
            @can('create', App\Models\Tire::class)
                <a href="{{ route('tires.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium shadow">
                    <i class="fas fa-plus"></i> Novo
                </a>
            @endcan
        </x-page-header>
    </x-slot>

    <x-page-container>
        {{-- Filtros / Busca --}}
        <form method="GET" action="{{ route('tires.index') }}" class="flex flex-wrap gap-2 mb-4 items-end">
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar série, marca, modelo..." class="w-full md:w-64 rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500" />
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm rounded">Filtrar</button>
                @if($search)
                    <a href="{{ route('tires.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-sm rounded text-gray-700 dark:text-gray-200">Limpar</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-2">Série</th>
                        <th class="px-4 py-2">Marca</th>
                        <th class="px-4 py-2">Modelo</th>
                        <th class="px-4 py-2">Dimensão</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Veículo</th>
                        <th class="px-4 py-2 w-24">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-100">
                    @forelse($tires as $t)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-2 font-medium">{{ $t->serial_number }}</td>
                            <td class="px-4 py-2">{{ $t->brand }}</td>
                            <td class="px-4 py-2">{{ $t->model }}</td>
                            <td class="px-4 py-2">{{ $t->dimension }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium @class([
                                      'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'=> $t->status==='stock',
                                      'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'=> $t->status==='in_use',
                                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'=> $t->status==='attention',
                                      'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'=> $t->status==='critical',
                                      'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'=> $t->status==='recap_out'
                                    ])">{{ ucfirst(str_replace('_',' ', $t->status)) }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $t->vehicle?->plate ?? $t->vehicle?->prefix }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                @can('update',$t)
                                    <a href="{{ route('tires.edit',$t) }}" class="text-blue-600 dark:text-blue-400 hover:underline" title="Editar"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('delete',$t)
                                    <form method="POST" action="{{ route('tires.destroy',$t) }}" onsubmit="return confirm('Excluir pneu?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline" title="Excluir"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Nenhum pneu encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tires->links() }}</div>
    </x-page-container>
</x-app-layout>
