<?php

namespace App\Modules\Finance\Models;

use App\Modules\Support\PackflowModel;

class JournalEntry extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\JournalLine::class);
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
