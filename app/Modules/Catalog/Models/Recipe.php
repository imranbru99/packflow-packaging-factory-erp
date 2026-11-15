<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class Recipe extends PackflowModel
{

    public function components(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\RecipeComponent::class);
    }
}
