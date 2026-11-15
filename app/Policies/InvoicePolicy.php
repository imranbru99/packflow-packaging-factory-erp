<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Invoice;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->customer_id && $user->customer_id === $invoice->customer_id) {
            return true;
        }

        return $user->canAccessModule('finance');
    }
}
