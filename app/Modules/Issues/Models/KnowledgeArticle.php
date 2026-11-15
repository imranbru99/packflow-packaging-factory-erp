<?php

namespace App\Modules\Issues\Models;

use App\Modules\Support\PackflowModel;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

class KnowledgeArticle extends PackflowModel
{
    use HasUniversalSlug;

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }


    public function getSlugOptions(): \ImranDev\UniversalSlug\SlugOptions
    {
        return \ImranDev\UniversalSlug\SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingMode('preserve_unicode');
    }
}
