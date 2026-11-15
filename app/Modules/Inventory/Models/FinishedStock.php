<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Support\PackflowModel;

class FinishedStock extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }
}
