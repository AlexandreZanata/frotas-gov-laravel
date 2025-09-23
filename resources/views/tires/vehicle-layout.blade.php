<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-800 dark:text-gray-100 m-0"><i class="fas fa-car-side text-gray-500 dark:text-gray-400"></i> Layout de Pneus - {{ $vehicle->plate ?? $vehicle->prefix }}</h1>
            <a href="{{ route('tires.dashboard') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
        </div>
    </x-slot>

    {{-- Corpo principal --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto p-4 space-y-6">
                {{-- mensagens de sessão --}}
                @if(session('success'))
                    <div class="p-3 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-sm">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-3 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-sm">{{ session('error') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow rounded p-4 relative">
                        <h2 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">Diagrama</h2>
                        <div class="relative w-full aspect-[4/3] bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 overflow-hidden">
                            @php $hasCoords = collect($positions)->contains(fn($p)=>isset($p['x'],$p['y']) && $p['x']!==null && $p['y']!==null); @endphp
                            @if($hasCoords)
                                @foreach($positions as $p)
                                    @php($code = $p['code'])
                                    @php($tire = $tiresMap[$code] ?? null)
                                    <div class="absolute flex flex-col items-center select-none group"
                                         style="left:{{ $p['x'] ?? 0 }}%; top:{{ $p['y'] ?? 0 }}%; transform:translate(-50%,-50%);">
                                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-[10px] font-semibold border transition
                                            {{ $tire ? 'bg-indigo-600 text-white border-indigo-400 dark:border-indigo-500' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600' }}">
                                            {{ $code }}
                                        </div>
                                        <div class="mt-1 text-[10px] text-gray-600 dark:text-gray-400 max-w-[80px] text-center truncate" title="{{ $p['label'] }}">{{ $p['label'] }}</div>
                                        @if($tire)
                                            <div class="absolute -bottom-16 w-44 opacity-0 group-hover:opacity-100 pointer-events-none transition bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow p-2 text-[10px] z-10">
                                                <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $tire->serial_number }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">{{ $tire->brand }} {{ $tire->model }}</div>
                                                <div class="mt-1"><span class="px-1.5 py-0.5 rounded text-[9px] @class([
                                                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'=>$tire->status==='stock',
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'=>$tire->status==='in_use',
                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'=>$tire->status==='attention',
                                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'=>$tire->status==='critical',
                                                    'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'=>$tire->status==='recap_out'
                                                ])">{{ ucfirst(str_replace('_',' ',$tire->status)) }}</span></div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="grid grid-cols-2 gap-6 p-4">
                                    @foreach($positions as $p)
                                        @php($code=$p['code'])
                                        @php($tire=$tiresMap[$code] ?? null)
                                        <div class="border rounded p-3 {{ $tire ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-700' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700' }}">
                                            <div class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-2">{{ $code }} <span class="block normal-case text-gray-700 dark:text-gray-300">{{ $p['label'] }}</span></div>
                                            @if($tire)
                                                <div class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $tire->serial_number }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tire->brand }} {{ $tire->model }}</div>
                                                <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 flex items-center gap-1">Status:
                                                    <span class="px-2 py-0.5 rounded font-medium @class([
                                                        'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'=>$tire->status==='stock',
                                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'=>$tire->status==='in_use',
                                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'=>$tire->status==='attention',
                                                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'=>$tire->status==='critical',
                                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'=>$tire->status==='recap_out'
                                                    ])">{{ ucfirst(str_replace('_',' ',$tire->status)) }}</span>
                                                </div>
                                            @else
                                                <div class="text-xs italic text-gray-400 dark:text-gray-500">Vazio</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="mt-3 text-[10px] text-gray-500 dark:text-gray-500">Posicione seu cursor sobre um pneu para detalhes. Layout baseado na categoria do veículo.</p>
                    </div>
                    <div class="space-y-6">
                        @php($codes = collect($positions)->pluck('code'))
                        <div class="bg-white dark:bg-gray-800 shadow rounded p-4 space-y-4">
                            <h3 class="font-semibold text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">Rodízio Interno</h3>
                            <form method="POST" action="{{ route('tires.vehicle.rotation.internal',$vehicle) }}" class="space-y-2">
                                @csrf
                                <div class="flex gap-2">
                                    <select name="pos_a" class="w-1/2 rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100" required>
                                        <option value="">Posição A</option>
                                        @foreach($codes as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                    </select>
                                    <select name="pos_b" class="w-1/2 rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100" required>
                                        <option value="">Posição B</option>
                                        @foreach($codes as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                    </select>
                                </div>
                                <button class="w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs">Executar</button>
                            </form>
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow rounded p-4 space-y-4">
                            <h3 class="font-semibold text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">Remover para Estoque</h3>
                            <form method="POST" action="{{ route('tires.vehicle.rotation.external.out',$vehicle) }}" class="space-y-2">
                                @csrf
                                <select name="position" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100" required>
                                    <option value="">Posição</option>
                                    @foreach($codes as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                </select>
                                <button class="w-full px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-xs">Remover</button>
                            </form>
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow rounded p-4 space-y-4">
                            <h3 class="font-semibold text-sm uppercase tracking-wide text-gray-600 dark:text-gray-400">Instalar do Estoque</h3>
                            <form method="POST" action="{{ route('tires.vehicle.rotation.external.in',$vehicle) }}" class="space-y-2" x-data="tireStockInstall({ searchUrl: '{{ route('tires.search-stock') }}' })">
                                @csrf
                                <input type="hidden" name="tire_id" x-model="selectedId" required>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400 mb-1">Buscar Pneu (nº de série, marca, modelo, medida, notas, ID)</label>
                                    <div class="relative" @keydown.escape.prevent="open=false">
                                        <input x-model="query" @input="debouncedSearch()" @focus="onFocus" type="text" placeholder="Digite para buscar..." class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100" autocomplete="off">
                                        <div x-show="loading" class="absolute inset-y-0 right-2 flex items-center">
                                            <svg class="animate-spin h-4 w-4 text-gray-500" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                        </div>
                                        <ul x-show="open && results.length" x-transition class="absolute z-20 mt-1 w-full max-h-60 overflow-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded shadow text-xs text-gray-700 dark:text-gray-200 divide-y divide-gray-100 dark:divide-gray-800">
                                            <template x-for="r in results" :key="r.id"><li>
                                                <button type="button" @click="select(r)" class="w-full text-left px-3 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-700/40 flex flex-col">
                                                    <span class="font-semibold" x-text="'#'+r.id+' • '+r.serial_number"></span>
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400" x-text="r.brand+' '+r.model+' • '+(r.dimension||'')"></span>
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-500" x-text="depthInfo(r)"></span>
                                                </button>
                                            </li></template>
                                            <li x-show="!results.length && !loading" class="px-3 py-2 text-gray-400 text-center">Nenhum resultado</li>
                                        </ul>
                                    </div>
                                    <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-500">Digite 1+ termos para filtrar. Ex: "michelin 275".</p>
                                    <template x-if="selected">
                                        <div class="mt-2 p-2 border border-green-300 dark:border-green-600 rounded bg-green-50 dark:bg-green-900/20 text-[11px] flex justify-between items-start">
                                            <div>
                                                <div class="font-semibold" x-text="'Selecionado: #'+selected.id+' '+selected.serial_number"></div>
                                                <div class="text-[10px]" x-text="selected.brand+' '+selected.model+' '+(selected.dimension||'')"></div>
                                            </div>
                                            <button type="button" @click="clearSelection" class="text-red-600 dark:text-red-400 text-[10px] ml-2">remover</button>
                                        </div>
                                    </template>
                                </div>
                                <select name="position" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100" required>
                                    <option value="">Posição</option>
                                    @foreach($codes as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                </select>
                                <button :disabled="!selectedId" :class="{'opacity-50 cursor-not-allowed': !selectedId}" class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 disabled:hover:bg-green-600 text-white rounded text-xs">Instalar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tireStockInstall', (cfg) => ({
            searchUrl: cfg.searchUrl,
            query: '',
            results: [],
            loading: false,
            open: false,
            selected: null,
            selectedId: '',
            timer: null,
            onFocus(){ if(this.results.length) this.open = true; },
            debouncedSearch(){
                clearTimeout(this.timer);
                if (!this.query || this.query.trim().length < 2){ this.results = []; this.open=false; return; }
                this.timer = setTimeout(()=>this.search(), 300);
            },
            async search(){
                this.loading = true; this.open = true;
                try { const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(this.query)); if(!res.ok) throw new Error('Erro'); const data = await res.json(); this.results = data.data || []; }
                catch(e){ console.error(e); this.results = []; }
                finally { this.loading = false; }
            },
            select(r){ this.selected = r; this.selectedId = r.id; this.open=false; },
            clearSelection(){ this.selected=null; this.selectedId=''; },
            depthInfo(r){ const cur = r.current_tread_depth_mm != null ? (r.current_tread_depth_mm + 'mm') : '-'; const exp = r.expected_tread_life_km != null ? (r.expected_tread_life_km + 'km') : '-'; return 'Sulco: '+cur+' • Vida Esperada: '+exp; }
        }))
    });
</script>
