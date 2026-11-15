<?php

namespace App\Modules\Production\Models;

use App\Modules\Support\PackflowModel;

class Machine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'installed_on' => 'date',
            'oee_target' => 'decimal:2',
        ];
    }

    public function workCenter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\WorkCenter::class);
    }

    public function plans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Maintenance\Models\MaintenancePlan::class);
    }

    public function downtimeEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Maintenance\Models\DowntimeEvent::class);
    }
}
