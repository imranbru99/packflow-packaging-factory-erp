<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class Contact extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }
}
