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

use LCV\\Database;

function lcv_config()
{
    return [
        'name' => 'Staff Permission & Support PIN',
        'description' => 'Granular WHMCS staff roles, page, action, field permissions and Support PIN verification.',
        'version' => '0.2.0',
        'author' => 'MD Mahfuz Reham',
        'language' => 'english',
        'fields' => [],
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
    } catch (\\Throwable $e) {
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

    if ($view === 'permissions') {
        require $base . '/pages/permissions.php';
        return;
    }

    if ($view === 'staff') {
        require $base . '/pages/staff.php';
        return;
    }

    if ($view === 'security') {
        require $base . '/pages/security.php';
        return;
    }

    if ($view === 'verify') {
        require $base . '/pages/verify.php';
        return;
    }

    require $base . '/pages/dashboard.php';
}
