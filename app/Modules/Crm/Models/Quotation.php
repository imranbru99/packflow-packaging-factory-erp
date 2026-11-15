<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;
use App\Modules\Support\Auditable;

class Quotation extends PackflowModel
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'margin_percent' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'date',
            'ai_notes' => 'array',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Customer::class);
    }

    public function opportunity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Opportunity::class);
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Crm\Models\Lead::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\QuotationLine::class);
    }
}
