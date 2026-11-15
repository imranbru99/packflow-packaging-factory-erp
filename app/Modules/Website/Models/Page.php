<?php

namespace App\Modules\Website\Models;

use App\Modules\Support\PackflowModel;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

class Page extends PackflowModel
{
    use HasUniversalSlug;


    public function getSlugOptions(): \ImranDev\UniversalSlug\SlugOptions
    {
        return \ImranDev\UniversalSlug\SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->usingMode('ascii_only')->doNotGenerateSlugsOnUpdate();
    }
}
