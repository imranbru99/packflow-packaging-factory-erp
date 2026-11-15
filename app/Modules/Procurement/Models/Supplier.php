<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class Supplier extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'on_time_percent' => 'decimal:2',
            'quality_score' => 'decimal:2',
        ];
    }

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Procurement\Models\PurchaseOrder::class);
    }
}
