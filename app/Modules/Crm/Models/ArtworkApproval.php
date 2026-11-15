<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class ArtworkApproval extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\SalesOrder::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
