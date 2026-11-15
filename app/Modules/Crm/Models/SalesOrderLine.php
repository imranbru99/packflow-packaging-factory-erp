<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class SalesOrderLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:4',
            'line_total' => 'decimal:2',
            'spec' => 'array',
        ];
    }

    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\SalesOrder::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
