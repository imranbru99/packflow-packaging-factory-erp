<?php

namespace App\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Bom;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductCategory;
use App\Modules\Catalog\Models\Recipe;
use App\Modules\Catalog\Models\Routing;
use App\Modules\Catalog\Models\Tooling;
use App\Modules\Catalog\Models\WorkCenter;
use App\Modules\Crm\Models\ArtworkApproval;
use App\Modules\Crm\Models\Contact;
use App\Modules\Crm\Models\Customer;
use App\Modules\Crm\Models\Lead;
use App\Modules\Crm\Models\Opportunity;
use App\Modules\Crm\Models\Quotation;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\JobCost;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\Payment;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\LeaveRequest;
use App\Modules\Hr\Models\OvertimeEntry;
use App\Modules\Hr\Models\Shift;
use App\Modules\Inventory\Models\FinishedStock;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\Lot;
use App\Modules\Inventory\Models\Material;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\KnowledgeArticle;
use App\Modules\Maintenance\Models\DowntimeEvent;
use App\Modules\Maintenance\Models\MaintenanceOrder;
use App\Modules\Maintenance\Models\MaintenancePlan;
use App\Modules\Procurement\Models\GoodsReceipt;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseSuggestion;
use App\Modules\Procurement\Models\Rfq;
use App\Modules\Procurement\Models\Supplier;
use App\Modules\Production\Models\Machine;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Quality\Models\Complaint;
use App\Modules\Quality\Models\CorrectiveAction;
use App\Modules\Quality\Models\Defect;
use App\Modules\Quality\Models\Inspection;
use App\Modules\Settings\Models\ActivityLog;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Website\Models\CaseStudy;
use App\Modules\Website\Models\Page;
use App\Modules\Website\Models\Post;
use App\Modules\Website\Models\QuoteRequest;
use App\Modules\Website\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;

class ResourceRegistry
{
    /** @return array<string, class-string<Model>> */
    public static function models(): array
    {
        return [
            'departments' => Department::class,
            'shifts' => Shift::class,
            'employees' => User::class,
            'leave-requests' => LeaveRequest::class,
            'overtime' => OvertimeEntry::class,
            'customers' => Customer::class,
            'contacts' => Contact::class,
            'leads' => Lead::class,
            'opportunities' => Opportunity::class,
            'quotations' => Quotation::class,
            'sales-orders' => SalesOrder::class,
            'artwork' => ArtworkApproval::class,
            'categories' => ProductCategory::class,
            'products' => Product::class,
            'recipes' => Recipe::class,
            'toolings' => Tooling::class,
            'boms' => Bom::class,
            'routings' => Routing::class,
            'work-centers' => WorkCenter::class,
            'materials' => Material::class,
            'lots' => Lot::class,
            'warehouses' => Warehouse::class,
            'locations' => Location::class,
            'movements' => StockMovement::class,
            'finished-goods' => FinishedStock::class,
            'suppliers' => Supplier::class,
            'rfqs' => Rfq::class,
            'purchase-orders' => PurchaseOrder::class,
            'goods-receipts' => GoodsReceipt::class,
            'purchase-suggestions' => PurchaseSuggestion::class,
            'machines' => Machine::class,
            'production-orders' => ProductionOrder::class,
            'inspections' => Inspection::class,
            'defects' => Defect::class,
            'corrective-actions' => CorrectiveAction::class,
            'complaints' => Complaint::class,
            'maintenance-plans' => MaintenancePlan::class,
            'maintenance-orders' => MaintenanceOrder::class,
            'downtime' => DowntimeEvent::class,
            'invoices' => Invoice::class,
            'payments' => Payment::class,
            'journals' => JournalEntry::class,
            'job-costs' => JobCost::class,
            'issues' => Issue::class,
            'knowledge' => KnowledgeArticle::class,
            'activity' => ActivityLog::class,
            'company' => CompanySetting::class,
            'posts' => Post::class,
            'case-studies' => CaseStudy::class,
            'testimonials' => Testimonial::class,
            'pages' => Page::class,
            'quote-requests' => QuoteRequest::class,
        ];
    }

    /** @return array<string, string> */
    public static function modules(): array
    {
        return [
            'departments' => 'hr', 'shifts' => 'hr', 'employees' => 'hr', 'leave-requests' => 'hr', 'overtime' => 'hr',
            'customers' => 'crm', 'contacts' => 'crm', 'leads' => 'crm', 'opportunities' => 'crm', 'quotations' => 'crm', 'sales-orders' => 'crm', 'artwork' => 'crm',
            'categories' => 'catalog', 'products' => 'catalog', 'recipes' => 'catalog', 'toolings' => 'catalog', 'boms' => 'catalog', 'routings' => 'catalog', 'work-centers' => 'catalog',
            'materials' => 'inventory', 'lots' => 'inventory', 'warehouses' => 'inventory', 'locations' => 'inventory', 'movements' => 'inventory', 'finished-goods' => 'inventory',
            'suppliers' => 'procurement', 'rfqs' => 'procurement', 'purchase-orders' => 'procurement', 'goods-receipts' => 'procurement', 'purchase-suggestions' => 'procurement',
            'machines' => 'maintenance', 'production-orders' => 'production',
            'inspections' => 'quality', 'defects' => 'quality', 'corrective-actions' => 'quality', 'complaints' => 'quality',
            'maintenance-plans' => 'maintenance', 'maintenance-orders' => 'maintenance', 'downtime' => 'maintenance',
            'invoices' => 'finance', 'payments' => 'finance', 'journals' => 'finance', 'job-costs' => 'finance',
            'issues' => 'issues', 'knowledge' => 'issues',
            'activity' => 'settings', 'company' => 'settings',
            'posts' => 'website', 'case-studies' => 'website', 'testimonials' => 'website', 'pages' => 'website', 'quote-requests' => 'website',
        ];
    }

    /** @return array<string, string> */
    public static function groups(): array
    {
        return [
            'hr' => 'People',
            'crm' => 'Sales',
            'catalog' => 'Products',
            'inventory' => 'Warehouse',
            'procurement' => 'Buying',
            'production' => 'Production',
            'quality' => 'Quality',
            'maintenance' => 'Maintenance',
            'finance' => 'Finance',
            'issues' => 'Issues',
            'website' => 'Website',
            'settings' => 'System',
        ];
    }

    public static function label(string $slug): string
    {
        return str($slug)->replace('-', ' ')->title()->toString();
    }

    public static function find(string $slug): string
    {
        $class = static::models()[$slug] ?? null;

        if ($class === null) {
            abort(404);
        }

        return $class;
    }
}
