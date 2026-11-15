<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class WorkCenter extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'capacity_per_hour' => 'decimal:2',
            'rate_per_minute' => 'decimal:2',
        ];
    }

    public function machines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Production\Models\Machine::class);
    }

    public function steps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\RoutingStep::class);
    }
}
