<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class Tooling extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
