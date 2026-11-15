<?php

namespace App\Modules\Crm\Services;

use ImranDevBd\AiOcr\Facades\AiOcr;
use ImranDevBd\AiOcr\Schema\Schema;

/**
 * Turns a customer mail or a scanned spec into estimator inputs.
 * AI OCR is used when a file is uploaded; a plain-text parser covers
 * the common "400 x 300 x 200, 5 ply, 1000 pcs" messages with no model.
 */
class OrderOcrService
{
    /** @return array<string, mixed> */
    public function fromText(string $text): array
    {
        $parsed = [
            'product_type' => 'corrugated',
            'description' => trim(strtok($text, "\n")) ?: 'Customer specification',
            'quantity' => 1000,
            'length_mm' => null,
            'width_mm' => null,
            'height_mm' => null,
            'gsm' => null,
            'flute' => null,
            'ink_colors' => 0,
            'film_structure' => null,
            'source' => 'text-parser',
        ];

        if (preg_match('/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i', $text, $m)) {
            $parsed['length_mm'] = (float) $m[1];
            $parsed['width_mm'] = (float) $m[2];
            $parsed['height_mm'] = (float) $m[3];
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i', $text, $m)) {
            $parsed['width_mm'] = (float) $m[1];
            $parsed['height_mm'] = (float) $m[2];
            $parsed['length_mm'] = (float) $m[2];
            $parsed['product_type'] = 'label';
        }

        if (preg_match('/(\d[\d,]*)\s*(pcs|pieces|qty|quantity)/i', $text, $m)) {
            $parsed['quantity'] = (float) str_replace(',', '', $m[1]);
        }

        if (preg_match('/(\d{2,4})\s*gsm/i', $text, $m)) {
            $parsed['gsm'] = (float) $m[1];
        }

        if (preg_match('/\b(BC|EB|A|B|C|E|F)\s*flute/i', $text, $m)) {
            $parsed['flute'] = strtoupper($m[1]);
            $parsed['product_type'] = 'corrugated';
        }

        if (preg_match('/(\d+)\s*colou?rs?/i', $text, $m)) {
            $parsed['ink_colors'] = (int) $m[1];
        }

        if (preg_match('/\b(\d+\s*[A-Z]+(?:\/\d+\s*[A-Z]+)+)\b/', strtoupper($text), $m)) {
            $parsed['film_structure'] = preg_replace('/\s+/', '', $m[1]);
            $parsed['product_type'] = 'flexible';
        }

        if (str_contains(strtolower($text), 'pouch') || str_contains(strtolower($text), 'film')) {
            $parsed['product_type'] = 'flexible';
        }

        if (str_contains(strtolower($text), 'carton') || str_contains(strtolower($text), 'duplex')) {
            $parsed['product_type'] = 'carton';
        }

        return $parsed;
    }

    /** @return array<string, mixed> */
    public function fromFile(string $path): array
    {
        try {
            $schema = Schema::make('packaging_order')->string('description')->number('length_mm')->number('width_mm')->number('height_mm')->number('quantity')->number('gsm')->string('flute')->integer('ink_colors')->string('film_structure');
            $result = AiOcr::file($path)->as($schema)->languages(['en', 'bn'])->extract();
            $data = array_filter($result->data(), fn ($value) => $value !== null && $value !== '');

            if ($data !== []) {
                $data['source'] = 'ai-ocr';
                $data['product_type'] = $data['film_structure'] ?? null ? 'flexible' : ($data['flute'] ?? null ? 'corrugated' : 'corrugated');

                return $data;
            }
        } catch (\Throwable) {
            // Fall through to any text the file name or a sidecar might carry.
        }

        return $this->fromText((string) pathinfo($path, PATHINFO_FILENAME));
    }
}
