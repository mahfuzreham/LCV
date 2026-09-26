<?php

defined('WHMCS') or die('This file cannot be accessed directly');

$roles = \LCV\Role::defaults();
\LCV\Access::requirePermission('clients.view');
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <h1>Staff Permission &amp; Support PIN</h1>
            <p>Manage administrator roles, granular access, protected fields and Support PIN security.</p>
        </div>
        <div class="lcv-actions">
            <a class="lcv-btn lcv-btn-primary" href="addonmodules.php?module=lcv&view=permissions">Manage permissions</a>
            <?php if (!\LCV\Access::isSuperAdmin()): ?><a class="lcv-btn" href="addonmodules.php?module=lcv&view=verify">Verify Support PIN</a><?php endif; ?>
            <?php if (\LCV\Access::isSuperAdmin()): ?>
                <a class="lcv-btn" href="addonmodules.php?module=lcv&view=staff">Staff assignments</a>
                <a class="lcv-btn" href="addonmodules.php?module=lcv&view=security">Support PIN</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="lcv-card">
        <div class="lcv-card-head">
            <div>
                <h2>Roles</h2>
                <span><?= count($roles) ?> standard roles</span>
            </div>
        </div>
        <div class="lcv-table-wrap">
            <table class="lcv-table">
                <thead><tr><th>Role</th><th>Access model</th><th class="lcv-right">Action</th></tr></thead>
                <tbody>
                <?php foreach ($roles as $key => $name): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><?= $key === \LCV\Role::SUPER_ADMIN ? 'Full access' : 'Granular permissions' ?></td>
                        <td class="lcv-right"><a class="lcv-link" href="addonmodules.php?module=lcv&view=permissions&role=<?= urlencode($key) ?>">Edit access</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="lcv-card">
        <div class="lcv-card-head"><div><h2>Security model</h2><span>Backend authorization is enforced independently of hidden menu links.</span></div></div>
        <div class="lcv-permission-list">
            <div class="lcv-permission-row"><span><strong>Page access</strong><small>Direct URL access is checked server-side.</small></span><span>Enabled</span></div>
            <div class="lcv-permission-row"><span><strong>Support PIN</strong><small>Protected actions can require a hashed staff PIN.</small></span><span>Enabled</span></div>
            <div class="lcv-permission-row"><span><strong>Audit log</strong><small>Denied access and sensitive actions can be recorded.</small></span><span>Enabled</span></div>
        </div>
    </div>
</div>
