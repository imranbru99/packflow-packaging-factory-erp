<?php

namespace App\Modules\Production\Events;

use App\Modules\Production\Models\ProductionStage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StageCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductionStage $stage,
        public ?ProductionStage $next,
    ) {}
}
