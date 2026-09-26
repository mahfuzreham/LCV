<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\Role;

if (!Access::isSuperAdmin()) {
    http_response_code(403);
    exit('Access denied.');
}

$roles = Role::defaults();
$selectedRole = isset($_GET['role']) ? (string)$_GET['role'] : Role::SUPER_ADMIN;
if (!isset($roles[$selectedRole])) {
    $selectedRole = Role::SUPER_ADMIN;
}

if (empty($_SESSION['lcv_csrf'])) {
    $_SESSION['lcv_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['lcv_csrf'];

$groups = [
    'Clients' => [
        'clients.view' => 'View clients',
        'clients.summary.view' => 'Client summary',
        'clients.profile.view' => 'Client profile',
        'clients.contacts.view' => 'Contacts',
    ],
    'Products & Services' => [
        'services.view' => 'View services',
        'services.native.view' => 'Open native WHMCS service page',
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
    'dedicated_ip' => 'Dedicated IP',
    'registration_date' => 'Registration date',
    'next_due_date' => 'Next due date',
    'billing_cycle' => 'Billing cycle',
    'first_payment_amount' => 'First payment amount',
    'recurring_amount' => 'Recurring amount',
    'payment_method' => 'Payment method',
    'status' => 'Status',
    'assigned_product' => 'Assigned product',
    'custom_fields' => 'Custom fields',
    'service_notes' => 'Service notes',
    'credentials' => 'Credentials',
];

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $error = 'Invalid security token. Please reload the page.';
    } else {
        try {
            $role = Database::table('roles')->where('role_key', $selectedRole)->first();
            if (!$role) {
                throw new RuntimeException('Role not found.');
            }

            Database::table('role_permissions')->where('role_id', $role->id)->delete();
            $selected = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [];
            $validKeys = [];
            foreach ($groups as $permissions) {
                $validKeys = array_merge($validKeys, array_keys($permissions));
            }

            foreach (array_unique($selected) as $key) {
                if (!in_array($key, $validKeys, true)) continue;
                $permissionId = Database::table('permissions')->where('permission_key', $key)->value('id');
                if ($permissionId) {
                    Database::table('role_permissions')->insert(['role_id'=>$role->id,'permission_id'=>$permissionId]);
                }
            }

            Database::table('field_permissions')->where('role_id', $role->id)->delete();
            foreach ($serviceFields as $key => $label) {
                Database::table('field_permissions')->insert([
                    'role_id'=>$role->id,
                    'resource'=>'services',
                    'field_key'=>$key,
                    'can_view'=>isset($_POST['field_view'][$key]) ? 1 : 0,
                    'can_edit'=>isset($_POST['field_edit'][$key]) ? 1 : 0,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }

            Audit::record('permissions.updated', 'role', $role->id, ['role_key'=>$selectedRole]);
            $message = 'Permissions saved successfully.';
        } catch (Throwable $e) {
            $error = 'Unable to save permissions: ' . $e->getMessage();
        }
    }
}

$roleRow = Database::table('roles')->where('role_key', $selectedRole)->first();
$permissionKeys = [];
$fieldRows = [];
if ($roleRow) {
    $permissionIds = Database::table('role_permissions')->where('role_id', $roleRow->id)->pluck('permission_id')->toArray();
    if ($permissionIds) {
        $permissionKeys = Database::table('permissions')->whereIn('id', $permissionIds)->pluck('permission_key')->toArray();
    }
    $fieldRows = Database::table('field_permissions')->where('role_id', $roleRow->id)->get()->keyBy('field_key');
}
$fullAccess = $selectedRole === Role::SUPER_ADMIN;
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div>
            <a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a>
            <h1><?= htmlspecialchars($roles[$selectedRole], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Choose exactly what this role can see and do.</p>
        </div>
        <div class="lcv-actions">
            <button type="button" class="lcv-btn" data-lcv-clear>Clear all</button>
            <button type="button" class="lcv-btn lcv-btn-primary" data-lcv-select>Allow all</button>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="save_permissions" value="1">

        <div class="lcv-role-switch">
            <label>Role</label>
            <select onchange="window.location='addonmodules.php?module=lcv&view=permissions&role='+encodeURIComponent(this.value)">
                <?php foreach ($roles as $key => $name): ?>
                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $key ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php foreach ($groups as $group => $permissions): ?>
            <section class="lcv-card lcv-permission-group">
                <div class="lcv-card-head"><div><h2><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></h2><span><?= count($permissions) ?> permissions</span></div></div>
                <div class="lcv-permission-list">
                    <?php foreach ($permissions as $key => $label): ?>
                        <label class="lcv-permission-row">
                            <span><strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small></span>
                            <input type="checkbox" class="lcv-check" name="permissions[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $fullAccess || in_array($key, $permissionKeys, true) ? 'checked' : '' ?>>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="lcv-card">
            <div class="lcv-card-head"><div><h2>Service fields</h2><span>View and Edit are independent controls.</span></div></div>
            <div class="lcv-table-wrap">
                <table class="lcv-table lcv-field-table">
                    <thead><tr><th>Field</th><th>View</th><th>Edit</th></tr></thead>
                    <tbody>
                    <?php foreach ($serviceFields as $key => $label):
                        $row = isset($fieldRows[$key]) ? $fieldRows[$key] : null;
                        $view = $fullAccess ? $key !== 'password' : ($row ? (bool)$row->can_view : false);
                        $edit = $fullAccess ? !in_array($key, ['password','service_id','client_name','credentials'], true) : ($row ? (bool)$row->can_edit : false);
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong><small>services.field.<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td><input type="checkbox" class="lcv-check" name="field_view[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" <?= $view ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" class="lcv-check" name="field_edit[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" <?= $edit ? 'checked' : '' ?>></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="lcv-savebar"><button class="lcv-btn lcv-btn-primary" type="submit">Save permissions</button></div>
    </form>
</div>
