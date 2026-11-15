<?php

namespace App\Modules\Crm\Services;

use App\Modules\Crm\Events\SalesOrderConfirmed;
use App\Modules\Crm\Models\SalesOrder;

class SalesOrderService
{
    public function confirm(SalesOrder $order): SalesOrder
    {
        if ($order->status === 'confirmed') {
            return $order;
        }

        $order->status = 'confirmed';
        $order->confirmed_at = now();
        $order->save();

        event(new SalesOrderConfirmed($order));

        return $order->fresh('productionOrders');
    }
}
