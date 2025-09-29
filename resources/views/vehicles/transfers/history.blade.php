<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Histórico de Transferências de Veículos') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('vehicles.transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-list mr-2"></i> Solicitações
                </a>
                <a href="{{ route('vehicles.transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i> Nova Transferência
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('vehicles.transfers.history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendentes</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprovados</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeitados</option>
                                <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Devolvidos</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="type" value="Tipo" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="permanent" {{ request('type') == 'permanent' ? 'selected' : '' }}>Permanentes</option>
                                <option value="temporary" {{ request('type') == 'temporary' ? 'selected' : '' }}>Temporários</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" value="Data Inicial" />
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                        <div>
                            <x-input-label for="date_to" value="Data Final" />
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            @if(request()->anyFilled(['status', 'type', 'date_from', 'date_to']))
                                <a href="{{ route('vehicles.transfers.history') }}" class="mr-2 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Limpar Filtros
                                </a>
                            @endif
                            <x-primary-button>
                                <i class="fas fa-filter mr-2"></i> Filtrar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data/Hora</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Veículo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Origem/Destino</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solicitante</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aprovador</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detalhes</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($transfers as $transfer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        Criado: {{ $transfer->created_at->format('d/m/Y H:i') }}<br>
                                        @if($transfer->created_at != $transfer->updated_at)
                                            Atualizado: {{ $transfer->updated_at->format('d/m/Y H:i') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $transfer->vehicle->brand }} - {{ $transfer->vehicle->model }}<br>
                                        <span class="text-xs">Placa: {{ $transfer->vehicle->plate }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        De: {{ $transfer->originSecretariat->name }}<br>
                                        Para: {{ $transfer->destinationSecretariat->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transfer->transfer_type == 'permanent' ? 'Permanente' : 'Temporário' }}
                                        @if($transfer->transfer_type == 'temporary')
                                            <br>
                                            <span class="text-xs">
                                                {{ $transfer->start_date ? date('d/m/Y', strtotime($transfer->start_date)) : 'N/A' }} até
                                                {{ $transfer->end_date ? date('d/m/Y', strtotime($transfer->end_date)) : 'N/A' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($transfer->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                                            @if($transfer->status == 'approved') bg-green-100 text-green-800 @endif
                                            @if($transfer->status == 'rejected') bg-red-100 text-red-800 @endif
                                            @if($transfer->status == 'returned') bg-blue-100 text-blue-800 @endif
                                        ">
                                            @if($transfer->status == 'pending') Pendente @endif
                                            @if($transfer->status == 'approved') Aprovada @endif
                                            @if($transfer->status == 'rejected') Rejeitada @endif
                                            @if($transfer->status == 'returned') Devolvida @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transfer->requester->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transfer->approver ? $transfer->approver->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'transfer-details-{{ $transfer->id }}')" class="text-indigo-600 hover:text-indigo-900">
                                            Detalhes
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de Detalhes -->
                                <x-modal name="transfer-details-{{ $transfer->id }}" focusable>
                                    <div class="p-6">
                                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                            {{ __('Detalhes da Transferência') }}
                                        </h2>

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Veículo</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->vehicle->brand }} {{ $transfer->vehicle->model }} - Placa: {{ $transfer->vehicle->plate }}
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Secretaria de Origem</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->originSecretariat->name }}
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Secretaria de Destino</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->destinationSecretariat->name }}
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tipo de Transferência</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->transfer_type == 'permanent' ? 'Permanente' : 'Temporário' }}
                                                @if($transfer->transfer_type == 'temporary')
                                                    ({{ $transfer->start_date ? date('d/m/Y', strtotime($transfer->start_date)) : 'N/A' }} até
                                                    {{ $transfer->end_date ? date('d/m/Y', strtotime($transfer->end_date)) : 'N/A' }})
                                                @endif
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Solicitante</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->requester->name }} ({{ $transfer->created_at->format('d/m/Y H:i') }})
                                            </p>
                                        </div>

                                        @if($transfer->request_notes)
                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Observações da Solicitação</h3>
                                            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                                {{ $transfer->request_notes }}
                                            </p>
                                        </div>
                                        @endif

                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                @if($transfer->status == 'pending') Pendente @endif
                                                @if($transfer->status == 'approved') Aprovada @endif
                                                @if($transfer->status == 'rejected') Rejeitada @endif
                                                @if($transfer->status == 'returned') Devolvida @endif
                                            </p>
                                        </div>

                                        @if($transfer->approver)
                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Aprovado/Rejeitado por</h3>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $transfer->approver->name }} ({{ $transfer->updated_at->format('d/m/Y H:i') }})
                                            </p>
                                        </div>
                                        @endif

                                        @if($transfer->approval_notes)
                                        <div class="mb-4">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Observações da Aprovação/Rejeição</h3>
                                            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                                {{ $transfer->approval_notes }}
                                            </p>
                                        </div>
                                        @endif

                                        <div class="flex justify-end mt-6">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                {{ __('Fechar') }}
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </x-modal>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                        Nenhum registro de transferência encontrado.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $transfers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
