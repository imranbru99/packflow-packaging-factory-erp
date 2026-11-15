<?php

namespace App\Modules\Hr\Models;

use App\Modules\Support\PackflowModel;

class Shift extends PackflowModel
{

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
