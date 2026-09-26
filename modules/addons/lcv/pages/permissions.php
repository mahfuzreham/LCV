<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Permission;
use LCV\Role;

$selectedRole = isset($_GET['role']) ? (string) $_GET['role'] : Role::SUPER_ADMIN;
$roleName = Role::defaults()[$selectedRole] ?? 'Custom Role';

$groups = [
    'Clients' => [
        'clients.view' => 'View clients',
        'clients.summary.view' => 'Client summary',
        'clients.profile.view' => 'Client profile',
        'clients.contacts.view' => 'Contacts',
    ],
    'Products & Services' => [
        'services.view' => 'View services',
        'services.create' => 'Create services',
        'services.modify' => 'Modify services',
        'services.suspend' => 'Suspend services',
        'services.unsuspend' => 'Unsuspend services',
        'services.terminate' => 'Terminate services',
        'services.execute' => 'Module commands',
    ],
    'Support' => [
        'tickets.view' => 'View tickets',
        'tickets.reply' => 'Reply to tickets',
        'tickets.manage' => 'Manage tickets',
    ],
    'Billing' => [
        'billing.invoices.view' => 'View invoices',
        'billing.transactions.view' => 'View transactions',
        'billing.refund' => 'Refunds',
    ],
    'Servers' => [
        'servers.view' => 'View servers',
        'servers.manage' => 'Manage servers',
        'servers.execute' => 'Module commands',
    ],
];

$serviceFields = [
    'service_id' => 'Service ID',
    'client_name' => 'Client name',
    'product' => 'Product / service',
    'domain' => 'Domain',
    'username' => 'Username',
    'password' => 'Password',
    'server' => 'Server',
    'ip' => 'IP address',
    'next_due_date' => 'Next due date',
    'recurring_amount' => 'Recurring amount',
    'payment_method' => 'Payment method',
    'status' => 'Status',
    'custom_fields' => 'Custom fields',
];

$fullAccess = $selectedRole === Role::SUPER_ADMIN;
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a>
            <h1><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Choose exactly what this role can see and do.</p>
        </div>
        <div class="lcv-actions">
            <button type="button" class="lcv-btn" data-lcv-clear>Clear all</button>
            <button type="button" class="lcv-btn lcv-btn-primary" data-lcv-select>Allow all</button>
        </div>
    </div>

    <?php foreach ($groups as $group => $permissions): ?>
        <section class="lcv-card lcv-permission-group">
            <div class="lcv-card-head">
                <div>
                    <h2><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></h2>
                    <span><?= count($permissions) ?> permissions</span>
                </div>
            </div>
            <div class="lcv-permission-list">
                <?php foreach ($permissions as $key => $label): ?>
                    <label class="lcv-permission-row">
                        <span>
                            <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                        <input type="checkbox" class="lcv-check" <?= $fullAccess ? 'checked' : '' ?> name="permissions[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="lcv-card">
        <div class="lcv-card-head">
            <div>
                <h2>Service fields</h2>
                <span>Control visibility and editing separately.</span>
            </div>
        </div>
        <div class="lcv-table-wrap">
            <table class="lcv-table lcv-field-table">
                <thead>
                    <tr><th>Field</th><th>View</th><th>Edit</th></tr>
                </thead>
                <tbody>
                <?php foreach ($serviceFields as $key => $label): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>services.field.<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td><input type="checkbox" class="lcv-check" <?= $fullAccess && $key !== 'password' ? 'checked' : '' ?>></td>
                        <td><input type="checkbox" class="lcv-check" <?= $fullAccess && !in_array($key, ['password','service_id','client_name'], true) ? 'checked' : '' ?>></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
