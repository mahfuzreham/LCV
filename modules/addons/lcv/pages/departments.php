<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\Role;

if(!Access::isSuperAdmin()){http_response_code(403);exit('Access denied.');}
if(empty($_SESSION['lcv_csrf']))$_SESSION['lcv_csrf']=bin2hex(random_bytes(32));
$csrf=$_SESSION['lcv_csrf'];$roles=Role::defaults();$selectedRole=isset($_GET['role'])?(string)$_GET['role']:Role::SUPPORT_STAFF;if(!isset($roles[$selectedRole]))$selectedRole=Role::SUPPORT_STAFF;$message=null;$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save_departments'])){
 if(!hash_equals($csrf,(string)($_POST['csrf']??'')))$error='Invalid security token.';
 else{try{$role=Database::table('roles')->where('role_key',$selectedRole)->first();if(!$role)throw new RuntimeException('Role not found.');Database::table('role_departments')->where('role_id',$role->id)->delete();$selected=isset($_POST['departments'])&&is_array($_POST['departments'])?$_POST['departments']:[];foreach(array_unique($selected) as $departmentId){$departmentId=(int)$departmentId;if($departmentId>0&&Database::coreTable('tblticketdepartments')->where('id',$departmentId)->exists())Database::table('role_departments')->insert(['role_id'=>$role->id,'department_id'=>$departmentId]);}Audit::record('departments.updated','role',$role->id,['role_key'=>$selectedRole]);$message='Department restrictions saved.';}catch(Throwable $e){$error=$e->getMessage();}}
}
$departments=Database::coreTable('tblticketdepartments')->orderBy('name')->get();$roleRow=Database::table('roles')->where('role_key',$selectedRole)->first();$selectedDepartments=$roleRow?Database::table('role_departments')->where('role_id',$roleRow->id)->pluck('department_id')->toArray():[];$hasRestrictions=count($selectedDepartments)>0;
?>
<div class="lcv-wrap">
 <div class="lcv-page-head"><div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Support departments</h1><p>Limit a role to selected WHMCS support departments.</p></div></div>
 <nav class="lcv-nav"><a href="addonmodules.php?module=lcv">Overview</a><a href="addonmodules.php?module=lcv&view=permissions">Permissions</a><a href="addonmodules.php?module=lcv&view=staff">Staff</a><a href="addonmodules.php?module=lcv&view=security">Support PIN</a><a class="active" href="addonmodules.php?module=lcv&view=departments">Departments</a><a href="addonmodules.php?module=lcv&view=audit">Audit Log</a></nav>
 <?php if($message):?><div class="lcv-alert lcv-alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
 <?php if($error):?><div class="lcv-alert lcv-alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
 <form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf,ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="save_departments" value="1">
  <div class="lcv-card"><div class="lcv-card-head"><div><h2>Role</h2><span>Choose which role this department policy applies to.</span></div></div><div class="lcv-card-body"><select class="lcv-select" onchange="window.location='addonmodules.php?module=lcv&view=departments&role='+encodeURIComponent(this.value)"><?php foreach($roles as $key=>$name):?><option value="<?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?>" <?=$selectedRole===$key?'selected':''?>><?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div></div>
  <div class="lcv-card"><div class="lcv-card-head"><div><h2>Allowed departments</h2><span><?=$hasRestrictions?'Only selected departments are allowed.':'No restriction: all departments are allowed.'?></span></div><span class="lcv-status <?=$hasRestrictions?'lcv-status-warning':'lcv-status-success'?>"><?=$hasRestrictions?'Restricted':'All departments'?></span></div>
   <div class="lcv-permission-list"><?php foreach($departments as $department):?><label class="lcv-permission-row" data-lcv-filter-row><span><strong><?=htmlspecialchars($department->name,ENT_QUOTES,'UTF-8')?></strong><small>Department ID <?=(int)$department->id?></small></span><input class="lcv-check" type="checkbox" name="departments[]" value="<?=(int)$department->id?>" <?=in_array((int)$department->id,$selectedDepartments,true)?'checked':''?>></label><?php endforeach;?></div>
   <?php if(!$departments->count()):?><div class="lcv-empty">No support departments found.</div><?php endif;?>
   <div class="lcv-savebar"><button class="lcv-btn lcv-btn-primary" type="submit">Save departments</button></div>
  </div>
 </form>
</div>