<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class Lot extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'received_on' => 'date',
            'expires_on' => 'date',
            'qty_on_hand' => 'decimal:3',
            'qty_reserved' => 'decimal:3',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }
}
