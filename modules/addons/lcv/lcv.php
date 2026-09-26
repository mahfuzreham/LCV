<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Permission.php';
require_once __DIR__ . '/lib/Role.php';
require_once __DIR__ . '/lib/Audit.php';

function lcv_config()
{
    return [
        'name' => 'Staff Permission & Support PIN',
        'description' => 'Granular WHMCS staff roles, page, action, field permissions and Support PIN verification.',
        'version' => '0.1.0',
        'author' => 'MD Mahfuz Reham',
        'language' => 'english',
        'fields' => [],
    ];
}

function lcv_activate()
{
    return [
        'status' => 'success',
        'description' => 'Staff Permission & Support PIN foundation activated. Database migrations and enforcement are being implemented.',
    ];
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

    if (isset($_GET['view']) && $_GET['view'] === 'permissions') {
        require $base . '/pages/permissions.php';
        return;
    }

    require $base . '/pages/dashboard.php';
}
