<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class RoutingStep extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'setup_minutes' => 'decimal:2',
            'speed_per_minute' => 'decimal:2',
        ];
    }

    public function routing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Routing::class);
    }

    public function workCenter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\WorkCenter::class);
    }
}
