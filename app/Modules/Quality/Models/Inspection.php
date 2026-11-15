<?php

namespace App\Modules\Quality\Models;

use App\Modules\Support\PackflowModel;

class Inspection extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
        ];
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function inspector(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'inspector_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Quality\Models\InspectionItem::class);
    }

    public function defects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Quality\Models\Defect::class);
    }
}
