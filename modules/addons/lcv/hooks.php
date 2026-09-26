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
require_once __DIR__ . '/lib/License.php';

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\SupportPin;
use LCV\License;


function lcv_license_active()
{
    static $active = null;
    if ($active === null) {
        $active = License::valid();
    }
    return $active;
}

function lcv_guard_service_edit($vars)
{
    if (!lcv_license_active()) {
        return;
    }

    if (!Access::adminId() || Access::isSuperAdmin()) {
        return;
    }

    $serviceId = (int)($vars['serviceid'] ?? 0);
    if (!$serviceId) {
        return;
    }

    $map = [
        'domain' => 'domain',
        'username' => 'username',
        'password' => 'password',
        'server' => 'server',
        'dedicatedip' => 'dedicated_ip',
        'regdate' => 'registration_date',
        'nextduedate' => 'next_due_date',
        'billingcycle' => 'billing_cycle',
        'firstpaymentamount' => 'first_payment_amount',
        'amount' => 'recurring_amount',
        'paymentmethod' => 'payment_method',
        'domainstatus' => 'status',
        'packageid' => 'assigned_product',
        'notes' => 'service_notes',
    ];

    $submitted = [];
    foreach ($map as $requestKey => $fieldKey) {
        if (array_key_exists($requestKey, $_REQUEST)) {
            $submitted[$fieldKey] = $_REQUEST[$requestKey];
        }
    }

    if (!$submitted) {
        return;
    }

    if (!Access::can('services.modify')) {
        Audit::record('access.denied', 'service', $serviceId, ['reason' => 'services.modify']);
        http_response_code(403);
        exit('Staff Permission & Support PIN: Service modification denied.');
    }

    foreach ($submitted as $fieldKey => $value) {
        if (!Access::field('services', $fieldKey, 'edit')) {
            Audit::record('access.denied', 'service', $serviceId, ['reason' => 'field.edit', 'field' => $fieldKey]);
            http_response_code(403);
            exit('Staff Permission & Support PIN: Edit permission denied for ' . htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8') . '.');
        }
    }

    if (!SupportPin::verified()) {
        Audit::record('support_pin.required', 'service', $serviceId, ['reason' => 'service.edit']);
        http_response_code(403);
        exit('Staff Permission & Support PIN: Verify your Support PIN before editing a service.');
    }
}

add_hook('PreAdminServiceEdit', 1, 'lcv_guard_service_edit');
add_hook('PreServiceEdit', 1, 'lcv_guard_service_edit');

function lcv_guard_module_action($permission)
{
    if (!lcv_license_active()) {
        return [];
    }

    if (!Access::adminId() || Access::isSuperAdmin()) {
        return [];
    }

    if (!Access::can($permission)) {
        Audit::record('access.denied', 'module_action', null, ['permission' => $permission]);
        return ['abortcmd' => true];
    }

    if (!SupportPin::verified()) {
        Audit::record('support_pin.required', 'module_action', null, ['permission' => $permission]);
        return ['abortcmd' => true];
    }

    return [];
}

add_hook('PreModuleCreate', 1, function () {
    return lcv_guard_module_action('services.create');
});

add_hook('PreModuleSuspend', 1, function () {
    return lcv_guard_module_action('services.suspend');
});

add_hook('PreModuleUnsuspend', 1, function () {
    return lcv_guard_module_action('services.unsuspend');
});

add_hook('PreModuleTerminate', 1, function () {
    return lcv_guard_module_action('services.terminate');
});

add_hook('PreModuleCustom', 1, function () {
    return lcv_guard_module_action('services.execute');
});

add_hook('AdminAreaPage', 1, function ($vars) {
    if (!lcv_license_active()) {
        return [];
    }

    $filename = isset($vars['filename']) ? strtolower((string)$vars['filename']) : '';

    $rules = [
        'clientssummary.php' => 'clients.summary.view',
        'clients.php' => 'clients.view',
        'clientsservices.php' => 'services.native.view',
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
        exit('Staff Permission & Support PIN: Access denied. Use the Staff Access filtered service viewer when native service access is not permitted.');
    }

    return [];
});

add_hook('AdminAreaHeaderOutput', 1, function () {
    if (!lcv_license_active()) {
        return '';
    }

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
    if (!lcv_license_active()) {
        return '';
    }

    $css = '';
    $path = __DIR__ . '/assets/css/lcv.css';
    if (is_readable($path)) {
        $css = '<link rel="stylesheet" href="../modules/addons/lcv/assets/css/lcv.css">';
    }

    $js = '<script src="../modules/addons/lcv/assets/js/lcv.js"></script>';
    return $css . $js;
});


add_hook('AdminAreaViewTicketPage', 1, function ($vars) {
    if (!lcv_license_active()) {
        return '';
    }

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
    if (!lcv_license_active()) {
        return;
    }

    if (!empty($vars['adminid']) && !Access::isSuperAdmin((int)$vars['adminid'])) {
        Audit::record('ticket.admin_reply', 'ticket', $vars['ticketid'] ?? null, [
            'department' => $vars['deptname'] ?? null,
            'status' => $vars['status'] ?? null,
        ]);
    }
});
