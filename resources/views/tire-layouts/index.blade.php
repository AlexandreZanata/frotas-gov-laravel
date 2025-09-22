@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-object-group text-indigo-500"></i> Layouts de Pneus</h1>
        <div class="flex gap-2">
            <a href="{{ route('vehicle-categories.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Categorias</a>
        </div>
    </div>

    @if(isset($missingTable) && $missingTable)
        <div class="p-4 rounded bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 text-sm text-yellow-800 dark:text-yellow-300">
            <p class="font-semibold mb-1">Tabela não encontrada</p>
            <p>Parece que você ainda não executou as migrations que criam a tabela <code class="px-1 py-0.5 bg-yellow-200 dark:bg-yellow-800 rounded">tire_layouts</code>.</p>
            <p class="mt-2 font-medium">Execute no terminal:</p>
            <pre class="mt-2 p-2 bg-gray-900 text-gray-100 rounded text-xs overflow-auto">php artisan migrate
php artisan db:seed --class=TireLayoutSeeder</pre>
            <p class="mt-3 text-[11px] text-yellow-700 dark:text-yellow-400">Após rodar, recarregue esta página.</p>
        </div>
    @else
        @if(session('success'))
            <div class="p-3 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex justify-end">
            <a href="{{ route('tire-layouts.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm"><i class="fas fa-plus"></i> Novo Layout</a>
        </div>
        <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-2">Nome</th>
                        <th class="px-4 py-2">Código</th>
                        <th class="px-4 py-2">Posições</th>
                        <th class="px-4 py-2">Descrição</th>
                        <th class="px-4 py-2 w-32">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($layouts as $l)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 text-gray-800 dark:text-gray-100">
                            <td class="px-4 py-2 font-medium">{{ $l->name }}</td>
                            <td class="px-4 py-2">{{ $l->code }}</td>
                            <td class="px-4 py-2">{{ is_array($l->positions) ? count($l->positions) : '-' }}</td>
                            <td class="px-4 py-2 truncate max-w-xs" title="{{ $l->description }}">{{ \Illuminate\Support\Str::limit($l->description,40) }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="{{ route('tire-layouts.edit',$l) }}" class="text-indigo-600 dark:text-indigo-400" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('tire-layouts.destroy',$l) }}" onsubmit="return confirm('Remover layout?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 dark:text-red-400" title="Excluir"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Nenhum layout cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $layouts->links() }}</div>

        <details class="bg-white dark:bg-gray-800 rounded shadow p-4 text-sm">
            <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">Ajuda - Formato do JSON de posições</summary>
            <div class="mt-3 space-y-2 text-gray-600 dark:text-gray-300">
                <p>Cada posição deve conter pelo menos <code class="px-1 bg-gray-200 dark:bg-gray-700 rounded">code</code> e opcionalmente <code>label</code>, <code>x</code> e <code>y</code> (percentuais 0–100). Se <code>x</code>/<code>y</code> forem omitidos, o layout cai no modo grade.</p>
                <pre class="p-3 rounded bg-gray-900 text-gray-100 overflow-auto text-xs">[
  {"code":"FL","label":"Dianteiro Esquerdo","x":20,"y":30},
  {"code":"FR","label":"Dianteiro Direito","x":80,"y":30},
  {"code":"RL","label":"Traseiro Esquerdo","x":20,"y":75},
  {"code":"RR","label":"Traseiro Direito","x":80,"y":75}
]</pre>
            </div>
        </details>
    @endif
</div>
@endsection
