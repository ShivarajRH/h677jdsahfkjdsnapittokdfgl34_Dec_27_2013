<?php 
	$only_superadmin = $this->erpm->auth(true,true);
?>
<div class="container">
<h2>Product details</h2>
<table class="datagrid" width="100%">
<thead>
<tr>
<th>Product Name</th><th>Brand</th><th>Stock</th><th>Sourcable?</th><th>Short Description</th><th>MRP</th><th>Purchase Cost</th><th>Size</th><th><span style="font-size:80%">unit of measurement</span></th><th>VAT</th><th>Barcode</th><th>Remarks</th>
</tr>
</thead>
<tbody>
<tr>
<?php $p=$product;?>
<td>
<?=$p['product_name']?><br>
<a href="<?=site_url("admin/editproduct/{$p['product_id']}")?>">edit</a>
<br>
<br>
<span style="font-size: 11px;">SKU Code: <b><?=($p['sku_code']?$p['sku_code']:'-na-');?></b><br></span>

</td>
<td><a href="<?=site_url("admin/viewbrand/".$p['brand_id'])?>"><?=$p['brand']?></a></td>
<td width="150"><?=$p['stock']?>
	<div style="background:#ccc;padding:2px;margin:3px;font-size:85%;">
		<b style="white-space:nowrap;">Stock MRPs</b><br>
		<table cellpadding="0" cellspacing="0" style="background: #FFF;">
			<thead>
				<th><b>Barcode</b></th>
				<th><b>MRP</b></th>
				<th><b>Rackbin</b></th>
				<th><b>Stock</b></th>
			</thead>
		<tbody>
			
		
		<?php 
			$sql = "select sum(available_qty) as s,mrp,
							a.location_id,a.rack_bin_id,
							concat(rack_name,bin_name) as rbname,
							ifnull(product_barcode,'') as pbarcode,
							a.stock_id     
						from t_stock_info  a 
						join m_storage_location_info b on b.location_id = a.location_id 
						join m_rack_bin_info c on c.id = a.rack_bin_id  
						where product_id=? 
						group by mrp,pbarcode,a.location_id,a.rack_bin_id 
						having sum(available_qty)>=0 
						order by mrp asc ";
		?>
		
		<?php foreach($this->db->query($sql,$p['product_id'])->result_array() as $s){?>
			<tr>
				<td>
					<?php echo $s['pbarcode'];?> 
					<?php if($this->erpm->auth(UPDATE_PRODUCT_BARCODE,true)){?>
					<a href="javascript:void(0)" class="upd_stk_prodbc" stk_id="<?php echo $s['stock_id'] ?>" >Edit</a>
					<?php }?>	
				</td>
				<td width="40"><span><?php echo round((float)$s['mrp'],2);?></span></td>
				<td align="center"><?php echo $s['rbname'];?></td>
				<td align="center"><?php echo round($s['s']);?></td>
			</tr>
			
		<?php }?>
		</tbody>
		</table>
	</div>
	
</td>
<td><?=$p['is_sourceable']?"Sourcable":"Not Sourcable"?></td>
<td><?=$p['short_desc']?></td>
<td><?=$p['mrp']?></td>
<td><?=$p['purchase_cost']?></td>
<td><?=$p['size']?></td>
<td><?=$p['uom']?></td>
<td><?=$p['vat']?>%</td>
<td><?=$p['barcode']?></td>
<td><?=$p['remarks']?></td>
</tr>
</tbody>
</table>









<div id="prod_fea_tab" style="clear:both">
	<ul>
		<li><a href="#stock_log">Stock Log</a></li>
		<?php
			if($p['is_serial_required'])
			{
				echo '<li><a href="#prod_serial_nos">Product Serial nos</a></li>';
			}
		?>
		<li><a href="#price_change_log">Price ChangeLog</a></li>
		<li><a href="#linked_deals">Linked Deals</a></li>
		<li><a href="#grp_linked_deals">Group Linked Deals</a></li>
		<li><a href="#sourceable_changelog">Sourceable Change Log</a></li>
	</ul>
<div id="stock_log">

<?php 
	if($this->erpm->auth(STOCK_CORRECTION,true))
	{
?>
<div style="float:right;background:#efefef;padding:5px;border:1px solid #fcfcfc;margin-top:30px;font-size: 12px;width: 450px;">
	<h3 align="center">Stock Correction</h3>
<form id="stk_correction_frm" method="post" action="<?=site_url("admin/stock_correction")?>">
<input type="hidden" name="pid" value="<?=$p['product_id']?>">
<table cellpadding="2" cellspacing="0" width="100%">
<tr>
	<td>Type :</td>
	<td style="vertical-align: middle;">
		<input type="radio" checked="checked" value="1" name="type" />IN 
		<input type="radio" name="type" value="0" />OUT  
	</td>	
</tr>
<tr>
	<td>Stock Product:</td>
	<td>
		<select name="mrp_prod">
			<option value="">Choose</option>
			<option id="new_stock_prod" value="new">New</option>
		<?php 
			$sql_stkmrpprod = "select  a.location_id,a.rack_bin_id,concat(rack_name,bin_name) as rb_name,a.product_barcode,a.mrp,sum(a.available_qty) as available_qty,concat('Rs',ifnull(a.mrp,0),' - ',rack_name,bin_name,' - ',a.product_barcode) as stk_prod 
								from t_stock_info a
								join m_rack_bin_info b on a.rack_bin_id = b.id 
								where a.product_id = ? 
								group by a.mrp,a.location_id,a.rack_bin_id,a.product_barcode  
								order by a.mrp asc 
							";
			$stkmrpprod_res = $this->db->query($sql_stkmrpprod,$p['product_id']);
			if($stkmrpprod_res->num_rows())
			{
				foreach($stkmrpprod_res->result_array() as $stkmrppro)
				{
		?>
				<option avail_qty="<?php echo $stkmrppro['available_qty'];?>" value="<?php echo $stkmrppro['product_barcode'].'_'.$stkmrppro['mrp'].'_'.$stkmrppro['location_id'].'_'.$stkmrppro['rack_bin_id'] ?>"><?php echo $stkmrppro['stk_prod']?></option>
		<?php
				} 				
			}else
			{
		?>
				<option avail_qty="0" value="<?php echo $p['barcode'].'_'.$p['mrp'].'_1_10' ?>"><?php echo $p['barcode'].'_'.$p['mrp']?></option>
		<?php 		
			}
		?>
		</select>
		
		<div id="new_mrp_bc_block" style="display: none;">
			<div>
				MRP: &nbsp;&nbsp;&nbsp; <input type="text" name="n_mrp" size="6" value="" /> <br />  
				Barcode: <input type="text" name="n_barcode"  size="16" value="" />
			</div>
		</div>	
	</td>
</tr>
 
<tr>
	<td>Location :</td>
	<td>
		<select name="loc">
			<option value="">Choose</option>
		<?php 
			$sql_stklocs = "select  location_id,rack_bin_id,concat(rack_name,bin_name) as rb_name 
									from m_rack_bin_brand_link a 
									join m_rack_bin_info b on a.rack_bin_id = b.id 
									where brandid = ? ";
			$stk_locs_res = $this->db->query($sql_stklocs,$p['brand_id']);
			if($stk_locs_res->num_rows())
			{
				foreach($stk_locs_res->result_array() as $stk_loc)
				{
		?>
				<option value="<?php echo $stk_loc['location_id'].'_'.$stk_loc['rack_bin_id'] ?>"><?php echo $stk_loc['rb_name']?></option>
		<?php
				} 				
			}
		?>
		</select>
	</td>
</tr>
<tr><td>Quantity:</td><td><input type="text" name="corr" size=2>
	<span id="sc_preview_avail_qty" style="color: green;font-size: 10px;font-weight: bold;">0 Available</span>
</td></tr>


<tr><td>Msg :</td><td><textarea name="msg"></textarea></td></tr>
<tr><Td></Td><td><input type="submit" value="Update"></td></tr>
</table>
</form>
</div>
<?php } ?>

<?php 
	if($only_superadmin  || 1){
?>
	<div id="upd_stk_prodbc_dlg" title="Update Product Stock Barcode">
		<form id="upd_stk_prodbc_frm" method="post">
			<table>
				<tr><td><b>Old Barcode</b></td><td><input id="upd_old_bc" class="inp" type="text" disabled="disabled"></td></tr>
				<tr><td><b>New Barcode</b></td><td><input id="upd_new_bc" class="inp" autocomplete="off" type="text" value=""><input type="submit" value="Submit" style="visibility: hidden;" ></td></tr>
			</table>
		</form>	
	</div>
	
	
	<script type="text/javascript">
		$('#upd_stk_prodbc_dlg').dialog({
										autoOpen:false,
										width:400,
										height:200,
										modal:true,
										open:function(){
											stk_id = $(this).data('stk_id');
											$.getJSON(site_url+'/admin/jx_get_stkprobyid/'+stk_id,'',function(resp){
												if(resp.status == 'error')
												{
													alert(resp.error);
												}else
												{
													$('#upd_old_bc').val(resp.stkdet.product_barcode);
													$('#upd_new_bc').val('');
												}
											});
										},
										buttons:{
											'Cancel':function(){
												$('#upd_stk_prodbc_dlg').dialog('close');
											},
											'Update':function(){
												$(".ui-dialog-buttonpane button:contains('Update')").button().button("disable");
												var newbc = $('#upd_new_bc').val();
												
													$.post(site_url+'/admin/jx_upd_stkprodbc','stk_id='+stk_id+'&newbc='+newbc,function(resp){
														if(resp.status == 'error')
														{
															$(".ui-dialog-buttonpane button:contains('Update')").button().button("enable");
															alert(resp.error);
														}else
														{
															alert("Barcode updated successfully");
															location.href = location.href;
														}
													},'json');
												 
											}
										}
								});

		$('.upd_stk_prodbc').click(function(){
			$('#upd_stk_prodbc_dlg').data('stk_id',$(this).attr("stk_id")).dialog('open');
		});

		$('#upd_stk_prodbc_frm').submit(function(){
			$(".ui-dialog-buttonpane button:contains('Update')").button().trigger('click');
			return false;
		});
		
	</script>
	
<?php }?>

<script type="text/javascript">
	$('select[name="mrp_prod"]').change(function(){

		$('#new_mrp_bc_block input').val('');
		$('#new_mrp_bc_block').hide();
		
		if($(this).val())
		{
			if($(this).val() == 'new')
			{
				$('input[name="corr"]').attr('aqty',0);
				$('#sc_preview_avail_qty').text("");

				$('#new_mrp_bc_block input').val('');
				$('#new_mrp_bc_block').show();
				
			}else
			{
				avail_qty = $('option:selected',this).attr('avail_qty');
				$('#sc_preview_avail_qty').text(avail_qty+" Available");
				$('input[name="corr"]').attr('aqty',avail_qty);
			}	
			
		}else
		{
			$('input[name="corr"]').attr('aqty',0);
			$('#sc_preview_avail_qty').text("");
			
		}
		
	}).trigger("change");	

	$('input[name="corr"]').keyup(function(){
		
		if($('input[name="type"]:checked').val() == 0)
		{
			if($(this).val()>$(this).attr('aqty')*1)
			{
				alert("You have only "+$(this).attr('aqty')+" Qty Available");
				$(this).val(0);
			}
		}
			
		
	});

	$('input[name="type"]').change(function(){
		$('select[name="loc"]').val('');
		if($(this).val() == 1)
		{
			$('#new_stock_prod').show();
			if($('select[name="mrp_prod"]').val() == 'new')
			{
				$('#new_mrp_bc_block input').val('');
				$('#new_mrp_bc_block').show();
			}
			$('select[name="loc"]').attr('disabled',false);
		}else
		{
			$('select[name="mrp_prod"]').val('');
			$('#new_stock_prod').hide();	
			$('#new_mrp_bc_block input').val('');
			$('#new_mrp_bc_block').hide();
			$('select[name="loc"]').attr('disabled',true);
		}
		
	});

	$('input[name="type"]:checked').trigger('change');
	
	
	

	$('#stk_correction_frm').submit(function(){
		var error_msg = new Array();
		if(!$('select[name="mrp_prod"]').val())
		{
			error_msg.push("-Please Choose Stock product");
		}
		if(!$('input[name="corr"]').val())
		{
			error_msg.push("-Please Enter Qty");
		}
		if($('input[name="type"]:checked').val() == "1")
		{
			if(!$('select[name="loc"]').val())
			{
				error_msg.push("-Please Choose Location");
			}
		}
		
		if(error_msg.length)
		{
			alert("Unable to submit form \n"+error_msg.join("\n"));
			return false;
		}
			
		 
	});

	
	
</script>

<h4>Stock Log</h4>
<table class="datagrid">
<thead>
<tr><th>In / Out</th><th>Stock Intake/Invoice</th><th>Qty Affected</th><th>Stock After</th><th>Created By</th><th>On</th><th></th></tr>
</thead>
<tbody>
<?php foreach ($log as $l){?>
<tr>
<td><?=$l['update_type']?"In":"Out"?></td>
<td>
<?php if($l['corp_invoice_id']){?>
<a href="<?=site_url("admin/client_invoice/{$l['corp_invoice_id']}")?>"><?=$l['c_invoice_no']?></a>
<?php }?>
<?php if($l['invoice_id']){?>
<a href="<?=site_url("admin/invoice/{$l['invoice_no']}")?>"><?=$l['invoice_no']?></a>
<?php }?>
<?php if($l['grn_id']){?>
<a href="<?=site_url("admin/viewgrn/{$l['grn_id']}")?>">GRN<?=$l['grn_id']?></a>
<?php }?>
<?php if($l['p_invoice_id']){?>
<a href="<?=site_url("admin/proforma_invoice/{$l['p_invoice_no']}")?>">PI<?=$l['p_invoice_no']?></a>
<?php }?>
<?php if($l['return_prod_id']){?>
<a href="<?=site_url("admin/pnh_product_returnbyid/{$l['return_prod_id']}")?>">RI<?=$l['return_prod_id']?></a>
<?php }?>
</td>
<td><?=$l['qty']?></td>
<td><?=$l['current_stock']?></td>
<td><?=$l['username']?></td>
<td><?=format_datetime($l['created_on'])?></td>
<td><?=$l['msg']?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>


<?php
	if($p['is_serial_required'])
	{
?>
		<div id="prod_serial_nos">
			<h4 style="margin-bottom:0px;">Product Serial/IMEI nos</h4>
			<table class="datagrid ">
			<thead><tr><th>#</th><th>Serialno</th><th>GRNID</th><th>Status</th><th>Date</th></tr></thead>
			<tbody>
			<?php $i=1; foreach($this->db->query("select * from t_imei_no where product_id=? order by status asc ",$p['product_id'])->result_array() as $pc){?>
				<?php
					$pc['trans_id'] = $this->db->query('select transid from king_orders where id = ? ',$pc['order_id'])->row()->transid;
				?>
			<tr>
			<td><?=$i++?></td>
			<td width="150"><?=$pc['imei_no']?></td>
			<td><a href="<?php echo site_url('admin/viewgrn/'.$pc['grn_id']);?>" target="_blank" ><?=$pc['grn_id']?></a></td>
			<td><?=($pc['status']?anchor('admin/trans/'.$pc['trans_id'],$pc['order_id']):'In-Stock')?></td>
			<td><?=format_date_ts($pc['created_on'])?></td>
			</tr>
			<?php }?>
			</tbody>
			</table>
		</div>	
<?php		
	}
?>

<div id="price_change_log">

<h4 style="margin-bottom:0px;">Price changelog</h4>
<table class="datagrid ">
<thead><tr><th>Sno</th><th>Old MRP</th><th>New MRP</th><th>Reference</th><th>Date</th></tr></thead>
<tbody>
<?php $i=1; foreach($this->db->query("select * from product_price_changelog where product_id=? order by id desc",$p['product_id'])->result_array() as $pc){?>
<tr>
<td><?=$i++?></td>
<td>Rs <?=$pc['old_mrp']?></td>
<td>Rs <?=$pc['new_mrp']?></td>
<td>
<?php if($pc['reference_grn']==0) echo "MANUAL";else{?>
<a href="<?=site_url("admin/viewgrn/{$pc['reference_grn']}")?>"><?=$pc['reference_grn']?></a>
<?php }?>
</td>
<td><?=date("g:ia d/m/y",$pc['created_on'])?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>

<div id="linked_deals">
<h4 style="margin-bottom:0px;">Linked Deals</h4>
<table class="datagrid ">
<thead><tr><th>Sno</th><th>Deal</th><th>Qty</th><th>Type</th></tr></thead>
<tbody>
<?php $i=1; foreach($this->db->query("select i.is_pnh,i.id,i.name,l.qty from m_product_deal_link l join king_dealitems i on i.id=l.itemid where l.product_id=?",$p['product_id'])->result_array() as $d){?>
<tr>
<td><?=$i++?></td>
<td><a href="<?=site_url("admin/deal/{$d['id']}")?>"><?=$d['name']?></a></td>
<td><?=$d['qty']?></td>
<td><?=$d['is_pnh']?"PNH":"SNP"?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>

<div id="grp_linked_deals">
<h4 style="margin-bottom:0px;">Group Linked Deals</h4>
<table class="datagrid ">
<thead><tr><th>Sno</th><tH>Group</tH><th>Deal</th><th>Qty</th><th>Type</th></tr></thead>
<tbody>
<?php $i=1; foreach($this->db->query("select i.is_pnh,i.id,i.name,l.qty,g.group_id from products_group_pids g join m_product_group_deal_link l on l.group_id=g.group_id join king_dealitems i on i.id=l.itemid where g.product_id=? group by i.id",$p['product_id'])->result_array() as $d){?>
<tr>
<td><?=$i++?></td>
<td><a href="<?=site_url("admin/product_group/{$d['group_id']}")?>"><?=$this->db->query("select group_name from products_group where group_id=?",$d['group_id'])->row()->group_name?></a></td>
<td><a href="<?=site_url("admin/deal/{$d['id']}")?>"><?=$d['name']?></a></td>
<td><?=$d['qty']?></td>
<td><?=$d['is_pnh']?"PNH":"SNP"?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>

<div id="sourceable_changelog">
	<h4 style="margin-bottom:0px;">Is Sourceable / Not Sourceable changelog</h4>
	<table class="datagrid">
	<thead><tr><th>Changed to</th><th>By</th><th>On</th></tr></thead>
	<tbody>
	<?php foreach($this->db->query("select s.is_sourceable,s.created_on,u.name as `by` from products_src_changelog s join king_admin u on u.id=s.created_by where s.product_id=? order by s.id desc",$p['product_id'])->result_array() as $c){?>
	<tr><td><?=$c['is_sourceable']=="1"?"SOURCEABLE":"NOT SOURCEABLE"?></td><td><?=$c['by']?></td><td><?=date("g:ia d/m/y",$c['created_on'])?></td></tr>
	<?php }?>
	</tbody>
	</table>
</div>

</div>
</div>

<script type="text/javascript">
	$('#prod_fea_tab').tabs();
</script>
<style>
	#prod_fea_tab h4{margin:4px 0px;}
</style>
<?php
