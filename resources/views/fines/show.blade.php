<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Multa #'.$fine->id" icon="fas fa-gavel">
            <a href="{{ route('fines.index') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded text-xs font-semibold"><i class="fas fa-arrow-left"></i> Voltar</a>
            <a href="{{ route('fines.pdf',$fine) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-xs font-semibold"><i class="fas fa-file-pdf"></i> PDF</a>
            @can('update',$fine)
                <a href="{{ route('fines.edit',$fine) }}" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs font-semibold"><i class="fas fa-edit"></i> Editar</a>
            @endcan
        </x-page-header>
    </x-slot>

    <x-page-container>
        @if($fine->status === 'pago')
            <div class="mb-6 relative overflow-hidden rounded-lg border border-green-600/30 bg-gradient-to-r from-green-600 via-green-500 to-emerald-500 p-5 text-white shadow">
                <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(circle at 30% 50%, rgba(255,255,255,.35), transparent 70%);"></div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 flex items-center justify-center rounded-full bg-white/15 backdrop-blur ring-1 ring-white/30">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold flex items-center gap-2">Multa Paga
                                <span class="text-xs px-2 py-0.5 rounded bg-white/20 font-medium tracking-wide">Quitada</span>
                            </h2>
                            <p class="text-sm text-white/90">Esta multa foi quitada em {{ $fine->paid_at?->format('d/m/Y H:i') ?? '—' }}. Documento disponível em PDF para arquivamento.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('fines.pdf',$fine) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 rounded-md text-xs font-semibold tracking-wide transition">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </a>
                        @can('changeStatus',$fine)
                            <form method="post" action="{{ route('fines.change-status',$fine) }}" class="inline-flex" onsubmit="return confirm('Arquivar esta multa?')">
                                @csrf
                                <input type="hidden" name="status" value="arquivado" />
                                <button class="inline-flex items-center gap-2 px-4 py-2 bg-white/15 hover:bg-white/25 rounded-md text-xs font-semibold tracking-wide transition"><i class="fas fa-box-archive"></i> Arquivar</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-circle-info text-gray-400"></i> Dados Gerais</h3>
                        <x-fine-status-badge :status="$fine->status" />
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-0 text-sm">
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Auto</dt>
                                <dd class="mt-1 font-medium text-gray-800 dark:text-gray-100">{{ $fine->auto_number }}</dd>
                            </div>
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Veículo</dt>
                                <dd class="mt-1 text-gray-800 dark:text-gray-100">{{ $fine->vehicle?->plate }} — {{ $fine->vehicle?->model }}</dd>
                            </div>
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Condutor</dt>
                                <dd class="mt-1 text-gray-800 dark:text-gray-100">{{ $fine->driver?->name ?? '—' }}</dd>
                            </div>
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Valor Total</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-50">R$ {{ number_format($fine->total_amount,2,',','.') }}</dd>
                            </div>
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Primeira Visualização</dt>
                                <dd class="mt-1 text-gray-800 dark:text-gray-100">{{ $fine->first_view_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                            </div>
                            <div class="px-5 py-3">
                                <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ciência</dt>
                                <dd class="mt-1 text-gray-800 dark:text-gray-100">{{ $fine->acknowledged_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                            </div>
                        </dl>
                        <div class="px-5 py-4">
                            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Observações</dt>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $fine->notes ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-list text-gray-400"></i> Infrações ({{ $fine->infractions->count() }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Código</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Descrição</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Base</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Final</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Data</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Anexos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($fine->infractions as $inf)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $inf->code }}</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $inf->description }}</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">R$ {{ number_format($inf->base_amount,2,',','.') }}</td>
                                        <td class="px-4 py-2 font-semibold text-gray-900 dark:text-gray-50">R$ {{ number_format($inf->final_amount,2,',','.') }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400">{{ $inf->infraction_date ?? '—' }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400">{{ $inf->attachments->count() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma infração cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-code-branch text-gray-400"></i> Status</h3>
                    </div>
                    <div class="p-5 space-y-4 text-sm">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Atual</span>
                            <x-fine-status-badge :status="$fine->status" />
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="font-semibold text-gray-600 dark:text-gray-400">1ª Visualização</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $fine->first_view_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600 dark:text-gray-400">Ciência</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $fine->acknowledged_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-600 dark:text-gray-400">Pagamento</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $fine->paid_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                        </div>
                        @can('changeStatus',$fine)
                            <form method="post" action="{{ route('fines.change-status',$fine) }}" class="space-y-2 text-xs">
                                @csrf
                                <label class="block font-semibold text-gray-600 dark:text-gray-300">Alterar Status</label>
                                <select name="status" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-sm">
                                    @foreach(['draft'=>'Rascunho','aguardando_pagamento'=>'Aguardando Pagamento','pago'=>'Pago','cancelado'=>'Cancelado','arquivado'=>'Arquivado'] as $val=>$lbl)
                                        <option value="{{ $val }}" @selected($val===$fine->status)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                <button class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded font-medium"><i class="fas fa-rotate"></i> Atualizar</button>
                            </form>
                        @endcan
                        @can('acknowledge',$fine)
                            <form method="post" action="{{ route('fines.ack',$fine) }}" class="text-xs" onsubmit="return confirm('Confirmar ciência desta multa?')">
                                @csrf
                                <button class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-500 text-white rounded font-medium"><i class="fas fa-hand"></i> Dar Ciência</button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-clock-rotate-left text-gray-400"></i> Histórico de Status</h3>
                    </div>
                    <ul class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        @forelse($fine->statusHistories()->latest()->get() as $h)
                            <li class="px-5 py-3 flex flex-col gap-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800 dark:text-gray-100">{{ $h->from_status ?? '—' }} → {{ $h->to_status }}</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $h->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <span class="text-gray-600 dark:text-gray-400">{{ $h->user?->name ?? 'Sistema' }}</span>
                            </li>
                        @empty
                            <li class="px-5 py-4 text-center text-gray-500 dark:text-gray-400">Sem histórico.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-eye text-gray-400"></i> Visualizações</h3>
                    </div>
                    <ul class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        @forelse($fine->viewLogs()->latest()->limit(100)->get() as $vl)
                            <li class="px-5 py-2 flex items-center justify-between gap-4">
                                <span class="flex-1 truncate text-gray-700 dark:text-gray-300">{{ $vl->user?->name ?? '—' }}</span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $vl->viewed_at?->format('d/m/Y H:i') }}</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $vl->ip_address }}</span>
                            </li>
                        @empty
                            <li class="px-5 py-4 text-center text-gray-500 dark:text-gray-400">Sem visualizações.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
