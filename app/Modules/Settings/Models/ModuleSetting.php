<?php

namespace App\Modules\Settings\Models;

use App\Modules\Support\PackflowModel;

class ModuleSetting extends PackflowModel
{

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
