<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class PurchaseSuggestion extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'suggested_qty' => 'decimal:3',
        ];
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\Supplier::class);
    }
}
