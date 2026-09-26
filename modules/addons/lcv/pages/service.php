<?php

defined('WHMCS') or die('This file cannot be accessed directly');

use LCV\Access;
use LCV\Audit;
use LCV\Database;
use LCV\SupportPin;

if(!Access::can('services.view')){http_response_code(403);exit('Access denied.');}
$serviceId=(int)($_GET['id']??0);if(!$serviceId){http_response_code(400);exit('Invalid service ID.');}
$service=Database::coreTable('tblhosting')->leftJoin('tblclients','tblhosting.userid','=','tblclients.id')->leftJoin('tblproducts','tblhosting.packageid','=','tblproducts.id')->leftJoin('tblservers','tblhosting.server','=','tblservers.id')->where('tblhosting.id',$serviceId)->select('tblhosting.*','tblclients.firstname','tblclients.lastname','tblclients.companyname','tblproducts.name as product_name','tblservers.name as server_name','tblservers.ipaddress as server_ip')->first();
if(!$service){http_response_code(404);exit('Service not found.');}
$fields=[
'service_id'=>['Service ID',$service->id],'client_name'=>['Client name',trim(($service->firstname??'').' '.($service->lastname??''))?:($service->companyname??'')],'product'=>['Product / service',$service->product_name],
'domain'=>['Domain',$service->domain],'username'=>['Username',$service->username],'server'=>['Server',$service->server_name],'ip'=>['IP address',$service->server_ip],
'registration_date'=>['Registration date',$service->regdate],'next_due_date'=>['Next due date',$service->nextduedate],'billing_cycle'=>['Billing cycle',$service->billingcycle],
'first_payment_amount'=>['First payment amount',$service->firstpaymentamount],'recurring_amount'=>['Recurring amount',$service->amount],'payment_method'=>['Payment method',$service->paymentmethod],
'status'=>['Status',$service->domainstatus],'assigned_product'=>['Product ID',$service->packageid],
'custom_fields'=>['Custom fields',$custom?'Available':'None'],
];
if(Access::field('services','password','view')){if(SupportPin::verified()){$fields['password']=['Password','[Protected WHMCS credential — access verified]'];Audit::record('sensitive.view','service',$serviceId,['field'=>'password']);}else{$fields['password']=['Password','Support PIN verification required'];}}else{$fields['password']=['Password','Restricted'];}
$custom=[];
if(Access::field('services','custom_fields','view')){try{$custom=Database::coreTable('tblcustomfieldsvalues')->join('tblcustomfields','tblcustomfields.id','=','tblcustomfieldsvalues.fieldid')->where('tblcustomfieldsvalues.relid',$service->id)->where('tblcustomfields.type','product')->select('tblcustomfields.fieldname','tblcustomfieldsvalues.value')->get();}catch(Throwable $e){$custom=[];}}
?>
<div class="lcv-wrap">
 <div class="lcv-page-head"><div><a class="lcv-back" href="addonmodules.php?module=lcv">‹ Staff Access</a><h1>Service #<?=(int)$serviceId?></h1><p>Only fields permitted for the current staff role are displayed.</p></div><div class="lcv-actions"><a class="lcv-btn" href="addonmodules.php?module=lcv">Back to overview</a></div></div>
 <div class="lcv-card"><div class="lcv-card-head"><div><h2>Service details</h2><span>Server-side filtered output</span></div><span class="lcv-status lcv-status-success">Access checked</span></div>
  <div class="lcv-table-wrap"><table class="lcv-table"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>
  <?php foreach($fields as $key=>$field):if(!Access::field('services',$key,'view'))continue;?><tr><td><strong><?=htmlspecialchars($field[0],ENT_QUOTES,'UTF-8')?></strong><small>services.field.<?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?></small></td><td><?=htmlspecialchars((string)($field[1]??''),ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?>
  </tbody></table></div>
 </div>
 <?php if($custom):?><div class="lcv-card"><div class="lcv-card-head"><div><h2>Custom fields</h2><span>Product custom fields allowed by this role</span></div></div><div class="lcv-table-wrap"><table class="lcv-table"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody><?php foreach($custom as $item):?><tr><td><strong><?=htmlspecialchars($item->fieldname,ENT_QUOTES,'UTF-8')?></strong></td><td><?=htmlspecialchars($item->value,ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div></div><?php endif;?>
</div>