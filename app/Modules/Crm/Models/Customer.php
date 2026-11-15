<?php

namespace App\Modules\Crm\Models;

use App\Modules\Support\PackflowModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends PackflowModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\Contact::class);
    }

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\Lead::class);
    }

    public function opportunities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\Opportunity::class);
    }

    public function quotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\Quotation::class);
    }

    public function salesOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Models\SalesOrder::class);
    }

    public function complaints(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Quality\Models\Complaint::class);
    }
}
