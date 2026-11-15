<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Models\Bom;
use App\Modules\Catalog\Models\BomItem;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductCategory;
use App\Modules\Catalog\Models\Recipe;
use App\Modules\Catalog\Models\RecipeComponent;
use App\Modules\Catalog\Models\Routing;
use App\Modules\Catalog\Models\RoutingStep;
use App\Modules\Catalog\Models\Specification;
use App\Modules\Catalog\Models\Tooling;
use App\Modules\Catalog\Models\WorkCenter;
use App\Modules\Crm\Models\Contact;
use App\Modules\Crm\Models\Customer;
use App\Modules\Crm\Models\Lead;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Crm\Models\SalesOrderLine;
use App\Modules\Crm\Services\SalesOrderService;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Services\InvoicingService;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Shift;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\Material;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\StockLedger;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\KnowledgeArticle;
use App\Modules\Issues\Services\IssueDiagnosisService;
use App\Modules\Maintenance\Models\MaintenancePlan;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Procurement\Models\Supplier;
use App\Modules\Production\Models\Machine;
use App\Modules\Quality\Models\Complaint;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Settings\Models\ModuleSetting;
use App\Modules\Website\Models\CaseStudy;
use App\Modules\Website\Models\Page;
use App\Modules\Website\Models\Post;
use App\Modules\Website\Models\Testimonial;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use ImranDevBd\AttendanceHub\Models\AttendanceDevice;
use ImranDevBd\AttendanceHub\Models\AttendanceLog;

class PackflowSeeder extends Seeder
{
    public function run(): void
    {
        $company = CompanySetting::query()->updateOrCreate(['id' => 1], [
            'name' => 'Padma Convertors Ltd.',
            'name_bn' => 'পদ্মা কনভার্টার্স লিমিটেড',
            'tagline' => 'Boxes, pouches, labels, and cartons from one floor.',
            'email' => 'sales@padma-convertors.test',
            'phone' => '+880 1711-440220',
            'address' => 'Plot 14, BSCIC Industrial Area, Tongi',
            'city' => 'Gazipur',
            'country' => 'Bangladesh',
            'currency' => 'BDT',
            'tax_name' => 'VAT',
            'tax_percent' => 15,
            'timezone' => 'Asia/Dhaka',
        ]);

        foreach (config('packflow.modules') as $key => $meta) {
            ModuleSetting::query()->updateOrCreate(['key' => $key], ['label' => $meta['label'], 'enabled' => true]);
        }

        $departments = collect([
            ['code' => 'PROD', 'name' => 'Production', 'name_bn' => 'উৎপাদন'],
            ['code' => 'QC', 'name' => 'Quality', 'name_bn' => 'মান নিয়ন্ত্রণ'],
            ['code' => 'SALES', 'name' => 'Sales', 'name_bn' => 'বিক্রয়'],
            ['code' => 'WH', 'name' => 'Warehouse', 'name_bn' => 'গুদাম'],
        ])->mapWithKeys(fn ($row) => [$row['code'] => Department::query()->updateOrCreate(['code' => $row['code']], $row)]);

        $day = Shift::query()->updateOrCreate(['name' => 'Day'], ['name_bn' => 'দিন', 'starts_at' => '08:00', 'ends_at' => '17:00']);
        Shift::query()->updateOrCreate(['name' => 'Night'], ['name_bn' => 'রাত', 'starts_at' => '20:00', 'ends_at' => '05:00']);

        $users = [];
        foreach ([
            ['admin@packflow.test', 'Factory Admin', 'super_admin', 'EMP-001', 'PROD', 'Plant director'],
            ['manager@packflow.test', 'Nabila Rahman', 'factory_manager', 'EMP-002', 'PROD', 'Factory manager'],
            ['sales@packflow.test', 'Imtiaz Chowdhury', 'sales', 'EMP-010', 'SALES', 'Estimator'],
            ['supervisor@packflow.test', 'Kamal Hossain', 'production_supervisor', 'EMP-020', 'PROD', 'Shift supervisor'],
            ['qc@packflow.test', 'Farhana Akter', 'qc_inspector', 'EMP-030', 'QC', 'QC inspector'],
            ['warehouse@packflow.test', 'Sohel Rana', 'warehouse', 'EMP-040', 'WH', 'Store keeper'],
            ['finance@packflow.test', 'Lamia Sultana', 'finance', 'EMP-050', 'SALES', 'Accounts'],
            ['operator@packflow.test', 'Jahidul Islam', 'operator', 'EMP-060', 'PROD', 'Flexo operator'],
        ] as [$email, $name, $role, $code, $dept, $title]) {
            $users[$role] = User::query()->updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => $role,
                'employee_code' => $code,
                'designation' => $title,
                'department_id' => $departments[$dept]->id,
                'shift_id' => $day->id,
                'phone' => '+8801700000'.substr($code, -3),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $raw = Warehouse::query()->updateOrCreate(['code' => 'RAW'], ['name' => 'Reel store', 'kind' => 'raw', 'address' => 'Bay A']);
        $fg = Warehouse::query()->updateOrCreate(['code' => 'FG'], ['name' => 'Finished goods', 'kind' => 'finished', 'address' => 'Bay C']);
        Location::query()->updateOrCreate(['warehouse_id' => $raw->id, 'code' => 'A-01'], ['name' => 'Kraft aisle']);

        $ledger = app(StockLedger::class);
        $materials = [];
        foreach ([
            ['KRAFT-175', 'Kraft liner 175 GSM', 'paper', 'kg', 175, 500, 68, 8000],
            ['FLUTE-112', 'Fluting medium 112 GSM', 'paper', 'kg', 112, 400, 54, 6000],
            ['TEST-140', 'Test liner 140 GSM', 'paper', 'kg', 140, 300, 61, 4000],
            ['DUPLEX-300', 'White duplex 300 GSM', 'board', 'kg', 300, 200, 96, 2500],
            ['PET-12', 'PET film 12 micron', 'film', 'kg', null, 150, 240, 900],
            ['MET-12', 'Metallised PET 12 micron', 'film', 'kg', null, 120, 280, 700],
            ['PE-80', 'PE sealant 80 micron', 'film', 'kg', null, 200, 190, 1200],
            ['INK-CMYK', 'Process ink set', 'ink', 'kg', null, 40, 480, 180],
            ['ADH-SL', 'Solventless adhesive', 'adhesive', 'kg', null, 80, 320, 400],
            ['LBL-PP', 'PP label stock', 'label', 'm2', null, 1000, 22, 5000],
            ['WIRE-ST', 'Stitching wire', 'spare', 'kg', null, 50, 140, 12],
        ] as [$sku, $name, $category, $uom, $gsm, $reorder, $cost, $qty]) {
            $material = Material::query()->updateOrCreate(['sku' => $sku], [
                'name' => $name,
                'category' => $category,
                'uom' => $uom,
                'gsm' => $gsm,
                'reorder_level' => $reorder,
                'standard_cost' => $cost,
                'barcode' => 'PF'.$sku,
                'is_active' => true,
            ]);
            $materials[$sku] = $material;
            if ($material->lots()->doesntExist()) {
                $ledger->receive($material, $raw->id, 'LOT-'.$sku.'-01', $qty, $cost, null, [
                    'gsm' => $gsm,
                    'reel_width_mm' => in_array($category, ['paper', 'film', 'label'], true) ? 1600 : null,
                    'reel_weight_kg' => $uom === 'kg' ? $qty : null,
                ]);
            }
        }

        $centers = [];
        foreach ([
            ['PRT', 'Flexo / gravure print', 'printing', 4500, 55],
            ['CUT', 'Die cutting', 'cutting', 3000, 40],
            ['GLU', 'Folder gluer', 'gluing', 6000, 35],
            ['LAM', 'Solventless laminator', 'lamination', 2500, 70],
            ['SLT', 'Slitter rewinder', 'slitting', 8000, 30],
            ['PCH', 'Pouch making', 'forming', 4000, 48],
            ['FIN', 'Label finishing', 'finishing', 5000, 32],
            ['PKG', 'Packing', 'packing', 7000, 12],
        ] as [$code, $name, $process, $cap, $rate]) {
            $centers[$process] = WorkCenter::query()->updateOrCreate(['code' => $code], [
                'name' => $name, 'process' => $process, 'capacity_per_hour' => $cap, 'rate_per_minute' => $rate,
            ]);
        }

        $machines = [
            ['FP-01', '3-colour flexo printer slotter', 'printing'],
            ['DC-01', 'Flatbed die cutter', 'cutting'],
            ['FG-01', 'Folder gluer', 'gluing'],
            ['GR-01', '8-colour gravure', 'printing'],
            ['LM-01', 'Solventless laminator', 'lamination'],
            ['SR-01', 'Slitter rewinder', 'slitting'],
            ['PM-01', 'Stand-up pouch machine', 'forming'],
            ['LF-01', 'Label press', 'finishing'],
        ];
        foreach ($machines as [$code, $name, $process]) {
            Machine::query()->updateOrCreate(['code' => $code], [
                'work_center_id' => $centers[$process]->id,
                'name' => $name,
                'status' => 'running',
                'installed_on' => '2022-06-01',
                'oee_target' => 78,
            ]);
        }

        $categories = [
            'corrugated' => ProductCategory::query()->updateOrCreate(['slug' => 'corrugated'], ['name' => 'Corrugated boxes', 'name_bn' => 'ঢেউটিন বাক্স', 'type' => 'corrugated', 'summary' => 'RSC shippers, die-cuts, and mailers in B, C, and BC flute.']),
            'flexible' => ProductCategory::query()->updateOrCreate(['slug' => 'flexible'], ['name' => 'Flexible packaging', 'name_bn' => 'ফ্লেক্সিবল প্যাকেজিং', 'type' => 'flexible', 'summary' => 'Pouches, sachets, and lidding films.']),
            'label' => ProductCategory::query()->updateOrCreate(['slug' => 'labels'], ['name' => 'Labels', 'name_bn' => 'লেবেল', 'type' => 'label', 'summary' => 'Self-adhesive labels for food and pharma.']),
            'carton' => ProductCategory::query()->updateOrCreate(['slug' => 'cartons'], ['name' => 'Mono cartons', 'name_bn' => 'মনো কার্টন', 'type' => 'carton', 'summary' => 'Printed folding cartons in duplex and kraft.']),
        ];

        $catalog = [
            ['SKU-RSC-400', 'Export RSC shipper 400×300×200', 'রপ্তানি আরএসসি শিপার', 'corrugated', 400, 300, 200, null, 140, 'C', null, 2, 'Five-ply feel from double-wall liner and C flute. Stapled or glued joint.'],
            ['SKU-MAIL-220', 'Die-cut mailer 220×160×80', 'ডাই-কাট মেইলার', 'corrugated', 220, 160, 80, null, 125, 'E', null, 1, 'E flute mailer for e-commerce, crash-lock base.'],
            ['SKU-COF-200', 'Coffee stand-up pouch 200 g', 'কফি স্ট্যান্ড-আপ পাউচ', 'flexible', 140, 220, null, 40, null, null, '12PET/12MET/80PE', 6, 'High barrier coffee pouch with valve area and zipper.'],
            ['SKU-SAC-15', 'Sauce sachet 15 ml', 'সস স্যাশে', 'flexible', 70, 110, null, 0, null, null, '12PET/80PE', 4, 'Three-side seal sachet for condiments.'],
            ['SKU-LBL-HNY', 'Honey jar label 90×50', 'মধুর লেবেল', 'label', 90, 50, null, null, null, null, null, 4, 'PP self-adhesive label, 4 colour plus varnish.'],
            ['SKU-TEA-250', 'Tea carton 250 g', 'চা কার্টন', 'carton', 90, 70, 180, null, 300, null, null, 5, 'Straight-tuck duplex carton with aqueous coating.'],
        ];

        $products = [];
        foreach ($catalog as [$sku, $name, $bn, $type, $l, $w, $h, $gusset, $gsm, $flute, $film, $colors, $desc]) {
            $product = Product::query()->updateOrCreate(['sku' => $sku], [
                'product_category_id' => $categories[$type]->id,
                'name' => $name,
                'name_bn' => $bn,
                'type' => $type,
                'length_mm' => $l,
                'width_mm' => $w,
                'height_mm' => $h,
                'gusset_mm' => $gusset,
                'board_gsm' => $gsm,
                'flute' => $flute,
                'film_structure' => $film,
                'ink_colors' => $colors,
                'description' => $desc,
                'description_bn' => $bn.' — '.$desc,
                'list_price' => 0,
                'is_public' => true,
                'is_active' => true,
                'barcode' => 'PF'.$sku,
            ]);
            $products[$sku] = $product;
            Specification::query()->updateOrCreate(
                ['product_id' => $product->id, 'key' => 'Joint'],
                ['value' => $type === 'corrugated' ? 'Glue or stitch' : 'Heat seal', 'unit' => null]
            );
        }

        $this->bom($products['SKU-RSC-400'], [
            [$materials['KRAFT-175'], 0.18, 8],
            [$materials['FLUTE-112'], 0.12, 8],
            [$materials['INK-CMYK'], 0.004, 5],
        ], $centers, ['printing', 'cutting', 'gluing', 'packing']);

        $this->bom($products['SKU-COF-200'], [
            [$materials['PET-12'], 0.008, 12],
            [$materials['MET-12'], 0.008, 12],
            [$materials['PE-80'], 0.02, 12],
            [$materials['ADH-SL'], 0.003, 5],
        ], $centers, ['printing', 'lamination', 'slitting', 'forming', 'packing']);

        Tooling::query()->updateOrCreate(['code' => 'DIE-RSC-400'], [
            'product_id' => $products['SKU-RSC-400']->id,
            'name' => 'RSC 400 rotary die',
            'kind' => 'die',
            'status' => 'available',
            'cost' => 45000,
            'location' => 'Die rack 2',
        ]);

        $recipe = Recipe::query()->updateOrCreate(['code' => 'FILM-COFFEE'], ['name' => 'Coffee barrier', 'kind' => 'film', 'notes' => 'PET / METPET / PE']);
        RecipeComponent::query()->updateOrCreate(['recipe_id' => $recipe->id, 'material_id' => $materials['PET-12']->id], ['gsm' => 16.8, 'sequence' => 1]);
        RecipeComponent::query()->updateOrCreate(['recipe_id' => $recipe->id, 'material_id' => $materials['MET-12']->id], ['gsm' => 16.8, 'sequence' => 2]);
        RecipeComponent::query()->updateOrCreate(['recipe_id' => $recipe->id, 'material_id' => $materials['PE-80']->id], ['gsm' => 73.6, 'sequence' => 3]);

        $customers = [];
        foreach ([
            ['CUST-01', 'Meghna Foods Ltd', 'Dhaka', 'Food'],
            ['CUST-02', 'Riverdale Tea', 'Sreemangal', 'Tea'],
            ['CUST-03', 'Shonar Agro', 'Rajshahi', 'Agro'],
            ['CUST-04', 'City Pharma', 'Gazipur', 'Pharma'],
            ['CUST-05', 'Delta Biscuits', 'Narayanganj', 'Snacks'],
            ['CUST-06', 'Lotus Cosmetics', 'Chattogram', 'Personal care'],
        ] as [$code, $name, $city, $segment]) {
            $customer = Customer::query()->updateOrCreate(['code' => $code], [
                'name' => $name, 'city' => $city, 'segment' => $segment, 'email' => strtolower($code).'@buyer.test', 'is_active' => true, 'credit_limit' => 2500000,
            ]);
            Contact::query()->updateOrCreate(['customer_id' => $customer->id, 'name' => $name.' buyer'], ['email' => 'buyer.'.strtolower($code).'@buyer.test', 'is_primary' => true, 'role' => 'Procurement']);
            $customers[$code] = $customer;
        }

        User::query()->updateOrCreate(['email' => 'customer@packflow.test'], [
            'name' => 'Meghna Buyer',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_id' => $customers['CUST-01']->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Lead::query()->updateOrCreate(['email' => 'hello@northstar.test'], [
            'company' => 'Northstar Electronics',
            'contact_name' => 'Rafi',
            'source' => 'website',
            'status' => 'qualified',
            'product_interest' => 'corrugated',
            'owner_id' => $users['sales']->id,
            'notes' => 'Needs 5,000 export shippers a month.',
        ]);

        $supplier = Supplier::query()->updateOrCreate(['code' => 'SUP-PAPER'], [
            'name' => 'Karnafuli Paper Mills', 'city' => 'Chattogram', 'category' => 'paper', 'on_time_percent' => 92, 'quality_score' => 88, 'email' => 'mill@karnafuli.test',
        ]);
        Supplier::query()->updateOrCreate(['code' => 'SUP-FILM'], [
            'name' => 'Bay Film Co.', 'city' => 'Dhaka', 'category' => 'film', 'on_time_percent' => 86, 'quality_score' => 90,
        ]);

        if (PurchaseOrder::query()->doesntExist()) {
            $po = PurchaseOrder::query()->create([
                'number' => DocumentNumber::next('PO'),
                'supplier_id' => $supplier->id,
                'user_id' => $users['warehouse']->id,
                'status' => 'sent',
                'ordered_on' => now()->subDays(4)->toDateString(),
                'expected_on' => now()->addDays(6)->toDateString(),
                'total' => 68000,
                'notes' => 'Cover the stitching wire shortage and a kraft top-up.',
            ]);
            PurchaseOrderLine::query()->create([
                'purchase_order_id' => $po->id,
                'material_id' => $materials['WIRE-ST']->id,
                'quantity' => 100,
                'unit_cost' => 140,
                'line_total' => 14000,
            ]);
            PurchaseOrderLine::query()->create([
                'purchase_order_id' => $po->id,
                'material_id' => $materials['KRAFT-175']->id,
                'quantity' => 800,
                'unit_cost' => 67.5,
                'line_total' => 54000,
            ]);
        }

        if (SalesOrder::query()->doesntExist()) {
            $order = SalesOrder::query()->create([
                'number' => DocumentNumber::next('SO'),
                'customer_id' => $customers['CUST-01']->id,
                'user_id' => $users['sales']->id,
                'status' => 'draft',
                'ordered_on' => now()->toDateString(),
                'due_on' => now()->addDays(10)->toDateString(),
                'tax_percent' => 15,
                'artwork_status' => 'approved',
                'notes' => 'Export marks on two sides. No metal staples.',
            ]);
            SalesOrderLine::query()->create([
                'sales_order_id' => $order->id,
                'product_id' => $products['SKU-RSC-400']->id,
                'description' => $products['SKU-RSC-400']->name,
                'quantity' => 2000,
                'unit_price' => 42.5,
                'line_total' => 85000,
                'spec' => ['product_type' => 'corrugated', 'flute' => 'C', 'ink_colors' => 2],
            ]);
            $order->subtotal = 85000;
            $order->tax_amount = 12750;
            $order->total = 97750;
            $order->save();
            app(SalesOrderService::class)->confirm($order->fresh('lines'));
        }

        $closed = SalesOrder::query()->where('number', 'like', 'SO-%')->first();
        if ($closed && Invoice::query()->doesntExist()) {
            $copy = SalesOrder::query()->create([
                'number' => DocumentNumber::next('SO'),
                'customer_id' => $customers['CUST-02']->id,
                'user_id' => $users['sales']->id,
                'status' => 'confirmed',
                'ordered_on' => now()->subDays(20)->toDateString(),
                'due_on' => now()->subDays(5)->toDateString(),
                'confirmed_at' => now()->subDays(18),
                'subtotal' => 64000,
                'tax_percent' => 15,
                'tax_amount' => 9600,
                'total' => 73600,
            ]);
            SalesOrderLine::query()->create([
                'sales_order_id' => $copy->id,
                'product_id' => $products['SKU-TEA-250']->id,
                'description' => $products['SKU-TEA-250']->name,
                'quantity' => 8000,
                'unit_price' => 8,
                'line_total' => 64000,
            ]);
            app(InvoicingService::class)->fromSalesOrder($copy->fresh('lines'));
        }

        MaintenancePlan::query()->updateOrCreate(['machine_id' => Machine::query()->where('code', 'FP-01')->value('id'), 'name' => 'Anilox and blade check'], [
            'interval_days' => 14,
            'last_done_on' => now()->subDays(20)->toDateString(),
            'next_due_on' => now()->subDay()->toDateString(),
            'tasks' => 'Clean anilox, check doctor blade, grease folder.',
        ]);

        Complaint::query()->updateOrCreate(['number' => 'CC-2026-0001'], [
            'customer_id' => $customers['CUST-05']->id,
            'product_id' => $products['SKU-SAC-15']->id,
            'title' => 'Seal leak on sauce sachets',
            'description' => 'Two cartons from last week showed channel leakers on the fin seal. Hold the lot and check jaw temperature.',
            'status' => 'open',
            'severity' => 'major',
        ]);

        $issue = Issue::query()->updateOrCreate(['title' => 'Flexo deck 2 is walking out of register'], [
            'number' => Issue::query()->where('title', 'Flexo deck 2 is walking out of register')->value('number') ?? DocumentNumber::next('IS'),
            'reporter_id' => $users['operator']->id,
            'assignee_id' => $users['production_supervisor']->id,
            'category' => 'quality_reject',
            'severity' => 'high',
            'status' => 'open',
            'description' => 'Register drifts after 20 minutes on the RSC shipper. Same plate cylinder as yesterday.',
        ]);
        app(IssueDiagnosisService::class)->diagnose($issue);

        foreach ([
            ['flute-crush', 'Flute crush after printing', 'quality', 'Board looks flat after the flexo.', 'Lower impression, check the feed rollers, and confirm the board was not stored on its side in a damp bay.'],
            ['reel-splice', 'Too many splices in a pouch run', 'material_shortage', 'Film reels are short and splices are landing in the pouch repeat.', 'Reject reels under the agreed length at goods receipt. Group the remaining meters onto a short job, not the export order.'],
            ['estimation-blank', 'Quoted board weight does not match the blank', 'estimation_error', 'The customer says the kg per thousand is light.', 'For an RSC, blank length is 2×(L+W)+joint and blank width is H+W. Apply the flute take-up before the waste percent.'],
        ] as [$slug, $title, $category, $problem, $solution]) {
            KnowledgeArticle::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'category' => $category,
                'problem' => $problem,
                'solution' => $solution,
                'solution_bn' => $solution,
                'is_public' => true,
            ]);
        }

        Page::query()->updateOrCreate(['slug' => 'about'], [
            'title' => 'About',
            'title_bn' => 'আমাদের কথা',
            'body' => "Padma Convertors started as a two-colour flexo shed in Tongi and now runs corrugated, flexible, labels, and cartons on one site.\n\nThe office still keeps a sample room. PackFlow is the record that used to live in job-card binders.",
            'body_bn' => 'পদ্মা কনভার্টার্স টঙ্গীতে দুই রঙের ফ্লেক্সো শেড থেকে শুরু। এখন একই সাইটে ঢেউটিন, ফ্লেক্সিবল, লেবেল ও কার্টন চলে।',
            'meta_title' => 'About Padma Convertors',
        ]);
        Page::query()->updateOrCreate(['slug' => 'capabilities'], [
            'title' => 'Capabilities',
            'title_bn' => 'সক্ষমতা',
            'body' => "Printing: 3-colour flexo and 8-colour gravure.\nLamination: solventless.\nConverting: die cut, folder gluer, slitter, pouch machine, label finishing.\nQuality: dimensions, bond, print density, and count on every closed job.",
            'body_bn' => 'প্রিন্ট, ল্যামিনেশন, ডাই-কাট, ফোল্ডার গ্লুয়ার, স্লিটার, পাউচ মেশিন ও লেবেল ফিনিশিং।',
        ]);
        Page::query()->updateOrCreate(['slug' => 'services'], [
            'title' => 'Services',
            'title_bn' => 'সেবা',
            'body' => "We estimate from a sketch, cut a die, print, convert, inspect, and deliver with a bilingual invoice.\n\nBring a dieline or a pack you already sell. The plant will match the structure.",
            'body_bn' => 'স্কেচ থেকে এস্টিমেট, ডাই, প্রিন্ট, কনভার্ট, ইন্সপেকশন ও দ্বিভাষিক চালান।',
        ]);

        Post::query()->updateOrCreate(['slug' => 'why-rsc-blank-weight-surprises-buyers'], [
            'user_id' => $users['sales']->id,
            'title' => 'Why RSC blank weight surprises buyers',
            'title_bn' => 'আরএসসি ব্ল্যাঙ্কের ওজন কেন বেশি লাগে',
            'excerpt' => 'The box you hold is not the sheet the corrugator cuts.',
            'body' => "An RSC blank is two lengths and two widths, plus a joint, and the sheet width is the height plus the width because the flaps are half the width.\n\nFlute take-up then adds the medium. A C flute board is not 140 GSM of paper. It is closer to 140 times 1.46, and waste sits on top of that.",
            'body_bn' => 'আরএসসি ব্ল্যাঙ্ক মানে দুই দৈর্ঘ্য, দুই প্রস্থ, আর জয়েন্ট। ফ্লুটের টেক-আপ তারপর যোগ হয়।',
            'is_published' => true,
            'published_at' => now()->subDays(3),
        ]);

        CaseStudy::query()->updateOrCreate(['slug' => 'meghna-export-shipper'], [
            'title' => 'Export shippers without metal staples',
            'title_bn' => 'স্ট্যাপল ছাড়া রপ্তানি শিপার',
            'client' => 'Meghna Foods',
            'pack_type' => 'Corrugated',
            'summary' => 'A glued RSC that clears a buyer audit and still stacks four high.',
            'body' => 'The buyer banned staples. We moved the joint to glue, kept C flute, and printed export marks in two colours. Waste on the first accepted run was 6.4 percent.',
            'is_public' => true,
        ]);

        Testimonial::query()->updateOrCreate(['author' => 'Procurement, Riverdale Tea'], [
            'company' => 'Riverdale Tea',
            'role' => 'Packaging buyer',
            'quote' => 'The carton colour matches the last shipment. That used to take three emails and a courier sample.',
            'quote_bn' => 'কার্টনের রং গত চালানের সাথে মিলেছে। আগে এতে তিনটি ইমেল আর একটি কুরিয়ার স্যাম্পল লাগত।',
            'is_public' => true,
        ]);

        $device = AttendanceDevice::query()->updateOrCreate(['serial_number' => 'PF-GATE-1'], [
            'name' => 'Gate terminal',
            'provider' => 'virtual',
            'model' => 'demo',
            'status' => 'online',
            'timezone' => 'Asia/Dhaka',
            'last_seen_at' => now(),
        ]);
        $stamp = now()->setTime(8, 5);
        AttendanceLog::query()->updateOrCreate(['punch_hash' => hash('sha256', 'EMP-060'.$stamp->toIso8601String())], [
            'device_id' => $device->id,
            'device_user_id' => 'EMP-060',
            'employee_id' => 'EMP-060',
            'punched_at' => $stamp,
            'verify_mode' => 'face',
            'punch_type' => 'in',
        ]);

        $company->touch();
    }

    /** @param  array<string, WorkCenter>  $centers
     * @param  list<array{0: Material, 1: float, 2: float}>  $items
     * @param  list<string>  $processes
     */
    protected function bom(Product $product, array $items, array $centers, array $processes): void
    {
        $bom = Bom::query()->updateOrCreate(['product_id' => $product->id, 'name' => 'Standard'], ['version' => 1, 'is_active' => true]);
        foreach ($items as [$material, $qty, $scrap]) {
            BomItem::query()->updateOrCreate(['bom_id' => $bom->id, 'material_id' => $material->id], [
                'qty_per' => $qty, 'uom' => $material->uom, 'scrap_percent' => $scrap,
            ]);
        }
        $routing = Routing::query()->updateOrCreate(['product_id' => $product->id, 'name' => 'Standard route'], ['is_active' => true]);
        foreach ($processes as $index => $process) {
            RoutingStep::query()->updateOrCreate(['routing_id' => $routing->id, 'sequence' => $index + 1], [
                'work_center_id' => $centers[$process]->id,
                'name' => ucfirst($process),
                'setup_minutes' => 25,
                'speed_per_minute' => $process === 'printing' ? 90 : 70,
                'crew' => 3,
            ]);
        }
    }
}
