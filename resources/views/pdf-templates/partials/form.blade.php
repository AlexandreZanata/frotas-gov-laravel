<div class="space-y-8">
    {{-- CONFIGURAÇÕES GERAIS --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Configurações Gerais</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <x-input-label for="name" value="Nome do Modelo" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" x-model="templateData.name" required />
            </div>
            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input id="real_time_preview" name="real_time_preview" type="checkbox" class="form-checkbox rounded" x-model="templateData.real_time_preview">
                    <label for="real_time_preview" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Atualizar visualização em tempo real</label>
                </div>
            </div>
        </div>
    </div>

    {{-- CABEÇALHO --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Cabeçalho</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="header_scope" value="Aplicar em" />
                <select id="header_scope" name="header_scope" x-model="templateData.header_scope" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="all">Todas as páginas</option>
                    <option value="first">Apenas na primeira página</option>
                    <option value="none">Não usar cabeçalho</option>
                </select>
            </div>
            <div>
                <x-input-label for="header_image" value="Imagem do Cabeçalho" />
                <div class="mt-1 relative">
                    <label class="flex items-center justify-center w-full h-10 px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none">
                        <span class="text-sm text-gray-600 dark:text-gray-400" x-text="templateData.header_image_url ? 'Alterar imagem' : 'Escolher arquivo'"></span>
                        <input id="header_image" name="header_image" type="file" class="sr-only"
                               @change="handleImageChange($event, 'header')">
                    </label>
                </div>
                <div x-show="templateData.header_image_url" class="mt-2 relative inline-block group">
                    <img :src="templateData.header_image_url" :style="{ width: (templateData.header_image_width || 60) + 'px', height: (templateData.header_image_height || 20) + 'px' }" class="max-w-full max-h-24 object-contain border dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 p-1" />
                    <button type="button" @click="removeImage('header')" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs shadow">×</button>
                </div>
            </div>
            <div>
                <x-input-label for="header_image_width" value="Largura da Imagem (mm)" />
                <x-text-input id="header_image_width" name="header_image_width" type="number" class="mt-1 block w-full" x-model.number="templateData.header_image_width" />
            </div>
            <div>
                <x-input-label for="header_image_height" value="Altura da Imagem (mm)" />
                <x-text-input id="header_image_height" name="header_image_height" type="number" class="mt-1 block w-full" x-model.number="templateData.header_image_height" />
            </div>
            <div>
                <x-input-label for="header_image_align" value="Alinhamento Horizontal da Imagem" />
                <select id="header_image_align" name="header_image_align" x-model="templateData.header_image_align" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="L">Esquerda</option>
                    <option value="C">Centralizado</option>
                    <option value="R">Direita</option>
                </select>
            </div>
            <div>
                <x-input-label for="header_image_vertical_position" value="Posição da Imagem" />
                <select id="header_image_vertical_position" name="header_image_vertical_position" x-model="templateData.header_image_vertical_position" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="above">Acima do texto</option>
                    <option value="below">Abaixo do texto</option>
                    <option value="inline-left">Inline (Imagem à esquerda)</option>
                    <option value="inline-right">Inline (Imagem à direita)</option>
                </select>
            </div>
            <div>
                <x-input-label for="header_text_align" value="Alinhamento do Texto" />
                <select id="header_text_align" name="header_text_align" x-model="templateData.header_text_align" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="L">Esquerda</option>
                    <option value="C">Centralizado</option>
                    <option value="R">Direita</option>
                </select>
            </div>
            <div>
                <x-input-label for="header_font_family" value="Fonte do Cabeçalho" />
                <select id="header_font_family" name="header_font_family" x-model="templateData.header_font_family" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="helvetica">Helvetica</option>
                    <option value="times">Times New Roman</option>
                    <option value="courier">Courier</option>
                    <option value="dejavusans">DejaVu Sans</option>
                    <option value="arial">Arial</option>
                    <option value="verdana">Verdana</option>
                </select>
            </div>
            <div>
                <x-input-label for="header_font_size" value="Tamanho da Fonte (pt)" />
                <x-text-input id="header_font_size" name="header_font_size" type="number" class="mt-1 block w-full" x-model.number="templateData.header_font_size" />
            </div>
            <div>
                <x-input-label for="header_font_style" value="Estilo da Fonte" />
                <select id="header_font_style" name="header_font_style" x-model="templateData.header_font_style" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Normal</option>
                    <option value="B">Negrito</option>
                    <option value="I">Itálico</option>
                    <option value="BI">Negrito e Itálico</option>
                    <option value="U">Sublinhado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="header_text" value="Texto do Cabeçalho" />
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="token in ['{data}','{pagina}','{empresa}']" :key="token">
                        <button type="button" @click="insertToken('header_text', token)" class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200"><span x-text="token"></span></button>
                    </template>
                </div>
                <textarea id="header_text" name="header_text" x-model="templateData.header_text" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                <p class="mt-1 text-sm text-gray-500">Variáveis disponíveis: {data}, {pagina}, {empresa}</p>
            </div>
        </div>
    </div>

    {{-- CORPO DO DOCUMENTO --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Corpo do Documento</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="margin_top" value="Margem Superior (mm)" />
                <x-text-input id="margin_top" name="margin_top" type="number" class="mt-1 block w-full" x-model.number="templateData.margin_top" />
            </div>
            <div>
                <x-input-label for="margin_bottom" value="Margem Inferior (mm)" />
                <x-text-input id="margin_bottom" name="margin_bottom" type="number" class="mt-1 block w-full" x-model.number="templateData.margin_bottom" />
            </div>
            <div>
                <x-input-label for="margin_left" value="Margem Esquerda (mm)" />
                <x-text-input id="margin_left" name="margin_left" type="number" class="mt-1 block w-full" x-model.number="templateData.margin_left" />
            </div>
            <div>
                <x-input-label for="margin_right" value="Margem Direita (mm)" />
                <x-text-input id="margin_right" name="margin_right" type="number" class="mt-1 block w-full" x-model.number="templateData.margin_right" />
            </div>
            <div>
                <x-input-label for="font_family" value="Fonte Principal" />
                <select id="font_family" name="font_family" x-model="templateData.font_family" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="helvetica">Helvetica</option>
                    <option value="times">Times New Roman</option>
                    <option value="courier">Courier</option>
                    <option value="dejavusans">DejaVu Sans</option>
                    <option value="arial">Arial</option>
                    <option value="verdana">Verdana</option>
                </select>
            </div>
            <div>
                <x-input-label for="font_family_body" value="Fonte do Corpo" />
                <select id="font_family_body" name="font_family_body" x-model="templateData.font_family_body" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="helvetica">Helvetica</option>
                    <option value="times">Times New Roman</option>
                    <option value="courier">Courier</option>
                    <option value="dejavusans">DejaVu Sans</option>
                    <option value="arial">Arial</option>
                    <option value="verdana">Verdana</option>
                </select>
            </div>
            <div>
                <x-input-label for="font_size_title" value="Tamanho dos Títulos (pt)" />
                <x-text-input id="font_size_title" name="font_size_title" type="number" class="mt-1 block w-full" x-model.number="templateData.font_size_title" />
            </div>
            <div>
                <x-input-label for="font_size_text" value="Tamanho do Texto (pt)" />
                <x-text-input id="font_size_text" name="font_size_text" type="number" class="mt-1 block w-full" x-model.number="templateData.font_size_text" />
            </div>
            <div>
                <x-input-label for="font_style_title" value="Estilo dos Títulos" />
                <select id="font_style_title" name="font_style_title" x-model="templateData.font_style_title" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Normal</option>
                    <option value="B">Negrito</option>
                    <option value="I">Itálico</option>
                    <option value="BI">Negrito e Itálico</option>
                    <option value="U">Sublinhado</option>
                </select>
            </div>
            <div>
                <x-input-label for="font_style_text" value="Estilo do Texto" />
                <select id="font_style_text" name="font_style_text" x-model="templateData.font_style_text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Normal</option>
                    <option value="B">Negrito</option>
                    <option value="I">Itálico</option>
                    <option value="BI">Negrito e Itálico</option>
                    <option value="U">Sublinhado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="body_text" value="Texto Livre (antes da tabela)" />
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="token in ['<h1>','<p>','{data}']" :key="token">
                        <button type="button" @click="insertToken('body_text', token)" class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200"><span x-text="token"></span></button>
                    </template>
                </div>
                <textarea id="body_text" name="body_text" x-model="templateData.body_text" rows="5" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
        </div>
    </div>

    {{-- TABELA DE DADOS --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tabela de Dados</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="table_style" value="Estilo da Tabela" />
                <select id="table_style" name="table_style" x-model="templateData.table_style" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="grid">Com Bordas (Grid)</option>
                    <option value="striped">Listrada (Sem Bordas)</option>
                    <option value="minimal">Minimalista</option>
                </select>
            </div>
            <div>
                <x-input-label for="font_size_table" value="Tamanho da Fonte da Tabela (pt)" />
                <x-text-input id="font_size_table" name="font_size_table" type="number" class="mt-1 block w-full" x-model.number="templateData.font_size_table" />
            </div>
            <div>
                <x-input-label for="table_header_bg" value="Cor de Fundo do Cabeçalho" />
                <x-text-input id="table_header_bg" name="table_header_bg" type="color" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="templateData.table_header_bg" />
            </div>
            <div>
                <x-input-label for="table_header_text" value="Cor do Texto do Cabeçalho" />
                <x-text-input id="table_header_text" name="table_header_text" type="color" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="templateData.table_header_text" />
            </div>
            <div>
                <x-input-label for="table_row_height" value="Altura da Linha (mm)" />
                <x-text-input id="table_row_height" name="table_row_height" type="number" class="mt-1 block w-full" x-model.number="templateData.table_row_height" />
            </div>
            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input id="show_table_lines" name="show_table_lines" type="checkbox" class="form-checkbox rounded" x-model="templateData.show_table_lines">
                    <label for="show_table_lines" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Mostrar linhas entre as colunas</label>
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input id="use_zebra_stripes" name="use_zebra_stripes" type="checkbox" class="form-checkbox rounded" x-model="templateData.use_zebra_stripes">
                    <label for="use_zebra_stripes" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Usar linhas zebradas</label>
                </div>
            </div>
            <div class="md:col-span-2">
                <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Visualização da Tabela</h4>
                <div class="overflow-x-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700" :style="{ backgroundColor: templateData.table_header_bg }">
                            <tr>
                                <template x-for="col in 3" :key="col">
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: templateData.table_header_text }">Coluna <span x-text="col"></span></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="i in 4" :key="i">
                                <tr :class="{ 'bg-gray-50 dark:bg-gray-700': templateData.use_zebra_stripes && (i % 2 === 0) }">
                                    <template x-for="j in 3" :key="j">
                                        <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">Dados <span x-text="i"></span>-<span x-text="j"></span></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="table_columns" value="Colunas da Tabela (separadas por vírgula)" />
                <x-text-input id="table_columns" name="table_columns" type="text" class="mt-1 block w-full" x-model="templateData.table_columns" placeholder="Nome, Email, Telefone, etc" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="after_table_text" value="Texto Livre (após a tabela)" />
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="token in ['<p>','{data}','{pagina}']" :key="token">
                        <button type="button" @click="insertToken('after_table_text', token)" class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200"><span x-text="token"></span></button>
                    </template>
                </div>
                <textarea id="after_table_text" name="after_table_text" x-model="templateData.after_table_text" rows="5" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
        </div>
    </div>

    {{-- RODAPÉ --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Rodapé</h3>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="footer_scope" value="Aplicar em" />
                <select id="footer_scope" name="footer_scope" x-model="templateData.footer_scope" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="all">Todas as páginas</option>
                    <option value="first">Apenas na primeira página</option>
                    <option value="none">Não usar rodapé</option>
                </select>
            </div>
            <div>
                <x-input-label for="footer_image" value="Imagem do Rodapé" />
                <div class="mt-1 relative">
                    <label class="flex items-center justify-center w-full h-10 px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none">
                        <span class="text-sm text-gray-600 dark:text-gray-400" x-text="templateData.footer_image_url ? 'Alterar imagem' : 'Escolher arquivo'"></span>
                        <input id="footer_image" name="footer_image" type="file" class="sr-only"
                               @change="handleImageChange($event, 'footer')">
                    </label>
                </div>
                <div x-show="templateData.footer_image_url" class="mt-2 relative inline-block">
                    <img :src="templateData.footer_image_url" :style="{ width: (templateData.footer_image_width || 40) + 'px', height: (templateData.footer_image_height || 15) + 'px' }" class="max-w-full max-h-20 object-contain border dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 p-1" />
                    <button type="button" @click="removeImage('footer')" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs shadow">×</button>
                </div>
            </div>
            <div>
                <x-input-label for="footer_image_width" value="Largura da Imagem (mm)" />
                <x-text-input id="footer_image_width" name="footer_image_width" type="number" class="mt-1 block w-full" x-model.number="templateData.footer_image_width" />
            </div>
            <div>
                <x-input-label for="footer_image_height" value="Altura da Imagem (mm)" />
                <x-text-input id="footer_image_height" name="footer_image_height" type="number" class="mt-1 block w-full" x-model.number="templateData.footer_image_height" />
            </div>
            <div>
                <x-input-label for="footer_image_align" value="Alinhamento Horizontal da Imagem" />
                <select id="footer_image_align" name="footer_image_align" x-model="templateData.footer_image_align" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="L">Esquerda</option>
                    <option value="C">Centralizado</option>
                    <option value="R">Direita</option>
                </select>
            </div>
            <div>
                <x-input-label for="footer_image_vertical_position" value="Posição da Imagem" />
                <select id="footer_image_vertical_position" name="footer_image_vertical_position" x-model="templateData.footer_image_vertical_position" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="above">Acima do texto</option>
                    <option value="below">Abaixo do texto</option>
                    <option value="inline-left">Inline (Imagem à esquerda)</option>
                    <option value="inline-right">Inline (Imagem à direita)</option>
                </select>
            </div>
            <div>
                <x-input-label for="footer_text_align" value="Alinhamento do Texto" />
                <select id="footer_text_align" name="footer_text_align" x-model="templateData.footer_text_align" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="L">Esquerda</option>
                    <option value="C">Centralizado</option>
                    <option value="R">Direita</option>
                </select>
            </div>
            <div>
                <x-input-label for="footer_font_family" value="Fonte do Rodapé" />
                <select id="footer_font_family" name="footer_font_family" x-model="templateData.footer_font_family" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="helvetica">Helvetica</option>
                    <option value="times">Times New Roman</option>
                    <option value="courier">Courier</option>
                    <option value="dejavusans">DejaVu Sans</option>
                    <option value="arial">Arial</option>
                    <option value="verdana">Verdana</option>
                </select>
            </div>
            <div>
                <x-input-label for="footer_font_size" value="Tamanho da Fonte (pt)" />
                <x-text-input id="footer_font_size" name="footer_font_size" type="number" class="mt-1 block w-full" x-model.number="templateData.footer_font_size" />
            </div>
            <div>
                <x-input-label for="footer_font_style" value="Estilo da Fonte" />
                <select id="footer_font_style" name="footer_font_style" x-model="templateData.footer_font_style" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Normal</option>
                    <option value="B">Negrito</option>
                    <option value="I">Itálico</option>
                    <option value="BI">Negrito e Itálico</option>
                    <option value="U">Sublinhado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="footer_text" value="Texto do Rodapé" />
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="token in ['{data}','{pagina}','{total_paginas}']" :key="token">
                        <button type="button" @click="insertToken('footer_text', token)" class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200"><span x-text="token"></span></button>
                    </template>
                </div>
                <textarea id="footer_text" name="footer_text" x-model="templateData.footer_text" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                <p class="mt-1 text-sm text-gray-500">Variáveis disponíveis: {data}, {pagina}, {total_paginas}</p>
            </div>
        </div>
    </div>
</div>
<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Observação: Largura/Altura são em milímetros (mm) no PDF; deixe altura em branco (0) para preservar proporção.</p>
