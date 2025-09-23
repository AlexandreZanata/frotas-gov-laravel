<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Editar Multa #'.$fine->id" icon="fas fa-gavel">
            <a href="{{ route('fines.index') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded text-xs font-semibold"><i class="fas fa-arrow-left"></i> Voltar</a>
            <a href="{{ route('fines.show',$fine) }}" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-xs font-semibold"><i class="fas fa-eye"></i> Detalhes</a>
            <a href="{{ route('fines.pdf',$fine) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-700 hover:bg-indigo-600 text-white rounded text-xs font-semibold"><i class="fas fa-file-pdf"></i> PDF</a>
        </x-page-header>
    </x-slot>

    <x-page-container x-data="{ tab: 'dados' }">
        @if(session('success'))
            <div class="mb-6 p-4 rounded border border-green-300/40 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-200 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 rounded border border-red-300/40 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-200 text-sm">
                <ul class="list-disc ml-4 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if($fine->status === 'pago')
            <div class="mb-8 relative overflow-hidden rounded-lg border border-green-600/30 bg-gradient-to-r from-green-600 via-green-500 to-emerald-500 p-5 text-white shadow">
                <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(circle at 30% 50%, rgba(255,255,255,.35), transparent 70%);"></div>
                <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex gap-3 items-start">
                        <div class="h-10 w-10 flex items-center justify-center rounded-full bg-white/15 backdrop-blur ring-1 ring-white/30"><i class="fas fa-check-circle text-2xl"></i></div>
                        <div>
                            <h2 class="font-semibold text-lg flex items-center gap-2">Multa Paga <span class="px-2 py-0.5 rounded text-xs bg-white/20">Quitada</span></h2>
                            <p class="text-sm text-white/90">Pagamento registrado em {{ $fine->paid_at?->format('d/m/Y H:i') ?? '—' }}.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @can('changeStatus',$fine)
                            <form method="post" action="{{ route('fines.change-status',$fine) }}" onsubmit="return confirm('Arquivar esta multa paga?')" class="inline-flex">@csrf<input type="hidden" name="status" value="arquivado"><button class="px-4 py-2 rounded-md bg-white/15 hover:bg-white/25 text-xs font-semibold inline-flex items-center gap-2"><i class="fas fa-box-archive"></i> Arquivar</button></form>
                        @endcan
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 text-sm">
            <button @click="tab='dados'" :class="tab==='dados' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-4 py-2 border-b-2 font-medium">Dados</button>
            <button @click="tab='infracoes'" :class="tab==='infracoes' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-4 py-2 border-b-2 font-medium">Infrações ({{ $fine->infractions->count() }})</button>
            <button @click="tab='auditoria'" :class="tab==='auditoria' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'" class="px-4 py-2 border-b-2 font-medium">Auditoria</button>
        </div>

        <div x-show="tab==='dados'" x-cloak class="space-y-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <form method="post" action="{{ route('fines.update',$fine) }}" class="lg:col-span-2 space-y-6">@csrf @method('PUT')
                    <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-pen text-gray-400"></i> Informações Principais</h3>
                            <x-fine-status-badge :status="$fine->status" />
                        </div>
                        <div class="p-6 grid md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Auto</label>
                                <input name="auto_number" value="{{ old('auto_number',$fine->auto_number) }}" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Veículo</label>
                                <select name="vehicle_id" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}" @selected($v->id==$fine->vehicle_id)>{{ $v->plate }} - {{ $v->model }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Condutor</label>
                                <select name="driver_id" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Sem condutor --</option>
                                    @foreach($drivers as $d)
                                        <option value="{{ $d->id }}" @selected($d->id==$fine->driver_id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Observações</label>
                                <textarea name="notes" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes',$fine->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"><i class="fas fa-save"></i> Salvar</button>
                        <a href="{{ route('fines.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Voltar</a>
                    </div>
                </form>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-code-branch text-gray-400"></i> Status</h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">
                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div><p class="font-semibold text-gray-600 dark:text-gray-400">1ª Visualização</p><p class="text-gray-800 dark:text-gray-200">{{ $fine->first_view_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
                                <div><p class="font-semibold text-gray-600 dark:text-gray-400">Ciência</p><p class="text-gray-800 dark:text-gray-200">{{ $fine->acknowledged_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
                                <div><p class="font-semibold text-gray-600 dark:text-gray-400">Pagamento</p><p class="text-gray-800 dark:text-gray-200">{{ $fine->paid_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
                                <div><p class="font-semibold text-gray-600 dark:text-gray-400">Valor</p><p class="font-semibold text-gray-900 dark:text-gray-50">R$ {{ number_format($fine->total_amount,2,',','.') }}</p></div>
                            </div>
                            @can('changeStatus',$fine)
                                <form method="post" action="{{ route('fines.change-status',$fine) }}" class="space-y-2 text-xs">@csrf
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
                                <form method="post" action="{{ route('fines.ack',$fine) }}" class="text-xs" onsubmit="return confirm('Confirmar ciência desta multa?')">@csrf
                                    <button class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-500 text-white rounded font-medium"><i class="fas fa-hand"></i> Dar Ciência</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-clock-rotate-left text-gray-400"></i> Histórico</h3>
                        </div>
                        <ul class="max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                            @foreach($fine->statusHistories()->latest()->get() as $h)
                                <li class="px-5 py-3 flex flex-col gap-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $h->from_status ?? '—' }} → {{ $h->to_status }}</span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $h->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $h->user?->name ?? 'Sistema' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab==='infracoes'" x-cloak class="space-y-8">
            <div class="flex flex-col gap-6">
                <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-list text-gray-400"></i> Infrações Cadastradas</h3>
                        <span class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ $fine->infractions->count() }}</span>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($fine->infractions as $inf)
                            <li class="p-5 space-y-4">
                                <div class="flex flex-wrap justify-between gap-3">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $inf->code }} - {{ $inf->description }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Valor Final: R$ {{ number_format($inf->final_amount,2,',','.') }}</span>
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-600/20 text-indigo-700 dark:text-indigo-300">Base R$ {{ number_format($inf->base_amount,2,',','.') }}</span>
                                </div>
                                <details class="group" open>
                                    <summary class="cursor-pointer text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-gear text-gray-400"></i> Ajustar Valores</summary>
                                    <form method="post" action="{{ route('fines.infractions.update',[$fine,$inf]) }}" class="mt-3 grid md:grid-cols-6 gap-2 text-xs">@csrf @method('PATCH')
                                        <input name="code" value="{{ $inf->code }}" class="col-span-2 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="description" value="{{ $inf->description }}" class="col-span-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="base_amount" value="{{ $inf->base_amount }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="extra_fixed" value="{{ $inf->extra_fixed }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="extra_percent" value="{{ $inf->extra_percent }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="discount_fixed" value="{{ $inf->discount_fixed }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="discount_percent" value="{{ $inf->discount_percent }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="infraction_date" value="{{ $inf->infraction_date }}" type="date" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="due_date" value="{{ $inf->due_date }}" type="date" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                                        <input name="notes" value="{{ $inf->notes }}" class="col-span-3 md:col-span-6 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" placeholder="Notas" />
                                        <div class="col-span-full flex flex-wrap gap-2 mt-1">
                                            <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium"><i class="fas fa-floppy-disk"></i> Salvar</button>
                                            <form method="post" action="{{ route('fines.infractions.destroy',[$fine,$inf]) }}" onsubmit="return confirm('Remover esta infração?')" class="inline-flex">@csrf @method('DELETE') <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded bg-red-600 hover:bg-red-500 text-white text-xs font-medium"><i class="fas fa-trash"></i> Remover</button></form>
                                        </div>
                                    </form>
                                    <div class="mt-4 space-y-2">
                                        <form method="post" action="{{ route('fines.infractions.attachments.store',[$fine,$inf]) }}" enctype="multipart/form-data" class="flex items-center gap-2 text-xs">@csrf
                                            <input type="file" name="file" required class="text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 dark:file:bg-gray-700 file:text-indigo-700 dark:file:text-gray-200 hover:file:bg-indigo-100" />
                                            <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded bg-gray-700 hover:bg-gray-600 text-white text-xs font-medium"><i class="fas fa-paperclip"></i> Anexar</button>
                                        </form>
                                        <ul class="text-xs divide-y divide-gray-100 dark:divide-gray-700 rounded border border-gray-200 dark:border-gray-700">
                                            @forelse($inf->attachments as $att)
                                                <li class="flex items-center justify-between px-3 py-1.5 text-gray-700 dark:text-gray-300">{{ $att->original_name }}
                                                    <form method="post" action="{{ route('fines.attachments.destroy',$att) }}" onsubmit="return confirm('Excluir anexo?')" class="ml-3">@csrf @method('DELETE') <button class="text-red-500 hover:text-red-400"><i class="fas fa-times"></i></button></form>
                                                </li>
                                            @empty
                                                <li class="px-3 py-2 text-gray-500 dark:text-gray-400">Sem anexos.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </details>
                            </li>
                        @empty
                            <li class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma infração cadastrada.</li>
                        @endforelse
                    </ul>
                </div>

                @can('update',$fine)
                    <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-plus text-gray-400"></i> Adicionar Infração</h3>
                        </div>
                        <form method="post" action="{{ route('fines.infractions.store',$fine) }}" class="p-5 grid md:grid-cols-8 gap-2 text-xs">@csrf
                            <input name="code" placeholder="Código" class="md:col-span-2 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" required />
                            <input name="description" placeholder="Descrição" class="md:col-span-3 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="base_amount" type="number" step="0.01" placeholder="Base" class="md:col-span-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" required />
                            <input name="extra_fixed" type="number" step="0.01" placeholder="+R$" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="extra_percent" type="number" step="0.01" placeholder="+%" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="discount_fixed" type="number" step="0.01" placeholder="-R$" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="discount_percent" type="number" step="0.01" placeholder="-%" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="infraction_date" type="date" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="due_date" type="date" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <input name="notes" placeholder="Notas" class="md:col-span-5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 px-2 py-1" />
                            <div class="md:col-span-3 flex justify-end">
                                <button class="inline-flex items-center gap-2 px-4 py-1.5 rounded bg-green-600 hover:bg-green-500 text-white font-medium"><i class="fas fa-plus"></i> Adicionar</button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        <div x-show="tab==='auditoria'" x-cloak class="space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 flex items-center gap-2"><i class="fas fa-eye text-gray-400"></i> Visualizações</h3>
                </div>
                <ul class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                    @foreach($fine->viewLogs()->latest()->limit(100)->get() as $vl)
                        <li class="px-5 py-2 flex items-center justify-between gap-4">
                            <span class="flex-1 truncate text-gray-700 dark:text-gray-300">{{ $vl->user?->name ?? '—' }}</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $vl->viewed_at?->format('d/m/Y H:i') }}</span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $vl->ip_address }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-page-container>

    <script>function fineEdit(){return {}}</script>
</x-app-layout>
