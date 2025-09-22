@csrf
@php($p = $product ?? null)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Coluna Principal Dados Básicos --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="grid md:grid-cols-2 gap-6">
            @if(Schema::hasColumn('oil_products','name'))
            <div class="relative group">
                <input type="text" name="name" value="{{ old('name', $p->name ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" @required(true) />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Nome *</label>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            @if(Schema::hasColumn('oil_products','code'))
            <div class="relative group">
                <input type="text" name="code" value="{{ old('code', $p->code ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" @required(true) />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs flex items-center gap-1">Código * <span class="text-[10px] font-normal text-gray-400">SKU</span></label>
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Se vazio, gere manualmente antes de salvar.</p>
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="relative">
                <input type="text" name="brand" value="{{ old('brand', $p->brand ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Marca</label>
                @error('brand')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="text" name="viscosity" value="{{ old('viscosity', $p->viscosity ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Viscosidade</label>
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Ex: 5W30 / 10W40</p>
                @error('viscosity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', $p->unit_cost ?? 0) }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Custo Unitário (R$)</label>
                @error('unit_cost')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
            <div class="relative">
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $p->stock_quantity ?? 0) }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Estoque Atual</label>
                @error('stock_quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="number" name="reorder_level" value="{{ old('reorder_level', $p->reorder_level ?? 0) }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Nível de Reposição</label>
                @error('reorder_level')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="number" name="recommended_interval_km" value="{{ old('recommended_interval_km', $p->recommended_interval_km ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Int. KM (override)</label>
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Deixe em branco para usar a categoria do veículo.</p>
                @error('recommended_interval_km')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="number" name="recommended_interval_days" value="{{ old('recommended_interval_days', $p->recommended_interval_days ?? '') }}" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs">Int. Dias (override)</label>
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Deixe em branco para usar a categoria do veículo.</p>
                @error('recommended_interval_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="relative">
            <textarea name="description" rows="4" placeholder=" " class="peer w-full px-3 pt-5 pb-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $p->description ?? '') }}</textarea>
            <label class="absolute left-3 top-2 text-xs text-gray-500 dark:text-gray-400 transition-all peer-focus:top-2 peer-focus:text-xs">Descrição</label>
            @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Coluna Lateral Ajudas --}}
    <div class="space-y-6">
        <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 text-xs text-gray-600 dark:text-gray-300">
            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-200 text-sm">Orientações</h4>
            <ul class="list-disc ml-4 space-y-1">
                <li>Intervalos em branco usam a categoria do veículo.</li>
                <li>Custo unitário é gravado como referência para logs futuros.</li>
                <li>Alterações são auditadas (criação, edição, exclusão).</li>
                <li>Estoque decrementa somente em registros de troca.</li>
            </ul>
        </div>
        @isset($p)
        <a href="{{ route('oil-products.history',$p) }}" class="w-full text-center inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium shadow">
            <i class="fas fa-clock-rotate-left"></i> Histórico do Produto
        </a>
        @endisset
    </div>
</div>

<div class="mt-8 flex gap-3">
    <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold shadow">Salvar</button>
    <a href="{{ route('oil-products.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded font-medium">Cancelar</a>
</div>
