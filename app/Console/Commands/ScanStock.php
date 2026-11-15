<?php

namespace App\Console\Commands;

use App\Modules\Inventory\Services\StockLedger;
use Illuminate\Console\Command;

class ScanStock extends Command
{
    protected $signature = 'packflow:scan-stock';

    protected $description = 'Raise purchase suggestions for materials under their reorder level';

    public function handle(StockLedger $ledger): int
    {
        $count = $ledger->scanReorderLevels();
        $this->info("Suggestions touched: {$count}");

        return self::SUCCESS;
    }
}
