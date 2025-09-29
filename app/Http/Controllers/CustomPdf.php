<?php

namespace App\Helpers;

use App\Models\PdfTemplate;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class CustomPdf extends TCPDF
{
    protected $template;

    public function __construct(PdfTemplate $template)
    {
        parent::__construct();
        $this->template = $template;
    }

    public function Header()
    {
        if ($this->template->header_scope === 'none' || ($this->template->header_scope === 'first' && $this->getPage() > 1)) {
            return;
        }

        $this->processHeaderOrFooter('header');
    }

    public function Footer()
    {
        if ($this->template->footer_scope === 'none' || ($this->template->footer_scope === 'first' && $this->getPage() > 1)) {
            return;
        }

        $this->processHeaderOrFooter('footer');
    }

    private function processHeaderOrFooter(string $type)
    {
        $imagePath = $this->template->{$type.'_image'};
        $text = $this->template->{$type.'_text'};

        $imageAlign = $this->template->{$type.'_image_align'} ?? 'L';
        $textAlign = $this->template->{$type.'_text_align'} ?? 'R';
        $fontFamily = $this->template->{$type.'_font_family'} ?? 'helvetica';
        $fontStyle = $this->template->{$type.'_font_style'} ?? '';
        $fontSize = $this->template->{$type.'_font_size'} ?? 10;
        $width = (float)($this->template->{$type.'_image_width'} ?? 0);
        $height = (float)($this->template->{$type.'_image_height'} ?? 0);
        $verticalPosition = $this->template->{$type.'_image_vertical_position'} ?? 'above';

        // Posição Y (Header no topo, Footer na base)
        $yPos = ($type === 'header') ? $this->GetY() : $this->GetY() - $this->template->margin_bottom;

        $this->SetFont($fontFamily, $fontStyle, $fontSize);

        $hasImage = $imagePath && (Storage::disk('public')->exists($imagePath) || file_exists($imagePath));
        $hasText = !empty($text);

        $pageWidth = $this->getPageWidth() - $this->template->margin_left - $this->template->margin_right;
        $imageX = $this->getXforAlign($imageAlign, $width, $pageWidth);

        $text = $this->replaceTokens($text);

        if ($hasImage && in_array($verticalPosition, ['inline-left', 'inline-right'])) {
            $this->drawInlineHeaderFooter($type, $hasImage, $hasText, $imagePath, $width, $height, $imageAlign, $textAlign, $text);
        } else {
            $this->drawBlockHeaderFooter($type, $hasImage, $hasText, $imagePath, $width, $height, $imageAlign, $textAlign, $text, $verticalPosition);
        }
    }

    private function drawInlineHeaderFooter($type, $hasImage, $hasText, $imagePath, $width, $height, $imageAlign, $textAlign, $text) {
        $pageWidth = $this->getPageWidth() - $this->template->margin_left - $this->template->margin_right;
        $yPos = ($type === 'header') ? $this->getHeaderMargin() : -$this->template->margin_bottom;
        $this->SetY($yPos, false);

        if ($this->template->{$type.'_image_vertical_position'} === 'inline-left') {
            if ($hasImage) $this->Image(Storage::disk('public')->path($imagePath), $this->template->margin_left, $yPos, $width, $height, '', '', 'T', false, 300, 'L');
            if ($hasText) $this->writeHTMLCell($pageWidth - $width - 5, $height, $this->template->margin_left + $width + 5, $yPos, $text, 0, 1, false, true, $textAlign, true);
        } else { // inline-right
            if ($hasText) $this->writeHTMLCell($pageWidth - $width - 5, $height, $this->template->margin_left, $yPos, $text, 0, 0, false, true, $textAlign, true);
            if ($hasImage) $this->Image(Storage::disk('public')->path($imagePath), $this->template->margin_left + $pageWidth - $width, $yPos, $width, $height, '', '', 'T', false, 300, 'R');
        }
    }

    private function drawBlockHeaderFooter($type, $hasImage, $hasText, $imagePath, $width, $height, $imageAlign, $textAlign, $text, $verticalPosition) {
        $pageWidth = $this->getPageWidth() - $this->template->margin_left - $this->template->margin_right;
        $yPos = ($type === 'header') ? $this->getHeaderMargin() : -$this->template->margin_bottom;
        $this->SetY($yPos, false);
        $cursorY = $yPos;

        $parts = [];
        if ($hasImage) $parts[] = 'image';
        if ($hasText) $parts[] = 'text';
        if ($verticalPosition === 'below') $parts = array_reverse($parts);

        foreach ($parts as $part) {
            if ($part === 'image') {
                $this->Image(Storage::disk('public')->path($imagePath), $this->getXforAlign($imageAlign, $width, $pageWidth), $cursorY, $width, $height, '', '', 'T', false, 300, $imageAlign);
                if ($hasText) $cursorY += $height + 2;
            } else { // text
                $this->writeHTMLCell($pageWidth, 0, $this->template->margin_left, $cursorY, $text, 0, 1, false, true, $textAlign, true);
            }
        }
    }


    private function getXforAlign($align, $elementWidth, $containerWidth)
    {
        $x = $this->GetX();
        if ($align === 'C') {
            $x = $x + ($containerWidth - $elementWidth) / 2;
        } elseif ($align === 'R') {
            $x = $x + $containerWidth - $elementWidth;
        }
        return $x;
    }

    private function replaceTokens(string $text): string
    {
        return str_replace(
            ['{data}', '{pagina}', '{total_paginas}', '{empresa}'],
            [date('d/m/Y'), $this->getAliasNumPage(), $this->getAliasNbPages(), config('app.name')],
            $text
        );
    }
}
