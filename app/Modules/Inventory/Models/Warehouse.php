<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Support\PackflowModel;

class Warehouse extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function locations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\Location::class);
    }

    public function lots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\Lot::class);
    }
}
