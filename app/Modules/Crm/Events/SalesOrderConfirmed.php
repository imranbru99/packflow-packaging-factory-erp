<?php

namespace App\Modules\Crm\Events;

use App\Modules\Crm\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesOrderConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public SalesOrder $order) {}
}
