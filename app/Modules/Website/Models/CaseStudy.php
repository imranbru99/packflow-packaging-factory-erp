<?php

namespace App\Modules\Website\Models;

use App\Modules\Support\PackflowModel;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

class CaseStudy extends PackflowModel
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
        return \ImranDev\UniversalSlug\SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->usingMode('ascii_only');
    }
}
