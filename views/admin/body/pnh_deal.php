
<div class="container">

<div style="float:right;padding:0px 50px;">
<?php $d=$deal; $stock=$this->erpm->do_stock_check(array($d['id'])); if(empty($stock)){?>
<h4 style="color:red">OUT OF STOCK</h4>
<?php }else{?>
<h4 style="color:green">IN-STOCK</h4>
<?php }?>
</div>

<?=$deal['menu_name']?> &raquo; <a href="<?=site_url("admin/viewcat/{$deal['catid']}")?>"><?=$deal['category']?></a> &raquo; <a href="<?=site_url("admin/viewbrand/{$deal['brandid']}")?>"><?=$deal['brand']?></a>

<h2><?=$deal['name']?> <a style="font-size: 11px;" href="<?=site_url("admin/pnh_editdeal/{$deal['id']}")?>">Edit</a> </h2>
 
<img src="<?=IMAGES_URL?>items/<?=$deal['pic']?>.jpg" style="float:right;margin-right:20px;">
<table cellpadding=3 class="datagrid">
<tr><td>PNH PID :</td><Td><?=$deal['pnh_id']?></Td></tr>
<tr><td>Deal Name :</td><Td style="font-weight:bold;"><?=$deal['name']?></Td></tr>
<tr><td>Tagline :</td><td><?=$deal['tagline']?></td></tr>
<tr><td>MRP :</td><td>Rs <?=$deal['orgprice']?></td></tr>
<tr><td>Offer price/Dealer Price :</td><td>Rs <?=$deal['price']?></td></tr>
<tr><td>Store price :</td><td>Rs <?=$deal['store_price']?></td></tr>
<tr><td>NYP price :</td><td>Rs <?=$deal['nyp_price']?></td></tr>
<tr><Td>Gender Attribute :</Td><td><?=$deal['gender_attr']?></td></tr>
<tr><td>Brand :</td><Td style="font-weight:bold;"><?=$deal['brand']?></Td></tr>
<tr><td>Category :</td><Td style="font-weight:bold;"><?=$deal['category']?></Td></tr>
<tr>
<td>Description :</td>
<Td style="font-weight:bold;">
	<div id="description" >
		<p class="show"><?=$deal['description']?></p>
	</div>
</Td>
</tr>
<tr><td>Created by :</td><Td style="font-weight:bold;"><?=$deal['created_by']?></Td></tr>
<tr><td>Modified by :</td><Td style="font-weight:bold;"><?=$deal['mod_name']?></Td></tr>

<?php $d=$deal;?>
<tr>
<td>Status :</td><td><?=$d['publish']==1?"Enabled":"Disabled"?> 
<a class="danger_link" href="<?=site_url("admin/pnh_pub_deal/{$d['id']}/{$d['publish']}")?>">change</a></td></tr>
<tr>
<?php $sch_status=$this->db->query("select 1 from pnh_superscheme_deals where itemid=? and is_active=0 and ? between valid_from and valid_to order by created_on desc  limit 1 ",array($deal['id'],time()))->row();?>

<td width="30%">Pnh Super Scheme:</td><td><?=$sch_status?"Disabled":"Enabled"?>
</td>
</tr>
</table>

<div>
<table>
<tr>
<td>
<h4 style="margin-bottom:0px;">Make it as Special Margin Deal</h4>
<div style="color:#777;display:inline-block;padding:3px 5px;background:#eee;">(Calculate Margin on <b>Offer Price</b>)</div>
<form action="<?=site_url("admin/pnh_special_margin_deal/{$deal['id']}")?>" method="post">
<table class="datagrid noprint">
<tr><Td>Special Margin : </Td><td><input type="text" name="special_margin" class="inp" size=6> in <label><input type="radio" name="type" value=0 checked="checked">%</label> <label><input type="radio" name="type" value=1>Rs</label></td></tr>
<tr><td>From : </td><td><input type="text" class="inp" name="from" id="sm_from"></td></tr>
<tr><td>To : </td><td><input type="text" class="inp" name="to" id="sm_to"></td></tr>

<tr><td></td><Td><input type="submit" value="Submit"></Td></tr>
</table>
</form>
</td>
<td></td>
<td></td>
<td></td>
<td>
<h4 style="margin-bottom:0px;">Enable/Disable from Super Scheme</h4>
<form action="<?=site_url("admin/pnh_superscheme_deal/{$deal['id']}")?>" method="post">
<table class="datagrid noprint">
<tr><td>Super Scheme Status :</td>
<td><select name="super_schstatus">
<option value="0">Disable</option>
<option value="1">Enable</option>
</select></td></tr>
<tr><td>From : </td><td><input type="text" class="inp" name="from" id="supersch_from"></td></tr>
<tr><td>To : </td><td><input type="text" class="inp" name="to" id="supersch_to"></td></tr>
<tr><td>Reason: </td><td><textarea class="inp" name="reason" style="width: 144px; height: 44px;"></textarea></td></tr>
<tr><td></td><Td><input type="submit" value="Submit"></Td></tr>
</table>
</form>
</td>
<td></td>
<td></td>
<?php if($deal['menuid']==112){?>
<td>
<h4 style="margin-bottom:0px;">Enable/Disable from Member Scheme</h4>
<form action="<?=site_url("admin/pnh_memberscheme_deal/{$deal['id']}")?>" method="post">
<table class="datagrid noprint">
<tr><td>Member Scheme Status :</td>
<td><select name="mbr_schstatus">
<option value="0">Disable</option>
<option value="1">Enable</option>
</select></td></tr>
<tr><td>From : </td><td><input type="text" class="inp" name="from" id="mbrsch_from"></td></tr>
<tr><td>To : </td><td><input type="text" class="inp" name="to" id="mbrsch_to"></td></tr>
<!--  <tr><td>Reason: </td><td><textarea class="inp" name="reason" style="width: 144px; height: 44px;"></textarea></td></tr>-->
<tr><td></td><Td><input type="submit" value="Submit"></Td></tr>
</table>
</form>
</td>
<?php }?>
</tr>
</table>
</div>


	
	<div style="float:left;margin-right:20px;">
	<h4 style="margin-bottom:0px;">Price changelog</h4>
	<table class="datagrid smallheader noprint">
	<thead><tr><th>Sno</th><th>Old MRP</th><th>New MRP</th><th>Old Price</th><th>New Price</th><th>Reference</th><th>Updated By</th><th>Date</th></tr></thead>
	<tbody>
	<?php $i=1; foreach($this->db->query("select a.*,b.username as logged_by from deal_price_changelog a left join king_admin b on a.created_by = b.id where itemid=? order by id desc",$deal['id'])->result_array() as $pc){?>
	<tr>
	<td><?=$i++?></td>
	<td>Rs <?=$pc['old_mrp']?></td>
	<td>Rs <?=$pc['new_mrp']?></td>
	<td>Rs <?=$pc['old_price']?></td>
	<td>Rs <?=$pc['new_price']?></td>
	<td>
	<?php if($pc['reference_grn']==0) echo "MANUAL";else{?>
	<a href="<?=site_url("admin/view_grn/{$pc['reference_grn']}")?>"><?=$pc['reference_grn']?></a>
	<?php }?>
	</td>
	<td><?=$pc['logged_by']?></td>
	<td><?=format_datetime_ts($pc['created_on'])?></td>
	</tr>
	<?php }?>
	</tbody>
	</table>
	</div>

<div style="margin-right:20px;">
<h4 style="margin-bottom:0px;">Special Margin history</h4>
<table class="datagrid smallheader">
<thead><tr><Th style="text-align: center;">Margin %</Th><Th style="text-align: center;">Margin Rs</Th><Th style="text-align: center;">Price</Th><th>From</th><th>To</th><th>Assigned on</th><th>Assigned by</th><th></th></tr></thead>
<tbodY>
<?php foreach($this->db->query("select s.*,a.name as admin from pnh_special_margin_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id'])->result_array() as $s){?>
<tr><td style="color: maroon;font-weight: bold"><?=$s['special_margin']?>%</td><td style="color: maroon;font-weight: bold"><?php echo (($deal['price']*$s['special_margin']/100))?> </td><td style="color: green;font-weight: bold;"><?php echo ($deal['price']-($deal['price']*$s['special_margin']/100))?> </td><td><b><?=date("d/m/y",$s['from'])?></b></td><td><b><?=date("d/m/y",$s['to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>

<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
<?php }?>
</tbodY>
</table>
</div>

<div style="margin-right:20px;">
<h4 style="margin-bottom:0px;">Super Scheme Log</h4>
<table class="datagrid smallheader">
<thead><tr><Th style="text-align: center;">Status</Th><Th style="text-align: center;">Valid from</Th><Th style="text-align: center;">Valid from</Th><th>Assigned on</th><th>Assigned by</th></tr></thead>
<tbodY>
<?php foreach($this->db->query("select s.*,a.name as admin from pnh_superscheme_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id'])->result_array() as $s){?>
<tr><td style="color: maroon;font-weight: bold"><?=$s['is_active']==0?'Disabled':'Enabled'?></td><td><b><?=date("d/m/y",$s['valid_from'])?></b></td><td><b><?=date("d/m/y",$s['valid_to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>

<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
<?php }?>
</tbodY>
</table>
</div>

<div style="margin-right:20px;">
<h4 style="margin-bottom:0px;">Member Scheme Log</h4>
<table class="datagrid smallheader">
<thead><tr><Th style="text-align: center;">Status</Th><Th style="text-align: center;">Valid from</Th><Th style="text-align: center;">Valid from</Th><th>Assigned on</th><th>Assigned by</th></tr></thead>
<tbodY>
<?php foreach($this->db->query("select s.*,a.name as admin from pnh_membersch_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id'])->result_array() as $s){?>
<tr><td style="color: maroon;font-weight: bold"><?=$s['is_active']==0?'Disabled':'Enabled'?></td><td><b><?=date("d/m/y",$s['valid_from'])?></b></td><td><b><?=date("d/m/y",$s['valid_to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>
<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
<?php }?>
</tbodY>
</table>
</div>

<div style="float:left;padding-right:20px;clear:both;">
<h4 style="margin-bottom:0px;">Linked Product Groups</h4>
<table class="datagrid smallheader noprint">
<thead><tr><th>Sno</th><th>ID</th><th>Group Name</th><th>Qty</th></tr></thead>
<tbody>
<?php foreach($this->db->query("select gl.*,g.group_name from m_product_group_deal_link gl join products_group g on g.group_id=gl.group_id where gl.itemid=?",$deal['id'])->result_array() as $i=>$p){?>
<tr><td><?=++$i?></td><td><?=$p['group_id']?></td><td><a href="<?=site_url("admin/product_group/{$p['group_id']}")?>"><?=$p['group_name']?></a></td><td><?=$p['qty']?></td></tr>
<?php }?>
</tbody>
</table>
</div>


<h4 style="margin-bottom:0px;">Linked Products</h4>
<table class="datagrid smallheader noprint">
<thead><tr><th>Sno</th><th>ID</th><th>Product Name</th><th>Qty</th></tr></thead>
<tbody>
<?php foreach($prods as $i=>$p){?>
<tr><td><?=++$i?></td><td><?=$p['product_id']?></td><td><a href="<?=site_url("admin/product/{$p['product_id']}")?>"><?=$p['product_name']?></a></td><td><?=$p['qty']?></td></tr>
<?php }?>
</tbody>
</table>


<div class="clear"></div>

<h4 style="margin-bottom:0px;">Recent Orders</h4>
<div id="recent_orders_list"></div>
</table>

<h4 style="margin-bottom:0px;">Product link update log</h4>
<table class="datagrid smallheader noprint">
	<thead>
		<tr>
			<th>#</th>
			<th>Product Name</th>
			<th>Action</th>
			<th>Updated On</th>
			<th>Updated by</th>
		</tr>
	</thead>
	<tbody>
	<?php if($pnh_Deal_upd_log)
	{
		foreach($pnh_Deal_upd_log as $i=>$log_det)
		{
	?>
		<tr>
			<td><?php echo $i+1;?></td>
			<td><?php echo $log_det['product_name'];?></td>
			<td>
				<?php echo ($log_det['is_updated']==1)?'Removed':'Added';?>
			</td>
			<td><?php echo date('d/m/Y',strtotime($log_det['perform_on']));?></td>
			<td><?php echo $log_det['username'];?></td>
		</tr>
		
	<?php }
	}?>
	</tbody>
</table>


<h3 style="margin-bottom:0px;">Extra Images</h3><a href="<?=site_url("admin/pnh_deal_extra_images/{$deal['id']}")?>">Upload more pics</a>
<div>
<?php foreach($this->db->query("select id from king_resources where itemid=? and type=0",$deal['id'])->result_array() as $img){?>
<img src="<?=IMAGES_URL?>items/small/<?=$img['id']?>.jpg" style="float:left;margin:10px;border:1px solid #aaa;">
<?php }?>
</div>


</div>

<style>
	#description {padding:10px;background: #fcfcfc;max-height: 200px;overflow: hidden}
	#description table{background: #FFF;font-size: 11px;width: 100%;}
	#description table th{background: #f8f8f8;color:#555}
	#description table td{font-weight: normal}
	.pagination a{background: #cdcdcd;color:#555;}
</style>

<script>
var itemid = "<?php echo $this->uri->segment(3);?>";
$(function(){
$("#sm_from,#sm_to").datepicker();

$('#description').prepend("<div style='text-align:right'><a stat='1' href='javascript:void(0)' class='tgl_desc'>More</a></div>");

$('.tgl_desc').click(function(){
	if($(this).attr('stat') == 1)
	{
		$(this).attr('stat',0);
		$('#description').css('max-height','none');
		$(this).text('Less');
	}else
	{
		$(this).attr('stat',1);
		$('#description').css('max-height','200px');
		$(this).text('More');
	}
});

$("#supersch_from,#supersch_to").datepicker({changeMonth: false,minDate:0,}).focus(function(){
	  $(".ui-datepicker-prev, .ui-datepicker-next").remove();
		});
$("#mbrsch_from,#mbrsch_to").datepicker({changeMonth: false,minDate:0,}).focus(function(){
	  $(".ui-datepicker-prev, .ui-datepicker-next").remove();
});

});

function load_recent_orders(link)
{
	$('#recent_orders_list').html("Loading...");
	$.get(link,function(resp){
		$('#recent_orders_list').html(resp);
	});
}

$('#recent_orders_list .pagination a').live('click',function(e){
	e.preventDefault();
	load_recent_orders($(this).attr('href'));
});

load_recent_orders(site_url+'/admin/jx_getordersbydeal/'+itemid);

</script>

<?php
