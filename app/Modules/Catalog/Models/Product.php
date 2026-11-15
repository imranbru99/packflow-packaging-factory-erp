<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

class Product extends PackflowModel
{
    use SoftDeletes, HasUniversalSlug;

    protected function casts(): array
    {
        return [
            'finishing' => 'array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'length_mm' => 'decimal:2',
            'width_mm' => 'decimal:2',
            'height_mm' => 'decimal:2',
            'list_price' => 'decimal:2',
            'standard_cost' => 'decimal:2',
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\ProductCategory::class, 'product_category_id');
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\ProductVariant::class);
    }

    public function specifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\Specification::class);
    }

    public function boms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\Bom::class);
    }

    public function routings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\Routing::class);
    }

    public function toolings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\Tooling::class);
    }


    public function getSlugOptions(): \ImranDev\UniversalSlug\SlugOptions
    {
        return \ImranDev\UniversalSlug\SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->usingMode('ascii_only');
    }
}
