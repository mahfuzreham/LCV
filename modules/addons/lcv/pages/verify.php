<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\SupportPin;

if (!Access::can('tickets.view') && !Access::can('services.view') && !Access::can('billing.invoices.view')) {
    http_response_code(403);
    exit('Access denied.');
}

if (empty($_SESSION['lcv_csrf'])) {
    $_SESSION['lcv_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['lcv_csrf'];
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $error = 'Invalid security token.';
    } elseif (SupportPin::verify(Access::adminId(), (string)($_POST['pin'] ?? ''))) {
        Audit::record('support_pin.verified','admin',Access::adminId());
        $message = 'Support PIN verified. Protected actions are unlocked for 15 minutes.';
    } else {
        Audit::record('support_pin.failed','admin',Access::adminId());
        $error = 'Invalid or locked Support PIN.';
    }
}
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Verify Support PIN</h1><p>Verify your staff PIN before performing a protected action.</p></div>
    </div>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <div class="lcv-card">
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf,ENT_QUOTES,'UTF-8') ?>">
            <div class="form-group">
                <label>Support PIN</label>
                <input class="form-control" type="password" name="pin" inputmode="numeric" pattern="[0-9]{4,8}" minlength="4" maxlength="8" autocomplete="off" required>
            </div>
            <button class="lcv-btn lcv-btn-primary" type="submit">Verify PIN</button>
        </form>
    </div>
</div>
