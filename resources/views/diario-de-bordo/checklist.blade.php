<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Diário de Bordo - Etapa 2 de 3
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('diario.storeChecklist', $vehicle) }}">
                    @csrf

                    <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                            Checklist do Veículo: {{ $vehicle->model }} ({{ $vehicle->plate }})
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Verifique os itens abaixo. O estado atual reflete a última vistoria realizada.
                        </p>

                        <div class="space-y-6">
                            @foreach($checklistItems as $item)
                                @php
                                    $lastAnswer = $lastAnswers[$item->id] ?? null;
                                    $lastStatus = $lastAnswer['status'] ?? '';
                                    $lastNotes = $lastAnswer['notes'] ?? '';
                                @endphp

                                <div x-data="{
                                        status: '{{ $lastStatus }}',
                                        notes: `{{ addslashes($lastNotes) }}`
                                     }"
                                     class="border-t border-gray-200 dark:border-gray-700 pt-6 first:border-t-0 first:pt-0">

                                    <label class="font-medium text-base text-gray-800 dark:text-gray-200">{{ $item->description }}</label>

                                    @if($lastAnswer && !empty($lastAnswer['notes']))
                                        <div class="mt-2 text-sm text-yellow-600 bg-yellow-100 dark:bg-yellow-900/50 dark:text-yellow-400 p-2 rounded-md">
                                            <strong>Obs. da última vistoria:</strong> {{ $lastAnswer['notes'] }}
                                        </div>
                                    @endif

                                    {{-- BOTÕES --}}
                                    <div class="mt-3 flex flex-wrap gap-4">
                                        {{-- Opção OK --}}
                                        <label @click="status = 'ok'" class="flex items-center p-2 rounded-lg cursor-pointer border-2 transition-all duration-200" :class="{ 'bg-green-500 text-white border-green-600': status === 'ok', 'bg-gray-100 dark:bg-gray-900 border-transparent hover:border-green-400 dark:hover:border-green-500': status !== 'ok' }">
                                            <input type="radio" name="answers[{{ $item->id }}][status]" value="ok" x-model="status" class="hidden" required>
                                            <div class="w-5 h-5 rounded-full mr-2 flex items-center justify-center border" :class="{ 'bg-white border-green-600': status === 'ok', 'bg-gray-300 dark:bg-gray-600 border-transparent': status !== 'ok' }">
                                                <svg x-show="status === 'ok'" class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            OK
                                        </label>
                                        {{-- Opção Atenção --}}
                                        <label @click="status = 'attention'" class="flex items-center p-2 rounded-lg cursor-pointer border-2 transition-all duration-200" :class="{ 'bg-yellow-500 text-white border-yellow-600': status === 'attention', 'bg-gray-100 dark:bg-gray-900 border-transparent hover:border-yellow-400 dark:hover:border-yellow-500': status !== 'attention' }">
                                            <input type="radio" name="answers[{{ $item->id }}][status]" value="attention" x-model="status" class="hidden" required>
                                            <div class="w-5 h-5 rounded-full mr-2 flex items-center justify-center border" :class="{ 'bg-white border-yellow-600': status === 'attention', 'bg-gray-300 dark:bg-gray-600 border-transparent': status !== 'attention' }">
                                                <svg x-show="status === 'attention'" class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            Atenção
                                        </label>
                                        {{-- Opção Problema --}}
                                        <label @click="status = 'problem'" class="flex items-center p-2 rounded-lg cursor-pointer border-2 transition-all duration-200" :class="{ 'bg-red-500 text-white border-red-600': status === 'problem', 'bg-gray-100 dark:bg-gray-900 border-transparent hover:border-red-400 dark:hover:border-red-500': status !== 'problem' }">
                                            <input type="radio" name="answers[{{ $item->id }}][status]" value="problem" x-model="status" class="hidden" required>
                                            <div class="w-5 h-5 rounded-full mr-2 flex items-center justify-center border" :class="{ 'bg-white border-red-600': status === 'problem', 'bg-gray-300 dark:bg-gray-600 border-transparent': status !== 'problem' }">
                                                <svg x-show="status === 'problem'" class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            Problema
                                        </label>
                                    </div>

                                    {{-- Campo de observação --}}
                                    <div x-show="status === 'attention' || status === 'problem'" x-transition class="mt-4">
                                        <x-input-label for="notes_{{ $item->id }}" value="Observações (Obrigatório se houver problema)" />
                                        <textarea id="notes_{{ $item->id }}"
                                                  class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                  name="answers[{{ $item->id }}][notes]"
                                                  x-model="notes"
                                                  ::required="status === 'problem'"
                                                  placeholder="Descreva o que foi encontrado...">{{ $lastNotes }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900 p-6">
                        <a href="{{ route('diario.selectVehicle') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                            ← Voltar
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Salvar Checklist e Avançar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Inicializa os valores corretamente após o carregamento da página
        document.addEventListener('alpine:init', () => {
            @foreach($checklistItems as $item)
            @php
                $lastAnswer = $lastAnswers[$item->id] ?? null;
                $lastStatus = $lastAnswer['status'] ?? '';
                $lastNotes = $lastAnswer['notes'] ?? '';
            @endphp

            @if($lastStatus)
            // Força a atualização do estado após a inicialização do Alpine
            setTimeout(() => {
                const component = document.querySelector('[x-data]');
                if (component) {
                    component.__x.$data.status = '{{ $lastStatus }}';
                    component.__x.$data.notes = `{{ addslashes($lastNotes) }}`;
                }
            }, 100);
            @endif
            @endforeach
        });
    </script>
</x-app-layout>
