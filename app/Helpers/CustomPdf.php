<?php

namespace App\Helpers;

use App\Models\PdfTemplate;
use Illuminate\Support\Facades\Storage;
use TCPDF;

// Estendemos a classe TCPDF para customizar cabeçalhos e rodapés
class CustomPdf extends TCPDF {
    protected $template;

    public function __construct(PdfTemplate $template) {
        parent::__construct();
        $this->template = $template;
    }

    // Método para renderizar o cabeçalho
    public function Header() {
        if ($this->template->header_scope === 'none' || ($this->template->header_scope === 'first' && $this->getPage() > 1)) return;

        // Pega o valor de espaçamento (line height) do template
        $lineHeight = (float)($this->template->header_line_height ?? 1.25);
        $this->setCellHeightRatio($lineHeight);

        $this->SetY(10); // Posição inicial do cabeçalho
        $this->SetFont($this->template->header_font_family, $this->template->header_font_style, $this->template->header_font_size);

        $imagePath = $this->template->header_image ? Storage::disk('public')->path($this->template->header_image) : null;
        $text = $this->replaceTokens($this->template->header_text);

        // Chama a nova função de renderização com alinhamento independente
        $this->renderIndependentContent($imagePath, $text, 'header');
    }

    // Método para renderizar o rodapé
    public function Footer() {
        if ($this->template->footer_scope === 'none' || ($this->template->footer_scope === 'first' && $this->getPage() > 1)) return;

        // Pega o valor de espaçamento (line height) do template
        $lineHeight = (float)($this->template->footer_line_height ?? 1.25);
        $this->setCellHeightRatio($lineHeight);

        $this->SetY(-15); // Posição a 1.5 cm do final
        $this->SetFont($this->template->footer_font_family, $this->template->footer_font_style, $this->template->footer_font_size);

        $imagePath = $this->template->footer_image ? Storage::disk('public')->path($this->template->footer_image) : null;
        $text = $this->replaceTokens($this->template->footer_text);

        // Chama a nova função de renderização com alinhamento independente
        $this->renderIndependentContent($imagePath, $text, 'footer');
    }

    /**
     * NOVA LÓGICA DE RENDERIZAÇÃO
     * Renderiza texto e imagem de forma independente para garantir o alinhamento correto do texto.
     */
    private function renderIndependentContent($imagePath, $text, $type) {
        $textAlign = $this->template->{$type.'_text_align'} ?? 'R';
        $imageAlign = $this->template->{$type.'_image_align'} ?? 'L';
        $imgWidth = (float)($this->template->{$type.'_image_width'} ?? 0);
        $imgHeight = (float)($this->template->{$type.'_image_height'} ?? 0);

        $hasImage = $imagePath && file_exists($imagePath);
        $hasText = !empty(strip_tags(trim($text)));

        $pageWidth = $this->getPageWidth() - $this->original_lMargin - $this->original_rMargin;

        // Posições X e Y atuais
        $currentX = $this->GetX();
        $currentY = $this->GetY();

        // 1. Desenha o TEXTO primeiro, usando a LARGURA TOTAL da página
        if ($hasText) {
            $this->writeHTMLCell(
                0, // Largura 0 = até a margem direita
                0, // Altura 0 = automática
                $currentX, // Posição X inicial
                $currentY, // Posição Y inicial
                $text,     // Conteúdo HTML
                0,         // Sem borda
                1,         // Nova linha após o texto
                false,     // Sem preenchimento de fundo
                true,      // Reseta a altura da última célula
                $textAlign // Alinhamento do texto
            );
        }

        // 2. Desenha a IMAGEM depois, calculando sua posição X de forma independente
        if ($hasImage) {
            $imageX = $this->original_lMargin; // Padrão é Esquerda
            if ($imageAlign === 'C') {
                $imageX = $this->original_lMargin + ($pageWidth - $imgWidth) / 2;
            } elseif ($imageAlign === 'R') {
                $imageX = $this->getPageWidth() - $this->original_rMargin - $imgWidth;
            }

            $this->Image($imagePath, $imageX, $currentY, $imgWidth, $imgHeight, '', '', 'T', false, 300);
        }
    }


    // Substitui os tokens genéricos (mantido como no seu original)
    private function replaceTokens($text) {
        if (empty($text)) return '';

        return str_replace(
            ['{data}', '{pagina}', '{total_paginas}', '{empresa}'],
            [date('d/m/Y'), $this->getAliasNumPage(), $this->getAliasNbPages(), config('app.name')],
            $text
        );
    }
}
