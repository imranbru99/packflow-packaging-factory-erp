<?php

namespace App\Modules\Website\Models;

use App\Modules\Support\PackflowModel;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

class Post extends PackflowModel
{
    use HasUniversalSlug;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }


    public function getSlugOptions(): \ImranDev\UniversalSlug\SlugOptions
    {
        return \ImranDev\UniversalSlug\SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->usingMode('preserve_unicode');
    }
}
