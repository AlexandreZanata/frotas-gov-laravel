<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class PdfTemplateController extends Controller
{
    // Lista templates
    public function index()
    {
        $templates = PdfTemplate::all();
        return view('pdf-templates.index', compact('templates'));
    }

    // Form de criação
    public function create()
    {
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
        ]);
        return view('pdf-templates.create', [
            'pdfTemplate' => $pdfTemplate,
            'headerImageUrl' => '',
            'footerImageUrl' => '',
        ]);
    }

    // Salva
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->handleImages($request, $data);
        PdfTemplate::create($data);
        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF criado com sucesso.');
    }

    // Exibe
    public function show(PdfTemplate $pdfTemplate)
    {
        return view('pdf-templates.show', compact('pdfTemplate'));
    }

    // Edita
    public function edit(PdfTemplate $pdfTemplate)
    {
        $headerImageUrl = $pdfTemplate->header_image ? Storage::url($pdfTemplate->header_image) : '';
        $footerImageUrl = $pdfTemplate->footer_image ? Storage::url($pdfTemplate->footer_image) : '';
        return view('pdf-templates.edit', compact('pdfTemplate', 'headerImageUrl', 'footerImageUrl'));
    }

    // Atualiza
    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        $data = $this->validateData($request);
        $this->handleImages($request, $data, $pdfTemplate);
        $pdfTemplate->update($data);
        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF atualizado com sucesso.');
    }

    // Remove
    public function destroy(PdfTemplate $pdfTemplate)
    {
        if ($pdfTemplate->header_image) { Storage::delete($pdfTemplate->header_image); }
        if ($pdfTemplate->footer_image) { Storage::delete($pdfTemplate->footer_image); }
        $pdfTemplate->delete();
        return redirect()->route('pdf-templates.index')->with('success', 'Modelo de PDF excluído com sucesso.');
    }

    // Preview simples
    public function preview(PdfTemplate $pdfTemplate)
    {
        $pdf = $this->generatePdf($pdfTemplate);
        return response($pdf->Output('preview.pdf', 'I'), 200)->header('Content-Type', 'application/pdf');
    }

    // Preview AJAX
    public function ajaxPreview(Request $request)
    {
        try {
            $tempTemplate = new PdfTemplate($request->all());
            $pdf = $this->generatePdf($tempTemplate);
            $pdfContent = base64_encode($pdf->Output('preview.pdf', 'S'));
            return response()->json([
                'success' => true,
                'pdf_base64' => 'data:application/pdf;base64,' . $pdfContent,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar preview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ===== Helpers ===== //
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'table_columns' => 'nullable|string',
            'margin_top' => 'nullable|numeric|min:0',
            'margin_bottom' => 'nullable|numeric|min:0',
            'margin_left' => 'nullable|numeric|min:0',
            'margin_right' => 'nullable|numeric|min:0',
            'font_family' => 'nullable|string',
            'font_family_body' => 'nullable|string',
            'header_font_family' => 'nullable|string',
            'footer_font_family' => 'nullable|string',
            'font_size_title' => 'nullable|integer|min:6',
            'font_size_text' => 'nullable|integer|min:6',
            'font_size_table' => 'nullable|integer|min:6',
            'header_font_size' => 'nullable|integer|min:6',
            'footer_font_size' => 'nullable|integer|min:6',
            'font_style_title' => 'nullable|string',
            'font_style_text' => 'nullable|string',
            'header_font_style' => 'nullable|string',
            'footer_font_style' => 'nullable|string',
            'header_image' => 'nullable|image|max:2048',
            'footer_image' => 'nullable|image|max:2048',
        ]);
    }

    private function handleImages(Request $request, array &$data, ?PdfTemplate $existing = null): void
    {
        if ($request->hasFile('header_image')) {
            if ($existing && $existing->header_image) { Storage::delete($existing->header_image); }
            $data['header_image'] = $request->file('header_image')->store('public/templates');
        }
        if ($request->hasFile('footer_image')) {
            if ($existing && $existing->footer_image) { Storage::delete($existing->footer_image); }
            $data['footer_image'] = $request->file('footer_image')->store('public/templates');
        }
    }

    private function generatePdf(PdfTemplate $template, array $data = [])
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $ml = $template->margin_left ?? 15; $mt = $template->margin_top ?? 15; $mr = $template->margin_right ?? 15; $mb = $template->margin_bottom ?? 15;
        $pdf->SetMargins($ml, $mt, $mr);
        $pdf->SetAutoPageBreak(true, $mb + 10);
        $pdf->AddPage();
        if (!empty($template->header_text)) {
            $pdf->SetFont($template->header_font_family ?: 'helvetica', $template->header_font_style ?: '', $template->header_font_size ?: 12);
            $pdf->MultiCell(0, 8, $this->replaceHeaderTokens($template->header_text, $pdf), 0, 'L');
            $pdf->Ln(2);
        }
        $pdf->SetFont($template->font_family_body ?: $template->font_family ?: 'helvetica', $template->font_style_text ?: '', $template->font_size_text ?: 11);
        $pdf->MultiCell(0, 6, 'Pré-visualização do Template: ' . ($template->name ?: '[sem nome]'), 0, 'L');
        $pdf->Ln(4);
        if (!empty($template->table_columns)) {
            $columns = array_filter(array_map('trim', explode(',', $template->table_columns)));
            if ($columns) {
                $pdf->SetFont($template->font_family ?: 'helvetica', 'B', $template->font_size_table ?: 10);
                foreach ($columns as $col) { $pdf->Cell(190 / max(count($columns),1), 8, $col, 1, 0, 'C'); }
                $pdf->Ln();
                $pdf->SetFont($template->font_family ?: 'helvetica', '', $template->font_size_table ?: 10);
                for ($i = 1; $i <= 5; $i++) {
                    foreach ($columns as $col) { $pdf->Cell(190 / max(count($columns),1), 7, "Linha $i - $col", 1, 0, 'L'); }
                    $pdf->Ln();
                }
                $pdf->Ln(4);
            }
        }
        if (!empty($template->footer_text)) {
            $pdf->SetY(-30);
            $pdf->SetFont($template->footer_font_family ?: 'helvetica', $template->footer_font_style ?: '', $template->footer_font_size ?: 10);
            $pdf->MultiCell(0, 6, $this->replaceFooterTokens($template->footer_text, $pdf), 0, 'C');
        }
        return $pdf;
    }

    private function replaceHeaderTokens(string $text, TCPDF $pdf): string
    {
        return str_replace(['{data}', '{pagina}', '{empresa}'], [date('d/m/Y'), $pdf->PageNo(), config('app.name')], $text);
    }

    private function replaceFooterTokens(string $text, TCPDF $pdf): string
    {
        return str_replace(['{data}', '{pagina}', '{total_paginas}'], [date('d/m/Y'), $pdf->PageNo(), $pdf->getAliasNbPages()], $text);
    }
}
