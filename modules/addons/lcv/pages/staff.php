<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\Role;

if (!Access::isSuperAdmin()) { http_response_code(403); exit('Access denied.'); }
if (empty($_SESSION['lcv_csrf'])) $_SESSION['lcv_csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['lcv_csrf']; $message = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) $error = 'Invalid security token.';
    else {
        try {
            $adminId=(int)($_POST['admin_id']??0); $roleKey=(string)($_POST['role_key']??'');
            $role=Database::table('roles')->where('role_key',$roleKey)->first();
            if(!$adminId||!$role) throw new RuntimeException('Invalid staff or role.');
            Database::table('admin_roles')->updateOrInsert(['admin_id'=>$adminId],['role_id'=>$role->id,'updated_at'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s')]);
            Audit::record('staff.role.updated','admin',$adminId,['role'=>$roleKey]); $message='Staff role updated.';
        } catch(Throwable $e){$error=$e->getMessage();}
    }
}
$admins=Database::coreTable('tbladmins')->leftJoin('mod_lcv_admin_roles','tbladmins.id','=','mod_lcv_admin_roles.admin_id')->leftJoin('mod_lcv_roles','mod_lcv_admin_roles.role_id','=','mod_lcv_roles.id')->select('tbladmins.id','tbladmins.username','tbladmins.firstname','tbladmins.lastname','tbladmins.disabled','mod_lcv_roles.role_key','mod_lcv_roles.name as role_name')->orderBy('tbladmins.username')->get();
$roles=Role::defaults();
?>
<div class="lcv-wrap">
 <div class="lcv-page-head"><div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Staff assignments</h1><p>Assign an access role to each WHMCS administrator.</p></div></div>
 <nav class="lcv-nav"><a href="addonmodules.php?module=lcv">Overview</a><a href="addonmodules.php?module=lcv&view=permissions">Permissions</a><a class="active" href="addonmodules.php?module=lcv&view=staff">Staff</a><a href="addonmodules.php?module=lcv&view=security">Support PIN</a><a href="addonmodules.php?module=lcv&view=departments">Departments</a><a href="addonmodules.php?module=lcv&view=audit">Audit Log</a></nav>
 <?php if($message):?><div class="lcv-alert lcv-alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
 <?php if($error):?><div class="lcv-alert lcv-alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
 <div class="lcv-card">
  <div class="lcv-card-head"><div><h2>Administrators</h2><span><?=count($admins)?> staff accounts</span></div><input class="lcv-input" data-lcv-filter type="search" placeholder="Search staff..." aria-label="Search staff"></div>
  <div class="lcv-table-wrap"><table class="lcv-table"><thead><tr><th>Administrator</th><th>Status</th><th>Current role</th><th>Assign role</th></tr></thead><tbody>
  <?php foreach($admins as $admin):?><tr data-lcv-filter-row>
   <td><strong><?=htmlspecialchars(trim($admin->firstname.' '.$admin->lastname)?:$admin->username,ENT_QUOTES,'UTF-8')?></strong><small><?=htmlspecialchars($admin->username,ENT_QUOTES,'UTF-8')?></small></td>
   <td><span class="lcv-status <?=$admin->disabled?'lcv-status-danger':'lcv-status-success'?>"><?=$admin->disabled?'Disabled':'Active'?></span></td>
   <td><?=htmlspecialchars($admin->role_name?:'Super Admin (default)',ENT_QUOTES,'UTF-8')?></td>
   <td><form method="post" class="lcv-inline-form"><input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf,ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="save_staff" value="1"><input type="hidden" name="admin_id" value="<?=(int)$admin->id?>">
    <select class="lcv-select" name="role_key"><?php foreach($roles as $key=>$name):?><option value="<?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?>" <?=($admin->role_key?:Role::SUPER_ADMIN)===$key?'selected':''?>><?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select><button class="lcv-btn lcv-btn-primary" type="submit">Save</button>
   </form></td>
  </tr><?php endforeach;?>
  <?php if(!$admins->count()):?><tr><td colspan="4" class="lcv-empty">No administrator accounts found.</td></tr><?php endif;?>
  </tbody></table></div>
 </div>
</div>