<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class QuotationLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'breakdown' => 'array',
            'unit_price' => 'decimal:4',
            'line_total' => 'decimal:2',
            'material_kg' => 'decimal:3',
        ];
    }

    public function quotation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Quotation::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
