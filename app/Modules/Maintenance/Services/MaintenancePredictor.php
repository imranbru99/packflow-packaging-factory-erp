<?php

namespace App\Modules\Maintenance\Services;

use App\Modules\Issues\Models\Issue;
use App\Modules\Maintenance\Models\MaintenanceOrder;
use App\Modules\Maintenance\Models\MaintenancePlan;
use App\Modules\Production\Models\Machine;
use App\Support\DocumentNumber;

class MaintenancePredictor
{
    public function scan(): int
    {
        $opened = 0;

        MaintenancePlan::query()->whereDate('next_due_on', '<=', today())->each(function (MaintenancePlan $plan) use (&$opened) {
            $exists = MaintenanceOrder::query()
                ->where('maintenance_plan_id', $plan->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->exists();

            if ($exists) {
                return;
            }

            MaintenanceOrder::query()->create([
                'number' => DocumentNumber::next('MO'),
                'machine_id' => $plan->machine_id,
                'maintenance_plan_id' => $plan->id,
                'kind' => 'preventive',
                'status' => 'open',
                'title' => 'Preventive: '.$plan->name,
            ]);
            $opened++;
        });

        Machine::query()->withCount(['downtimeEvents' => fn ($q) => $q->where('created_at', '>=', now()->subDays(30))])
            ->get()
            ->filter(fn (Machine $machine) => $machine->downtime_events_count >= 3)
            ->each(function (Machine $machine) use (&$opened) {
                $title = 'Repeated downtime on '.$machine->code;
                $exists = Issue::query()->where('title', $title)->where('status', '!=', 'closed')->exists();
                if ($exists) {
                    return;
                }

                Issue::query()->create([
                    'number' => DocumentNumber::next('IS'),
                    'subject_type' => $machine->getMorphClass(),
                    'subject_id' => $machine->id,
                    'category' => 'machine_breakdown',
                    'severity' => 'high',
                    'status' => 'open',
                    'title' => $title,
                    'description' => 'Three or more downtime events in 30 days. Inspect before the next long run.',
                ]);
                $machine->status = 'watch';
                $machine->save();
                $opened++;
            });

        return $opened;
    }
}
