<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Permission.php';
require_once __DIR__ . '/lib/Role.php';
require_once __DIR__ . '/lib/Audit.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Access.php';

use LCV\\Access;
use LCV\\Audit;
use LCV\\Database;

add_hook('AdminAreaPage', 1, function ($vars) {
    $filename = isset($vars['filename']) ? strtolower((string)$vars['filename']) : '';

    $rules = [
        'clientssummary.php' => 'clients.summary.view',
        'clients.php' => 'clients.view',
        'supporttickets.php' => 'tickets.view',
        'supportticket.php' => 'tickets.view',
        'invoices.php' => 'billing.invoices.view',
        'transactions.php' => 'billing.transactions.view',
        'configservers.php' => 'servers.view',
    ];

    if (isset($rules[$filename]) && !Access::can($rules[$filename])) {
        Audit::record('access.denied', $filename, null, ['permission' => $rules[$filename]]);
        http_response_code(403);
        exit('Staff Permission & Support PIN: Access denied.');
    }

    return [];
});

add_hook('AdminAreaHeaderOutput', 1, function () {
    if (Access::isSuperAdmin()) {
        return '';
    }

    $items = [
        'clients.php' => 'clients.view',
        'clientssummary.php' => 'clients.summary.view',
        'supporttickets.php' => 'tickets.view',
        'invoices.php' => 'billing.invoices.view',
        'transactions.php' => 'billing.transactions.view',
        'configservers.php' => 'servers.view',
    ];

    $filename = strtolower(basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)));
    if (isset($items[$filename]) && !Access::can($items[$filename])) {
        return '<style>#sidebar,#contentarea{display:none!important}</style>';
    }

    return '';
});

add_hook('AdminAreaFooterOutput', 1, function () {
    $css = '';
    $path = __DIR__ . '/assets/css/lcv.css';
    if (is_readable($path)) {
        $css = '<link rel="stylesheet" href="../modules/addons/lcv/assets/css/lcv.css">';
    }

    $js = '<script src="../modules/addons/lcv/assets/js/lcv.js"></script>';
    return $css . $js;
});
