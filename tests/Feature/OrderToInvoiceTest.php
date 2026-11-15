<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\Bom;
use App\Modules\Catalog\Models\BomItem;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\WorkCenter;
use App\Modules\Crm\Models\Customer;
use App\Modules\Crm\Models\Lead;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Crm\Models\SalesOrderLine;
use App\Modules\Crm\Services\SalesOrderService;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\InvoicingService;
use App\Modules\Inventory\Models\Material;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Production\Services\ShopFloorService;
use App\Modules\Settings\Models\ModuleSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrderToInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_order_becomes_a_job_and_then_an_invoice(): void
    {
        $user = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($user);

        $customer = Customer::query()->create(['code' => 'C1', 'name' => 'Meghna Foods', 'is_active' => true]);
        $product = Product::query()->create([
            'sku' => 'BOX-1',
            'name' => 'RSC shipper',
            'type' => 'corrugated',
            'is_active' => true,
            'is_public' => false,
        ]);
        $center = WorkCenter::query()->create([
            'code' => 'PRT', 'name' => 'Print', 'process' => 'printing', 'capacity_per_hour' => 1000, 'rate_per_minute' => 40,
        ]);
        $material = Material::query()->create([
            'sku' => 'KRAFT', 'name' => 'Kraft', 'category' => 'paper', 'uom' => 'kg', 'standard_cost' => 70, 'reorder_level' => 10,
        ]);
        $warehouse = Warehouse::query()->create(['code' => 'RAW', 'name' => 'Store', 'kind' => 'raw']);
        Warehouse::query()->create(['code' => 'FG', 'name' => 'Finished', 'kind' => 'finished']);
        app(StockLedger::class)->receive($material, $warehouse->id, 'L1', 500, 70);

        $bom = Bom::query()->create(['product_id' => $product->id, 'name' => 'Std', 'is_active' => true]);
        BomItem::query()->create(['bom_id' => $bom->id, 'material_id' => $material->id, 'qty_per' => 0.2, 'uom' => 'kg', 'scrap_percent' => 5]);

        $order = SalesOrder::query()->create([
            'number' => 'SO-TEST-1',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'draft',
            'ordered_on' => now()->toDateString(),
            'due_on' => now()->addDays(12)->toDateString(),
            'tax_percent' => 15,
            'subtotal' => 1000,
            'tax_amount' => 150,
            'total' => 1150,
        ]);
        SalesOrderLine::query()->create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id,
            'description' => 'RSC shipper',
            'quantity' => 100,
            'unit_price' => 10,
            'line_total' => 1000,
        ]);

        app(SalesOrderService::class)->confirm($order);

        $job = ProductionOrder::query()->where('sales_order_id', $order->id)->first();
        $this->assertNotNull($job);
        $this->assertGreaterThan(0, $job->stages()->count());
        $this->assertGreaterThan(0, $job->requirements()->first()->reserved_qty);
        $this->assertSame($center->id, $job->stages()->first()->work_center_id);

        app(ShopFloorService::class)->completeAll($job);

        $job->refresh();
        $this->assertSame('qc', $job->status);
        $this->assertEquals(100, (float) $job->good_qty);

        $invoice = app(InvoicingService::class)->fromSalesOrder($order->fresh('lines'));
        $this->assertSame('issued', $invoice->status);
        $this->assertEquals(1150.0, (float) $invoice->total);
        $this->assertDatabaseHas('journal_lines', ['account' => 'Accounts receivable', 'debit' => 1150]);
    }

    public function test_public_quote_creates_a_lead(): void
    {
        $this->get('/')->assertOk();

        $this->post('/contact', [
            'company' => 'Northstar Electronics',
            'contact_name' => 'Rafi',
            'email' => 'rafi@northstar.test',
            'pack_type' => 'corrugated',
            'quantity' => 1000,
            'specification' => '400 x 300 x 200 B flute',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['email' => 'rafi@northstar.test']);
        $this->assertSame(1, Lead::query()->count());
    }

    public function test_disabled_module_is_hidden_from_staff(): void
    {
        $user = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        ModuleSetting::query()->create(['key' => 'crm', 'label' => 'CRM', 'enabled' => false]);
        Cache::flush();

        $this->actingAs($user)->get('/admin/resources/customers')->assertForbidden();
    }
}
