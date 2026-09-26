<?php

defined('WHMCS') or die('This file cannot be accessed directly');

$roles = \LCV\Role::defaults();
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <h1>Staff Access</h1>
            <p>Manage administrator roles and granular access to WHMCS.</p>
        </div>
        <div class="lcv-actions">
            <a class="lcv-btn lcv-btn-primary" href="addonmodules.php?module=lcv&view=permissions">
                Manage permissions
            </a>
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
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Access model</th>
                        <th class="lcv-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($roles as $key => $name): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td><?= $key === \LCV\Role::SUPER_ADMIN ? 'Full access' : 'Granular permissions' ?></td>
                        <td class="lcv-right">
                            <a class="lcv-link" href="addonmodules.php?module=lcv&view=permissions&role=<?= urlencode($key) ?>">Edit access</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
