<?php

namespace App\Modules\Quality\Models;

use App\Modules\Support\PackflowModel;

class Defect extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
        ];
    }

    public function inspection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Quality\Models\Inspection::class);
    }

    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function machine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Production\Models\Machine::class);
    }

    public function lot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Lot::class);
    }

    public function operator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'operator_id');
    }

    public function actions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Quality\Models\CorrectiveAction::class);
    }
}
