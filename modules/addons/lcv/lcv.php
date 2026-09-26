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
        'name' => 'LCV Staff Access Control',
        'description' => 'Granular WHMCS staff roles, page, action and field permissions.',
        'version' => '0.1.0',
        'author' => 'LCV',
        'language' => 'english',
        'fields' => [],
    ];
}

function lcv_activate()
{
    return [
        'status' => 'success',
        'description' => 'LCV foundation activated. Database migrations will be added in the next implementation stage.',
    ];
}

function lcv_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'LCV deactivated. Existing permission data is preserved.',
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
