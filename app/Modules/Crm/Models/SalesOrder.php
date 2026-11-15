<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class SalesOrder extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'ordered_on' => 'date',
            'due_on' => 'date',
            'confirmed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_percent' => 'decimal:2',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }

    public function quotation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Quotation::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\SalesOrderLine::class);
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\ArtworkApproval::class);
    }

    public function productionOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Production\Models\ProductionOrder::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\Invoice::class);
    }
}
