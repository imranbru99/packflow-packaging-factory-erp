<?php

namespace App\Modules\Production\Models;

use App\Modules\Support\PackflowModel;

class MaterialRequirement extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'required_qty' => 'decimal:3',
            'reserved_qty' => 'decimal:3',
            'issued_qty' => 'decimal:3',
            'shortage_qty' => 'decimal:3',
        ];
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }
}
