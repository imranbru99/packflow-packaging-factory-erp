<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class RfqLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'target_price' => 'decimal:4',
        ];
    }

    public function rfq(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\Rfq::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }
}
