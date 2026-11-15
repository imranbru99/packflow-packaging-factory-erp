<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PackflowNotification;
use Illuminate\Console\Command;

class EmailReports extends Command
{
    protected $signature = 'packflow:email-reports';

    protected $description = 'Queue the weekly plant summary to finance and the factory manager';

    public function handle(): int
    {
        User::query()->whereIn('role', ['finance', 'factory_manager', 'super_admin'])->each(function (User $user) {
            $user->notify(new PackflowNotification(
                'Weekly plant pack',
                'Inventory valuation, waste, and open orders are ready in Reports.',
                url('/admin/reports'),
            ));
        });

        $this->info('Report notes queued.');

        return self::SUCCESS;
    }
}
