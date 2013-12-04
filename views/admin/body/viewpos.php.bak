<div class="container">

<div>

<div class="dash_bar">
<a href="<?=site_url("admin/purchaseorders")?>"></a>
<span><?=$this->db->query("select count(1) as l from t_po_info")->row()->l?></span> Total POs 
</div>

<div class="dash_bar">
<span><?=$this->db->query("select count(1) as l from t_po_info where po_status=2")->row()->l?></span> Complete POs 
</div>

<div class="dash_bar">
<span><?=$this->db->query("select count(1) as l from t_po_info where po_status=1")->row()->l?></span> Partially received 
</div>

<div class="dash_bar">
<span><?=$this->db->query("select count(1) as l from t_po_info where created_on>?",date("Y-m-d",mktime(0,0,0,date("n"),1)))->row()->l?></span> POs this month 
</div>

<div class="dash_bar">
<span><?=$this->db->query("select count(1) as l from t_po_info where created_on between ? and ?",array(date("Y-m-d",mktime(0,0,0,date("n")-1,1)),date("Y-m-d",mktime(0,0,0,date("n"),date("t")))))->row()->l?></span> POs prev month
</div>

<div class="dash_bar">
Total Value : <span>Rs <?=number_format($this->db->query("select sum(total_value) as l from t_po_info")->row()->l)?></span>
</div>

<div class="dash_bar">
Value this month : <span>Rs <?=number_format($this->db->query("select sum(total_value) as l from t_po_info where created_on>?",date("Y-m-d",mktime(0,0,0,date("n"),1)))->row()->l)?></span>
</div>

<div class="dash_bar">
Value  prev month: <span>Rs <?=number_format($this->db->query("select sum(total_value) as l from t_po_info where created_on between ? and ?",array(date("Y-m-d",mktime(0,0,0,date("n")-1,1)),date("Y-m-d",mktime(0,0,0,date("n"),date("t")))))->row()->l)?></span>
</div>

<div class="dash_bar">
Date range: <input type="texT" size="8" class="inp" id="ds_range" value="<?=$this->uri->segment(3)?>"> to <input size="8" type="text" class="inp"id="de_range" value="<?=$this->uri->segment(4)?>"> <input type="button" value="Show" onclick='showrange()'>
</div>

<div class="clear"></div>

</div>

<h2><?=isset($pagetitle)?"":"Recent 20 "?>Purchase Orders <?=isset($pagetitle)?$pagetitle:""?></h2>


<table class="datagrid" style="margin-top:10px;">
<thead>
<tr>
<th>ID</th>
<th>Created On</th>
<th>Vendor</th>
<th>Value</th>
<th>Purchase Status</th>
<th>Stock Status</th>
<th></th>
<th>Remarks</th>
</tr>
</thead>
<tbody>
<?php foreach($pos as $p){?>
<tr>
<td>PO<?=$p['po_id']?></td>
<td><?=date("g:ia d/m/y",strtotime($p['created_on']))?></td>
<td><a href=""><?=$p['vendor_name']?></a><br><?=$p['city']?></td>
<td>Rs <?=number_format($p['total_value'])?></td>
<td><?php switch($p['po_status']){
	case 1:
	case 0: echo 'Open'; break;
	case 2: echo 'Complete'; break;
	case 3: echo 'Cancelled';
}?></td>
<td>
<?php switch($p['po_status']){
	case 0: echo 'Not received'; break;
	case 1: echo 'Partially received'; break;
	case 2: echo 'Fully received'; break;
	case 3: echo 'NA';
}?>
</td>
<td>
<a class="link" href="<?=site_url("admin/viewpo/{$p['po_id']}")?>">view</a>
<?php if($p['po_status']!=2 && $p['po_status']!=3){?>
&nbsp;&nbsp;&nbsp;<a href="<?=site_url("admin/apply_grn/{$p['po_id']}")?>">Stock Intake</a>
<?php }?>
</td>
<td><?=$p['remarks']?></td>
</tr>
<?php } if(empty($pos)){?><tr><td colspan="100%">no POs to show</td></tr><?php }?>
</tbody>
</table>

</div>
<script>
$(function(){
	$("#ds_range,#de_range").datepicker();
});
function showrange()
{
	if($("#ds_range").val().length==0 ||$("#ds_range").val().length==0)
	{
		alert("Pls enter date range");
		return;
	}
	location='<?=site_url("admin/purchaseorders")?>/'+$("#ds_range").val()+"/"+$("#de_range").val(); 
}
</script>
<?php
