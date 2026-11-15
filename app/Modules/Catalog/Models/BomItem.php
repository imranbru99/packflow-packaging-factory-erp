<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class BomItem extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'qty_per' => 'decimal:4',
            'scrap_percent' => 'decimal:2',
        ];
    }

    public function bom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Bom::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }
}
