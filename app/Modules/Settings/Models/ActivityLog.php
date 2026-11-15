<?php

namespace App\Modules\Settings\Models;

use App\Modules\Support\PackflowModel;

class ActivityLog extends PackflowModel
{
    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
