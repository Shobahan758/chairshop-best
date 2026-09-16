<?php

return [
    'dashboard' => ['label' => 'Dashboard reports', 'routes' => []],
    'catalog' => ['label' => 'Categories, subcategories & brands', 'routes' => ['admin.categories.*', 'admin.subcategories.*', 'admin.brands.*']],
    'products' => ['label' => 'Products', 'routes' => ['admin.products.*']],
    'orders' => ['label' => 'Orders', 'routes' => ['admin.orders.*', 'admin.order']],
    'incomplete_orders' => ['label' => 'Incomplete Orders', 'routes' => ['admin.incomplete-orders.*']],
    'fake_orders' => ['label' => 'Fake Orders', 'routes' => ['admin.fake-orders.*']],
    'messages' => ['label' => 'Messages & support', 'routes' => ['admin.support.*']],
    'tracking' => ['label' => 'Tracking & pixels', 'routes' => ['admin.tracking*']],
    'site_settings' => ['label' => 'Site Settings', 'routes' => ['admin.settings.site*']],
    'general_settings' => ['label' => 'General Settings', 'routes' => ['admin.settings.general*']],
];
