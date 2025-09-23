@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-route text-red-600"></i> Bloquear / Desbloquear Corridas</h2>
@endsection
@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6" x-data="runBlocking()" x-init="init()">
    <div class="flex flex-wrap gap-3 items-center justify-between bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-4 py-3 shadow-sm">
        <div class="text-xs text-gray-500 dark:text-gray-400">Gerencie corridas em andamento ou pendentes. Bloquear previne qualquer alteração até desbloqueio.</div>
        <div class="w-full md:w-80 relative">
            <input type="text" x-model="search" @input="debouncedSearch()" placeholder="Buscar por placa, modelo ou motorista..." class="w-full pl-9 pr-3 py-2 rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" />
            <span class="absolute left-2 top-2.5 text-gray-400"><i class="fas fa-search"></i></span>
        </div>
    </div>

    <div x-show="loading" class="text-center py-10 text-gray-500 dark:text-gray-300"><i class="fas fa-spinner fa-spin mr-2"></i> Carregando...</div>
    <div x-show="!loading && runs.length===0" class="text-center py-10 text-gray-500 dark:text-gray-300">Nenhuma corrida encontrada.</div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" x-show="!loading && runs.length>0">
        <template x-for="r in runs" :key="r.id">
            <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="'Veículo: '+(r.vehicle||'—')"></div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400" x-text="'Motorista: '+(r.driver||'—')"></div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400" x-text="r.destination ? ('Destino: '+r.destination) : ''"></div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold" :class="statusBadgeClass(r)">
                        <span x-text="statusLabel(r)"></span>
                    </span>
                </div>
                <div class="text-[11px] space-y-1 text-gray-600 dark:text-gray-300">
                    <div><strong>Status:</strong> <span x-text="r.status"></span></div>
                    <template x-if="r.blocked_at"><div><strong>Bloqueada em:</strong> <span x-text="r.blocked_at"></span></div></template>
                    <template x-if="r.blocked_by"><div><strong>Por:</strong> <span x-text="r.blocked_by"></span></div></template>
                    <div><strong>Início:</strong> <span x-text="r.start_time || '—'"></span></div>
                    <div><strong>KM Inicial:</strong> <span x-text="r.start_km ?? '—'"></span></div>
                </div>
                <div class="flex justify-end">
                    <template x-if="r.status==='blocked'">
                        <button @click="openModal(r,'unblock')" class="px-3 py-1.5 rounded text-xs bg-green-600 hover:bg-green-700 text-white font-medium flex items-center gap-1"><i class="fas fa-unlock"></i> Desbloquear</button>
                    </template>
                    <template x-if="r.status!=='blocked'">
                        <button @click="openModal(r,'block')" class="px-3 py-1.5 rounded text-xs bg-red-600 hover:bg-red-700 text-white font-medium flex items-center gap-1"><i class="fas fa-ban"></i> Bloquear</button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Modal -->
    <div x-show="modal.open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative z-10 w-full max-w-md rounded bg-white dark:bg-gray-800 shadow-xl p-6 space-y-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-full" :class="modal.action==='block' ? 'bg-red-100 dark:bg-red-900/40 text-red-600':'bg-green-100 dark:bg-green-900/40 text-green-600'">
                    <i :class="modal.action==='block' ? 'fas fa-ban':'fas fa-unlock'"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="modal.action==='block' ? 'Confirmar Bloqueio da Corrida':'Confirmar Desbloqueio da Corrida'"></h2>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400" x-text="modal.action==='block' ? 'Digite BLOQUEAR para confirmar a ação.':'Digite DESBLOQUEAR para confirmar a ação.'"></p>
                </div>
            </div>
            <div class="text-xs bg-gray-50 dark:bg-gray-900/40 p-3 rounded border border-gray-200 dark:border-gray-700">
                <p><span class="font-medium">Corrida:</span> <span x-text="modal.run ? ('ID '+modal.run.id) : ''"></span></p>
                <p><span class="font-medium">Veículo:</span> <span x-text="modal.run?.vehicle || '—'"></span></p>
                <p><span class="font-medium">Motorista:</span> <span x-text="modal.run?.driver || '—'"></span></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Palavra-Chave</label>
                <input type="text" x-model="modal.keyword" placeholder="Digite aqui" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="closeModal()" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-200">Cancelar</button>
                <button type="button" @click="confirmAction()" :disabled="!keywordValid() || processing" :class="keywordValid() ? (modal.action==='block' ? 'bg-red-600 hover:bg-red-700':'bg-green-600 hover:bg-green-700') : 'bg-gray-300 dark:bg-gray-600 cursor-not-allowed'" class="px-5 py-2 rounded text-xs font-semibold text-white flex items-center gap-2">
                    <i class="fas" :class="processing ? 'fa-spinner fa-spin' : (modal.action==='block' ? 'fa-ban':'fa-unlock')"></i>
                    <span x-text="processing ? 'Processando...' : (modal.action==='block' ? 'Bloquear':'Desbloquear')"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function runBlocking(){
    return {
        search:'', runs:[], loading:false, debounceTimer:null, processing:false,
        modal:{open:false, action:null, run:null, keyword:''},
        init(){ this.fetch(); },
        debouncedSearch(){ clearTimeout(this.debounceTimer); this.debounceTimer=setTimeout(()=>this.fetch(),400); },
        fetch(){ this.loading=true; fetch(`/api/runs/blocking/search?q=${encodeURIComponent(this.search)}`,{headers:{'Accept':'application/json'}})
            .then(r=>r.json()).then(j=>{ this.runs=j.data||[]; }).finally(()=>this.loading=false); },
        statusBadgeClass(r){ if(r.status==='blocked') return 'bg-red-600 text-white'; if(r.status==='in_progress') return 'bg-blue-600 text-white'; return 'bg-gray-500 text-white'; },
        statusLabel(r){ return r.status==='blocked' ? 'Bloqueada' : (r.status==='in_progress' ? 'Em Andamento':'Pendente'); },
        openModal(run,action){ this.modal={open:true,action,run,keyword:''}; },
        closeModal(){ if(this.processing) return; this.modal.open=false; },
        keywordValid(){ return (this.modal.action==='block' && this.modal.keyword==='BLOQUEAR') || (this.modal.action==='unblock' && this.modal.keyword==='DESBLOQUEAR'); },
        confirmAction(){ if(!this.keywordValid()||this.processing) return; this.processing=true; const url=this.modal.action==='block'?`/api/runs/${this.modal.run.id}/block`:`/api/runs/${this.modal.run.id}/unblock`; fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({keyword:this.modal.keyword})}).then(r=>r.json().catch(()=>({}))).then(j=>{ this.flash(j.message||'OK','success'); this.fetch(); this.closeModal();}).catch(()=>this.flash('Erro','error')).finally(()=>this.processing=false); },
        flash(msg,type){ const d=document.createElement('div'); d.className=`fixed top-4 right-4 z-50 px-4 py-2 rounded shadow text-sm font-medium ${type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`; d.textContent=msg; document.body.appendChild(d); setTimeout(()=>d.remove(),4000); }
    }
}
</script>
@endsection
