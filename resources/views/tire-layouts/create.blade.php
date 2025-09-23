@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-plus-circle text-green-600"></i> Novo Layout de Pneus</h1>
        <a href="{{ route('tire-layouts.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
    </div>

    @if($errors->any())
        <div class="p-3 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc ml-4 space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tire-layouts.store') }}" class="space-y-6 bg-white dark:bg-gray-800 p-5 rounded shadow">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome *</label>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código Único *</label>
                <input name="code" value="{{ old('code') }}" required class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-gray-100">{{ old('description') }}</textarea>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posições (JSON) *</label>
                <button type="button" onclick="document.getElementById('positions').value=document.getElementById('examplePositions').textContent.trim();" class="text-xs px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">Exemplo</button>
            </div>
            <textarea id="positions" name="positions" rows="10" required class="font-mono text-xs leading-relaxed mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-gray-800 dark:text-gray-100">{{ old('positions') }}</textarea>
            <p class="text-[11px] text-gray-500 dark:text-gray-400">Use um array JSON. Se fornecer <code>x</code> e <code>y</code> (0–100) o layout será posicionado; caso contrário, será exibido em grade.</p>
            <pre id="examplePositions" class="hidden">[
  {"code":"FL","label":"Dianteiro Esquerdo","x":20,"y":30},
  {"code":"FR","label":"Dianteiro Direito","x":80,"y":30},
  {"code":"RL","label":"Traseiro Esquerdo","x":20,"y":75},
  {"code":"RR","label":"Traseiro Direito","x":80,"y":75}
]</pre>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('tire-layouts.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded text-sm text-gray-800 dark:text-gray-100">Cancelar</a>
            <button class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium">Salvar</button>
        </div>
    </form>
</div>
@endsection
