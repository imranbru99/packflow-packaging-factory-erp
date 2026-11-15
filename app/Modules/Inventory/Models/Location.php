<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Support\PackflowModel;

class Location extends PackflowModel
{

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }
}
