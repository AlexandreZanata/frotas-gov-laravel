@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6" x-data="vehicleBlocking()" x-init="init()">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <i class="fas fa-lock text-red-600"></i> Bloquear / Desbloquear Veículos
        </h1>
        <div class="flex-1 md:flex-none"></div>
        <div class="w-full md:w-80 relative">
            <input type="text" x-model="search" @input="debouncedSearch()" placeholder="Buscar por modelo, marca ou placa..." class="w-full pl-9 pr-3 py-2 rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" />
            <span class="absolute left-2 top-2.5 text-gray-400"><i class="fas fa-search"></i></span>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" x-show="!loading && vehicles.length>0" x-cloak>
        <template x-for="v in vehicles" :key="v.id">
            <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col gap-3 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="vehicleTitle(v)"></div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400" x-text="'Placa: '+(v.plate||'—')"></div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold" :class="statusBadgeClass(v)"> <span x-text="v.status||'Sem Status'"></span> </span>
                </div>
                <div class="flex justify-end">
                    <template x-if="v.status_slug==='bloqueado'">
                        <button @click="openModal(v,'unblock')" class="px-3 py-1.5 rounded text-xs bg-green-600 hover:bg-green-700 text-white font-medium flex items-center gap-1"><i class="fas fa-unlock"></i> Desbloquear</button>
                    </template>
                    <template x-if="v.status_slug!=='bloqueado'">
                        <button @click="openModal(v,'block')" class="px-3 py-1.5 rounded text-xs bg-red-600 hover:bg-red-700 text-white font-medium flex items-center gap-1"><i class="fas fa-ban"></i> Bloquear</button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <div x-show="loading" class="text-center py-10 text-gray-500 dark:text-gray-300" x-cloak>
        <i class="fas fa-spinner fa-spin mr-2"></i> Carregando...
    </div>
    <div x-show="!loading && vehicles.length===0" class="text-center py-10 text-gray-500 dark:text-gray-300" x-cloak>
        Nenhum veículo encontrado.
    </div>

    <!-- Modal -->
    <div x-show="modal.open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative z-10 w-full max-w-md rounded bg-white dark:bg-gray-800 shadow-xl p-6 space-y-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-full" :class="modal.action==='block' ? 'bg-red-100 dark:bg-red-900/40 text-red-600':'bg-green-100 dark:bg-green-900/40 text-green-600'">
                    <i :class="modal.action==='block' ? 'fas fa-ban':'fas fa-unlock' "></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="modal.action==='block' ? 'Confirmar Bloqueio':'Confirmar Desbloqueio'"></h2>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400" x-text="modal.action==='block' ? 'Digite BLOQUEAR para confirmar a ação.':'Digite DESBLOQUEAR para confirmar a ação.'"></p>
                </div>
            </div>
            <div class="text-xs bg-gray-50 dark:bg-gray-900/40 p-3 rounded border border-gray-200 dark:border-gray-700">
                <p><span class="font-medium">Veículo:</span> <span x-text="modal.vehicle ? vehicleTitle(modal.vehicle) : ''"></span></p>
                <p><span class="font-medium">Status Atual:</span> <span x-text="modal.vehicle?.status || '—'"></span></p>
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
function vehicleBlocking(){
    return {
        search: '',
        vehicles: [],
        loading: false,
        debounceTimer: null,
        processing:false,
        modal: { open:false, action:null, vehicle:null, keyword:''},
        init(){ this.fetch(); },
        debouncedSearch(){ clearTimeout(this.debounceTimer); this.debounceTimer=setTimeout(()=>this.fetch(),400); },
        fetch(){
            this.loading = true;
            fetch(`/api/vehicles/blocking/search?q=${encodeURIComponent(this.search)}`, {headers:{'Accept':'application/json'}})
                .then(r=>r.json())
                .then(j=>{ this.vehicles=j.data||[]; })
                .catch(()=>{})
                .finally(()=>{ this.loading=false; });
        },
        vehicleTitle(v){ return `${v.brand||''} ${v.model||''}`.trim(); },
        statusBadgeClass(v){
            if(v.status_slug==='bloqueado') return 'bg-red-600 text-white';
            if(v.status_slug==='disponivel') return 'bg-green-600 text-white';
            return 'bg-gray-400 text-white';
        },
        openModal(v,action){ this.modal={ open:true, action, vehicle:v, keyword:''}; },
        closeModal(){ if(this.processing) return; this.modal.open=false; },
        keywordValid(){ return (this.modal.action==='block' && this.modal.keyword==='BLOQUEAR') || (this.modal.action==='unblock' && this.modal.keyword==='DESBLOQUEAR'); },
        confirmAction(){
            if(!this.keywordValid() || this.processing) return; this.processing=true;
            const url = this.modal.action==='block' ? `/api/vehicles/${this.modal.vehicle.id}/block` : `/api/vehicles/${this.modal.vehicle.id}/unblock`;
            fetch(url, {
                method:'POST',
                headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({ keyword: this.modal.keyword })
            }).then(r=>r.json().catch(()=>({})))
              .then(j=>{
                  if(j && j.message){ this.flash(j.message, 'success'); }
                  this.fetch();
                  this.closeModal();
              })
              .catch(()=>{ this.flash('Erro ao processar ação','error'); })
              .finally(()=>{ this.processing=false; });
        },
        flash(msg,type){
            const div=document.createElement('div');
            div.className=`fixed top-4 right-4 z-50 px-4 py-2 rounded shadow text-sm font-medium ${type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
            div.textContent=msg; document.body.appendChild(div); setTimeout(()=>div.remove(),4000);
        }
    }
}
</script>
@endsection
