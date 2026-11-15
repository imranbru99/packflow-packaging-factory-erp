<?php

namespace App\Modules\Finance\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class Invoice extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'due_on' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'tax_percent' => 'decimal:2',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }

    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\SalesOrder::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\InvoiceLine::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\Payment::class);
    }
}
