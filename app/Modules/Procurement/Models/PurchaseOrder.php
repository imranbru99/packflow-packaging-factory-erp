<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class PurchaseOrder extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'ordered_on' => 'date',
            'expected_on' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\Supplier::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Procurement\Models\PurchaseOrderLine::class);
    }

    public function receipts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Procurement\Models\GoodsReceipt::class);
    }
}
