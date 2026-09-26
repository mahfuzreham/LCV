<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Database;

if(!Access::isSuperAdmin()){http_response_code(403);exit('Access denied.');}
$logs=Database::coreTable('tbladmins')->rightJoin('mod_lcv_audit_logs','tbladmins.id','=','mod_lcv_audit_logs.admin_id')->select('mod_lcv_audit_logs.*','tbladmins.username')->orderBy('mod_lcv_audit_logs.id','desc')->limit(200)->get();
?>
<div class="lcv-wrap">
 <div class="lcv-page-head"><div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Audit log</h1><p>Review recent permission, Support PIN and sensitive staff activity.</p></div></div>
 <nav class="lcv-nav"><a href="addonmodules.php?module=lcv">Overview</a><a href="addonmodules.php?module=lcv&view=permissions">Permissions</a><a href="addonmodules.php?module=lcv&view=staff">Staff</a><a href="addonmodules.php?module=lcv&view=security">Support PIN</a><a href="addonmodules.php?module=lcv&view=departments">Departments</a><a class="active" href="addonmodules.php?module=lcv&view=audit">Audit Log</a></nav>
 <div class="lcv-card"><div class="lcv-card-head"><div><h2>Recent events</h2><span>Latest 200 recorded events</span></div><input class="lcv-input" data-lcv-filter type="search" placeholder="Filter events..." aria-label="Filter audit events"></div>
  <div class="lcv-table-wrap"><table class="lcv-table"><thead><tr><th>Time</th><th>Administrator</th><th>Action</th><th>Resource</th><th>Details</th><th>IP</th></tr></thead><tbody>
  <?php foreach($logs as $log):?><tr data-lcv-filter-row><td><?=htmlspecialchars($log->created_at,ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($log->username?:('Admin #'.$log->admin_id),ENT_QUOTES,'UTF-8')?></td><td><strong><?=htmlspecialchars($log->action,ENT_QUOTES,'UTF-8')?></strong></td><td><?=htmlspecialchars(($log->resource?:'').($log->resource_id?' #'.$log->resource_id:''),ENT_QUOTES,'UTF-8')?></td><td><small><?=htmlspecialchars($log->details?:'',ENT_QUOTES,'UTF-8')?></small></td><td><?=htmlspecialchars($log->ip_address?:'',ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?>
  <?php if(!$logs->count()):?><tr><td colspan="6" class="lcv-empty">No audit events recorded yet.</td></tr><?php endif;?>
  </tbody></table></div>
 </div>
</div>