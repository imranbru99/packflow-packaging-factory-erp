<?php

namespace App\Modules\Production\Listeners;

use App\Modules\Crm\Events\SalesOrderConfirmed;
use App\Modules\Production\Services\ProductionPlanner;
use App\Notifications\PackflowNotification;
use App\Support\ModuleManager;

class PlanProduction
{
    public function __construct(
        protected ProductionPlanner $planner,
        protected ModuleManager $modules,
    ) {}

    public function handle(SalesOrderConfirmed $event): void
    {
        if (! $this->modules->enabled('production')) {
            return;
        }

        $job = $this->planner->fromSalesOrder($event->order);

        $event->order->user?->notify(new PackflowNotification(
            'Production order '.$job->number,
            'Materials were reserved and the routing was opened from '.$event->order->number.'.',
            route('admin.resources.show', ['resource' => 'production-orders', 'id' => $job->id]),
        ));
    }
}
