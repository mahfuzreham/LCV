<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\SupportPin;

if (!Access::isSuperAdmin()) {
    http_response_code(403);
    exit('Access denied.');
}

if (empty($_SESSION['lcv_csrf'])) {
    $_SESSION['lcv_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['lcv_csrf'];
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pin'])) {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $error = 'Invalid security token.';
    } else {
        try {
            $adminId = (int)($_POST['admin_id'] ?? 0);
            SupportPin::setPin($adminId, (string)($_POST['pin'] ?? ''));
            Audit::record('support_pin.updated','admin',$adminId);
            $message = 'Support PIN saved. The PIN is stored as a secure password hash.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$admins = \LCV\Database::table('admins')->where('disabled',0)->orderBy('username')->get();
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Support PIN</h1><p>Configure a separate verification PIN for protected staff actions.</p></div>
    </div>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <div class="lcv-card">
        <div class="lcv-card-head"><div><h2>Administrator PINs</h2><span>4-8 digits, hashed at rest, five failed attempts trigger a temporary lock.</span></div></div>
        <div class="lcv-table-wrap">
            <table class="lcv-table">
                <thead><tr><th>Administrator</th><th>New Support PIN</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars(trim($admin->firstname.' '.$admin->lastname) ?: $admin->username,ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars($admin->username,ENT_QUOTES,'UTF-8') ?></small></td>
                        <td><input form="pin-<?= (int)$admin->id ?>" type="password" name="pin" inputmode="numeric" pattern="[0-9]{4,8}" minlength="4" maxlength="8" required></td>
                        <td>
                            <form id="pin-<?= (int)$admin->id ?>" method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES,'UTF-8') ?>">
                                <input type="hidden" name="save_pin" value="1">
                                <input type="hidden" name="admin_id" value="<?= (int)$admin->id ?>">
                                <button class="lcv-btn lcv-btn-primary" type="submit">Save PIN</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
