<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Gerenciar Frota de Veículos" icon="fas fa-car">
            <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-semibold uppercase tracking-wide">
                <i class="fas fa-plus"></i> Adicionar Veículo
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        {{-- Container de conteúdo principal --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca/Modelo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Placa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Secretaria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($vehicles as $vehicle)
                            @php($vStatus = $vehicle->status) {{-- relacionamento status() -> VehicleStatus --}}
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->plate }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $vehicle->secretariat->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($vStatus)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium text-white"
                                              style="background-color: {{ match($vStatus->color){ 'green'=>'#16a34a','red'=>'#dc2626','yellow'=>'#ca8a04','blue'=>'#2563eb','gray'=>'#6b7280', default=>'#4b5563'} }}">
                                            {{ $vStatus->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sem status</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $vehicles->links() }}</div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
