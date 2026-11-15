<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Production\Models\ProductionOrder;

class ProductionOrderPolicy
{
    public function view(User $user, ProductionOrder $order): bool
    {
        return $user->canAccessModule('production') || $user->canAccessModule('shopfloor');
    }

    public function execute(User $user, ProductionOrder $order): bool
    {
        return $user->canAccessModule('shopfloor');
    }
}
