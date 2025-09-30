<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Solicitações de Transferência de Veículos') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('vehicles.transfers.history') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-history mr-2"></i> Histórico
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Veículo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Origem/Destino</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solicitante</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($transfers as $transfer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $transfer->vehicle->brand }} - {{ $transfer->vehicle->model }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        De: {{ $transfer->originSecretariat->name }}<br>
                                        Para: {{ $transfer->destinationSecretariat->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $transfer->transfer_type == 'permanent' ? 'Permanente' : 'Temporário' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($transfer->status == 'pending') bg-yellow-100 text-yellow-800 @endif
                                            @if($transfer->status == 'approved') bg-green-100 text-green-800 @endif
                                            @if($transfer->status == 'rejected') bg-red-100 text-red-800 @endif
                                            @if($transfer->status == 'returned') bg-blue-100 text-blue-800 @endif
                                        ">
                                            {{ ucfirst($transfer->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $transfer->requester->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($transfer->status == 'pending')
                                            @can('manage-transfer', $transfer)
                                                <form action="{{ route('vehicles.transfers.approve', $transfer) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Aprovar</button>
                                                </form>
                                                <span class="mx-1">|</span>
                                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-transfer-{{ $transfer->id }}')" class="text-red-600 hover:text-red-900">Rejeitar</button>
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">Aguardando aprovação</span>
                                            @endcan
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">{{ $transfer->status == 'approved' ? 'Aprovado' : 'Rejeitado' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <x-modal name="reject-transfer-{{ $transfer->id }}" focusable>
                                    <form method="post" action="{{ route('vehicles.transfers.reject', $transfer) }}" class="p-6">
                                        @csrf
                                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ __('Rejeitar Transferência') }}
                                        </h2>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('Por favor, informe o motivo da rejeição.') }}
                                        </p>
                                        <div class="mt-6">
                                            <x-input-label for="approval_notes_{{ $transfer->id }}" value="{{ __('Motivo') }}" class="sr-only" />
                                            <textarea id="approval_notes_{{ $transfer->id }}" name="approval_notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required></textarea>
                                        </div>
                                        <div class="mt-6 flex justify-end">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                {{ __('Cancelar') }}
                                            </x-secondary-button>
                                            <x-danger-button class="ms-3">
                                                {{ __('Confirmar Rejeição') }}
                                            </x-danger-button>
                                        </div>
                                    </form>
                                </x-modal>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma solicitação de transferência encontrada.
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
