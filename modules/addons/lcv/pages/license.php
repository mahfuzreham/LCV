<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCVAccess;
use LCVAudit;
use LCVLicense;

if (!Access::isSuperAdmin()) {
    http_response_code(403);
    exit('Access denied.');
}

if (empty($_SESSION['lcv_license_csrf'])) {
    $_SESSION['lcv_license_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['lcv_license_csrf'];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_license'])) {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $result = ['status' => 'Invalid', 'description' => 'Invalid security token.'];
    } else {
        $result = License::status();
        Audit::record('license.check', 'license', null, [
            'status' => $result['status'] ?? 'Unknown',
            'installation_mismatch' => !empty($result['installation_mismatch']),
        ]);
    }
}

$current = $result ?: License::status();
$status = (string)($current['status'] ?? 'Unknown');
$active = $status === License::STATUS_ACTIVE;
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a>
            <h1>License</h1>
            <p>Verify the current WHMCS Software Licensing status without exposing private licensing credentials.</p>
        </div>
        <div class="lcv-actions">
            <form method="post" class="lcv-inline-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <button class="lcv-btn lcv-btn-primary" type="submit" name="check_license" value="1">Check License Now</button>
            </form>
        </div>
    </div>

    <nav class="lcv-nav">
        <a href="addonmodules.php?module=lcv">Overview</a>
        <a href="addonmodules.php?module=lcv&view=permissions">Permissions</a>
        <a href="addonmodules.php?module=lcv&view=staff">Staff</a>
        <a href="addonmodules.php?module=lcv&view=security">Support PIN</a>
        <a href="addonmodules.php?module=lcv&view=departments">Departments</a>
        <a href="addonmodules.php?module=lcv&view=audit">Audit Log</a>
        <a class="active" href="addonmodules.php?module=lcv&view=license">License</a>
    </nav>

    <?php if ($result): ?>
        <div class="lcv-alert <?= $active ? 'lcv-alert-success' : 'lcv-alert-danger' ?>">
            <?= htmlspecialchars((string)($current['description'] ?? 'License check completed.'), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="lcv-card">
        <div class="lcv-card-head">
            <div><h2>License status</h2><span>Private server URL and verification secret are never displayed here.</span></div>
            <span class="lcv-status <?= $active ? 'lcv-status-success' : 'lcv-status-danger' ?>">
                <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div class="lcv-card-body">
            <div class="lcv-permission-list">
                <div class="lcv-permission-row"><span><strong>License Key</strong><small>Configured in Addon Modules → Configure.</small></span><span class="lcv-muted">Hidden</span></div>
                <div class="lcv-permission-row"><span><strong>Installation Binding</strong><small>Domain, IP and installation directory are checked by the licensing service.</small></span><span class="lcv-muted"><?= !empty($current['installation_mismatch']) ? 'Mismatch' : 'Checked' ?></span></div>
                <div class="lcv-permission-row"><span><strong>Remote Check</strong><small>Current licensing response from the configured WHMCS licensing server.</small></span><span class="lcv-muted"><?= isset($current['remotecheck']) && $current['remotecheck'] === false ? 'Grace' : 'Completed' ?></span></div>
            </div>
        </div>
    </div>
</div>
