<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Database;

if (!Access::isSuperAdmin()) {
    http_response_code(403);
    exit('Access denied.');
}

$logs = Database::coreTable('tbladmins')
    ->rightJoin('mod_lcv_audit_logs','tbladmins.id','=','mod_lcv_audit_logs.admin_id')
    ->select('mod_lcv_audit_logs.*','tbladmins.username')
    ->orderBy('mod_lcv_audit_logs.id','desc')
    ->limit(200)
    ->get();
?>
<div class="lcv-wrap">
    <div class="lcv-page-head">
        <div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Audit log</h1><p>Recent permission, Support PIN and sensitive staff activity.</p></div>
    </div>
    <div class="lcv-card">
        <div class="lcv-table-wrap">
            <table class="lcv-table">
                <thead><tr><th>Time</th><th>Administrator</th><th>Action</th><th>Resource</th><th>Details</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log->created_at,ENT_QUOTES,'UTF-8') ?></td>
                        <td><?= htmlspecialchars($log->username ?: ('Admin #'.$log->admin_id),ENT_QUOTES,'UTF-8') ?></td>
                        <td><strong><?= htmlspecialchars($log->action,ENT_QUOTES,'UTF-8') ?></strong></td>
                        <td><?= htmlspecialchars(($log->resource ?: '').($log->resource_id ? ' #'.$log->resource_id : ''),ENT_QUOTES,'UTF-8') ?></td>
                        <td><small><?= htmlspecialchars($log->details ?: '',ENT_QUOTES,'UTF-8') ?></small></td>
                        <td><?= htmlspecialchars($log->ip_address ?: '',ENT_QUOTES,'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$logs->count()): ?><tr><td colspan="6">No audit events recorded yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
