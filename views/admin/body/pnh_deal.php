<?php

	$item_id = $this->uri->segment(3);
?>

<style>
.jqplot-highlighter-tooltip, .jqplot-canvasOverlay-tooltip
{
	font-size: 13px !important;
    margin-left:15px;
    background:none repeat scroll 0 0 #FFFFFF !important; 
}
.deal_sales_left_wrap b
{
	float: left;
    margin: 0 6px;
    text-align: right;
    width: 224px;
}
.deal_sales_left_wrap span
{
	background: none repeat scroll 0 0 #EEE9D3;
    float: left;
    margin: 1px 0;
    padding: 7px 0;
    width: 100%;
}
#deal_stat
{
	margin-top:30px;clear: both;width:800px;
}
#deal_stat_terr
{
	width:440px;
	float: left;
}
.franch_list
{
    float: right;
    margin: 10px 9px 0 0;
    width: 363px;
}
ul.tabs 
{
	margin: 0;
	padding: 0;
	
	list-style: none;
	width: 100%;
	border:none;
	margin-top:20px;
}

ul.tabs li 
{
	float: left;
	margin: 0;
	cursor: pointer;
	padding: 3px 20px;
	line-height: 25px;
	border: 1px solid #f8f8f8;
	font-weight: bold;
	background: #EEEEEE;
	position: relative;
	border-bottom:0px;
	border-radius:5px 5px 0px 0px;
	margin-right:3px;
}
ul.tabs li:hover 
{
	background: #CCCCCC;
}	
ul.tabs li.active
{
	background: #FAFAF5;
	border: 1px solid #FAFAF5;
}
.tab_container 
{
	
	border-top: none;
	clear: both;
	float: left; 
	width: 100%;
	background: #FAFAF5;
}
.tabcontent 
{
    padding: 10px;
	display: none;
}
.tabs
{
	border-radius : 0px !important;
}
.link
{
	color:blue;	
}
.badge 
{
  display: block;
  position: absolute;
  top: -12px;
  right: 3px;
  line-height: 16px;
  height: 16px;
  padding: 0 5px;
  font-family: Arial, sans-serif;
  color: white;
  text-shadow: 0 1px rgba(0, 0, 0, 0.25);
  border: 1px solid;
  border-radius: 10px;
  -webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), 0 1px 1px rgba(0, 0, 0, 0.08);
  box-shadow: inset 0 1px rgba(255, 255, 255, 0.3), 0 1px 1px rgba(0, 0, 0, 0.08);
}
.badge 
{
  background: #67c1ef;
  border-color: #30aae9;
  background-image: -webkit-linear-gradient(top, #acddf6, #67c1ef);
  background-image: -moz-linear-gradient(top, #acddf6, #67c1ef);
  background-image: -o-linear-gradient(top, #acddf6, #67c1ef);
  background-image: linear-gradient(to bottom, #acddf6, #67c1ef);
}
.badge.green 
{
  background: #77cc51;
  border-color: #59ad33;
  background-image: -webkit-linear-gradient(top, #a5dd8c, #77cc51);
  background-image: -moz-linear-gradient(top, #a5dd8c, #77cc51);
  background-image: -o-linear-gradient(top, #a5dd8c, #77cc51);
  background-image: linear-gradient(to bottom, #a5dd8c, #77cc51);
}
/*
.badge.yellow {
  background: #faba3e;
  border-color: #f4a306;
  background-image: -webkit-linear-gradient(top, #fcd589, #faba3e);
  background-image: -moz-linear-gradient(top, #fcd589, #faba3e);
  background-image: -o-linear-gradient(top, #fcd589, #faba3e);
  background-image: linear-gradient(to bottom, #fcd589, #faba3e);
}
.badge.red {
  background: #fa623f;
  border-color: #fa5a35;
  background-image: -webkit-linear-gradient(top, #fc9f8a, #fa623f);
  background-image: -moz-linear-gradient(top, #fc9f8a, #fa623f);
  background-image: -o-linear-gradient(top, #fc9f8a, #fa623f);
  background-image: linear-gradient(to bottom, #fc9f8a, #fa623f);
}
*/
</style>

<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/css/analytic.css" />
<link class="include" rel="stylesheet" type="text/css" href="<?php echo base_url();?>/js/jq_plot/jquery.jqplot.min.css" />
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/jquery.jqplot.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.highlighter.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.cursor.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.barRenderer.min.js"></script>
<script class="include"  type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.pieRenderer.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script class="include" type="text/javascript" src="<?php echo base_url();?>/js/jq_plot/plugins/jqplot.canvasTextRenderer.min.js"></script>

<?php $linked_prod = $this->db->query("select gl.*,g.group_name from m_product_group_deal_link gl join products_group g on g.group_id=gl.group_id where gl.itemid=?",$deal['id']);
	
	  $pack_qty = $this->db->query("select * from m_product_deal_link where qty>1 and itemid = ?",$deal['id']);
?>


<div class="container page_wrap" style="padding:10px">


<div style="float:right">
	<div>
	<?php $d=$deal; $stock=$this->erpm->do_stock_check(array($d['id'])); if(empty($stock)){?>
	<h5 style="color:red;margin:10px;font-size: 14px">OUT OF STOCK</h5>
	<?php }else{?>
	<h5 style="color:green;margin:10px;font-size: 14px">IN-STOCK</h5>
	<?php }?>
	</div>
</div>

<div style="width:75%;">	
	<?=$deal['menu_name']?>  &raquo; 
	<a href="<?=site_url("admin/viewcat/{$deal['catid']}")?>"><?=$deal['category']?></a>  &raquo; 
	<a href="<?=site_url("admin/viewbrand/{$deal['brandid']}")?>"><?=$deal['brand']?></a>
</div>
<h3 class="page_title">
	<br>
	<?=$deal['name']?> <a style="font-size: 9px;" href="<?=site_url("admin/pnh_editdeal/{$deal['id']}")?>">Edit</a> 
</h3>

<div class="body_center_content">
	<div class="">
		<div class="">
			<ul class="tabs"> 
		        <li rel="deal_det" style="margin-left:10px;" class="active" >Deal Details</li>
		        <li rel="prod_linked">Product Linked</li>
		        <li rel="prc_chanlog">Price Changelog</li>
		        <li rel="recent">Recent Orders</li>
		        <li rel="spec_marg_his">Special Margin History</li>
		        <li rel="analytics" class="sales_anl">Sales Analytics</li>
		    </ul>
		    
	     	<div class="tab_container"> 
	       		<!-- #Deal Details Block start -->
	       		<div id="deal_det" class="tabcontent">
	       			<table width="100%">
	       				<tr>
	       					<td width="50%">
				       			<table cellpadding=3 class="datagrid" width="100%">
									<tr>
										<td>PNH PID :</td><Td><?=$deal['pnh_id']?></Td>
									</tr>
									<tr><td>Deal Name :</td><Td style="font-weight:bold;"><?=$deal['name']?></Td></tr>
									<tr><td>Print Name :</td><Td style="font-weight:bold;"><?=$deal['print_name']?$deal['print_name']:$deal['name']?></Td></tr>
									<tr><td>Tagline :</td><td><?=$deal['tagline']?></td></tr>
									<tr><td>MRP :</td><td>Rs <?=$deal['orgprice']?></td></tr>
									<tr><td>Offer price/Dealer Price :</td><td>Rs <?=$deal['price']?></td></tr>
									<tr><td>Store price :</td><td>Rs <?=$deal['store_price']?></td></tr>
									<tr><td>NYP price :</td><td>Rs <?=$deal['nyp_price']?></td></tr>
									<tr><Td>Gender Attribute :</Td><td><?=$deal['gender_attr']?></td></tr>
									<tr><Td>Max Allowed Qty <br>(for franchise per day):</Td><td><?=$deal['max_allowed_qty']?></td></tr>
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
							</td>
							<td width="50%">
								<img src="<?=IMAGES_URL?>items/<?=$deal['pic']?>.jpg" style="margin-left:20px;">
							</td>
						</tr>
					</table>
				       			
	       		</div>	
	       		<!-- #Deal Details Block End -->
       		
       		
	       		<!-- #Price Changelog Block start -->
	       		<div id="prc_chanlog" class="tabcontent">
	       			<?php
       					$prc_chan = $this->db->query("select a.*,b.username as logged_by from deal_price_changelog a left join king_admin b on a.created_by = b.id where itemid=? order by id desc",$deal['id']);
						if($prc_chan->num_rows())
						{	
					?>
					<table class="datagrid smallheader noprint">
	       				<thead>
							<tr>
								<th>Sno</th><th>Old MRP</th><th>New MRP</th><th>Old Price</th><th>New Price</th><th>Reference</th><th>Updated By</th><th>Date</th>
							</tr>
						</thead>
						
						<tbody>
							<?php $i=1; foreach($prc_chan->result_array() as $pc){?>
							<tr>
							<td><?=$i++?></td>
							<td>Rs <?=$pc['old_mrp']?></td>
							<td>Rs <?=$pc['new_mrp']?></td>
							<td>Rs <?=$pc['old_price']?></td>
							<td>Rs <?=$pc['new_price']?></td>
							<td>
							<?php if($pc['reference_grn']==0) echo "MANUAL";else{?>
							<a href="<?=site_url("admin/viewgrn/{$pc['reference_grn']}")?>"><?=$pc['reference_grn']?></a>
							<?php }?>
							</td>
							<td><?=$pc['logged_by']?></td>
							<td><?=format_datetime_ts($pc['created_on'])?></td>
							</tr>
							<?php }?>
						</tbody>
					</table>
				<?php } else { ?>
					<div style="margin:10px;font-weight:bold"> No Details Found </div>
				<?php } ?>
	       		</div>	
	       		<!-- #Price Changelog Block End -->
       		
       		
	       		<!-- #Special Margin History Block start -->
	       		<div id="spec_marg_his" class="tabcontent">
	       			<div id="margin_his" style="clear: both;">
						<ul>
							<li><a href="#spec_margin">Special Margin</a></li>
							<li><a href="#sup_scheme">Super Scheme</a></li>
							<li><a href="#mem_scheme">Member Scheme</a></li>
						</ul>
						
						<div id="spec_margin">
							<table width="100%">
								<tr>
									<td width="45%">
										<div>
								  			<h4>Make it as Special Margin Deal<span style="color:#777;padding:1px 4px;background:#eee;">(Calculate Margin on Offer Price)</span></h4>
											<form action="<?=site_url("admin/pnh_special_margin_deal/{$deal['id']}")?>" method="post">
												<table class="datagrid noprint" width="88%">
												<tr><Td>Special Margin : </Td><td><input type="text" name="special_margin" class="inp" size=6> in <label><input type="radio" name="type" value=0 checked="checked">%</label> <label><input type="radio" name="type" value=1>Rs</label></td></tr>
												<tr><td>From : </td><td><input type="text" class="inp" name="from" id="sm_from"></td></tr>
												<tr><td>To : </td><td><input type="text" class="inp" name="to" id="sm_to"></td></tr>
												
												<tr><td></td><Td><input type="submit" value="Submit" style="float:right"></Td></tr>
												</table>
											</form>
								  		</div>
								  	</td>
								  	<td>
										<div>
											<h4>Special Margin history</h4>
											<table class="datagrid smallheader" width="88%">
												<thead>
													<tr><Th style="text-align: center;">Margin %</Th><Th style="text-align: center;">Margin Rs</Th><Th style="text-align: center;">Price</Th><th>From</th><th>To</th><th>Assigned on</th><th>Assigned by</th><th></th>
													</tr>
												</thead>
												<?php $spec_margin_his = $this->db->query("select s.*,a.name as admin from pnh_special_margin_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id']);?>
												<tbodY>
													<?php
														if($spec_margin_his->num_rows())
														{ 
														foreach($spec_margin_his->result_array() as $s){?>
															<tr><td style="color: maroon;font-weight: bold"><?=$s['special_margin']?>%</td><td style="color: maroon;font-weight: bold"><?php echo (($deal['price']*$s['special_margin']/100))?> </td><td style="color: green;font-weight: bold;"><?php echo ($deal['price']-($deal['price']*$s['special_margin']/100))?> </td><td><b><?=date("d/m/y",$s['from'])?></b></td><td><b><?=date("d/m/y",$s['to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>
													
															<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
															<?php }}else {?>
																<tr><td style="margin:10px;font-weight: bold">No Data</td></tr>
															<?php } ?>
												</tbodY>
											</table>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<div id="sup_scheme">
							<table width="100%">
								<tr>
									<td width="45%">
										<div>
								  			<h4>Enable/Disable from Super Scheme</h4>
											<form action="<?=site_url("admin/pnh_superscheme_deal/{$deal['id']}")?>" method="post">
												<table class="datagrid noprint" width="88%"> 
													<tr><td>Super Scheme Status :</td>
													<td><select name="super_schstatus">
													<option value="0">Disable</option>
													<option value="1">Enable</option>
													</select></td></tr>
													<tr><td>From : </td><td><input type="text" class="inp" name="from" id="supersch_from"></td></tr>
													<tr><td>To : </td><td><input type="text" class="inp" name="to" id="supersch_to"></td></tr>
													<tr><td>Reason: </td><td><textarea class="inp" name="reason" style="width: 144px; height: 44px;"></textarea></td></tr>
													<tr><td></td><Td><input type="submit" value="Submit" style="float:right"></Td></tr>
												</table>
											</form>
								  		</div>
								  	</td>
								  	<td>
										<div>
											<h4>Super Scheme Log</h4>
											<table class="datagrid smallheader" width="88%">
												<thead>
													<tr>
														<Th style="text-align: center;">Status</Th>
														<Th style="text-align: center;">Valid from</Th>
														<Th style="text-align: center;">Valid from</Th>
														<th>Assigned on</th><th>Assigned by</th>
													</tr>
												</thead>
												<?php $sup_scheme_log = $this->db->query("select s.*,a.name as admin from pnh_superscheme_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id']);?>
												<tbodY>
													<?php 
													if($sup_scheme_log->num_rows())
													{
														foreach($sup_scheme_log->result_array() as $s){?>
															<tr><td style="color: maroon;font-weight: bold"><?=$s['is_active']==0?'Disabled':'Enabled'?></td><td><b><?=date("d/m/y",$s['valid_from'])?></b></td><td><b><?=date("d/m/y",$s['valid_to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>
														<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
														<?php }}else{ ?>
															<tr><td style="margin:10px;font-weight: bold">No Data</td></tr>
														<?php } ?>
												</tbodY>
											</table>
										</div>
									</td>
								</tr>
							</table>
						</div>
				
						<div id="mem_scheme">
							<table width="100%">
								<tr>
									<?php if($deal['menuid']==112){?>
										<td width="45%">
											<div>
									  			<h4>Enable/Disable from Member Scheme</h4>
												<form action="<?=site_url("admin/pnh_memberscheme_deal/{$deal['id']}")?>" method="post">
													<table class="datagrid noprint" width="88%">
														<tr><td>Member Scheme Status :</td>
														<td><select name="mbr_schstatus">
														<option value="0">Disable</option>
														<option value="1">Enable</option>
														</select></td></tr>
														<tr><td>From : </td><td><input type="text" class="inp" name="from" id="mbrsch_from"></td></tr>
														<tr><td>To : </td><td><input type="text" class="inp" name="to" id="mbrsch_to"></td></tr>
														<!--  <tr><td>Reason: </td><td><textarea class="inp" name="reason" style="width: 144px; height: 44px;"></textarea></td></tr>-->
														<tr><td></td><Td><input type="submit" value="Submit" style="float:right"></Td></tr>
													</table>
												</form>
									  		</div>
									  	</td>
									  	<td>
											<div>
												<h4>Member Scheme Log</h4>
												<table class="datagrid smallheader" width="88%">
													<thead>
														<tr>
															<Th style="text-align: center;">Status</Th>
															<Th style="text-align: center;">Valid from</Th>
															<Th style="text-align: center;">Valid from</Th>
															<th>Assigned on</th><th>Assigned by</th>
														</tr>
													</thead>
													<?php $member_scheme_log = $this->db->query("select s.*,a.name as admin from pnh_membersch_deals s join king_admin a on a.id=s.created_by where s.itemid=? order by id desc limit 10",$deal['id']);?>
													<tbodY>
														<?php 
														if($member_scheme_log->num_rows())
														{
														foreach($member_scheme_log->result_array() as $s){?>
														<tr><td style="color: maroon;font-weight: bold"><?=$s['is_active']==0?'Disabled':'Enabled'?></td><td><b><?=date("d/m/y",$s['valid_from'])?></b></td><td><b><?=date("d/m/y",$s['valid_to'])?></b></td><td><?=date("g:ia d/m/y",$s['created_on'])?></td><td><?=$s['admin']?></td>
														<?php if(format_date_ts($s['to'])>=date("d/m/Y")&& $this->erpm->auth(SPECIAL_MARGIN_UPDATE,true)){?><td><?php if($s['is_active']==1){?><a href="<?=site_url("admin/pnh_disable_special_margin/{$s['id']}")?>" class="danger_link">Disable</a><?php }else{?><?php echo "<b>Disabled</b>"; }?><?php }?></td></tr>
														<?php } }else{ ?>
															<tr><td style="margin:10px;font-weight: bold">No Data</td></tr>
														<?php } ?>
													</tbodY>
												</table>
											</div>
										</td>
									<?php }
									else {?>
										<td style="margin:10px;font-weight: bold">Not Available</td>
									<?php } ?>
								</tr>
							</table>
						</div>
					</div>
	       		</div>	
	       		<!-- #Special Margin History Block End -->
       		
       		
	       		<!-- #Product Linked Block start -->
	       		<div id="prod_linked" class="tabcontent">
	       			
	       			
	       			<table width="100%">
	       				<tr>
	       					<td width="50%">
	       						<?php if($linked_prod->num_rows()){ ?>
		       						<h4>Linked Product Groups</h4>
									<table class="datagrid smallheader noprint" width="88%">
										<thead>
											<tr>
												<th>Sno</th><th>ID</th><th>Group Name</th><th>Qty</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($linked_prod->result_array() as $i=>$p){?>
											<tr><td><?=++$i?></td><td><?=$p['group_id']?></td><td><a href="<?=site_url("admin/product_group/{$p['group_id']}")?>"><?=$p['group_name']?></a></td><td><?=$p['qty']?></td></tr>
											<?php }?>
										</tbody>
									</table>
								<?php }
								if($prods){ ?>
									<h4>Linked Products</h4>
									<table class="datagrid smallheader noprint"  width="88%">
										<thead>
											<tr><th>Sno</th><th>ID</th><th>Product Name</th><th>Qty</th></tr>
										</thead>
										
										<tbody>
											<?php foreach($prods as $i=>$p){?>
											<tr><td><?=++$i?></td><td><?=$p['product_id']?></td><td><a href="<?=site_url("admin/product/{$p['product_id']}")?>"><?=$p['product_name']?></a></td><td><?=$p['qty']?></td></tr>
											<?php }?>
										</tbody>
									</table>
								<?php } ?>
	       					</td>
	       					<td width="50%">
	       						<h4>Product Link Update Log</h4>
	       						<?php 
	       						if($pnh_Deal_upd_log)
									{
	       						 ?>
					       		<table class="datagrid smallheader noprint" width="88%">
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
									<?php 
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
										
									<?php }?>
									</tbody>
								</table>
								<?php } else{?>
									<table class="datagrid smallheader noprint" width="88%">
										<tbody>
											<tr><td width="100%" style="margin:10px;font-weight: bold;">No Data</td></tr>
										</tbody>
									</table><?php } ?>
	       					</td>
	       				</tr>
	       			</table>
	       		</div>	
	       		<!-- #Product Linked Block End -->
       		
       		
	       		<!-- #Recent Orders Block start -->
	       		<div id="recent" class="tabcontent">
					<div id="recent_orders_list"></div>
	       		</div>	
	       		<!-- #Recent Orders Block End -->
       		
       		
	       		<!-- #Product Link Update Log Block start -->
	       		<div id="prod_update_log" class="tabcontent">
	       			
	       		</div>	
	       		<!-- #Product Link Update Log Block End -->
	       		
       		<!-- #Sale anaylics Block start -->
       		<div id="analytics" class="tabcontent">
       			<table width="100%">
					<tr>
						<td width="100%">
							<h4>Sales Analytics</h4>
							<div style="width:100%;float:left;border:1px solid #ccc;">
								<table>
									<tr>
										<td width="37%">
											<div class="deal_sales_left_wrap">
												<span><b>Total Amount of Sales :</b> <?=round($this->db->query("select sum(i_orgprice*quantity) as amt from king_orders  where itemid = '".$item_id."' and status != 3 ")->row()->amt);?></span>
												<span><b>Till Date Quantity Sold :</b> <?=round($this->db->query("select sum(quantity) as qty from king_orders  where itemid = '".$item_id."' and status != 3")->row()->qty);?></span>
												<span><b>Min Selling Price :</b> <?=round($this->db->query("select min(i_orgprice-(i_discount+i_coup_discount)) as min_disc_price 
																					from king_orders where itemid = '".$item_id."' and status != 3 ")->row()->min_disc_price) ?></span>
												<span><b>Max Selling Price :</b> <?=round($this->db->query("select max(i_orgprice-(i_discount+i_coup_discount)) as max_disc_price 
																				from king_orders where itemid = '".$item_id."' and status != 3 ")->row()->max_disc_price) ?></span>
											</div>
									
											<div id="tab_deal_sales" style="clear: both;">
												<ul>
													<li><a href="#most_sold">Most Sold</a></li>
													<li><a href="#last_sold">Latest Sold</a></li>
													<li><a href="#all_sales">All Franchises<span class="badge green"><?=count($ttl_fran)?></span></a></li>
												</ul>
												
												<div id="most_sold">
													<table class="datagrid" width="100%">
														<tbody>
															<tr>
																<th>Name</th><th>Qty</th><th>Transaction Id</th>
															</tr>
															
															<?php
															foreach($most_sold_fran as $most)
															{
															?>
															<tr>
																<td width="250px"><a target="_blank" href="<?=site_url('admin/pnh_franchise/'.$most['franchise_id'])?>"><?=$most['franchise_name']?></a></td>
																<td><?=$most['sold_qty']?></td>
																<td><a target="_blank" style="color:blue" href="<?=site_url("admin/trans/{$most['transid']}")?>" class="link"><?=$most['transid']?></a></td>
															</tr>
															<?php 
															}								
															?>
														</tbody>
													</table>
												</div>
												<div id="last_sold">
													<table class="datagrid" width="100%">
														<tbody>
															<tr>
																<th>Name</th><th>Qty</th><th>Transaction Id</th><th>Date</th>
															</tr>
															<?php
															foreach($latest_fran as $l)
															{
															?>
															<tr>
																<td width="250px"><a target="_blank" href="<?=site_url('admin/pnh_franchise/'.$l['franchise_id'])?>"><?=$l['franchise_name']?></a></td>
																<td><?=$l['sold_qty']?></td>
																<td><a target="_blank" style="color:blue" href="<?=site_url("admin/trans/{$l['transid']}")?>"><?=$l['transid']?></a></td>
																<td><?=date($l['date'])?></td>
															</tr>
															<?php 
															}								
															?>
														</tbody>
													</table>
												</div>
										
												<div id="all_sales">
													<table class="datagrid" width="100%">
														<tbody>
															<tr>
																<th>Name</th><th>Qty</th><th>Value</th>
															</tr>
															
															<?php
															foreach($ttl_fran as $f)
															{
															?>
															<tr>
																<td width="250px"><a target="_blank" href="<?=site_url('admin/pnh_franchise/'.$f['franchise_id'])?>"><?=$f['franchise_name']?></a></td>
																<td><?=$f['sold_qty']?></td>
																<td><?=round($f['ttl_sales_value'])?></td>
															</tr>
															<?php 
															}								
															?>
														</tbody>
													</table>
												</div>
											</div>
										</td>
										
										<td>
											<div>
												<?php $deal_yr=$this->db->query("select year(date(from_unixtime(b.init))) as year
																			from king_orders a 
																			join king_transactions b on b.transid = a.transid
																			where itemid ='".$item_id."' and a.status != 3 group by year")->result_array();?> 	
													<select name="deal_by_year" style="float:right;margin: 5px" >
														<?php
															foreach($deal_yr as $dr)
															{
														?>
														<option value="<?=$dr['year']?>"><?=$dr['year']?></option>
														<?php
															}
														?>
													</select>
													
													<div id="deal_stat">
														<div class="deal_grph_view">
														</div>
													</div>
													
													<div class="franch_list">
														
													</div>
													
													<div id="deal_stat_terr">
														<div class="deal_piestat_view">
														</div>
													</div>
												</div>
											</td>
										</tr>
									</table>	
								</div>
							</td>	
						</tr>	
					</table>
	       		</div><!-- #Sale anaylics Block End -->	
	       	</div> 		
		 </div>      		
	</div>	       			
</div>	       			
</div>
<style>
	#description {padding:10px;background: #fcfcfc;max-height: 200px;overflow: hidden}
	#description table{background: #FFF;font-size: 11px;width: 100%;}
	#description table th{background: #f8f8f8;color:#555}
	#description table td{font-weight: normal}
	.pagination a{background: #cdcdcd;color:#555;}
	.leftcont{display: none;}
</style>

<script>

$(document).ready(function() 
{
	$('.franch_list').html("");
	$(".tabcontent").hide();
	$(".tabcontent:first").show(); 
	
	$('.sales_anl').click(function(){
			var deal_yr = $('select[name="deal_by_year"]').val();
			deal_sale_stat(deal_yr);
	});

	
	$("ul.tabs li").click(function() 
	{
		$("ul.tabs li").removeClass("active");
		$(this).addClass("active");
		$(".tabcontent").hide();
		var activeTab = $(this).attr("rel"); 
		$("#"+activeTab).fadeIn(); 
	});
});

$('#analytics').click(function(){
	$('select[name="deal_by_year"]').change(function(){
		var deal_yr = $('select[name="deal_by_year"]').val();
		deal_sale_stat(deal_yr);
	});
});

var itemid = "<?php echo $this->uri->segment(3);?>";

$(function()
{
	
	$('#tab_deal_sales').tabs();
	$('#margin_his').tabs();

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


function deal_sale_stat(deal_yr)
{
	var yr = $('select[name="deal_by_year"]').val();
	$.getJSON(site_url+'/admin/jx_deal_getsales/'+yr+'/'+itemid,'',function(resp){
		
		if(resp.status == 'error')
		{
			alert(resp.message);	
		}
		else
		{
			// reformat data ;
			$('#deal_stat .deal_grph_view').empty();
			plot2 = $.jqplot('deal_stat .deal_grph_view', [resp.summary.ttl], {
		       	pointLabels: 
		       	{
		           show: true
		        },
		       
		       	series:
		       	[
		       	
		       			{highlighter: {formatString: "Deal<br />Month : %d<br />Quantity : %d"},label:'Deal'}
		      	
		      	],
		       
		       	legend: 
		       	{
	                show: true,
	                location: 't',
	                placement: 'inside',
	                top:'0'
	            },
	            
	            highlighter: {
			        show: true,
			        sizeAdjust: 7.5
			    },
			    cursor: 
			    {
			        show: false
			    },
			      
		        axes: 
		        {
		           xaxis: 
		           {
		                pad: 0,
		                // a factor multiplied by the data range on the axis to give the            
		                renderer: $.jqplot.CategoryAxisRenderer,
		                // renderer to use to draw the axis,     
			                  ticks: resp.month
			            },
			            
			            yaxis: 
			            {
			
			            }
			        }
			    });
 
			}
		});
		
		$.getJSON(site_url+'/admin/jx_deal_getsales_by_territory/'+itemid,'',function(resp){
			
		if(resp.status == 'error')
		{
			alert(resp.message);	
		}
		else
		{
			// reformat data ;
			$('#deal_stat_terr .deal_piestat_view').empty();
			var resp = resp.result;
			plot3 = jQuery.jqplot('deal_stat_terr .deal_piestat_view', [resp], 
			{
				seriesDefaults:{
		            renderer: jQuery.jqplot.PieRenderer,
		            pointLabels: { show: true },
	                rendererOptions: {
	                    // Put data labels on the pie slices.
	                    // By default, labels show the percentage of the slice.
	                    showDataLabels: true,
	                  }
		        },
		        highlighter: {
				    show: true,
				    useAxesFormatters: false, // must be false for piechart   
				    tooltipLocation: 's',
				    formatString:'Territory : %s <br />Quantity : %P',
				},
				grid: {borderWidth:0, shadow:false,background:'#ccc'},
		        legend:{show:true}
		        
		    });
		    	
		    $('#deal_stat_terr .deal_piestat_view').bind('jqplotDataClick', function(ev,seriesIndex,pointIndex,data) {
		    	var territory_id = resp[pointIndex][2];
		    	//alert(territory_id);
		    	$.getJSON(site_url+'/admin/jx_deal_getfranchise_by_territory/'+territory_id+'/'+itemid,'',function(resp)
		    	{
		    		var fran_html='';
		    		if(resp.status == 'error')
					{
						alert(resp.message);	
					}
					else
					{
						fran_html+="<b style='float:left;margin-bottom:10px'>List of Franchise</b>";
						fran_html+='<table class="datagrid" width="100%"><tr><th style="font-size: 10px;line-height: 5px;padding: 7px 8px !important;">Sl.No</th><th style="font-size: 10px;line-height: 5px;padding: 7px 8px !important;">Franchise Name</th></tr>';
						var k=1;
						$.each(resp.fran_list,function(i,f){
							fran_html+='<tr><td style="font-size: 10px;line-height: 5px;padding: 7px 8px !important;">'+(k++)+'</td><td style="font-size: 10px;line-height: 5px;padding: 7px 8px !important;"><a href="<?php echo site_url("admin/pnh_franchise/'+f.franchise_id+'") ?>">'+f.franchise_name+'</td></tr>';
						});
						fran_html+='</table>';
						$('.franch_list').html(fran_html);
						}	
			    	});
				});
 
			}
		});
}




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
