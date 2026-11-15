<?php

namespace App\Modules\Production\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class ProductionOrder extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'good_qty' => 'decimal:3',
            'waste_qty' => 'decimal:3',
            'planned_start' => 'date',
            'planned_end' => 'date',
        ];
    }

    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\SalesOrder::class);
    }

    public function salesOrderLine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\SalesOrderLine::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Catalog\Models\Product::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function stages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Production\Models\ProductionStage::class);
    }

    public function requirements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Production\Models\MaterialRequirement::class);
    }

    public function slots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Production\Models\ScheduleSlot::class);
    }

    public function jobCost(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\Finance\Models\JobCost::class);
    }
}
