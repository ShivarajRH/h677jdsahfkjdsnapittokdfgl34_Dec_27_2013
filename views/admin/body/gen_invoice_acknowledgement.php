<?php
	$this->load->plugin('barcode');
?>
<div style="font-family: arial">	
<style>

table{
	font-size:12px;
}

@media print {
	.cancelled_invoice_text{
		font-size: 800% !important;
	}
	#print_inv_msg{
		display:none;
	}
	.hideinprint{
		display:none;
	}
	.showinprint{
					display:block;
				}
}
</style>
<?php

foreach($dispatch_list as $dispatch_det)
{
	$ttl_inv_amt = 0;
	
	$dispatch_id = $dispatch_det['dispatch_id'];
	$invs = $dispatch_det['invs'];
	$invoice_list = explode(',',$invs);
	$orderslist_byproduct = $this->db->query("
												select a.transid,a.createdon as invoiced_on,b.bill_address,b.bill_landmark,b.bill_city,b.bill_state,b.bill_pincode,d.init,b.itemid,c.name,if(c.print_name,c.print_name,c.name) as print_name,c.pnh_id,group_concat(distinct a.invoice_no) as invs,
															sum((i_orgprice-(i_discount+i_coup_discount))*a.invoice_qty) as amt,
															sum(a.invoice_qty) as qty 
														from king_invoice a 
														join king_orders b on a.order_id = b.id 
														join king_dealitems c on c.id = b.itemid
														join king_transactions d on d.transid = a.transid
														where a.invoice_no in ($invs) 
												group by itemid ")->result_array();
	$order = $orderslist_byproduct[0];									
?>
<div id="tax_inv_ack_copy" >
	<div style="border-bottom:2px solid #000;padding:5px;font-weight:bold;text-align:center;overflow: hidden;text-align: center">
	<br><br>
	<br><br>
	TAX INVOICE - Acknowledgement Copy 
	</div>
	<table width="100%" style="margin-top:10px">
				<tr>
					<td valign="top">
					<?php 
						$tin_no = '29230678061';
						$service_no = 'AACCL2418ASD001';	
						echo 'Local Cube commerce Pvt Ltd<br>1060,15th cross,BSK 2nd stage,bangalore -560070';
					?>
					</td>
					<td align="right" valign="top">
						<table border=1 cellspacing	=0 cellpadding=5>
							<td><br>Date:</td><td width="100"><b><?=date("d/m/Y",($order['invoiced_on']))?></b></td>
							<td>Transaction<br><div align="center">ID/Date :</div></td>
							<td width="100">
								<b><?=$order['transid']?></b> 
								<br />
								(<?php echo date('dM',$order['init'])?>) 
							</td>
							</tr>
						</table>
					</td>
					
				</tr>
				
			</table>
			 
			<table cellspacing=0 border=1 cellpadding=3 width="100%">
				<tr><th width="100">BILL TO</th>
					<th colspan="3"><?=$order['bill_person']?></th>
					<th>Dispatch ID</th>
				</tr>
				<tr><td><b>Address :</b></td><td colspan="3"><?=nl2br($order['bill_address'])?>, <?=$order['bill_landmark']?>, <?=$order['bill_city']?> <?=$order['bill_state']?> - <?=$order['bill_pincode']?> 
				<?php
					if($inv_type !='auditing'){
				?>
				Mobile : <?=$order['bill_phone']?>
				<?php } 
				
				 
				?>
				</td>
				<?php if($dispatch_id) { ?>
				<td align="right" valign="top">
					<?php
						$dispatch_bc = generate_barcode($dispatch_id,300,40,2); 
					?>
					<div align="center"><?php echo count($invoice_list);?></div>
					<div align="center"><img src="data:image/png;base64,<?=base64_encode($dispatch_bc);?>" /></div>
					<div align="center"><b><?php echo $dispatch_id ?></b>  </div>
				</td>
				<?php } ?>
				</tr>
			</table>
			<br>		 
	<table width="100%" cellpadding="5" cellspacing="0" border="1">
		<tr>
			<th>No</th>
			<th>Item</th>
			<th>Invoice</th>
			<th>Qty</th>
			<th>Total</th>
		</tr>
		<?php 
			$k1=0;
			foreach($orderslist_byproduct as $itm_ord){ 
			?>
		<tr>
			<td><?php echo ++$k1; ?></td>
			<td>
				<span class="showinprint"><?php echo $itm_ord['print_name'].'-'.$itm_ord['pnh_id'];?></span>
				<span class="hideinprint"><?php echo $itm_ord['name'].'-'.$itm_ord['pnh_id'];?></span>
			</td>
			<td><?php echo str_replace(',',', ',$itm_ord['invs']);?></td>
			<td><?php echo $itm_ord['qty'];?></td>
			<td><?php echo $itm_ord['amt'];?></td>
		</tr>
		<?php 		
				$ttl_inv_amt +=  $itm_ord['amt'];
			} 
		?>
		<tr>
			<td colspan="4">&nbsp;</td>
			<td><?php echo $ttl_inv_amt;?></td>
		</tr>	
	</table>
	
	</div>

<?php } ?>
</div>