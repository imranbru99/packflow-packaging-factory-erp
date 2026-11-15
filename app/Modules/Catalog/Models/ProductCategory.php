<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Support\PackflowModel;

class ProductCategory extends PackflowModel
{

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Catalog\Models\Product::class);
    }
}
