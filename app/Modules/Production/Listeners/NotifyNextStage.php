<?php

namespace App\Modules\Production\Listeners;

use App\Modules\Production\Events\StageCompleted;
use App\Notifications\PackflowNotification;
use App\Models\User;

class NotifyNextStage
{
    public function handle(StageCompleted $event): void
    {
        $stage = $event->stage;
        $message = $event->next
            ? $stage->name.' is done. '.$event->next->name.' can start on '.$stage->productionOrder->number.'.'
            : $stage->productionOrder->number.' is waiting for final inspection.';

        User::query()
            ->whereIn('role', ['production_supervisor', 'factory_manager', 'operator'])
            ->where('is_active', true)
            ->each(fn (User $user) => $user->notify(new PackflowNotification(
                'Stage handoff',
                $message,
                route('admin.shop-floor.show', $stage->production_order_id),
            )));
    }
}
