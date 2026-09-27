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

use LCV\Database;

function lcv_config()
{
    return [
        'name' => 'Staff Permission & Support PIN',
        'description' => 'Granular WHMCS staff roles, page, action, field permissions and Support PIN verification.',
        'version' => '0.5.7',
        'author' => 'Resellnom',
        'authorlink' => 'https://resellnom.com',
        'language' => 'english',
        'fields' => [
        ],
    ];
}

function lcv_activate()
{
    try {
        Database::install();
        return [
            'status' => 'success',
            'description' => 'Staff Permission & Support PIN activated and database tables initialized.',
        ];
    } catch (\Throwable $e) {
        return [
            'status' => 'error',
            'description' => 'Installation failed: ' . $e->getMessage(),
        ];
    }
}

function lcv_upgrade($vars)
{
    Database::install();
}

function lcv_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'Staff Permission & Support PIN deactivated. Existing permission data is preserved.',
    ];
}

function lcv_output($vars)
{
    $base = __DIR__;
    $view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';

    if ($view === 'license') {
        if (!\LCV\Access::isSuperAdmin()) {
            http_response_code(403);
            exit('Access denied.');
        }
        require $base . '/pages/license.php';
        return;
    }

    $license = \LCV\License::status();

    if (!empty($license['instal
    }

    if ($view === 'departments') {
        require $base . '/pages/departments.php';
        return;
    }

    if ($view === 'audit') {
        require $base . '/pages/audit.php';
        return;
    }

    if ($view === 'service') {
        require $base . '/pages/service.php';
        return;
    }

    if ($view === 'verify') {
        require $base . '/pages/verify.php';
        return;
    }

    require $base . '/pages/dashboard.php';
}
