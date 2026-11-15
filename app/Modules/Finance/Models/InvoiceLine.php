<?php

namespace App\Modules\Finance\Models;

use App\Modules\Support\PackflowModel;

class InvoiceLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:4',
            'line_total' => 'decimal:2',
        ];
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Finance\Models\Invoice::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
