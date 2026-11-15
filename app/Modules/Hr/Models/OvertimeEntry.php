<?php

namespace App\Modules\Hr\Models;

use App\Modules\Support\PackflowModel;

class OvertimeEntry extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'worked_on' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
