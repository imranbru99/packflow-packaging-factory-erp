<?php

namespace App\Modules\Finance\Models;

use App\Modules\Support\PackflowModel;

class JournalLine extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function entry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Finance\Models\JournalEntry::class, 'journal_entry_id');
    }
}
