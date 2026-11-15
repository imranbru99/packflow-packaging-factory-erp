<?php

namespace App\Modules\Issues\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class Issue extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'escalated_at' => 'datetime',
        ];
    }

    public function reporter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reporter_id');
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Issues\Models\IssueComment::class);
    }
}
