<?php

defined('WHMCS') or die('This file cannot be accessed directly');

$roles = \LCV\Role::defaults();
\LCV\Access::requirePermission('clients.view');

$nav = [
    'dashboard' => 'Overview',
    'permissions' => 'Permissions',
    'staff' => 'Staff',
    'security' => 'Support PIN',
    'departments' => 'Departments',
    'audit' => 'Audit Log',
    'license' => 'License',
];
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <div class="lcv-muted" style="font-size:12px">WHMCS Administration</div>
            <h1>Staff Permission &amp; Support PIN</h1>
            <p>Control staff access, protected service fields and sensitive actions from one place.</p>
        </div>
        <div class="lcv-actions">
            <?php if (\LCV\Access::isSuperAdmin()): ?>
                <a class="lcv-btn lcv-btn-primary" href="addonmodules.php?module=lcv&view=permissions">Manage permissions</a>
            <?php else: ?>
                <a class="lcv-btn lcv-btn-primary" href="addonmodules.php?module=lcv&view=verify">Verify Support PIN</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="lcv-nav" aria-label="Staff Permission navigation">
        <?php foreach ($nav as $key => $label): ?>
            <?php if ($key === 'dashboard' || \LCV\Access::isSuperAdmin()): ?>
                <a class="<?= $key === 'dashboard' ? 'active' : '' ?>" href="addonmodules.php?module=lcv<?= $key === 'dashboard' ? '' : '&view='.urlencode($key) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="lcv-stats">
        <div class="lcv-stat"><div class="lcv-stat-label">Standard roles</div><div class="lcv-stat-value"><?= count($roles) ?></div><div class="lcv-stat-note">Ready-to-use access models</div></div>
        <div class="lcv-stat"><div class="lcv-stat-label">Page protection</div><div class="lcv-stat-value">On</div><div class="lcv-stat-note">Server-side authorization</div></div>
        <div class="lcv-stat"><div class="lcv-stat-label">Support PIN</div><div class="lcv-stat-value">On</div><div class="lcv-stat-note">Protected actions can require verification</div></div>
        <div class="lcv-stat"><div class="lcv-stat-label">Audit logging</div><div class="lcv-stat-value">On</div><div class="lcv-stat-note">Sensitive activity is recorded</div></div>
    </div>

    <div class="lcv-card">
        <div class="lcv-card-head">
            <div><h2>Roles</h2><span><?= count($roles) ?> standard access profiles</span></div>
            <?php if (\LCV\Access::isSuperAdmin()): ?><a class="lcv-link" href="addonmodules.php?module=lcv&view=permissions">Manage all permissions →</a><?php endif; ?>
        </div>
        <div class="lcv-table-wrap">
            <table class="lcv-table">
                <thead><tr><th>Role</th><th>Access model</th><th class="lcv-right">Action</th></tr></thead>
                <tbody>
                <?php foreach ($roles as $key => $name): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><?= $key === \LCV\Role::SUPER_ADMIN ? '<span class="lcv-status lcv-status-success">Full access</span>' : '<span class="lcv-status">Granular permissions</span>' ?></td>
                        <td class="lcv-right"><?php if (\LCV\Access::isSuperAdmin()): ?><a class="lcv-link" href="addonmodules.php?module=lcv&view=permissions&role=<?= urlencode($key) ?>">Edit access</a><?php else: ?><span class="lcv-muted">—</span><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="lcv-card">
        <div class="lcv-card-head"><div><h2>Security model</h2><span>Access controls are enforced on the server, not only hidden in the interface.</span></div></div>
        <div class="lcv-permission-list">
            <div class="lcv-permission-row"><span><strong>Page access</strong><small>Direct URL access is checked server-side.</small></span><span class="lcv-status lcv-status-success">Enabled</span></div>
            <div class="lcv-permission-row"><span><strong>Field permissions</strong><small>Service fields have independent View and Edit controls.</small></span><span class="lcv-status lcv-status-success">Enabled</span></div>
            <div class="lcv-permission-row"><span><strong>Support PIN</strong><small>Protected actions can require a hashed staff PIN.</small></span><span class="lcv-status lcv-status-success">Enabled</span></div>
            <div class="lcv-permission-row"><span><strong>Audit log</strong><small>Denied access and sensitive actions can be recorded.</small></span><span class="lcv-status lcv-status-success">Enabled</span></div>
        </div>
    </div>
</div>
