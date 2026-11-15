<?php

namespace App\Modules\Maintenance\Models;

use App\Modules\Support\PackflowModel;

class MaintenancePlan extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'last_done_on' => 'date',
            'next_due_on' => 'date',
        ];
    }

    public function machine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\Machine::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Maintenance\Models\MaintenanceOrder::class);
    }
}
