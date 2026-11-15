<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class RecipeComponent extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'gsm' => 'decimal:2',
        ];
    }

    public function recipe(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Recipe::class);
    }

    public function material(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Material::class);
    }
}
