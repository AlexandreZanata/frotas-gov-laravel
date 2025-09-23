<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Histórico do Produto" description="Todas as alterações auditadas (criação, edição e exclusão)." icon="fas fa-clock-rotate-left">
            <a href="{{ route('oil-products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded text-xs font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="{{ route('oil-products.edit',$product) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">
                <i class="fas fa-edit"></i> Editar
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container innerClass="space-y-8">
        {{-- Resumo do Produto --}}
        <section class="bg-white dark:bg-gray-800/70 backdrop-blur border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wide">Resumo Atual</h3>
            <div class="grid md:grid-cols-4 gap-4 text-xs">
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Nome</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->display_name }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Código</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->code ?? '—' }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Marca</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->brand ?? '—' }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Viscosidade</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->viscosity ?? '—' }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Estoque</p><p class="font-semibold {{ $product->isLowStock() ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-100' }}">{{ $product->stock_quantity }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Reposição</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->reorder_level }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Int. KM (override)</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->recommended_interval_km ?? 'Categoria' }}</p></div>
                <div class="space-y-1"><p class="text-gray-500 dark:text-gray-400">Int. Dias (override)</p><p class="font-semibold text-gray-800 dark:text-gray-100">{{ $product->recommended_interval_days ?? 'Categoria' }}</p></div>
            </div>
            @if($product->description)
                <div class="mt-4 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    <p class="font-semibold text-gray-500 dark:text-gray-400 mb-1">Descrição</p>
                    <p class="whitespace-pre-line">{{ $product->description }}</p>
                </div>
            @endif
        </section>

        {{-- Histórico de Auditoria --}}
        <section class="bg-white dark:bg-gray-800/70 backdrop-blur border border-gray-200 dark:border-gray-700 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Registros de Alterações</h3>
                <span class="text-[10px] px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Total: {{ $logs->total() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 uppercase tracking-wide text-[10px]">
                        <tr>
                            <th class="px-4 py-2 text-left">Data/Hora</th>
                            <th class="px-4 py-2 text-left">Usuário</th>
                            <th class="px-4 py-2 text-left">Ação</th>
                            <th class="px-4 py-2 text-left w-1/2">Alterações</th>
                            <th class="px-4 py-2 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php($changes = [])
                        @php($new = $log->new_value ?? [])
                        @php($old = $log->old_value ?? [])
                        @if($log->action === 'update')
                            @foreach(($new ?: []) as $k=>$v)
                                @php($oldVal = $old[$k] ?? null)
                                @if($oldVal !== $v) @php($changes[$k] = ['old'=>$oldVal,'new'=>$v]) @endif
                            @endforeach
                        @elseif($log->action === 'create')
                            @foreach(($new ?: []) as $k=>$v) @php($changes[$k] = ['old'=>null,'new'=>$v]) @endforeach
                        @elseif($log->action === 'delete')
                            @foreach(($old ?: []) as $k=>$v) @php($changes[$k] = ['old'=>$v,'new'=>null]) @endforeach
                        @endif
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide',
                                    'bg-green-600 text-white'=> $log->action==='create',
                                    'bg-yellow-500 text-white'=> $log->action==='update',
                                    'bg-red-600 text-white'=> $log->action==='delete',
                                ])>{{ strtoupper($log->action) }}</span>
                            </td>
                            <td class="px-4 py-2 align-top">
                                @if(empty($changes))
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @else
                                    <ul class="space-y-1">
                                        @foreach($changes as $field=>$diff)
                                            <li class="flex flex-col bg-gray-100 dark:bg-gray-900/40 rounded p-2">
                                                <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $field }}</span>
                                                <div class="text-[11px] flex flex-col sm:flex-row sm:items-center gap-1">
                                                    <span class="text-red-600 dark:text-red-400 line-through">{{ $diff['old'] === null ? '∅' : Str::limit(json_encode($diff['old'], JSON_UNESCAPED_UNICODE),40) }}</span>
                                                    <i class="fas fa-arrow-right text-gray-400 text-[10px] hidden sm:inline"></i>
                                                    <span class="text-green-600 dark:text-green-400">{{ $diff['new'] === null ? '∅' : Str::limit(json_encode($diff['new'], JSON_UNESCAPED_UNICODE),40) }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $logs->links() }}</div>
            @endif
        </section>
    </x-page-container>
</x-app-layout>
