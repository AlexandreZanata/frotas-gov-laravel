@php($t = $tire ?? null)
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Série *</label>
        <input name="serial_number" required value="{{ old('serial_number', $t->serial_number ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
        @error('serial_number')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
        <input name="brand" value="{{ old('brand', $t->brand ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
        <input name="model" value="{{ old('model', $t->model ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dimensão</label>
        <input name="dimension" value="{{ old('dimension', $t->dimension ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Compra</label>
        <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($t->purchase_date ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sulco Inicial (mm)</label>
        <input type="number" step="0.01" name="initial_tread_depth_mm" value="{{ old('initial_tread_depth_mm', $t->initial_tread_depth_mm ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sulco Atual (mm)</label>
        <input type="number" step="0.01" name="current_tread_depth_mm" value="{{ old('current_tread_depth_mm', $t->current_tread_depth_mm ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vida Útil Esperada (Km)</label>
        <input type="number" name="expected_tread_life_km" value="{{ old('expected_tread_life_km', $t->expected_tread_life_km ?? '') }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
    </div>
    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
        <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $t->notes ?? '') }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-medium shadow">Salvar</button>
    <a href="{{ route('tires.index') }}" class="px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded font-medium">Cancelar</a>
</div>
