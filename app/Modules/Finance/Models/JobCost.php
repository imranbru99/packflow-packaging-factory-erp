<?php

namespace App\Modules\Finance\Models;

use App\Modules\Support\PackflowModel;

class JobCost extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'planned_material' => 'decimal:2',
            'actual_material' => 'decimal:2',
            'planned_conversion' => 'decimal:2',
            'actual_conversion' => 'decimal:2',
            'waste_cost' => 'decimal:2',
            'planned_total' => 'decimal:2',
            'actual_total' => 'decimal:2',
        ];
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }
}
