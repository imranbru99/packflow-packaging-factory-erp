<?php

return [

    'name' => 'PackFlow',

    /*
    | Each module can be switched off per installation. The database row in
    | module_settings wins after install; these flags are the defaults.
    */
    'modules' => [
        'website' => ['label' => 'Public website', 'enabled' => true],
        'dashboard' => ['label' => 'Dashboard & analytics', 'enabled' => true],
        'crm' => ['label' => 'CRM, sales & estimation', 'enabled' => true],
        'catalog' => ['label' => 'Product, specification & BOM', 'enabled' => true],
        'inventory' => ['label' => 'Inventory & warehouse', 'enabled' => true],
        'procurement' => ['label' => 'Procurement', 'enabled' => true],
        'production' => ['label' => 'Production planning', 'enabled' => true],
        'shopfloor' => ['label' => 'Shop floor execution', 'enabled' => true],
        'quality' => ['label' => 'Quality control', 'enabled' => true],
        'maintenance' => ['label' => 'Maintenance', 'enabled' => true],
        'hr' => ['label' => 'HR & attendance', 'enabled' => true],
        'finance' => ['label' => 'Finance & costing', 'enabled' => true],
        'issues' => ['label' => 'Issues & AI resolution', 'enabled' => true],
        'reports' => ['label' => 'Reports & exports', 'enabled' => true],
        'settings' => ['label' => 'Settings & users', 'enabled' => true],
    ],

    /*
    | Staff roles mirror a converting plant. super_admin and factory_manager
    | can open every enabled module.
    */
    'role_modules' => [
        'production_supervisor' => ['dashboard', 'catalog', 'inventory', 'production', 'shopfloor', 'quality', 'maintenance', 'issues', 'reports', 'hr'],
        'qc_inspector' => ['dashboard', 'quality', 'shopfloor', 'issues', 'reports'],
        'sales' => ['dashboard', 'crm', 'catalog', 'website', 'issues', 'reports'],
        'warehouse' => ['dashboard', 'inventory', 'procurement', 'issues', 'reports'],
        'finance' => ['dashboard', 'finance', 'crm', 'procurement', 'reports'],
        'operator' => ['shopfloor', 'issues', 'hr'],
        'customer' => [],
    ],

    'rates' => [
        'labor_per_minute' => 8,
        'default_machine_per_minute' => 45,
        'board_price_per_kg' => 72,
        'film_price_per_kg' => 210,
        'ink_price_per_kg' => 480,
        'label_stock_per_sqm' => 18,
        'carton_board_per_kg' => 95,
    ],

    'notifications' => [
        'whatsapp' => env('PACKFLOW_WHATSAPP', false),
    ],

];
