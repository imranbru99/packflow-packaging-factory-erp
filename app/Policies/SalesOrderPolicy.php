<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Crm\Models\SalesOrder;

class SalesOrderPolicy
{
    public function view(User $user, SalesOrder $order): bool
    {
        if ($user->customer_id && $user->customer_id === $order->customer_id) {
            return true;
        }

        return $user->canAccessModule('crm');
    }

    public function confirm(User $user, SalesOrder $order): bool
    {
        return $user->canAccessModule('crm') && in_array($user->role, ['super_admin', 'factory_manager', 'sales'], true);
    }

    public function invoice(User $user, SalesOrder $order): bool
    {
        return $user->canAccessModule('finance') || $user->canAccessModule('crm');
    }
}
