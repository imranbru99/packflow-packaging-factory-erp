<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class Specification extends PackflowModel
{

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }
}
