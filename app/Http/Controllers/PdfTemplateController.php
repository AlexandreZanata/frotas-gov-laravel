<?php

namespace App\Http\Controllers;

use App\Helpers\CustomPdf;
use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class PdfTemplateController extends Controller
{
    public function index()
    {
        $templates = PdfTemplate::orderBy('name')->get();
        return view('pdf-templates.index', compact('templates'));
    }

    public function create()
    {
        $pdfTemplate = new PdfTemplate();
        // Valores padrão para um novo template
        $pdfTemplate->fill([
            'name' => 'Novo Modelo de Relatório',
            'margin_top' => 25, 'margin_bottom' => 25, 'margin_left' => 15, 'margin_right' => 15,
            'header_scope' => 'all', 'footer_scope' => 'all',
            'font_family' => 'helvetica', 'font_family_body' => 'helvetica',
            'header_font_family' => 'helvetica', 'footer_font_family' => 'helvetica',
            'font_size_title' => 14, 'font_size_text' => 11, 'font_size_table' => 9,
            'header_font_size' => 10, 'footer_font_size' => 8,
            'header_line_height' => 1.2, 'footer_line_height' => 1.2,
            'font_style_title' => 'B', 'header_font_style' => '', 'footer_font_style' => 'I', 'font_style_text' => '',
            'header_text' => '<h2>{empresa}</h2><p>Relatório Interno</p>',
            'footer_text' => 'Página {pagina} de {total_paginas} | Gerado em: {data}',
            'body_text' => '<h1>Título do Relatório</h1><p>Este é um parágrafo de exemplo que aparece antes da tabela de dados. Você pode usar <b>HTML</b> simples aqui.</p>',
            'after_table_text' => '<p>Considerações finais ou observações adicionais podem ser inseridas aqui, após a tabela de dados.</p>',
            'header_image_align' => 'L', 'header_text_align' => 'R',
            'footer_image_align' => 'L', 'footer_text_align' => 'C',
            'header_image_width' => 30, 'header_image_height' => 15,
            'footer_image_width' => 20, 'footer_image_height' => 10,
            'header_image_vertical_position' => 'inline-left',
            'footer_image_vertical_position' => 'inline-left',
            'table_style' => 'grid', 'table_header_bg' => '#E5E7EB', 'table_header_text' => '#1F2937',
            'table_row_height' => 7, 'show_table_lines' => true, 'use_zebra_stripes' => true,
            'table_columns' => 'ID, Nome, Email, Status',
            'real_time_preview' => true,
        ]);

        return view('pdf-templates.create', ['pdfTemplate' => $pdfTemplate]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->handleImages($request, $data);

        $pdfTemplate = PdfTemplate::create($data);

        return redirect()->route('pdf-templates.index')->with('success', "Modelo '{$pdfTemplate->name}' criado com sucesso.");
    }

    public function edit(PdfTemplate $pdfTemplate)
    {
        return view('pdf-templates.edit', compact('pdfTemplate'));
    }

    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        $data = $this->validateData($request);
        $this->handleImages($request, $data, $pdfTemplate);
        $pdfTemplate->update($data);

        return redirect()->route('pdf-templates.index')->with('success', "Modelo '{$pdfTemplate->name}' atualizado com sucesso.");
    }

    public function destroy(PdfTemplate $pdfTemplate)
    {
        if ($pdfTemplate->header_image) { Storage::disk('public')->delete($pdfTemplate->header_image); }
        if ($pdfTemplate->footer_image) { Storage::disk('public')->delete($pdfTemplate->footer_image); }
        $pdfTemplate->delete();
        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF excluído com sucesso.');
    }

    public function preview(PdfTemplate $pdfTemplate)
    {
        $pdf = $this->generatePdf($pdfTemplate, true);
        return response($pdf->Output('preview.pdf', 'I'), 200)->header('Content-Type', 'application/pdf');
    }

    public function ajaxStorePreview(Request $request)
    {
        return $this->generatePreviewFromRequest($request, new PdfTemplate());
    }

    public function ajaxUpdatePreview(Request $request, PdfTemplate $pdfTemplate)
    {
        return $this->generatePreviewFromRequest($request, $pdfTemplate->replicate());
    }

    private function generatePreviewFromRequest(Request $request, PdfTemplate $template)
    {
        $tempPaths = [];
        try {
            $validatedData = $this->validateData($request, true);
            $template->fill($validatedData);

            foreach (['header_image', 'footer_image'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    $path = $request->file($imageField)->store('temp_previews', 'public');
                    $template->{$imageField} = $path;
                    $tempPaths[] = $path;
                } elseif ($request->input($imageField.'_existing')) {
                    $template->{$imageField} = $request->input($imageField.'_existing');
                } else {
                    $template->{$imageField} = null;
                }
            }

            $pdf = $this->generatePdf($template, false);
            $pdfContent = base64_encode($pdf->Output('preview.pdf', 'S'));

            return response()->json([
                'success' => true,
                'pdf_base64' => 'data:application/pdf;base64,' . $pdfContent,
            ]);

        } catch (\Throwable $e) {
            Log::error('PDF Preview Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(), 'file' => $e->getFile(), 'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Erro ao gerar preview: ' . $e->getMessage()], 500);
        } finally {
            foreach ($tempPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
    }

    private function validateData(Request $request, bool $isPreview = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'header_image' => 'nullable|image|max:2048',
            'footer_image' => 'nullable|image|max:2048',
            'header_scope' => 'required|in:all,first,none',
            'header_image_align' => 'required|in:L,C,R',
            'header_image_width' => 'nullable|numeric|min:0',
            'header_image_height' => 'nullable|numeric|min:0',
            'header_text' => 'nullable|string',
            'header_text_align' => 'required|in:L,C,R',
            'header_line_height' => 'nullable|numeric|min:0',
            'footer_scope' => 'required|in:all,first,none',
            'footer_image_align' => 'required|in:L,C,R',
            'footer_image_width' => 'nullable|numeric|min:0',
            'footer_image_height' => 'nullable|numeric|min:0',
            'footer_text' => 'nullable|string',
            'footer_text_align' => 'required|in:L,C,R',
            'footer_line_height' => 'nullable|numeric|min:0',
            'body_text' => 'nullable|string',
            'after_table_text' => 'nullable|string',
            'table_style' => 'nullable|string',
            'table_header_bg' => 'nullable|string',
            'table_header_text' => 'nullable|string',
            'table_row_height' => 'nullable|numeric|min:1',
            'table_columns' => 'nullable|string',
            'margin_top' => 'required|numeric|min:0',
            'margin_bottom' => 'required|numeric|min:0',
            'margin_left' => 'required|numeric|min:0',
            'margin_right' => 'required|numeric|min:0',
            'font_family' => 'required|string',
            'font_family_body' => 'required|string',
            'header_font_family' => 'required|string',
            'footer_font_family' => 'required|string',
            'font_size_title' => 'required|integer|min:6',
            'font_size_text' => 'required|integer|min:6',
            'font_size_table' => 'required|integer|min:6',
            'header_font_size' => 'required|integer|min:6',
            'footer_font_size' => 'required|integer|min:6',
            'font_style_title' => 'nullable|string',
            'font_style_text' => 'nullable|string',
            'header_font_style' => 'nullable|string',
            'footer_font_style' => 'nullable|string',
            'header_image_vertical_position' => 'required|string',
            'footer_image_vertical_position' => 'required|string',
            // Booleans não precisam de validação, mas serão tratados abaixo
        ];

        $validated = $isPreview ? $request->all() : $request->validate($rules);

        // Trata os booleans, que podem não vir na request se não forem marcados
        $validated['show_table_lines'] = $request->boolean('show_table_lines');
        $validated['use_zebra_stripes'] = $request->boolean('use_zebra_stripes');
        $validated['real_time_preview'] = $request->boolean('real_time_preview');

        return $validated;
    }

    private function handleImages(Request $request, array &$data, ?PdfTemplate $existing = null): void
    {
        foreach (['header_image', 'footer_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($existing && $existing->$imageField) { Storage::disk('public')->delete($existing->$imageField); }
                $data[$imageField] = $request->file($imageField)->store('pdf_templates', 'public');
            } elseif ($request->boolean('remove_' . $imageField)) {
                if ($existing && $existing->$imageField) { Storage::disk('public')->delete($existing->$imageField); }
                $data[$imageField] = null;
            }
        }
    }
    private function generatePdf(PdfTemplate $template, bool $fullData = false): TCPDF
    {
        $pdf = new CustomPdf($template);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetTitle($template->name);
        $pdf->SetSubject('Relatório de Exemplo');
        $pdf->setPrintHeader($template->header_scope !== 'none');
        $pdf->setPrintFooter($template->footer_scope !== 'none');
        $pdf->SetMargins($template->margin_left, $template->margin_top, $template->margin_right);
        $pdf->SetAutoPageBreak(true, $template->margin_bottom);
        $pdf->AddPage();
        $pdf->SetFont($template->font_family_body, $template->font_style_text, $template->font_size_text);
        if (!empty($template->body_text)) {
            $html = $this->replaceGenericTokens($template->body_text, $pdf);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(5);
        }

        $columns = array_filter(array_map('trim', explode(',', $template->table_columns)));
        if (!empty($columns)) {
            $numRows = $fullData ? 25 : 4;
            $this->drawSampleTable($pdf, $template, $columns, $numRows);
            $pdf->Ln(5);
        }

        if (!empty($template->after_table_text)) {
            $pdf->SetFont($template->font_family_body, $template->font_style_text, $template->font_size_text);
            $html = $this->replaceGenericTokens($template->after_table_text, $pdf);
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        return $pdf;
    }

    private function drawSampleTable(TCPDF &$pdf, PdfTemplate $template, array $columns, int $numRows) {
        $headerBg = $this->hexToRgb($template->table_header_bg);
        $headerText = $this->hexToRgb($template->table_header_text);
        $zebraFill = $template->use_zebra_stripes;

        $pdf->SetFillColor($headerBg['r'], $headerBg['g'], $headerBg['b']);
        $pdf->SetTextColor($headerText['r'], $headerText['g'], $headerText['b']);
        $pdf->SetFont($template->font_family, 'B', $template->font_size_table);

        $w = array_fill(0, count($columns), 180 / max(count($columns), 1));
        for ($i = 0; $i < count($columns); $i++) {
            $pdf->Cell($w[$i], $template->table_row_height, $columns[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0);
        $pdf->SetFont($template->font_family, '', $template->font_size_table);

        for ($i = 1; $i <= $numRows; $i++) {
            $fill = $zebraFill && ($i % 2 === 0);
            foreach ($columns as $key => $col) {
                $pdf->Cell($w[$key], $template->table_row_height, "Dado de exemplo $i", 'LR', 0, 'L', $fill);
            }
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w), 0, '', 'T');
    }

    private function replaceGenericTokens(string $text, TCPDF $pdf): string
    {
        return str_replace(
            ['{data}', '{pagina}', '{empresa}', '{total_paginas}'],
            [date('d/m/Y'), $pdf->PageNo(), config('app.name'), $pdf->getAliasNbPages()],
            $text
        );
    }

    private function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
}

