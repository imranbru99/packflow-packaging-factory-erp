<?php

namespace App\Modules\Website\Models;

use App\Modules\Support\PackflowModel;

class Testimonial extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
