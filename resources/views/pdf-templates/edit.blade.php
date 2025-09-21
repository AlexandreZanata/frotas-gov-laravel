<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Modelo de PDF') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            {{-- Inicializa o Alpine.js com os dados do template --}}
            @php
                // Prepara as URLs das imagens para o Alpine, tratando o caso de não haver imagem
                $headerImageUrl = $pdfTemplate->header_image ? Storage::url($pdfTemplate->header_image) : '';
                $footerImageUrl = $pdfTemplate->footer_image ? Storage::url($pdfTemplate->footer_image) : '';
            @endphp
            <div x-data="{
                templateData: {
                    name: '{{ old('name', $pdfTemplate->name) }}',
                    header_image_url: '{{ $headerImageUrl }}',
                    footer_image_url: '{{ $footerImageUrl }}',
                    margin_top: {{ old('margin_top', $pdfTemplate->margin_top) }},
                    margin_bottom: {{ old('margin_bottom', $pdfTemplate->margin_bottom) }},
                    margin_left: {{ old('margin_left', $pdfTemplate->margin_left) }},
                    margin_right: {{ old('margin_right', $pdfTemplate->margin_right) }},
                    font_family: '{{ old('font_family', $pdfTemplate->font_family) }}',
                    font_family_body: '{{ old('font_family_body', $pdfTemplate->font_family_body) }}',
                    header_font_family: '{{ old('header_font_family', $pdfTemplate->header_font_family) }}',
                    footer_font_family: '{{ old('footer_font_family', $pdfTemplate->footer_font_family) }}',
                    font_size_title: {{ old('font_size_title', $pdfTemplate->font_size_title) }},
                    font_size_text: {{ old('font_size_text', $pdfTemplate->font_size_text) }},
                    font_size_table: {{ old('font_size_table', $pdfTemplate->font_size_table) }},
                    header_font_size: {{ old('header_font_size', $pdfTemplate->header_font_size) }},
                    footer_font_size: {{ old('footer_font_size', $pdfTemplate->footer_font_size) }},
                    font_style_title: '{{ old('font_style_title', $pdfTemplate->font_style_title) }}',
                    font_style_text: '{{ old('font_style_text', $pdfTemplate->font_style_text) }}',
                    header_font_style: '{{ old('header_font_style', $pdfTemplate->header_font_style) }}',
                    footer_font_style: '{{ old('footer_font_style', $pdfTemplate->footer_font_style) }}',
                    header_scope: '{{ old('header_scope', $pdfTemplate->header_scope) }}',
                    footer_scope: '{{ old('footer_scope', $pdfTemplate->footer_scope) }}',
                    header_image_align: '{{ old('header_image_align', $pdfTemplate->header_image_align) }}',
                    header_text_align: '{{ old('header_text_align', $pdfTemplate->header_text_align) }}',
                    footer_image_align: '{{ old('footer_image_align', $pdfTemplate->footer_image_align) }}',
                    footer_text_align: '{{ old('footer_text_align', $pdfTemplate->footer_text_align) }}',
                    header_text: `{!! old('header_text', $pdfTemplate->header_text) !!}`,
                    footer_text: `{!! old('footer_text', $pdfTemplate->footer_text) !!}`,
                    body_text: `{!! old('body_text', $pdfTemplate->body_text) !!}`,
                    after_table_text: `{!! old('after_table_text', $pdfTemplate->after_table_text) !!}`,
                    header_image_width: {{ old('header_image_width', $pdfTemplate->header_image_width) }},
                    header_image_height: {{ old('header_image_height', $pdfTemplate->header_image_height) }},
                    footer_image_width: {{ old('footer_image_width', $pdfTemplate->footer_image_width) }},
                    footer_image_height: {{ old('footer_image_height', $pdfTemplate->footer_image_height) }},
                    table_style: '{{ old('table_style', $pdfTemplate->table_style) }}',
                    table_header_bg: '{{ old('table_header_bg', $pdfTemplate->table_header_bg) }}',
                    table_header_text: '{{ old('table_header_text', $pdfTemplate->table_header_text) }}',
                    table_row_height: {{ old('table_row_height', $pdfTemplate->table_row_height) }},
                    show_table_lines: {{ old('show_table_lines', $pdfTemplate->show_table_lines) ? 'true' : 'false' }},
                    use_zebra_stripes: {{ old('use_zebra_stripes', $pdfTemplate->use_zebra_stripes) ? 'true' : 'false' }},
                    table_columns: '{{ old('table_columns', $pdfTemplate->table_columns) }}',
                    real_time_preview: {{ old('real_time_preview', $pdfTemplate->real_time_preview) ? 'true' : 'false' }}
                },
                previewLoading: false,
                previewUrl: null,
                debounceTimeout: null,

                init() {
                    // Generate initial preview immediately, without waiting for changes
                    this.updatePreview();

                    // Observe changes to templateData for real-time preview
                    this.$watch('templateData', (value) => {
                        if (value.real_time_preview) {
                            this.updatePreview();
                        }
                    }, { deep: true });
                },

                updatePreview() {
                    // Clear any existing timeout
                    if (this.debounceTimeout) {
                        clearTimeout(this.debounceTimeout);
                    }

                    // Debounce the preview update to avoid too many requests
                    this.debounceTimeout = setTimeout(() => {
                        this.previewLoading = true;

                        // Create a form data object with the current template data
                        const formData = new FormData();
                        Object.keys(this.templateData).forEach(key => {
                            if (key !== 'header_image_url' && key !== 'footer_image_url') {
                                formData.append(key, this.templateData[key]);
                            }
                        });

                        // Get CSRF token
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        // Send AJAX request to get preview
                        fetch('{{ route('pdf-templates.ajax-preview') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Erro na resposta do servidor: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                this.previewUrl = data.pdf_base64;
                            } else {
                                console.error('Resposta de sucesso falsa');
                            }
                            this.previewLoading = false;
                        })
                        .catch(error => {
                            console.error('Erro ao gerar preview:', error);
                            this.previewLoading = false;
                        });
                    }, 500);
                }
            }"
            class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form action="{{ route('pdf-templates.update', $pdfTemplate) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Inclui o formulário parcial --}}
                            @include('pdf-templates.partials.form', [
                                'template' => $pdfTemplate,
                                'headerImageUrl' => $headerImageUrl,
                                'footerImageUrl' => $footerImageUrl
                            ])

                            {{-- BOTÃO CENTRALIZADO --}}
                            <div class="mt-8 flex justify-center">
                                <x-primary-button class="px-10 py-3 text-sm">
                                    {{ __('Atualizar Modelo') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Painel de Pré-visualização -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Pré-visualização do PDF</h3>

                        <div class="flex justify-between items-center mb-4">
                            <span x-show="!previewLoading && !previewUrl" class="text-sm text-gray-500 dark:text-gray-400">
                                Status: Aguardando pré-visualização
                            </span>
                            <span x-show="!previewLoading && previewUrl" class="text-sm text-green-600 dark:text-green-400">
                                Status: PDF gerado com sucesso
                            </span>

                            <div class="flex gap-2">
                                <a href="{{ route('pdf-templates.preview', $pdfTemplate) }}" target="_blank" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm">
                                    Abrir em Nova Janela
                                </a>
                                <button
                                    @click="updatePreview()"
                                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm"
                                    :disabled="previewLoading"
                                >
                                    Atualizar Pré-visualização
                                </button>
                            </div>
                        </div>

                        <div x-show="previewLoading" class="flex justify-center my-4">
                            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-gray-900 dark:border-white"></div>
                        </div>

                        <div x-show="!previewLoading && !previewUrl" class="text-center py-10 text-gray-500 dark:text-gray-400">
                            <p>Gerando visualização do PDF...</p>
                        </div>

                        <div x-show="!previewLoading && previewUrl" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <iframe x-bind:src="previewUrl" class="w-full h-[800px]"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
