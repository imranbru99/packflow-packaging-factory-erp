<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class ProductVariant extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'is_active' => 'boolean',
            'list_price' => 'decimal:2',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
