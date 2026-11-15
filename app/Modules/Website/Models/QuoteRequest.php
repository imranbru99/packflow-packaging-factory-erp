<?php

namespace App\Modules\Website\Models;

use App\Modules\Support\PackflowModel;

class QuoteRequest extends PackflowModel
{

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Lead::class);
    }
}
