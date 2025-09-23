<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Produtos de Óleo">
            <a href="{{ route('oil-products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium shadow">
                <i class="fas fa-plus"></i> Novo Produto
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <form method="GET" class="flex gap-2 w-full md:w-2/3">
                <div class="flex-1 relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar: nome, código, marca ou viscosidade" class="w-full pl-10 pr-3 py-2 rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
                </div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow">Filtrar</button>
                @if($search)
                    <a href="{{ route('oil-products.index') }}" class="px-3 py-2 bg-gray-300 dark:bg-gray-700 dark:text-gray-100 rounded text-sm">Limpar</a>
                @endif
            </form>
            <div class="text-xs text-gray-500 dark:text-gray-400 md:text-right md:w-1/3">
                <p><span class="font-semibold">Total:</span> {{ $products->total() }} itens</p>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-800/70 backdrop-blur rounded border border-gray-200 dark:border-gray-700 shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">Produto</th>
                        <th class="px-4 py-2 text-left">Código</th>
                        <th class="px-4 py-2 text-left">Marca</th>
                        <th class="px-4 py-2 text-left">Visc.</th>
                        <th class="px-4 py-2 text-right">Estoque</th>
                        <th class="px-4 py-2 text-right">Reposição</th>
                        <th class="px-4 py-2 text-right">Custo (R$)</th>
                        <th class="px-4 py-2 text-right">Int. KM</th>
                        <th class="px-4 py-2 text-right">Int. Dias</th>
                        <th class="px-4 py-2 text-center w-40">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-700 dark:text-gray-100">
                @forelse($products as $p)
                    @php($low=$p->isLowStock())
                    <tr @class([
                        'hover:bg-gray-50 dark:hover:bg-gray-700/40 transition',
                        'bg-red-50/70 dark:bg-red-900/25'=>$low
                    ])>
                        <td class="px-4 py-2 font-medium"> {{-- já herda dark claro --}}
                            <div class="flex items-center gap-2">
                                <span>{{ $p->display_name }}</span>
                                @if($low)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-600 text-white font-semibold tracking-wide">LOW</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ $p->code ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $p->brand ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $p->viscosity ?? '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $p->stock_quantity ?? '0' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $p->reorder_level ?? '0' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ isset($p->unit_cost) ? number_format($p->unit_cost,2,',','.') : '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $p->recommended_interval_km ?? '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $p->recommended_interval_days ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-3 text-xs">
                                <a href="{{ route('oil-products.history',$p) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline" title="Histórico"><i class="fas fa-clock-rotate-left"></i></a>
                                <a href="{{ route('oil-products.edit',$p) }}" class="text-blue-600 dark:text-blue-400 hover:underline" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('oil-products.destroy',$p) }}" onsubmit="return confirm('Confirmar exclusão do produto?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline" title="Excluir"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400 text-sm">Nenhum produto encontrado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </x-page-container>
</x-app-layout>
