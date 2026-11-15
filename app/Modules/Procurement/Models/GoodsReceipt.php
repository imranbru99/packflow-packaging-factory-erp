<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class GoodsReceipt extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'received_on' => 'date',
        ];
    }

    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\PurchaseOrder::class);
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Procurement\Models\GoodsReceiptLine::class);
    }
}
