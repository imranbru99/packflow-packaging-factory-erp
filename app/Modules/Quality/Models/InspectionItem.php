<?php

namespace App\Modules\Quality\Models;

use App\Modules\Support\PackflowModel;

class InspectionItem extends PackflowModel
{

    public function inspection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Quality\Models\Inspection::class);
    }
}
