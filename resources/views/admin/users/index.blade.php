<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gerenciamento de Usuários') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Botão para criar novo usuário --}}
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('admin.users.create') }}">
                            <x-primary-button>
                                {{ __('Novo Usuário') }}
                            </x-primary-button>
                        </a>
                    </div>

                    {{-- Tabela de Usuários --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Perfil</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Perfil</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $user->role->name ?? 'N/A' }}
                                            </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        @if($user->status == 'active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        {{-- Botão Editar --}}
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Editar</a>

                                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline" id="reset-form-{{ $user->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                    @click="$dispatch('open-confirm-modal', {
                    message: 'Tem certeza que deseja resetar a senha deste usuário para a padrão?',
                    title: 'Resetar Senha',
                    targetFormId: 'reset-form-{{ $user->id }}'
                })"
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-200 cursor-pointer">
                                                Resetar Senha
                                            </button>
                                        </form>

                                        {{-- Formulário e Botão Excluir MODIFICADO --}}
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" id="delete-form-{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    @click="$dispatch('open-confirm-modal', {
                                                        message: 'Atenção! Esta ação é irreversível. Deseja realmente excluir este usuário?',
                                                        title: 'Confirmar Exclusão',
                                                        targetFormId: 'delete-form-{{ $user->id }}'
                                                    })"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 cursor-pointer">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout><?php
