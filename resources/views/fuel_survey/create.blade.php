@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-gas-pump text-amber-600"></i> Nova Cotação de Combustível</h2>
@endsection
@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-6" x-data="fuelSurveyBuilder()" x-init="init()">
    <div class="flex items-center justify-between flex-wrap gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-4 py-3 shadow-sm">
        <div class="flex flex-col gap-1">
            <p class="text-xs text-gray-500 dark:text-gray-400">Preencha os preços coletados nos postos, defina descontos e gere a tabela comparativa.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="saveLocal()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-200 dark:bg-gray-700 rounded text-[11px] font-medium text-gray-700 dark:text-gray-200">
                <i class="fas fa-save"></i> Rascunho
            </button>
            <button type="button" @click="loadLocal()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-200 dark:bg-gray-700 rounded text-[11px] font-medium text-gray-700 dark:text-gray-200">
                <i class="fas fa-upload"></i> Carregar
            </button>
            <button type="button" @click="clearLocal()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-200 dark:bg-red-800/50 rounded text-[11px] font-medium text-red-700 dark:text-red-300">
                <i class="fas fa-trash"></i> Limpar
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="p-3 rounded bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm border border-red-200 dark:border-red-700">
            <ul class="list-disc ml-4 space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('fuel-surveys.store') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-1 space-y-6">
                <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-3 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-info-circle text-amber-600"></i> Dados Gerais</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Data *</label>
                            <input type="date" name="survey_date" x-model="form.survey_date" required class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Método de Cálculo *</label>
                            <select name="method" x-model="form.method" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm">
                                <option value="simple">Média Aritmética Simples</option>
                                <option value="custom">Personalizado</option>
                            </select>
                            <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Personalizado permite informar a média manualmente.</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-percent text-amber-600"></i> Descontos (%)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @php $fuelMap = ['diesel_s500'=>'Diesel S500','diesel_s10'=>'Diesel S10','gasoline'=>'Gasolina','ethanol'=>'Etanol']; @endphp
                        @foreach($fuelMap as $k=>$label)
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400">{{ $label }}</label>
                                <input type="number" step="0.01" min="0" max="100" name="discount_{{ $k }}" x-model.number="form['discount_{{ $k }}']" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-xs" placeholder="0,00" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <template x-if="form.method==='custom'">
                    <div class="p-4 rounded border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 space-y-4 shadow-sm">
                        <h3 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 flex items-center gap-2"><i class="fas fa-sliders-h"></i> Médias Personalizadas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($fuelMap as $k=>$label)
                                <div>
                                    <label class="block text-[11px] font-medium text-indigo-700 dark:text-indigo-300">{{ $label }}</label>
                                    <input type="number" step="0.001" min="0" name="custom_avg_{{ $k }}" x-model.number="form['custom_avg_{{ $k }}']" class="mt-1 w-full rounded border-indigo-300 dark:bg-indigo-950 dark:border-indigo-700 text-xs" placeholder="0,000" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>
            </div>
            <div class="md:col-span-2 space-y-6">
                <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-building"></i> Postos & Preços</h3>
                        <button type="button" @click="addStationRow()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-[11px] font-medium"><i class="fas fa-plus"></i> Adicionar</button>
                    </div>
                    <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr class="text-gray-600 dark:text-gray-300">
                                    <th class="p-2 text-left">Posto</th>
                                    <th class="p-2">Média?</th>
                                    <th class="p-2">Comparar?</th>
                                    @foreach($fuelMap as $k=>$label)<th class="p-2">{{ $label }}</th><th class="p-2">Comp.</th>@endforeach
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row,idx) in form.station_prices" :key="row.uid">
                                    <tr class="border-t border-gray-200 dark:border-gray-700 align-top bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="p-1 w-48">
                                            <select :name="`station_prices[${idx}][gas_station_id]`" x-model="row.gas_station_id" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-xs">
                                                <option value="">Selecione...</option>
                                                @foreach($stations as $st)
                                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-1 text-center">
                                            <input type="checkbox" :name="`station_prices[${idx}][include_in_average]`" x-model="row.include_in_average" class="rounded" />
                                        </td>
                                        <td class="p-1 text-center">
                                            <input type="checkbox" :name="`station_prices[${idx}][include_in_comparison]`" x-model="row.include_in_comparison" class="rounded" />
                                        </td>
                                        @foreach($fuelMap as $k=>$label)
                                            <td class="p-1">
                                                <input type="number" step="0.001" min="0" :name="`station_prices[${idx}][{{ $k }}_price]`" x-model.number="row['{{ $k }}_price']" class="w-20 rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-xs" />
                                            </td>
                                            <td class="p-1 w-24">
                                                <input type="file" accept="image/*" class="text-[10px]" :name="`station_prices[${idx}][{{ $k }}_attachment]`" />
                                            </td>
                                        @endforeach
                                        <td class="p-1">
                                            <button type="button" @click="removeStationRow(idx)" class="text-red-600 hover:underline">Remover</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Selecione participação em média e/ou comparação final.</p>
                </div>

                <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-calculator"></i> Cálculo em Tempo Real</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        @foreach($fuelMap as $k=>$label)
                            <div class="p-3 rounded bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 space-y-1">
                                <div class="font-semibold text-[11px] text-gray-700 dark:text-gray-200">{{ $label }}</div>
                                <template x-if="form.method==='simple'">
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Média Simples: <span class="font-semibold" x-text="format(calcAverage('{{ $k }}'))"></span></div>
                                </template>
                                <template x-if="form.method==='custom'">
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Média Pers.: <span class="font-semibold" x-text="format(form['custom_avg_{{ $k }}'])"></span></div>
                                </template>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400">Desc (%): <span x-text="form['discount_{{ $k }}']||0"></span></div>
                                <div class="text-[10px]">Com Desconto: <span class="font-semibold" x-text="format(discounted('{{ $k }}'))"></span></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('fuel-surveys.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded text-sm text-gray-800 dark:text-gray-100">Cancelar</a>
                    <button class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium inline-flex items-center gap-2"><i class="fas fa-check"></i> Salvar Cotação</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function fuelSurveyBuilder(){
    return {
        form: {
            survey_date: new Date().toISOString().slice(0,10),
            method: 'simple',
            @foreach($fuelMap as $k=>$v)
            'discount_{{ $k }}': null,
            'custom_avg_{{ $k }}': null,
            @endforeach
            station_prices: []
        },
        init(){
            if(this.form.station_prices.length===0){ this.addStationRow(); }
            this.loadLocal();
        },
        addStationRow(){
            this.form.station_prices.push({
                uid: Date.now()+''+Math.random(),
                gas_station_id: '',
                include_in_average: true,
                include_in_comparison: true,
                @foreach($fuelMap as $k=>$v)
                '{{ $k }}_price': null,
                @endforeach
            });
        },
        removeStationRow(i){ this.form.station_prices.splice(i,1); },
        calcAverage(fuel){
            if(this.form.method!== 'simple') return null;
            const key = fuel+'_price';
            const items = this.form.station_prices.filter(r=>r.include_in_average && r[key]!==null && r[key]!=='' && !isNaN(r[key]));
            if(!items.length) return null;
            const avg = items.reduce((s,r)=>s+parseFloat(r[key]),0)/items.length;
            return +avg.toFixed(3);
        },
        discounted(fuel){
            let base = this.form.method==='custom' ? this.form['custom_avg_'+fuel] : this.calcAverage(fuel);
            if(base===null || base===undefined || base==='') return null;
            let d = this.form['discount_'+fuel];
            if(d===null || d==='' || isNaN(d)) return +parseFloat(base).toFixed(3);
            return +(parseFloat(base)*(1- (parseFloat(d)/100))).toFixed(3);
        },
        format(v){ if(v===null || v===undefined || v==='') return '—'; return parseFloat(v).toFixed(3).replace('.',','); },
        saveLocal(){ localStorage.setItem('fuelSurveyDraft', JSON.stringify(this.form)); alert('Rascunho salvo localmente'); },
        loadLocal(){ const raw = localStorage.getItem('fuelSurveyDraft'); if(raw){ try{ const d=JSON.parse(raw); this.form = Object.assign(this.form,d); }catch(e){} } },
        clearLocal(){ if(confirm('Remover rascunho local?')){ localStorage.removeItem('fuelSurveyDraft'); } }
    }
}
</script>
@endsection
