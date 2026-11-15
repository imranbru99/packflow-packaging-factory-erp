<?php

namespace App\Modules\Quality\Models;

use App\Modules\Support\PackflowModel;

class CorrectiveAction extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
        ];
    }

    public function defect(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Quality\Models\Defect::class);
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }
}
