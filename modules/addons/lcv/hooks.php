<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Permission.php';
require_once __DIR__ . '/lib/Role.php';
require_once __DIR__ . '/lib/Audit.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Access.php';
require_once __DIR__ . '/lib/SupportPin.php';

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\SupportPin;

add_hook('AdminAreaPage', 1, function ($vars) {
    $filename = isset($vars['filename']) ? strtolower((string)$vars['filename']) : '';

    $rules = [
        'clientssummary.php' => 'clients.summary.view',
        'clients.php' => 'clients.view',
        'clientsservices.php' => 'services.view',
        'supporttickets.php' => 'tickets.view',
        'supportticket.php' => 'tickets.view',
        'invoices.php' => 'billing.invoices.view',
        'transactions.php' => 'billing.transactions.view',
        'configservers.php' => 'servers.view',
    ];


    if ($filename === 'supportticket.php' && $_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message']) || isset($_POST['reply']) || isset($_POST['replymessage'])) && !Access::isSuperAdmin()) {
        if (!Access::can('tickets.reply')) {
            http_response_code(403);
            exit('Staff Permission & Support PIN: Ticket reply permission denied.');
        }
        if (!SupportPin::verified()) {
            http_response_code(403);
            exit('Staff Permission & Support PIN: Verify your Support PIN before replying.');
        }
    }

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


add_hook('AdminAreaViewTicketPage', 1, function ($vars) {
    if (Access::isSuperAdmin()) {
        return '';
    }

    $ticketId = (int)($vars['ticketid'] ?? 0);
    if (!$ticketId || !Access::can('tickets.view')) {
        http_response_code(403);
        exit('Staff Permission & Support PIN: Ticket access denied.');
    }

    $departmentId = Database::coreTable('tbltickets')->where('id', $ticketId)->value('deptid');
    if ($departmentId && !Access::canDepartment($departmentId)) {
        Audit::record('access.denied', 'ticket', $ticketId, ['reason' => 'department']);
        http_response_code(403);
        exit('Staff Permission & Support PIN: Department access denied.');
    }

    return '';
});

add_hook('TicketAdminReply', 10, function ($vars) {
    if (!empty($vars['adminid']) && !Access::isSuperAdmin((int)$vars['adminid'])) {
        Audit::record('ticket.admin_reply', 'ticket', $vars['ticketid'] ?? null, [
            'department' => $vars['deptname'] ?? null,
            'status' => $vars['status'] ?? null,
        ]);
    }
});
