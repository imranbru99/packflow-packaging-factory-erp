<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class Routing extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }

    public function steps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\RoutingStep::class);
    }
}
