<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class GoodsReceiptLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function goodsReceipt(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\GoodsReceipt::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }

    public function lot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Lot::class);
    }
}
