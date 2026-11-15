<?php

namespace App\Console\Commands;

use App\Modules\Maintenance\Services\MaintenancePredictor;
use Illuminate\Console\Command;

class MaintenanceDue extends Command
{
    protected $signature = 'packflow:maintenance-due';

    protected $description = 'Open preventive jobs and flag machines with repeated downtime';

    public function handle(MaintenancePredictor $predictor): int
    {
        $count = $predictor->scan();
        $this->info("Maintenance signals: {$count}");

        return self::SUCCESS;
    }
}
