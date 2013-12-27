
<?php $po_status_arr=array();
$po_status_arr[0]="Open";
$po_status_arr[1]="Partially Received";
$po_status_arr[2]="Complete";
$po_status_arr[3]="Cancelled";?>
<div class="container">
<h2>Purchase Order : <?=$po['po_id']?></h2>
<a class="btn fl_right" onclick="print_po(<?=$po['po_id']?>)" style="float:right;margin-top: -31px;">Print po</a>
<?php if($po['po_status']!="2" && $po['po_status']!="3"){?>
<input onclick='closepo( ) ' type="button" value="Close PO" style="float:right;margin-top:-27px;margin-right:115px;">
<?php } ?>
<fieldset style=" width: 50%;">
<legend><b>PO Info</b></legend>
<table>
<tr>
<td width="45%">
<div width="50%">
<table cellspacing="5" width="100%">
<tbody>
<tr><td><b>Supplier</b></td><td>|</td><td><a target="_blank" href="<?php echo site_url("/admin/vendor/{$po['vendor_id']}")?>"><?=$po['vendor_name'] ?></a></td></tr>
<tr><td><b>Purchase Order</b></td><td>|</td><td><?=$po['po_id'] ?></td></tr>
<tr><td><b>Created Date</b></td><td>|</td><td><?=format_date($po['created_on'] )?></td></tr>
<?php if($po['date_of_delivery'] && $po['remarks']){?>
<tr><td><b>Scheduled Date</b></td><td>|</td><td><?=format_date($po['date_of_delivery'] )?></td></tr>
<tr><td><b>Remarks</b></td><td>|</td><td><?=$po['remarks'] ?></td></tr>
<?php }?>
<tr><td><b>Created By</b></td><td>|</td><td><?=$po['created_byname'] ?></td></tr>
<?php if(!$po['date_of_delivery'] || !$po['remarks']){?>
<tr><td><input onclick='update_po_det(<?php echo $po['po_id'] ?>)' class="update_link" value="Update Remarks"></td></tr>
<?php }?>
</div>
</tbody>
</table>
</div></td>
<td width="45%">
<div width="50%" style="float:right;margin-left:117px;">
<table cellspacing="5" width="100%">
<tbody>
<tr><td><b>Po value</b></td><td>|</td><td>Rs <?=format_price($ttl_po_val)?></td></tr>
<tr><td><b>Status</b></td><td>|</td>
<td>
<?if($po['po_status']==0){?>
<span style="color: orange"><b><?php echo $po_status_arr[$po['po_status']]?></b></span>
<?php }if($po['po_status']==3){?>
<span style="color: red"><b><?php echo $po_status_arr[$po['po_status']] ?></b></span>
<?php }?>
</td></tr>
<?php if($po['modified_on']!=null){?>
<tr><td><b>Updated on</b></td><td>|</td><td><?=format_date($po['modified_on']) ?></td></tr>
<tr><td><b>Status Remarks</b></td><td>|</td><td><?=$po['status_remarks']?></td></tr>
<tr><td><b>Updated by</b></td><td>|</td><td><?=$po['modified_byname'] ?></td></tr>
<?php }?>
</tbody>
</table>
</div>
</td>
</tr>
</tbody>
</table>
</div>
</fieldset>

<div class="tab_view">
<ul>
<li><a href="#po_list"><b>Product List</b></a></li>

</ul>
<div id="po_list">
<table class="datagrid" width="100%">
<thead>
<th>Slno</th>
<th>Product Name</th>
<th>Order Qty</th>
<th>Received Qty</th>
<th>MRP</th>
<th>DP Price</th>
<th>Margin</th>
<th>Scheme Discount</th>
<th style="text-align:right;">Unit Price</th>
<th style="text-align:right;">Sub Total</th>
<th style="text-align:right;">Action</th>
</thead>
<tbody>
<?php $sno=1; foreach($items as $i){?>
<tr>
<td><?=$sno++?></td>
<td><a href="<?=site_url("admin/product/{$i['product_id']}")?>"><?=$i['product_name']?></a></td>
<td><?=$i['order_qty']?></td>
<td><?=$i['received_qty']?></td>
<td><?=$i['mrp']?></td>
<td><?=$i['dp_price']?></td>
<td><?=$i['margin']?>%</td>
<td><?=$i['scheme_discount_value']?$i['scheme_discount_value']:0?>%</td>
<td style="text-align:right;"><?=format_price($i['purchase_price'])?></td>
<td style="text-align:right;"><?=format_price($i['purchase_price']*$i['order_qty'])?></td>
<td style="text-align:right;"><a href="javascript:void(0)" onclick="remove_prod_frmpo(<?php echo $i['product_id']?>)" prodid=<?php echo $i['product_id'];?> ><img  src="<?php echo base_url().'images/icon_delete13.gif'?>"></a></td>
</tr>
<?php }?>
</tbody>
</table>
<br>
<div style="float:right;margin-right: 65px;margin-top:-11px;">
<b>Total Purchase Value:Rs&nbsp;&nbsp;<?=format_price($ttl_po_val)?></b>
</div>
</div>

</div>

<div id="status_rmrks_div" title="Remarks For status Update">
<form action="<?php echo site_url("admin/closepo/{$po['po_id']}")?>" method="post" data-validate="parsley" id="remrks_update_frm">
<table>
<tr valign="top"><td><b>Remarks</td><td>:</b></td><td><textarea name="status_remarks" data-required="true"></textarea></td></tr>
</table>
</form>
</div>

<div id="update_po_delivery_det" title="update Expected delivery details" style="display:none;">
<form method="post" action="<?php echo site_url('admin/updatedeliverydate/'.$po['po_id'])?>" id="delivery_det_frm"  data-validate="parsley" >
<table>
<tr><td>Expected Delivery Date</td><td><input type="text" name="po_deliverydate" id="po_deliverydate" value="" data-required="true"></td></tr>
<tr><td>Remarks</td><td><textarea name="po_remarks" value="" data-required="true"></textarea></td></tr>
</table>
</form>
</div>

<script>
$('.leftcont').hide();
$('.tab_view').tabs();
function update_po_det(po_id)
{
	$('#update_po_delivery_det').data('po_id',po_id).dialog('open');
}
$('#update_po_delivery_det').dialog({
modal:true,
autoOpen:false,
autoResize:true,
width:'400',
height:'auto',
open:function(){
	
},
buttons:{
	'Cancel' :function(){
	 $(this).dialog('close');
	},
	'Submit':function(){
		var dlg= $(this);
		var frm_podetails = $("#delivery_det_frm",this);
			 if(frm_podetails.parsley('validate')){
					frm_podetails.submit();
				 $("#delivery_det_frm").dialog('close');
			}
            else
            {
            	alert('All Fields are required!!!');
            }
	},
}
	
});
$("#po_deliverydate").datepicker({ minDate: 0 });
	
function closepo()
{
	/*if(confirm("Are you sure?"))
		location="<?//=site_url("admin/closepo/{$po['po_id']}")?>";*/
	$("#status_rmrks_div").dialog('open');
}
$("#status_rmrks_div").dialog({
modal:true,
autoOpen:false,
open:function(){
},
buttons:{
	'Cancel':function(){
		$(this).dialog('close');
	},
	'Submit':function(){
		var dlg= $(this);
		var status_rmrks_frm = $("#remrks_update_frm",this);
			 if(status_rmrks_frm.parsley('validate')){
				 status_rmrks_frm.submit();
				 $("#status_rmrks_div").dialog('close');
			}
            else
            {
            	alert('All Fields are required!!!');
            }
	},
	
}
});

function updateexpected_podeliverydate()
{
	if(confirm("Are you sure ?"))
		location="<?=site_url("admin/updatedeliverydate/{$po['po_id']}")?>";
}

function print_po(poid)
{
	var print_url = site_url+'/admin/print_po/'+poid;
		window.open(print_url);
}

function remove_prod_frmpo(pid)
{
	var prodid=pid;
	if(confirm("Are you sure  want to remove this product from PO?"))
		location="<?=site_url('admin/remove_prodfrmpo/'.$po['po_id'])?>/"+prodid;
}
</script>


<style>
.po_label{
font-weight: normal;
color:rgb(95, 73, 4)!important;
}
.btn {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background-color: rgb(245, 245, 245);
    background-image: linear-gradient(to bottom, rgb(255, 255, 255), rgb(230, 230, 230));
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgb(179, 179, 179);
    border-image: none;
    border-radius: 4px 4px 4px 4px;
    border-style: solid;
    border-width: 1px;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
    color: rgb(51, 51, 51);
    cursor: pointer;
    display: inline-block;
    font-size: 14px;
    line-height: 20px;
    margin-bottom: 0;
    padding: 4px 12px;
    text-align: center;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
    vertical-align: middle;
}

.update_link{
	border-radius:5px;
	background:#f77;
	display:inline-block;
	padding:3px 7px;
	color:#fff;
}
.update_link:hover{
	border-radius:0px;
	background:#f00;
	text-decoration:none;
}

</style>