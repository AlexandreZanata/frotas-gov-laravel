<x-app-layout>
    <div class="p-6 max-w-xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Verificar Autenticidade de Multa</h1>
        <form method="post" action="{{ route('fines.verify.submit') }}" class="space-y-4 bg-white dark:bg-gray-800 p-4 rounded shadow">
            @csrf
            <div>
                <label class="block text-sm font-medium">Código de Autenticidade</label>
                <input name="auth_code" value="{{ old('auth_code', $data['auth_code'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2" />
                @error('auth_code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Auto de Infração</label>
                <input name="auto_number" value="{{ old('auto_number', $data['auto_number'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2" />
                @error('auto_number')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Placa do Veículo</label>
                <input name="plate" value="{{ old('plate', $data['plate'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2" />
                @error('plate')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button class="bg-blue-600 text-white px-5 py-2 rounded">Verificar</button>
                <a href="{{ route('dashboard') }}" class="text-gray-600 px-5 py-2">Voltar</a>
            </div>
        </form>
        @isset($result)
            <div class="mt-6">
                @if($result)
                    <div class="p-4 bg-green-100 text-green-800 rounded">
                        <h2 class="font-semibold">Documento Válido</h2>
                        <p class="text-sm mt-2">Auto: {{ $result->auto_number }} | Veículo: {{ $result->vehicle?->plate }}</p>
                        <p class="text-sm">Total: R$ {{ number_format($result->total_amount,2,',','.') }}</p>
                        <p class="text-sm">Status: {{ $result->status }}</p>
                    </div>
                @else
                    <div class="p-4 bg-red-100 text-red-700 rounded">
                        <p class="font-semibold">Não foi encontrada multa com os dados informados.</p>
                    </div>
                @endif
            </div>
        @endisset
    </div>
</x-app-layout>

