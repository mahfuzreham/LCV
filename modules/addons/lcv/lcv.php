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
        'version' => '0.5.2',
        'author' => 'MD Mahfuz Reham',
        'language' => 'english',
        'fields' => [
            'license_key' => [
                'FriendlyName' => 'WHMCS License Key',
                'Type' => 'text',
                'Size' => '40',
                'Description' => 'Enter the license key issued by your WHMCS Software Licensing product. This value is stored in WHMCS addon configuration and is never included in the public source code.',
            ],
            'licensing_url' => [
                'FriendlyName' => 'WHMCS Licensing URL',
                'Type' => 'text',
                'Size' => '50',
                'Default' => 'https://my.resellnom.com/',
                'Description' => 'Base URL of the WHMCS installation that runs the Software Licensing addon.',
            ],
            'licensing_secret_key' => [
                'FriendlyName' => 'MD5 Hash Verification Secret',
                'Type' => 'password',
                'Size' => '40',
                'Description' => 'Enter the secret configured for the WHMCS Software Licensing product. Never publish this value in GitHub.',
            ],
            'local_key_days' => [
                'FriendlyName' => 'Local Key Days',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '30',
                'Description' => 'Number of days between remote license checks. Default: 30 days.',
            ],
            'allow_check_fail_days' => [
                'FriendlyName' => 'Remote Check Grace Days',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '5',
                'Description' => 'Grace period when the licensing server cannot be reached.',
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
        echo '<br><br>Configure the WHMCS License Key, Licensing URL and MD5 Hash Verification Secret under Addon Modules &gt; Configure.</div>';
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
