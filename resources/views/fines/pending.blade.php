<x-app-layout>
    <div class="p-6 max-w-5xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Multas Pendentes de Ciência</h1>
        @if(session('warning'))
            <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">{{ session('warning') }}</div>
        @endif
        <p class="text-sm mb-4 text-gray-600">Você deve registrar ciência antes de continuar usando o sistema.</p>
        <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left">Auto</th>
                        <th class="px-3 py-2 text-left">Veículo</th>
                        <th class="px-3 py-2 text-left">Valor</th>
                        <th class="px-3 py-2 text-left">Infrações</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fines as $fine)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ $fine->auto_number }}</td>
                            <td class="px-3 py-2">{{ $fine->vehicle?->plate }}</td>
                            <td class="px-3 py-2">R$ {{ number_format($fine->total_amount,2,',','.') }}</td>
                            <td class="px-3 py-2">{{ $fine->infractions->count() }}</td>
                            <td class="px-3 py-2 flex gap-2 text-xs">
                                <a href="{{ route('fines.show',$fine) }}" class="text-blue-600">Ver</a>
                                <form method="post" action="{{ route('fines.ack',$fine) }}" onsubmit="return confirm('Confirmar ciência desta multa?')">
                                    @csrf
                                    <button class="text-green-600">Dar Ciência</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">Nenhuma multa pendente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600">Voltar ao Painel</a>
        </div>
    </div>
</x-app-layout>

