<?php

namespace App\Modules\Production\Models;

use App\Modules\Support\PackflowModel;

class ProductionStage extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'planned_qty' => 'decimal:3',
            'good_qty' => 'decimal:3',
            'waste_qty' => 'decimal:3',
            'downtime_minutes' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function workCenter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\WorkCenter::class);
    }

    public function machine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\Machine::class);
    }

    public function operator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }
}
