<?php

namespace App\Modules\Issues\Models;

use App\Modules\Support\PackflowModel;

class IssueComment extends PackflowModel
{

    public function issue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Issues\Models\Issue::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
