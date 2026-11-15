<?php

namespace App\Modules\Production\Models;

use App\Modules\Support\PackflowModel;

class ScheduleSlot extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function machine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\Machine::class);
    }

    public function workCenter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\WorkCenter::class);
    }
}
