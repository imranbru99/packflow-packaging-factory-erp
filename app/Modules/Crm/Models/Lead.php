<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;

class Lead extends PackflowModel
{

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function quoteRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Website\Models\QuoteRequest::class);
    }
}
