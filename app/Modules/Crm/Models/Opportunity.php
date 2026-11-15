<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class Opportunity extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:2',
            'expected_close' => 'date',
        ];
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Lead::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }
}
