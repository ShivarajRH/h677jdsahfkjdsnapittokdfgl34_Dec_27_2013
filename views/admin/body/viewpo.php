<?php
	$user = $this->erpm->auth(); 
?>
<div class="container">
<h2>Purchase Order : <?=$po['po_id']?></h2>
<table class="datagrid" style="float:left">
<tr><td>Vendor :</td><td><a href="<?=site_url("admin/vendor/{$po['vendor_id']}")?>"><?=$po['vendor_name']?></a></td></tr>
<tr><td>Total Value :</td><td>Rs <b><?=number_format($po['total_value'])?></b></td></tr>
<tr><td>Remarks :</td><td><?=$po['remarks']?></td></tr>

<tr><td>Created on :</td><td><?=date("d/m/Y g:ia",strtotime($po['created_on']))?></td></tr>
<tr><td>Created By :</td><td><?=$this->db->query("select username from king_admin where id = ? ",$po['created_by'])->row()->username;?></td></tr>
<tr>
<td>Date of Delivery :</td>
<?php if(!$po['date_of_delivery']!=null){?>
<form method="post" action="<?php echo site_url("admin/updatedeliverydate/{$po['po_id']}")?>" >
<td>
<input type="text" name="po_deliverydate" id="po_deliverydate" value="" >
<input type="submit" value="save" >
</td>
</form>
<?php }else{?>
<td><?php echo format_datetime($po['date_of_delivery']);?></td>
<?php }?>
</tr>
<!--  <td style="font-weight:bold;<?php echo (strtotime($po['date_of_delivery']) < time())?'color:#cd0000;':'' ?> "><?=date("d/m/Y g:ia",strtotime($po['date_of_delivery']))?></td>-->

<tr><td>Status :</td>
<td>

<?php 
switch($po['po_status']){
	case 0: echo 'Open'; break;
	case 1: echo 'Partially Received'; break;
	case 2: echo 'Complete'; break;
	case 3: echo 'Cancelled';
}

?></td></tr>

<tr>
<td colspan=2 align="right">
<?php if($po['po_status']!="2" && $po['po_status']!="3"){?>
<input onclick='closepo()' type="button" value="Close PO">
<?php } ?>
</td>
</tr>
</table>

<div style="float:left;margin-left:20px;">
<h4 style="margin:0px;">Stock Intakes</h4>
<table class="datagrid">
<thead>
<tr><th>Stock Intake No</th><th>Status</th><th>Total Invoice Value</th></tr>
</thead>
<tbody>
<?php foreach($grns as $grn) {?>
<tr>
<td><a href="<?=site_url("admin/viewgrn/{$grn['grn_id']}")?>" class="link">GRN<?=$grn['grn_id']?></a></td>
<td><?php switch($grn['payment_status']){
	case 0: echo "Unaccounted";?><br><a href="<?=site_url("admin/account_grn/{$grn['grn_id']}")?>">account</a> <?php break;
	case 1: echo "Accounted, ready for payment";?><br><a href="<?=site_url("admin/create_voucher")?>">make payment</a> <?php break;
	case 2: echo "Payment made";break;
}?></td>
<td><?=$this->db->query("select sum(purchase_inv_value) as v from `t_grn_invoice_link` where grn_id=?",$grn['grn_id'])->row()->v?></td>
</tr>
<?php } if(empty($grns)){?><tr>
<td colspan="100%">No Stock Intakes made</td>
</tr>
<?php }?>
</tbody>
</table>
</div>

<div style="float:left;margin-left:20px;">
<div>
<h4 style="margin:0px;">Vouchers</h4>
<table class="datagrid">
<thead><tr><th>Voucher ID</th><th>Voucher Value</th><th>Amount paid for this PO</th><th>Created On</th></tr></thead>
<tbody>
<?php foreach($vouchers as $v){?>
<tr>
<td><a class="link" href="<?=site_url("admin/voucher/{$v['voucher_id']}")?>"><?=$v['voucher_id']?></a></td>
<td><?=$v['voucher_value']?></td>
<td><?=$v['adjusted_amount']?></td>
<td><?=$v['created_on']?></td>
</tr>
<?php }if(empty($vouchers)){?><tr><td colspan="100%">No Payments made</td></tr><?php }?>
</tbody>
</table>
</div>
</div>

<div class="clear"></div>


<div style="padding:20px 0px;">
<h4>Products in PO</h4>

<table id="po_prod_list" class="datagrid nofooter">
<thead>
<tr>
<th>Sno</th>
<th>Product</th>
<th style="text-align: left" width="50">Last <br> 30 Days Sales</th>
<th style="text-align: left" width="50">Required <br> Qty</th>
<th>PO <br> Order <br> Qty</th>
<th>Received <br> Qty</th>
<th class="hideinprint">MRP</th>
<th class="hideinprint">DP Price</th>
<th class="hideinprint">Margin</th>
<th class="hideinprint">Scheme Discount</th>
<th class="hideinprint">Purchase Price</th>
<th class="hideinprint">FOC</th>
<th class="hideinprint">Has Offer</th>
<th>Note</th>
</tr>
</thead>
<tbody>
<?php $sno=1; foreach($items as $i){
	
	$i['sales_30days']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.time>".(time()-(24*60*60*30)).' and o.time < ?  ',array($i['product_id'],strtotime($po['created_on'])))->row()->s;
	$i['sales_30days'] += $this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_group_deal_link l join king_orders o on o.itemid=l.itemid join products_group_orders pgo on pgo.order_id = o.id where pgo.product_id=? and o.time>".(time()-(24*60*60*30)).' and o.time < ?  ',array($i['product_id'],strtotime($po['created_on'])))->row()->s;
	
	$i['pen_ord_qty']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.status = 0 and o.time < ? ",array($i['product_id'],strtotime($po['created_on'])))->row()->s;
	
?>
<tr>
<td><?=$sno++?></td>
<td><a href="<?=site_url("admin/product/{$i['product_id']}")?>"><?=$i['product_name']?></a></td>
<td><?=$i['sales_30days']?></td>
<td><?=$i['pen_ord_qty']?></td>
<td><?=$i['order_qty']?></td>
<td><?=$i['received_qty']?></td>
<td class="hideinprint"><?=$i['mrp']?></td>
<td class="hideinprint"><?=$i['dp_price']?></td>
<td class="hideinprint"><?=$i['margin']?>%</td>
<td class="hideinprint"><?=$i['scheme_discount_value']?></td>
<td class="hideinprint"><?=$i['purchase_price']?></td>
<td class="hideinprint"><?=$i['is_foc']?"YES":"NO"?></td>
<td class="hideinprint"><?=$i['has_offer']?"YES":"NO"?></td>
<td><?=$i['special_note']?></td>
</tr>
<?php }?>

	<tr class="hideinprint">
		<td colspan="14" style="text-align: right">
			<a href="javascript:void(0)" onclick="print_podoc()">Print Document</a>
		</td>
	</tr>

</tbody>
</table>
</div>

</div>

<script>
/*var button = $('<input type="button" value="save" id="po_deliverydate_button">');
$('#po_deliverydate_button').after(button);
if("#po_deliverydate_button").click(function(){
	var value = $(this).prev().val();
	 save(value);
});*/



function print_podoc()
{
	var html = '<div><style> body{font-size:12px;font-family:arial;} .hideinprint{display:none}</style> <h2 align="center">PO Product List</h2> <div> <b style="float:right">Printed By : <?php echo $user['username'];?> <br> Printed On : <?php echo format_datetime_ts(time());?>  </b> <b style="font-size:14px;">PO: #<?=$po['po_id']?></b> </div><table cellpadding=5 cellspacing=0 border=1 width="100%" style="font-size:12px;font-family:arial;">'+$('#po_prod_list').html()+'</table></div>';
		prw=window.open("",'');
		prw.document.write(html);
		prw.focus();
		prw.print();
}


$("#po_deliverydate").datetimepicker({
	timeFormat: "hh:mm tt",
	dateFormat: "D MM d, yy"
});
function closepo()
{
	if(confirm("Are you sure?"))
		location="<?=site_url("admin/closepo/{$po['po_id']}")?>";
}

function updateexpected_podeliverydate()
{
	if(confirm("Are you sure?"))
		location="<?=site_url("admin/updatedeliverydate/{$po['po_id']}")?>";
}
</script>

<?php
