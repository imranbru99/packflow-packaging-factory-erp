<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Product;
use ImranDevBd\AiCatalog\Facades\AiCatalog;

class CatalogAssistant
{
    /** @return array{title: string, title_bn: string, body: string, body_bn: string, source: string} */
    public function listing(Product $product): array
    {
        $local = $this->local($product);

        try {
            $generation = AiCatalog::fromText($local['body'])->locale('en')->generate();
            $payload = $generation->payload ?? $generation->output ?? null;

            if (is_array($payload) && isset($payload['title'])) {
                return [
                    'title' => (string) $payload['title'],
                    'title_bn' => (string) ($payload['title_bn'] ?? $local['title_bn']),
                    'body' => (string) ($payload['description'] ?? $payload['body'] ?? $local['body']),
                    'body_bn' => (string) ($payload['description_bn'] ?? $local['body_bn']),
                    'source' => 'ai-catalog',
                ];
            }
        } catch (\Throwable) {
            // The catalog package needs a configured model. The plant copy still ships.
        }

        return $local;
    }

    /** @return array{title: string, title_bn: string, body: string, body_bn: string, source: string} */
    public function local(Product $product): array
    {
        $size = trim(implode(' × ', array_filter([
            $product->length_mm ? rtrim(rtrim((string) $product->length_mm, '0'), '.').' mm' : null,
            $product->width_mm ? rtrim(rtrim((string) $product->width_mm, '0'), '.').' mm' : null,
            $product->height_mm ? rtrim(rtrim((string) $product->height_mm, '0'), '.').' mm' : null,
        ])));

        $structure = $product->film_structure
            ?: ($product->flute ? $product->flute.' flute'.($product->board_gsm ? ', '.$product->board_gsm.' GSM' : '') : 'custom structure');

        $title = $product->name.($size ? ' '.$size : '');

        return [
            'title' => $title,
            'title_bn' => $product->name_bn ?: $product->name,
            'body' => trim($product->description."\n\n".$title.' is made as '.$structure.' with '.$product->ink_colors.' print colours. Built for food, pharma, and export shippers that need a repeatable spec.'),
            'body_bn' => $product->description_bn ?: ($product->name_bn ?: $product->name).' — '.$structure.'। খাদ্য, ফার্মা ও রপ্তানি চালানের জন্য পুনরাবৃত্ত স্পেসিফিকেশন।',
            'source' => 'packflow',
        ];
    }
}
