<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Novo Modelo de PDF') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            {{-- A CORREÇÃO ESTÁ AQUI: aspas simples envolvendo a chamada da função --}}
            <div x-data='pdfTemplateEditor(@json($pdfTemplate->toArray()))' x-init="init()" class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Coluna do Formulário --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form id="templateForm" action="{{ route('pdf-templates.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('pdf-templates.partials.form')
                            <div class="mt-8 flex justify-center">
                                <x-primary-button type="submit" class="px-10 py-3 text-sm">{{ __('Salvar Modelo') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Coluna da Pré-visualização --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:sticky lg:top-4 self-start transition-all duration-300" :class="{ 'ring-2 ring-indigo-500/30': previewLoading }">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4 flex items-center gap-2">
                            <span>Pré-visualização do PDF</span>
                            <span class="text-xs px-2 py-0.5 rounded" :class="templateData.real_time_preview ? 'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200' : 'bg-indigo-100 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-200'" x-text="templateData.real_time_preview ? 'Auto' : 'Manual'"></span>
                        </h3>
                        <div class="flex flex-wrap gap-3 items-center mb-4">
                            <span x-show="!previewLoading && !previewUrl" class="text-sm text-gray-500 dark:text-gray-400">Status: Aguardando</span>
                            <span x-show="!previewLoading && previewUrl" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Atualizado
                            </span>
                            <div class="flex gap-2 ml-auto">
                                <button type="button" @click="updatePreview(true)" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-md text-sm" :disabled="previewLoading || templateData.real_time_preview">Atualizar</button>
                            </div>
                        </div>
                        <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden transition-all" :class="{ 'opacity-50': previewLoading }">
                            <template x-if="previewUrl">
                                <iframe :src="previewUrl" class="w-full h-[800px] transition-opacity duration-300" :class="{ 'opacity-0': previewLoading, 'opacity-100': !previewLoading }"></iframe>
                            </template>
                            <div x-show="!previewUrl && !previewLoading" class="h-[400px] flex items-center justify-center text-gray-500 dark:text-gray-400">Gerando visualização...</div>
                            <div x-show="previewLoading" x-transition.opacity class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-white/70 dark:bg-gray-900/70 backdrop-blur-sm">
                                <div class="h-10 w-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 tracking-wide">Atualizando...</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">No modo Manual, clique em "Atualizar".</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function pdfTemplateEditor(initialData) {
            return {
                templateData: initialData,
                previewLoading: false,
                previewUrl: null,
                objectUrl: null,
                debounceTimeout: null,

                init() {
                    this.$watch('templateData', () => {
                        if (this.templateData.real_time_preview) this.updatePreview();
                    }, { deep: true });
                    this.updatePreview(true);
                },

                insertToken(fieldId, token) {
                    const textarea = document.getElementById(fieldId);
                    if (textarea) {
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        const text = textarea.value;
                        const before = text.substring(0, start);
                        const after = text.substring(end, text.length);
                        textarea.value = before + token + after;
                        this.templateData[fieldId] = textarea.value;
                        textarea.focus();
                        textarea.selectionStart = textarea.selectionEnd = start + token.length;
                    }
                },

                handleImageChange(e, scope) {
                    const file = e.target.files[0];
                    if (!file) return;
                    this.templateData[`${scope}_image_url`] = URL.createObjectURL(file);
                    this.updatePreview(true);
                },

                removeImage(scope) {
                    this.templateData[`${scope}_image_url`] = '';
                    const input = document.getElementById(`${scope}_image`);
                    if (input) input.value = '';
                    this.updatePreview(true);
                },

                updatePreview(forceImmediate = false) {
                    if (this.debounceTimeout) clearTimeout(this.debounceTimeout);
                    const delay = forceImmediate ? 0 : 500;

                    this.debounceTimeout = setTimeout(() => {
                        this.previewLoading = true;
                        const formData = new FormData(document.getElementById('templateForm'));

                        Object.keys(this.templateData).forEach(key => {
                            if (!formData.has(key)) {
                                let value = this.templateData[key];
                                if (typeof value === 'boolean') value = value ? 1 : 0;
                                if (value !== null) formData.append(key, value);
                            }
                        });

                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch(`{{ route('pdf-templates.ajax-preview.store') }}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: formData
                        })
                            .then(res => res.ok ? res.json() : res.json().then(err => Promise.reject(err)))
                            .then(data => {
                                if (data.success && data.pdf_base64) {
                                    if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
                                    const b64 = data.pdf_base64.split(',')[1];
                                    const byteChars = atob(b64);
                                    const byteNumbers = new Array(byteChars.length);
                                    for (let i = 0; i < byteChars.length; i++) byteNumbers[i] = byteChars.charCodeAt(i);
                                    const blob = new Blob([new Uint8Array(byteNumbers)], { type: 'application/pdf' });
                                    this.objectUrl = URL.createObjectURL(blob);
                                    this.previewUrl = `${this.objectUrl}#toolbar=0&view=FitH&v=${Date.now()}`;
                                } else {
                                    console.error('Falha no preview:', data.message);
                                    alert('Erro no servidor ao gerar preview: ' + data.message);
                                }
                            })
                            .catch(e => {
                                console.error('Erro na requisição de preview:', e.message || 'Erro de servidor.');
                                alert('Erro na requisição de preview: ' + (e.message || 'Verifique o console do navegador e os logs do Laravel.'));
                            })
                            .finally(() => { setTimeout(() => { this.previewLoading = false; }, 300); });
                    }, delay);
                }
            }
        }
    </script>
</x-app-layout>
