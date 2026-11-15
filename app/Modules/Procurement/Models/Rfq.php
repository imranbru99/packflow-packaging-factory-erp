<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Support\PackflowModel;

class Rfq extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
        ];
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Procurement\Models\Supplier::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Procurement\Models\RfqLine::class);
    }
}
