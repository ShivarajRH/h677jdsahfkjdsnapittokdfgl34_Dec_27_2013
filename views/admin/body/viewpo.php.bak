<div class="container">
<h2>Purchase Order : <?=$po['po_id']?></h2>
<table class="datagrid" style="float:left">
<tr><td>Vendor :</td><td><a href="<?=site_url("admin/vendor/{$po['vendor_id']}")?>"><?=$po['vendor_name']?></a></td></tr>
<tr><td>Total Value :</td><td>Rs <b><?=number_format($po['total_value'])?></b></td></tr>
<tr><td>Remarks :</td><td><?=$po['remarks']?></td></tr>

<tr><td>Created on :</td><td><?=date("d/m/Y g:ia",strtotime($po['created_on']))?></td></tr>
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
<td><?php echo format_date($po['date_of_delivery']);?></td>
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
<?php 


function dateDiff($start, $end) {

$start_ts = strtotime($start);

$end_ts = strtotime($end);

$diff = $end_ts - $start_ts;

return round($diff / 86400);

}

?>
<td>Delivery TAT</td>
<!--  <td><?php echo ($po['po_status'] <= 1)?timespan(strtotime($po['date_of_delivery'])):timespan(strtotime($po['date_of_delivery']),strtotime($po['modified_on']))?></td>-->
<td><?php echo $po['date_of_delivery']?dateDiff($po['created_on'],$po['date_of_delivery']).' days':'NA'?></td>
</tr>	

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
<table class="datagrid">
<thead>
<tr>
<th>Sno</th>
<th>Product</th>
<th>Order Qty</th>
<th>Received Qty</th>
<th>MRP</th>
<th>Margin</th>
<th>Scheme Discount</th>
<th>Purchase Price</th>
<th>FOC</th>
<th>Has Offer</th>
<th>Note</th>
</tr>
</thead>
<tbody>
<?php $sno=1; foreach($items as $i){?>
<tr>
<td><?=$sno++?></td>
<td><a href="<?=site_url("admin/product/{$i['product_id']}")?>"><?=$i['product_name']?></a></td>
<td><?=$i['order_qty']?></td>
<td><?=$i['received_qty']?></td>
<td><?=$i['mrp']?></td>
<td><?=$i['margin']?>%</td>
<td><?=$i['scheme_discount_value']?></td>
<td><?=$i['purchase_price']?></td>
<td><?=$i['is_foc']?"YES":"NO"?></td>
<td><?=$i['has_offer']?"YES":"NO"?></td>
<td><?=$i['special_note']?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>

</div>

<script>

$("#po_deliverydate").datepicker({});
function closepo()
{
	if(confirm("Are you sure?"))
		location="<?=site_url("admin/closepo/{$po['po_id']}")?>";
}


</script>

<?php
