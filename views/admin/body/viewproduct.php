<?php 
	$only_superadmin = $this->erpm->auth(true,true);
?>
<div class="container">
	<h2>Product details</h2>
	<table class="datagrid" width="100%">
		<thead>
			<tr>
				<th>Product Name</th>
				<th>Brand</th>
				<th>Stock</th>
				<th>Sourcable?</th>
				<th>Short Description</th>
				<th>MRP</th>
				<th>Purchase Cost</th>
				<th>Size</th>
				<th><span style="font-size:80%">unit of measurement</span></th>
				<th>VAT</th>
				<th>Barcode</th>
				<th>Remarks</th>
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
			
				<td>
					<a href="<?=site_url("admin/viewbrand/".$p['brand_id'])?>"><?=$p['brand']?></a>
				</td>
				
				<td width="150">
					<?=$p['stock']?>
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
													where a.product_id=? 
													group by a.product_id,mrp,pbarcode,a.location_id,a.rack_bin_id
													having sum(available_qty)>=0 
													order by mrp asc ";
								?>
								
								<?php foreach($this->db->query($sql,$p['product_id'])->result_array() as $s){
										
										$s['imei_nos'] = $this->db->query("select group_concat(imei_no)  as imei_nos from t_imei_no d where product_id = ? and d.status = 0 and stock_id = ? ",array($p['product_id'],$s['stock_id']))->row()->imei_nos;	
										//$s['is_serial_required'] = $this->db->query("select is_serial_required from m_product_info where product_id = ?",$p['product_id']);	
								?>
								<tr>
									<td>
										<?php echo $s['pbarcode'];?> 
										<?php if($this->erpm->auth(UPDATE_PRODUCT_BARCODE,true)){?>
										<a href="javascript:void(0)" class="upd_stk_prodbc" stk_id="<?php echo $s['stock_id'] ?>" >Edit</a>
										<?php }?>	
									</td>
									<td width="40"><span><?php echo round((float)$s['mrp']);?></span></td>
									<td align="center"><?php echo $s['rbname'];?></td>
									<td align="center"><?php echo round($s['s']);?>
										<?php if($p['is_serial_required'] == 1)
										{?>
										<a href="javascript:void(0)" class="stock_imei_view stock_imei_det_<?php echo $s['stock_id'];?> " stk_barcode="<?php echo $s['pbarcode'];?>" stk_ttl="<?php echo $s['s'];?>" imei_nos="<?php echo $s['imei_nos'] ?>" is_serial_required="<?php echo $s['is_serial_required'] ?>" stk_mrp="<?php echo $s['mrp'] ?>" stk_rackbin="<?php echo $s['rbname'] ?>" stk_id="<?php echo $s['stock_id'] ?>" >view</a>
										<?php }?>
									</td>	
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
			<li><a href="#stock_log" >Stock Log</a></li>
			<?php
				if($p['is_serial_required'])
				{
					echo '<li><a href="#prod_serial_nos" onclick="load_imeino($p["product_id"],0)">Product Serial nos</a></li>';
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
		<div style="float:right;background:#efefef;padding:5px;border:1px solid #fcfcfc;margin-top:23px;font-size: 12px;width: 422px;">
			<h3 align="center">Stock Correction</h3>
			
			<form name="new" id="stk_correction_frm" method="post" action="<?=site_url("admin/stock_correction")?>"   style="margin-left:17px ">
				<input type="hidden" name="pid" value="<?=$p['product_id']?>">
				<table cellpadding="2" cellspacing="0" style="width:345px">
					<tr>
						<td>Type :</td>
						<td style="vertical-align: middle;">
							<input type="radio" checked="checked" value="1" name="type" onclick="formreset()"/>IN 
							<input type="radio" name="type" value="0" onclick="formreset()"/>OUT  
						</td>	
					</tr>
					<tr>
						<td>Stock Product:</td>
						<td>
							<select name="mrp_prod" style="width:200px"> 
								<option value="">Choose</option>
								<option id="new_stock_prod" value="new">New</option>
								<?php 
									$sql_stkmrpprod = "select  a.stock_id,a.location_id,a.rack_bin_id,concat(rack_name,bin_name) as rb_name,a.product_barcode,a.mrp,sum(a.available_qty) as available_qty,concat('Rs',ifnull(a.mrp,0),' - ',rack_name,bin_name,' - ',a.product_barcode) as stk_prod 
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
									<option  style="width:200px" avail_qty="<?php echo $stkmrppro['available_qty'];?>" value="<?php echo $stkmrppro['product_barcode'].'_'.$stkmrppro['mrp'].'_'.$stkmrppro['location_id'].'_'.$stkmrppro['rack_bin_id'].'_'.$stkmrppro['stock_id'] ?>"><?php echo $stkmrppro['stk_prod']?></option>
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
							<select name="loc" style="width:200px">
								<option value ="">Choose</option>
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
					
					<tr>
						<td>Quantity:</td>
						<td>
							<input type="text" name="corr" size="2" value="" autocomplete='off' id="reset"> 
							<span id="sc_preview_avail_qty" style="color: green;font-size: 10px;font-weight: bold;">0 Available</span>
							<?php if($p['is_serial_required'] == 1)
							{?>
							<div id="correc_imei_list" style="display: none"></div>
						<?php }?>
						</td>
					</tr>
					
					<tr id="is_transfer" style="display:none">
						<td>Is Transfer : </td>
						<td><input type="checkbox" name="has_transfer" value="1"></td>
					</tr>
					
					<tr  id="prod_list">
						<td colspan="2" width="100%">
							<div>
								Product : <select name="to_pid" style="width:196px;margin-left: 32px;" id="prod_id">
											<option value="">Choose</option>
											<?php
												foreach($prdct_lst->result_array() as $prd)
												{
											?>				
											
												<option value="<?php echo $prd['product_id'] ?>" style="width:200px" ><?php echo $prd['product_name'] ?></option>
											
											<?php		
										 	}
																	
											?>
								</select>
								<input type="hidden" name="product_id" value="<?=$prd['product_id']?>">
								
								<br />
								<span style="margin: 15px 0px -15px 0px;float:left">Stock :</span> <div id="sel_stk_id"></div>
								<br />
								<span style="margin-top: 30px;float:left">Stock Product :</span> 
									<select name="to_stock_id" class="dest_stk"  data-placeholder="Choose" style="width:198px;margin:11px 0px 0px 15px" data-required="true"></select>
							</div>
						</td>
					</tr>
					
					<tr id="new_mrpblk" style="display: none">
						<td width="80px">
						MRP :</td> <td><input type="text" name="to_new_mrp" size="6" value="" /></td>
						</td>
					</tr>
					
					<tr id="new_bcblk" style="display: none">
						<td width="80px">
						Barcode :</td> <td><input type="text" name="to_new_bc" value="" /></td>
						</td>
					</tr>					
					
					<tr><td>Msg :</td><td><textarea name="msg" style="width:188px"></textarea></td></tr>
					<tr><Td></Td><td><input type="submit" value="Update" onclick="imei_validate()"></td></tr>
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
		
		<div id="stock_imei_view_dlg" title="IMEI Numbers">
			
			<div id="stock_entry_details">
				<table class="datagrid">
					<tbody>	
						<tr>
							<td>Barcode :</td><td class="stk_barcode"></td>
						</tr>
						<tr>
							<td>Mrp :</td><td class="stk_mrp"></td>
						</tr>
						<tr>
							<td>Rackbin :</td><td class="stk_rackbin"></td>
						</tr>
						<tr>
							<td>Stock :</td><td class="stk_ttl"></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="stockdet_imei_list">
				<h3>Imei Nos</h3>
				<div class="imei_list"></div>
			</div>
		</div>
		
	<script type="text/javascript">
		$(function(){
		  $("#sel_stk_prd").hide();
		 	$('#prod_list').hide();
		 	$('#new_mrpblk').hide();
		 	$('#new_bcblk').hide();
		 	$('.dest_stk').hide();
		 	
	
		  $('form[name=new]').submit(function(){
		  	
		  	//var arr = $('input[name="imei_nos[]"]');
		  	var imei_fld = document.getElementsByName('imei_nos[]');
		  	//var d_stock_prod = $('select[name="to_stock_id"]').val();
			var unique_values = {};
			var list_of_values = [];
			
			
			if($('select[name="to_stock_id"]').val() == '')
			{
				alert("Choose Destination stock product");
				return false;
			}
			
			if($('select[name="to_stock_id"]').val() == 'new')
			{
				if($('input[name="to_new_mrp"]').val() == '')
				{
					alert("Give MRP Value");
					return false;	
				}
				if($('input[name="to_new_bc"]').val() == '')
				{
					alert("Give Product Barcode Value");
					return false;	
				}
			}
			
			
			for (i=0; i<imei_fld.length; i++)
			{
				if (imei_fld[i].value == "")
				{
			 	 alert('Complete all IMEI fields');		 
			 	 return false;
				}
				
				
				
				if ( ! unique_values[imei_fld[i].value] ) 
				{
		            unique_values[imei_fld[i].value] = true;
		            list_of_values.push(imei_fld[i].value);
		        } else {
		            alert("Duplicate entry :"+imei_fld[i].value );
		            return false;
		        }
				
				
			}
			
			$.post($(this).attr('action'), $(this).serialize(), function(json) {
		      alert(json);
		    }, 'json');
		  });
		});
		
		
		$('select[name="to_stock_id"]').change(function(){
			
			if($('select[name="to_stock_id"]').val() == "new")
			{
				$('#new_mrpblk').show();
				$('#new_bcblk').show();
			}
			else
			{
				$('#new_mrpblk').hide();
				$('#new_bcblk').hide();
			}
		});
		
		
		$('#prod_id').change(function(){
		
		var prod_id = $(this).val();		
		
		//alert(prod_id);
		
		if(prod_id!='0')
		{
			$(".dest_stk").html('').trigger("liszt:updated");
			$.post("<?=site_url("admin/jx_stk_prd")?>",{prod_id:$(this).val()},function(resp){
				var stk_html='';
					if(resp.status=='error')
					{
						//alert(resp.message);
					}
					else
					{
						stk_html+='<option value="">Choose</option>';
						stk_html+='<option value="new">New</option>';
						$.each(resp.stk_list,function(i,b){
						stk_html+='<option value="'+b.stock_id+'">'+b.stk_prod+'</option>';
						});
					}
			 	$('.dest_stk').html(stk_html).trigger("liszt:updated");
			 	$('.dest_stk').trigger('change');
			},'json');
		}
			$('.dest_stk').show();
		});	
		
		
		$('select[name="to_pid"]').chosen().change(function(){
			$("#sel_stk_id").html(" ");
			$("#sel_stk_prd").html(" ");
			
			//$('select[name="product_name"]').val();
			$.post("<?=site_url("admin/jx_stock_info")?>",{product_id:$('select[name="to_pid"]').val()},function(data){
				if(data.status == 'success')
				$("#sel_stk_id").html(data.stk);
			},'json');
		 	
		 
		});	
	
		
		$('#stock_imei_view_dlg').dialog({
										autoOpen:false,
										open:function()
										{
											
											dlg = $(this);
											var stk_id = dlg.data('stk_id');
											var stk_det_ele = $('.stock_imei_det_'+stk_id);
												
												$('#stock_entry_details .stk_barcode',dlg).html(stk_det_ele.attr('stk_barcode'));
												$('#stock_entry_details .stk_mrp',dlg).html(stk_det_ele.attr('stk_mrp'));
												$('#stock_entry_details .stk_rackbin',dlg).html(stk_det_ele.attr('stk_rackbin'));
												$('#stock_entry_details .stk_ttl',dlg).html(stk_det_ele.attr('stk_ttl'));
												
												var stk_imeinos = stk_det_ele.attr('imei_nos');
													 
												var stk_imeinos_arr = stk_imeinos.split(',');
												var has_imeino = 0;
												var stk_imei_list_html = '<ol>';
												 
												 stk_imeinos_arr = stk_imeinos_arr.sort();
												 
													for(var k=0;k<stk_imeinos_arr.length;k++)
													{
														if(stk_imeinos_arr[k].length)
														{
															stk_imei_list_html +=  '<li>'+stk_imeinos_arr[k]+'</li>';
															has_imeino = 1;
														}
															
													}
													stk_imei_list_html += '</ol>';
														
													if(!has_imeino)
														stk_imei_list_html = 'No Imeis found';
														
													
												$('#stockdet_imei_list .imei_list',dlg).html(stk_imei_list_html);
												
										}		
								});
								
	
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
		$('.stock_imei_view').click(function(){
			$('#stock_imei_view_dlg').data('stk_id',$(this).attr("stk_id")).dialog('open');
		});
		//$('.stock_imeino_view').click(function(){
		//	$('#my_div_select').data('stk_id',$(this).attr("stk_id")).dialog('open');
		//});
		
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
			
			var correc_type =$('input[name="type"]:checked').val();
				if($('input[name="type"]:checked').val() == 0)
				{	
					if($(this).val()>$(this).attr('aqty')*1)
					{
						alert("You have only "+$(this).attr('aqty')+" Qty Available");
						$(this).val(0);
						
					}
				}
				
			var ttl_corr_qty = $(this).val();
				
				if(correc_type == 1)
				{
					$('#is_transfer').hide();
					$('#new_mrpblk').hide();
					$('#new_bcblk').hide();
					 
					$('#correc_imei_list').html('');
					var imei_inputele_list = '<ol>';
					for(var  k=0;k<ttl_corr_qty;k++)
					{
						imei_inputele_list += '<li> <input type="text" name="imei_nos[]" value="" style="width:174px"> </li>';
					}
					imei_inputele_list += '</ol>';
					$('#correc_imei_list').html(imei_inputele_list);
					
				}else
				{
					
					$('#is_transfer').show();
					var stk_avail_imeinos_list = '';
					var selected_stock_entry = $('select[name="mrp_prod"]').val();
						selected_stock_entry_arr = selected_stock_entry.split('_');
						
					var stk_id = selected_stock_entry_arr[selected_stock_entry_arr.length-1];
					var stk_imei_nos = $('.stock_imei_det_'+stk_id).attr('imei_nos');
					
					
					var stk_imei_nos_arr = stk_imei_nos.split(',');
							
					$('#correc_imei_list').html('');
					
					var imei_inputele_list = '';
					for(var k=0;k<stk_imei_nos_arr.length;k++)
						
						stk_avail_imeinos_list += '<option value="'+stk_imei_nos_arr[k]+'">'+stk_imei_nos_arr[k]+'</option>';	
					
					var imei_inputele_list = '<ol>';
					for(var k=0;k<ttl_corr_qty;k++)
						imei_inputele_list += '<li> <select type="text" name="imei_nos[]" style="width:203px"><option value="">Choose</option>'+stk_avail_imeinos_list+'</select> </li>';
					
					imei_inputele_list += '</ol>';
					if(!stk_imei_nos)
					{
						$('#correc_imei_list').style.display='none';
					}
					$('#correc_imei_list').html(imei_inputele_list);
				}
				
				$('#correc_imei_list').show();
			
		});
		
		$('input[name="has_transfer"]').change(function(){
			//var c = $('input[name="chk"]').val();
			
			if($(this).val() == 1)
			{
				$('#prod_list').show();
				$('#new_mrpblk').hide();
				$('#new_bcblk').hide();
			}
		});
		
		
		
	
		$('input[name="type"]').change(function(){
			$('select[name="loc"]').val('');
			$('input[name="corr"]').val(0);
			$('#correc_imei_list').html('');
			
			
			if($(this).val() == 1)
			{
				$('#new_mrpblk').hide();
				$('#new_bcblk').hide();
				$('#is_transfer').hide();
				$('#new_select_block').hide();
				$('#prod_list').hide();
				$('#new_stock_prod').show();
				if($('select[name="mrp_prod"]').val() == 'new')
				{
					$('#new_mrp_bc_block input').val('');
					$('#new_mrp_bc_block').show();
				}
				$('select[name="loc"]').attr('disabled',false);
				
			}else
			{
				$('#new_input_block').hide();
				$('select[name="mrp_prod"]').val('');
				$('#new_stock_prod').hide();	
				$('#new_mrp_bc_block input').val('');
				$('#new_mrp_bc_block').hide();
				$('select[name="loc"]').attr('disabled',true);
				$('#new_mrpblk').hide();
				$('#new_bcblk').hide();
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
				if($('input[name="imei_no[]"]').val())
				{
					error_msg.push("-IMEI Numbers cannot be blank");
				}
				
			}
			
			if(error_msg.length)
			{
				alert("Unable to submit form \n"+error_msg.join("\n"));
				return false;
			}
		});
			
			
			$('#stock_imei_view_dlg').dialog({
					autoOpen:false,
					open:function()
					{
						dlg = $(this);
						var stk_id = dlg.data('stk_id');
						var stk_det_ele = $('.stock_imei_det_'+stk_id);
							
							$('#stock_entry_details .stk_barcode',dlg).html(stk_det_ele.attr('stk_barcode'));
							$('#stock_entry_details .stk_mrp',dlg).html(stk_det_ele.attr('stk_mrp'));
							$('#stock_entry_details .stk_rackbin',dlg).html(stk_det_ele.attr('stk_rackbin'));
							$('#stock_entry_details .stk_ttl',dlg).html(stk_det_ele.attr('stk_ttl'));
							
							var stk_imeinos = stk_det_ele.attr('imei_nos');
								 
							var stk_imeinos_arr = stk_imeinos.split(',');
							var has_imeino = 0;
							var stk_imei_list_html = '<ol>';
							 
							 stk_imeinos_arr = stk_imeinos_arr.sort();
							 
								for(var k=0;k<stk_imeinos_arr.length;k++)
								{
									if(stk_imeinos_arr[k].length)
									{
										stk_imei_list_html +=  '<li>'+stk_imeinos_arr[k]+'</li>';
										has_imeino = 1;
									}
										
								}
								stk_imei_list_html += '</ol>';
									
								if(!has_imeino)
									stk_imei_list_html = 'No Imeis found';
									
								
							$('#stockdet_imei_list .imei_list',dlg).html(stk_imei_list_html);
							
					}		
			});
									
	</script>
	
	<h4>Total : <span id="stock_log_ttl">0</span></h4>
	
		<table id="stock_log_list" class="datagrid" width="60%">
			<thead>
			<tr><th>Slno</th><th>In / Out</th><th>Stock Intake/Invoice</th><th>Qty Affected</th><th>Stock After</th><th>Created By</th><th width="120">On</th><th></th></tr>
			</thead>
			
			<tbody>
			
			</tbody>
		</table>
		<div id="stock_log_pagination" style="display: none"></div>
	</div>


	<?php
		if($p['is_serial_required'])
		{
	?>
		<div id="prod_serial_nos">
			<h4>Total : <span id="stock_imei_ttl">0</span></h4>
			<table id="stock_imei_list" class="datagrid" width="60%">
				<thead><tr><th>#</th><th>Serialno</th><th>GRNID</th><th>Status</th><th>Date</th></tr></thead>
				<tbody>
				
				</tbody>
			</table>
			<div id="stock_imei_pagination" style="display: none"></div>
		</div>	
	<?php		
		}
	?>

	<div id="price_change_log">
	
		<h4 style="margin-bottom:0px;">Price changelog</h4>
		<table class="datagrid ">
			<thead>
				<tr><th>Sno</th><th>Old MRP</th><th>New MRP</th><th>Reference</th><th>Date</th></tr>
			</thead>
			
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
			<thead>
				<tr><th>Sno</th><th>Deal</th><th>Qty</th><th>Type</th></tr>
			</thead>
			
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
				<thead>
					<tr><th>Sno</th><tH>Group</tH><th>Deal</th><th>Qty</th><th>Type</th></tr>
				</thead>
				
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
			<thead>
				<tr><th>Changed to</th><th>By</th><th>On</th></tr>
			</thead>
			
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
	
	
	
	function load_product_stocklog(product_id,pg)
	{
		$('#stock_log_list tbody').html('<tr><td colspan="6"><div align="center"><img src="'+base_url+'/images/jx_loading.gif'+'"></div></td></tr>');
		$.post(site_url+'/admin/jx_stocklog/'+product_id+'/'+pg+'/25','',function(resp){
			$('#stock_log_list tbody').html(resp.log_data);
			if(resp.ttl > resp.limit)
				$('#stock_log_pagination').html(resp.pagi_links).show();
			else
				$('#stock_log_pagination').html("").hide();
			
			$('#stock_log_ttl').html(resp.ttl);	
				
		},'json');
	}
	
	function load_imeino(product_id,pg)
	{
		$('#stock_imei_list tbody').html('<tr><td colspan="6"><div align="center"><img src="'+base_url+'/images/jx_loading.gif'+'"></div></td></tr>');
		$.post(site_url+'/admin/jx_stockimeilist/'+product_id+'/'+pg+'/25','',function(resp){
			$('#stock_imei_list tbody').html(resp.imei_data);
			if(resp.imei_ttl > resp.limit)
				$('#stock_imei_pagination').html(resp.imei_pagi_links).show();
			else
				$('#stock_imei_pagination').html("").hide();
			
			$('#stock_imei_ttl').html(resp.imei_ttl);	
				
		},'json');
	}
	
	$('.log_pagination a').live('click',function(e){
		e.preventDefault();
		var url_prts = $(this).attr('href').split('/');
			pg = url_prts[url_prts.length-1];
			pg = pg*1;
			
		load_product_stocklog(<?php echo $p['product_id'];?>,pg);
		load_imeino(<?php echo $p['product_id'];?>,pg);
		 
	});
	
	
	$( document ).ready(function(){
		load_product_stocklog(<?php echo $p['product_id'];?>,0);
		load_imeino(<?php echo $p['product_id'];?>,0);
	});
	
	
	 
</script>


<style>
	#prod_fea_tab h4{margin:4px 0px;}
	#correc_imei_list{margin:-5px -5px -5px -43px;}
	
	#sel_stk_id {
	    float: right;
	    margin-right: 70px;
	    margin-top: 17px;
	    width: 50%;
	}
	#new_select_stkmove_block
	{
		margin-top: 11px;
    	width: 196px;
    	float:right;
	}
	#prod_id_chzn
	{
		margin-left: 46px;
    	vertical-align: middle;
    	width:198px !important;
	}
	#sel_stk_prd
	{
		float: right;
	    margin-top: 14px;
	    width: 243px;
	}
	.stock_det
	{
		width:57%;
	}
</style>
<?php
