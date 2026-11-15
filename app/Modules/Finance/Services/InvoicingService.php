<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceLine;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalLine;
use App\Modules\Finance\Models\Payment;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Settings\Models\CompanySetting;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class InvoicingService
{
    public function fromSalesOrder(SalesOrder $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $existing = $order->invoices()->where('status', '!=', 'void')->first();
            if ($existing) {
                return $existing;
            }

            $order->load('lines');
            $company = CompanySetting::current();
            $subtotal = (float) $order->lines->sum('line_total');
            $taxPercent = (float) ($order->tax_percent ?: $company->tax_percent);
            $tax = round($subtotal * $taxPercent / 100, 2);

            $invoice = Invoice::query()->create([
                'number' => DocumentNumber::next($company->invoice_prefix ?: 'INV'),
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'user_id' => auth()->id(),
                'status' => 'issued',
                'issued_on' => now()->toDateString(),
                'due_on' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'tax_percent' => $taxPercent,
                'tax_amount' => $tax,
                'total' => $subtotal + $tax,
                'paid' => 0,
            ]);

            foreach ($order->lines as $line) {
                InvoiceLine::query()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line->product_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'line_total' => $line->line_total,
                ]);
            }

            $entry = JournalEntry::query()->create([
                'number' => DocumentNumber::next('JV'),
                'entry_date' => $invoice->issued_on,
                'memo' => 'Invoice '.$invoice->number,
                'source_type' => $invoice->getMorphClass(),
                'source_id' => $invoice->id,
            ]);

            JournalLine::query()->create(['journal_entry_id' => $entry->id, 'account' => 'Accounts receivable', 'debit' => $invoice->total, 'credit' => 0]);
            JournalLine::query()->create(['journal_entry_id' => $entry->id, 'account' => 'Sales', 'debit' => 0, 'credit' => $subtotal]);
            if ($tax > 0) {
                JournalLine::query()->create(['journal_entry_id' => $entry->id, 'account' => $company->tax_name.' payable', 'debit' => 0, 'credit' => $tax]);
            }

            $order->status = 'invoiced';
            $order->save();

            return $invoice->load('lines', 'customer');
        });
    }

    public function pay(Invoice $invoice, float $amount, string $method = 'bank', ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $method, $reference) {
            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id(),
                'paid_on' => now()->toDateString(),
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
            ]);

            $invoice->paid = (float) $invoice->paid + $amount;
            $invoice->status = $invoice->paid + 0.01 >= (float) $invoice->total ? 'paid' : 'partial';
            $invoice->save();

            $entry = JournalEntry::query()->create([
                'number' => DocumentNumber::next('JV'),
                'entry_date' => $payment->paid_on,
                'memo' => 'Receipt '.$invoice->number,
                'source_type' => $payment->getMorphClass(),
                'source_id' => $payment->id,
            ]);
            JournalLine::query()->create(['journal_entry_id' => $entry->id, 'account' => 'Cash', 'debit' => $amount, 'credit' => 0]);
            JournalLine::query()->create(['journal_entry_id' => $entry->id, 'account' => 'Accounts receivable', 'debit' => 0, 'credit' => $amount]);

            return $payment;
        });
    }
}
