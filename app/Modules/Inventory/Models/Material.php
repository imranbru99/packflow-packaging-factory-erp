<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Support\PackflowModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends PackflowModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gsm' => 'decimal:2',
            'standard_cost' => 'decimal:4',
            'reorder_level' => 'decimal:3',
        ];
    }

    public function lots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\Lot::class);
    }

    public function movements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\StockMovement::class);
    }
}
