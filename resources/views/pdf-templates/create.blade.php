<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Novo Modelo de PDF') }}
        </h2>
    </x-slot>

    @php
    $initialData = [
        'name' => old('name', $pdfTemplate->name),
        'header_image_url' => $headerImageUrl,
        'footer_image_url' => $footerImageUrl,
        'header_image_existing' => $pdfTemplate->header_image ?? null,
        'footer_image_existing' => $pdfTemplate->footer_image ?? null,
        'header_image_vertical_position' => old('header_image_vertical_position', 'above'),
        'footer_image_vertical_position' => old('footer_image_vertical_position', 'above'),
        'margin_top' => old('margin_top', $pdfTemplate->margin_top ?? 15),
        'margin_bottom' => old('margin_bottom', $pdfTemplate->margin_bottom ?? 15),
        'margin_left' => old('margin_left', $pdfTemplate->margin_left ?? 15),
        'margin_right' => old('margin_right', $pdfTemplate->margin_right ?? 15),
        'font_family' => old('font_family', $pdfTemplate->font_family ?? 'helvetica'),
        'font_family_body' => old('font_family_body', $pdfTemplate->font_family_body ?? 'helvetica'),
        'header_font_family' => old('header_font_family', $pdfTemplate->header_font_family ?? 'helvetica'),
        'footer_font_family' => old('footer_font_family', $pdfTemplate->footer_font_family ?? 'helvetica'),
        'font_size_title' => old('font_size_title', $pdfTemplate->font_size_title ?? 14),
        'font_size_text' => old('font_size_text', $pdfTemplate->font_size_text ?? 11),
        'font_size_table' => old('font_size_table', $pdfTemplate->font_size_table ?? 10),
        'header_font_size' => old('header_font_size', $pdfTemplate->header_font_size ?? 12),
        'footer_font_size' => old('footer_font_size', $pdfTemplate->footer_font_size ?? 10),
        'font_style_title' => old('font_style_title', $pdfTemplate->font_style_title ?? 'B'),
        'font_style_text' => old('font_style_text', $pdfTemplate->font_style_text ?? ''),
        'header_font_style' => old('header_font_style', $pdfTemplate->header_font_style ?? 'B'),
        'footer_font_style' => old('footer_font_style', $pdfTemplate->footer_font_style ?? ''),
        'header_scope' => old('header_scope', $pdfTemplate->header_scope ?? 'all'),
        'footer_scope' => old('footer_scope', $pdfTemplate->footer_scope ?? 'all'),
        'header_image_align' => old('header_image_align', $pdfTemplate->header_image_align ?? 'L'),
        'header_text_align' => old('header_text_align', $pdfTemplate->header_text_align ?? 'R'),
        'footer_image_align' => old('footer_image_align', $pdfTemplate->footer_image_align ?? 'L'),
        'footer_text_align' => old('footer_text_align', $pdfTemplate->footer_text_align ?? 'R'),
        'header_text' => old('header_text', $pdfTemplate->header_text ?? 'Cabeçalho do documento'),
        'footer_text' => old('footer_text', $pdfTemplate->footer_text ?? 'Página {pagina} de {total_paginas}'),
        'body_text' => old('body_text', $pdfTemplate->body_text ?? '<h1>Exemplo de Relatório</h1><p>Este é um exemplo de relatório gerado pelo sistema. O texto aparece antes da tabela de dados.</p>'),
        'after_table_text' => old('after_table_text', $pdfTemplate->after_table_text ?? '<p>Este texto aparece após a tabela de dados.</p><p>Você pode adicionar informações adicionais, notas de rodapé ou qualquer conteúdo relevante aqui.</p>'),
        'header_image_width' => old('header_image_width', $pdfTemplate->header_image_width ?? 60),
        'header_image_height' => old('header_image_height', $pdfTemplate->header_image_height ?? 20),
        'footer_image_width' => old('footer_image_width', $pdfTemplate->footer_image_width ?? 40),
        'footer_image_height' => old('footer_image_height', $pdfTemplate->footer_image_height ?? 15),
        'table_style' => old('table_style', $pdfTemplate->table_style ?? 'grid'),
        'table_header_bg' => old('table_header_bg', $pdfTemplate->table_header_bg ?? '#f3f4f6'),
        'table_header_text' => old('table_header_text', $pdfTemplate->table_header_text ?? '#374151'),
        'table_row_height' => old('table_row_height', $pdfTemplate->table_row_height ?? 10),
        'show_table_lines' => old('show_table_lines', $pdfTemplate->show_table_lines ?? true),
        'use_zebra_stripes' => old('use_zebra_stripes', $pdfTemplate->use_zebra_stripes ?? false),
        'table_columns' => old('table_columns', $pdfTemplate->table_columns ?? 'Nome, Email, Telefone'),
        'real_time_preview' => old('real_time_preview', $pdfTemplate->real_time_preview ?? true),
    ];
    @endphp

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div x-data='pdfTemplateManager(@json($initialData))' class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form action="{{ route('pdf-templates.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('pdf-templates.partials.form')
                            <div class="mt-8 flex justify-center">
                                <x-primary-button class="px-10 py-3 text-sm">
                                    {{ __('Salvar Modelo') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:sticky lg:top-4 self-start transition-all duration-300" x-bind:class="{ 'ring-2 ring-indigo-500/30': previewLoading }">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4 flex items-center gap-2">
                            <span>Pré-visualização do PDF</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-200" x-text="templateData.real_time_preview ? 'Auto' : 'Manual'"></span>
                        </h3>
                        <div class="flex flex-wrap gap-3 items-center mb-4">
                            <span x-show="!previewLoading && !previewUrl" class="text-sm text-gray-500 dark:text-gray-400">
                                Status: Aguardando pré-visualização
                            </span>
                            <span x-show="!previewLoading && previewUrl" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                PDF atualizado
                            </span>
                            <button @click="updatePreview(true)" type="button" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-md text-sm transition-colors" :disabled="previewLoading">
                                Atualizar Agora
                            </button>
                        </div>

                        <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden group transition-all" x-bind:class="{ 'opacity-50': previewLoading }">
                            <template x-if="previewUrl">
                                <iframe x-bind:src="previewUrl" class="w-full h-[800px] transition-opacity duration-300" x-bind:class="{ 'opacity-0': previewLoading, 'opacity-100': !previewLoading }"></iframe>
                            </template>
                            <div x-show="!previewUrl && !previewLoading" class="h-[400px] flex items-center justify-center text-gray-500 dark:text-gray-400">
                                Gerando visualização do PDF...
                            </div>
                            <div x-show="previewLoading" x-transition.opacity class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-white/70 dark:bg-gray-900/70 backdrop-blur-sm">
                                <div class="h-10 w-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 tracking-wide">Atualizando pré-visualização...</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">As alterações são aplicadas automaticamente quando o modo em tempo real está ativo. Desative para atualizar manualmente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function pdfTemplateManager(initialData) {
            return {
                templateData: initialData,
                previewLoading: false,
                previewUrl: null,
                objectUrl: null,
                debounceTimeout: null,
                lastManual: 0,
                insertToken(field, token) {
                    if (!this.templateData[field]) this.templateData[field] = '';
                    this.templateData[field] += token;
                    this.updatePreview();
                },
                handleImageChange(e, scope) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        if (scope === 'header') {
                            this.templateData.header_image_url = url;
                            this.templateData.header_image_existing = null;
                        } else {
                            this.templateData.footer_image_url = url;
                            this.templateData.footer_image_existing = null;
                        }
                        this.updatePreview(true);
                    }
                },
                removeImage(scope) {
                    if (scope === 'header') {
                        this.templateData.header_image_url = '';
                        this.templateData.header_image_existing = null;
                        const input = document.getElementById('header_image');
                        if (input) input.value='';
                    } else {
                        this.templateData.footer_image_url = '';
                        this.templateData.footer_image_existing = null;
                        const input = document.getElementById('footer_image');
                        if (input) input.value='';
                    }
                    this.updatePreview(true);
                },
                updatePreview(forceImmediate = false) {
                    if (this.debounceTimeout) clearTimeout(this.debounceTimeout);
                    const delay = forceImmediate ? 0 : 500;
                    this.debounceTimeout = setTimeout(() => {
                        this.previewLoading = true;
                        const formData = new FormData();
                        Object.keys(this.templateData).forEach(key => {
                            if (!['header_image_url','footer_image_url'].includes(key)) {
                                formData.append(key, this.templateData[key]);
                            }
                        });
                        const headerInput = document.getElementById('header_image');
                        const footerInput = document.getElementById('footer_image');
                        if (headerInput && headerInput.files[0]) formData.append('header_image', headerInput.files[0]);
                        if (footerInput && footerInput.files[0]) formData.append('footer_image', footerInput.files[0]);
                        if ((!headerInput || !headerInput.files[0]) && this.templateData.header_image_existing) {
                            formData.append('header_image_existing', this.templateData.header_image_existing);
                        }
                        if ((!footerInput || !footerInput.files[0]) && this.templateData.footer_image_existing) {
                            formData.append('footer_image_existing', this.templateData.footer_image_existing);
                        }
                        // Normaliza booleans para 1/0
                        ['show_table_lines','use_zebra_stripes','real_time_preview'].forEach(k => {
                            if (k in this.templateData) {
                                formData.set(k, this.templateData[k] ? 1 : 0);
                            }
                        });
                        // Garante envio das posições e preview_mode
                        formData.set('preview_mode', '1');
                        formData.set('header_image_vertical_position', this.templateData.header_image_vertical_position || 'above');
                        formData.set('footer_image_vertical_position', this.templateData.footer_image_vertical_position || 'above');
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch('{{ route('pdf-templates.ajax-preview') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token },
                            body: formData
                        })
                        .then(r => { if(!r.ok) throw new Error('Erro '+r.status); return r.json(); })
                        .then(data => {
                            if (data.success) {
                                // Converter base64 em Blob para evitar warning do PDF.js com data URL
                                const b64 = data.pdf_base64.split(',')[1];
                                try {
                                    const byteChars = atob(b64);
                                    const byteNumbers = new Array(byteChars.length);
                                    for (let i = 0; i < byteChars.length; i++) byteNumbers[i] = byteChars.charCodeAt(i);
                                    const blob = new Blob([new Uint8Array(byteNumbers)], { type: 'application/pdf' });
                                    if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
                                    this.objectUrl = URL.createObjectURL(blob);
                                    const ts = Date.now();
                                    this.previewUrl = this.objectUrl + '#toolbar=0&v=' + ts;
                                } catch(e) {
                                    const ts = Date.now();
                                    this.previewUrl = data.pdf_base64 + '#toolbar=0&v=' + ts;
                                }
                            }
                        })
                        .catch(e => console.error('Erro preview:', e))
                        .finally(() => { setTimeout(() => { this.previewLoading = false; }, 150); });
                    }, delay);
                },
                init() {
                    this.updatePreview(true);
                    this.$watch('templateData', val => { if (val.real_time_preview) this.updatePreview(); }, { deep: true });
                }
            }
        }
    </script>
</x-app-layout>
