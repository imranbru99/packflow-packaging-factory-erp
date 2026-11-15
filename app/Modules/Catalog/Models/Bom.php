<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class Bom extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\BomItem::class);
    }
}
