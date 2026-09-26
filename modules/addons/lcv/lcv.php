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

use LCV\Database;

function lcv_config()
{
    return [
        'name' => 'Staff Permission & Support PIN',
        'description' => 'Granular WHMCS staff roles, page, action, field permissions and Support PIN verification.',
        'version' => '0.5.5',
        'author' => 'Resellnom',
        'authorlink' => 'https://resellnom.com',
        'language' => 'english',
        'fields' => [
            'license_key' => [
                'FriendlyName' => 'WHMCS License Key',
                'Type' => 'text',
                'Size' => '40',
                'Description' => 'Enter the license key issued for this installation.',
            ],
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

    $license = \LCV\License::status();

    if (!empty($license['installation_mismatch'])) {
        echo '<div class="alert alert-danger">';
        echo '<strong>Are You Cracking Our Software?</strong><br>';
        echo 'Unauthorized installation detected. The licensed domain, IP address, or installation directory does not match this license.';
        echo '<br><br>Please install this software only on the domain assigned to your license or request a license reissue.';
        echo '</div>';
        return;
    }

    if (($license['status'] ?? '') !== \LCV\License::STATUS_ACTIVE) {
        echo '<div class="alert alert-danger"><strong>Staff Permission & Support PIN is locked.</strong><br>';
        echo htmlspecialchars($license['description'] ?? 'A valid WHMCS Software Licensing license is required.', ENT_QUOTES, 'UTF-8');
        echo '<br><br>Configure a valid WHMCS License Key under Addon Modules &gt; Configure.</div>';
        return;
    }

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
