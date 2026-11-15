<?php

namespace App\Modules\Maintenance\Models;

use App\Modules\Support\PackflowModel;

class DowntimeEvent extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'minutes' => 'decimal:2',
            'started_at' => 'datetime',
        ];
    }

    public function machine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\Machine::class);
    }

    public function stage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionStage::class, 'production_stage_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
