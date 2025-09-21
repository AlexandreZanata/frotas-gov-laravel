<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class PdfTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = PdfTemplate::all();
        return view('pdf-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Definição de valores padrão para o novo template
        $pdfTemplate = new PdfTemplate([
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
            'font_family' => 'helvetica',
            'font_family_body' => 'helvetica',
            'header_font_family' => 'helvetica',
            'footer_font_family' => 'helvetica',
            'font_size_title' => 14,
            'font_size_text' => 11,
            'font_size_table' => 10,
            'header_font_size' => 12,
            'footer_font_size' => 10,
            'font_style_title' => 'B',
            'font_style_text' => '',
            'header_font_style' => 'B',
            'footer_font_style' => '',
            'header_scope' => 'all',
            'footer_scope' => 'all',
            'header_image_align' => 'L',
            'header_text_align' => 'R',
            'footer_image_align' => 'L',
            'footer_text_align' => 'R',
            'table_style' => 'grid',
            'table_header_bg' => '#f3f4f6',
            'table_header_text' => '#374151',
            'table_row_height' => 10,
            'show_table_lines' => true,
            'use_zebra_stripes' => false,
            'real_time_preview' => true,
            'header_image_width' => 60,
            'header_image_height' => 20,
            'footer_image_width' => 40,
            'footer_image_height' => 15,
        ]);

        return view('pdf-templates.create', [
            'pdfTemplate' => $pdfTemplate,
            'headerImageUrl' => '',
            'footerImageUrl' => '',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'header_image' => 'nullable|image|max:2048',
            'footer_image' => 'nullable|image|max:2048',
            'margin_top' => 'nullable|numeric|min:0',
            'margin_bottom' => 'nullable|numeric|min:0',
            'margin_left' => 'nullable|numeric|min:0',
            'margin_right' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();

        // Definir valores padrão para campos não enviados
        $data['font_style_text'] = $request->input('font_style_text', '');
        $data['font_style_title'] = $request->input('font_style_title', 'B');
        $data['header_font_style'] = $request->input('header_font_style', 'B');
        $data['footer_font_style'] = $request->input('footer_font_style', '');

        // Processar valores booleanos
        $data['show_table_lines'] = $request->has('show_table_lines');
        $data['use_zebra_stripes'] = $request->has('use_zebra_stripes');
        $data['real_time_preview'] = $request->has('real_time_preview');

        // Processar imagens
        if ($request->hasFile('header_image')) {
            $data['header_image'] = $request->file('header_image')->store('public/templates');
        }

        if ($request->hasFile('footer_image')) {
            $data['footer_image'] = $request->file('footer_image')->store('public/templates');
        }

        PdfTemplate::create($data);

        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PdfTemplate $pdfTemplate)
    {
        return view('pdf-templates.show', compact('pdfTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PdfTemplate $pdfTemplate)
    {
        // Preparar URLs de imagem para visualização
        $headerImageUrl = $pdfTemplate->header_image ? Storage::url($pdfTemplate->header_image) : '';
        $footerImageUrl = $pdfTemplate->footer_image ? Storage::url($pdfTemplate->footer_image) : '';

        return view('pdf-templates.edit', compact('pdfTemplate', 'headerImageUrl', 'footerImageUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'header_image' => 'nullable|image|max:2048',
            'footer_image' => 'nullable|image|max:2048',
            'margin_top' => 'nullable|numeric|min:0',
            'margin_bottom' => 'nullable|numeric|min:0',
            'margin_left' => 'nullable|numeric|min:0',
            'margin_right' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();

        // Definir valores padrão para campos não enviados
        $data['font_style_text'] = $request->input('font_style_text', '');
        $data['font_style_title'] = $request->input('font_style_title', 'B');
        $data['header_font_style'] = $request->input('header_font_style', 'B');
        $data['footer_font_style'] = $request->input('footer_font_style', '');

        // Processar valores booleanos
        $data['show_table_lines'] = $request->has('show_table_lines');
        $data['use_zebra_stripes'] = $request->has('use_zebra_stripes');
        $data['real_time_preview'] = $request->has('real_time_preview');

        // Processar imagens
        if ($request->hasFile('header_image')) {
            // Deleta a imagem antiga se existir
            if ($pdfTemplate->header_image) {
                Storage::delete($pdfTemplate->header_image);
            }
            $data['header_image'] = $request->file('header_image')->store('public/templates');
        }

        if ($request->hasFile('footer_image')) {
            // Deleta a imagem antiga se existir
            if ($pdfTemplate->footer_image) {
                Storage::delete($pdfTemplate->footer_image);
            }
            $data['footer_image'] = $request->file('footer_image')->store('public/templates');
        }

        $pdfTemplate->update($data);

        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PdfTemplate $pdfTemplate)
    {
        // Deleta as imagens associadas
        if ($pdfTemplate->header_image) {
            Storage::delete($pdfTemplate->header_image);
        }

        if ($pdfTemplate->footer_image) {
            Storage::delete($pdfTemplate->footer_image);
        }

        $pdfTemplate->delete();

        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF excluído com sucesso.');
    }

    /**
     * Preview do PDF baseado no template
     */
    public function preview(PdfTemplate $pdfTemplate)
    {
        // Gera um PDF de demonstração com o template
        $pdf = $this->generatePdf($pdfTemplate);

        // Retorna o PDF para visualização no navegador
        return response($pdf->Output('preview.pdf', 'I'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Gerar PDF baseado em um template
     */
    private function generatePdf(PdfTemplate $template, array $data = [])
    {
        // Cria uma nova instância de TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Remove cabeçalho e rodapé padrão
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Define margens
        $pdf->SetMargins(
            $template->margin_left ?: 15,
            $template->margin_top ?: 15,
            $template->margin_right ?: 15
        );

        // Adiciona uma página
        $pdf->AddPage();

        // Adiciona cabeçalho se necessário
        if ($template->header_scope !== 'none') {
            $this->addHeader($pdf, $template);
        }

        // Adiciona corpo do documento
        $this->addBody($pdf, $template, $data);

        // Adiciona rodapé se necessário
        if ($template->footer_scope !== 'none') {
            $this->addFooter($pdf, $template);
        }

        return $pdf;
    }

    /**
     * Adicionar cabeçalho ao PDF
     */
    private function addHeader($pdf, $template)
    {
        // Define a fonte do cabeçalho
        $pdf->SetFont(
            $template->header_font_family ?: 'helvetica',
            $template->header_font_style ?: '',
            $template->header_font_size ?: 12
        );

        // Posição Y inicial
        $startY = $pdf->GetY();

        // Adicionar imagem do cabeçalho, se existir
        if ($template->header_image) {
            $imagePath = storage_path('app/' . $template->header_image);
            if (file_exists($imagePath)) {
                $imageWidth = $template->header_image_width ?: 60;
                $imageHeight = $template->header_image_height ?: 20;

                // Determina a posição X baseada no alinhamento
                if ($template->header_image_align === 'L') {
                    $x = $pdf->GetX();
                } elseif ($template->header_image_align === 'C') {
                    $x = ($pdf->getPageWidth() - $imageWidth) / 2;
                } else { // 'R'
                    $x = $pdf->getPageWidth() - $pdf->GetX() - $imageWidth - $template->margin_right;
                }

                $pdf->Image($imagePath, $x, $startY, $imageWidth, $imageHeight);

                // Atualiza a posição Y para depois da imagem
                $pdf->SetY($startY + $imageHeight + 5);
            }
        }

        // Adicionar texto do cabeçalho, se existir
        if ($template->header_text) {
            // Substitui variáveis no texto
            $headerText = str_replace(
                ['{data}', '{pagina}', '{empresa}'],
                [date('d/m/Y'), $pdf->PageNo(), 'Nome da Empresa'],
                $template->header_text
            );

            // Define alinhamento do texto
            $align = $template->header_text_align ?: 'L';

            // Adiciona o texto
            $pdf->MultiCell(0, 10, $headerText, 0, $align);
        }

        // Adiciona uma linha separadora após o cabeçalho
        $pdf->Line(
            $pdf->GetX(),
            $pdf->GetY(),
            $pdf->getPageWidth() - $template->margin_right,
            $pdf->GetY()
        );

        // Adiciona espaço após a linha
        $pdf->Ln(5);
    }

    /**
     * Adicionar corpo ao PDF
     */
    private function addBody($pdf, $template, $data)
    {
        // Define a fonte para o corpo
        $pdf->SetFont(
            $template->font_family_body ?: $template->font_family ?: 'helvetica',
            $template->font_style_text ?: '',
            $template->font_size_text ?: 11
        );

        // Adiciona texto livre antes da tabela, se existir
        if ($template->body_text) {
            $pdf->writeHTML($template->body_text, true, false, true, false, '');
            $pdf->Ln(5);
        }

        // Adiciona a tabela de dados, se houver colunas definidas
        if ($template->table_columns) {
            $this->addTable($pdf, $template, $data);
            $pdf->Ln(5);
        }

        // Adiciona texto livre após a tabela, se existir
        if ($template->after_table_text) {
            $pdf->writeHTML($template->after_table_text, true, false, true, false, '');
        }
    }

    /**
     * Adicionar tabela ao PDF
     */
    private function addTable($pdf, $template, $data)
    {
        // Define a fonte para a tabela
        $pdf->SetFont(
            $template->font_family ?: 'helvetica',
            '',
            $template->font_size_table ?: 10
        );

        // Processa as colunas da tabela
        $columns = array_map('trim', explode(',', $template->table_columns));

        // Exemplo de dados para a tabela (substitua por dados reais)
        $tableData = $data['table_data'] ?? $this->getDemoTableData($columns);

        // Configurações da tabela
        $tableHeaderStyle = [
            'background-color' => $template->table_header_bg ?: '#f3f4f6',
            'color' => $template->table_header_text ?: '#374151',
            'font-weight' => 'bold',
        ];

        // Estilo zebrado, se ativado
        $zebraStyle = $template->use_zebra_stripes ? [
            'background-color' => '#f9fafb',
        ] : [];

        // Cria o HTML da tabela
        $html = '<table border="' . ($template->show_table_lines ? '1' : '0') . '" cellpadding="5">';

        // Cabeçalho da tabela
        $html .= '<thead><tr style="background-color:' . $tableHeaderStyle['background-color'] . '; color:' . $tableHeaderStyle['color'] . ';">';
        foreach ($columns as $column) {
            $html .= '<th>' . $column . '</th>';
        }
        $html .= '</tr></thead>';

        // Corpo da tabela
        $html .= '<tbody>';
        foreach ($tableData as $i => $row) {
            // Aplica estilo zebrado em linhas alternadas
            $rowStyle = ($template->use_zebra_stripes && $i % 2 == 1) ? ' style="background-color:' . $zebraStyle['background-color'] . ';"' : '';
            $html .= '<tr' . $rowStyle . '>';
            foreach ($row as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        // Renderiza a tabela
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    /**
     * Adicionar rodapé ao PDF
     */
    private function addFooter($pdf, $template)
    {
        // Salva a posição atual
        $currentY = $pdf->GetY();

        // Move para a parte inferior da página, deixando margem
        $pdf->SetY($pdf->getPageHeight() - $template->margin_bottom - 20);

        // Adiciona uma linha separadora antes do rodapé
        $pdf->Line(
            $pdf->GetX(),
            $pdf->GetY(),
            $pdf->getPageWidth() - $template->margin_right,
            $pdf->GetY()
        );

        // Adiciona espaço após a linha
        $pdf->Ln(2);

        // Define a fonte do rodapé
        $pdf->SetFont(
            $template->footer_font_family ?: 'helvetica',
            $template->footer_font_style ?: '',
            $template->footer_font_size ?: 10
        );

        // Adicionar imagem do rodapé, se existir
        if ($template->footer_image) {
            $imagePath = storage_path('app/' . $template->footer_image);
            if (file_exists($imagePath)) {
                $imageWidth = $template->footer_image_width ?: 40;
                $imageHeight = $template->footer_image_height ?: 15;

                // Determina a posição X baseada no alinhamento
                if ($template->footer_image_align === 'L') {
                    $x = $pdf->GetX();
                } elseif ($template->footer_image_align === 'C') {
                    $x = ($pdf->getPageWidth() - $imageWidth) / 2;
                } else { // 'R'
                    $x = $pdf->getPageWidth() - $pdf->GetX() - $imageWidth - $template->margin_right;
                }

                $pdf->Image($imagePath, $x, $pdf->GetY(), $imageWidth, $imageHeight);

                // Atualiza a posição Y para depois da imagem
                $pdf->SetY($pdf->GetY() + $imageHeight + 2);
            }
        }

        // Adicionar texto do rodapé, se existir
        if ($template->footer_text) {
            // Substitui variáveis no texto
            $footerText = str_replace(
                ['{data}', '{pagina}', '{total_paginas}'],
                [date('d/m/Y'), $pdf->PageNo(), $pdf->getAliasNbPages()],
                $template->footer_text
            );

            // Define alinhamento do texto
            $align = $template->footer_text_align ?: 'C';

            // Adiciona o texto
            $pdf->MultiCell(0, 10, $footerText, 0, $align);
        }
    }

    /**
     * Gerar dados de demonstração para a tabela
     */
    private function getDemoTableData($columns)
    {
        $data = [];
        $numRows = 5;

        for ($i = 0; $i < $numRows; $i++) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = "Exemplo " . ($i+1) . " - " . $column;
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Método para pré-visualização em tempo real via AJAX
     */
    public function ajaxPreview(Request $request)
    {
        // Cria um template temporário com os dados enviados
        $tempTemplate = new PdfTemplate($request->all());

        // Gera o PDF
        $pdf = $this->generatePdf($tempTemplate);

        // Retorna o PDF como base64 para exibição em um iframe
        $pdfContent = base64_encode($pdf->Output('preview.pdf', 'S'));

        return response()->json([
            'success' => true,
            'pdf_base64' => 'data:application/pdf;base64,' . $pdfContent
        ]);
    }
}
