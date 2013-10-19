<div class="container">

<div id="barcode_floater" style="display:none;margin:5px 0px;position:fixed;right:0px;bottom:40px;background:#F7EFB9;padding:15px;border:1px solid #aaa;">Highlight product of barcode : <input type="text" id="srch_barcode"></div> 

<div id="loading_bar">
Loading...
</div>

<div class="v_disp_container">
	<div class="v_disp">
	</div>
	<input type="button" value="Choose vendor" id="choosevendor">
</div>

<h2>Purchase Order</h2>
<form method="post" id="purchaseordefrm" autocomplete="off">
<input type="hidden" id="vendoridhidden" name="vendor">
Select Vendor : <select id="vendorsel">
<option value="0">select</option>
<?php foreach($vendors as $v){?>
<option value="<?=$v['vendor_id']?>"><?=$v['vendor_name']?></option>
<?php }?>
</select>

<div class="clear">	</div>

<div class="clear"></div>

<div class="showaftvendor">
<h4>Purchase Products</h4>
<table border=1 width="100%" cellpadding=5 id="pprods" class="datagrid">
<thead>
<tr>

<th>Sno</th>
<th>Product</th>
<th>Qty</th>
<th>Last 90 days order</th>
<th>MRP</th>
<th style="display: none;">margin</th>
<th>Scheme discount</th>
<th>Discount Type</th>
<th>Purchase Unit price</th>
<th>Total Purchase price</th>
<th>FOC</th>
<th>Has Offer</th>
<th colspan=2>Note</th>
</tr>
</thead>
<tbody>
</tbody>
</table>


<div style="padding:5px;">

<div style="float:right">
<span id="load_brands"></span>
<input type="button" value="Show & load" id="sl_show">
<input type="button" value="Load Unavailable Products" id="load_unavail">
</div>
Search &amp; Add : <input type="text" class="inp" id="po_search" style="width:400px;">
<div id="po_prod_list" class="closeonclick">
</div>
</div>


</div>

<div>
<h3>Total PO Value : Rs <span id="total_po_value">0</span></h3>
</div>


<h4>Purchase Order Details</h4>
<table>
<tr>
<td>Expected Date of Delivery</td>
<td>
	<input type="text" size="8" name="e_dod" class="datetimepick" value="<?php echo date('Y-m-d') ?>" >
		
	<select name="e_dod_h">
		<?php 
			for($d=8;$d<22;$d++){
		?>
				<option value="<?php echo $d ?>"> <?php echo ($d>12?($d-12).':00 PM':$d.':00 AM'); ?></option>
		<?php 
			}	 
		?>
	</select>
</td>
</tr>
<tr>
<td>Remarks</td>
<td><textarea name="remarks"></textarea></td>
</tr>
</table>


<div style="margin-top:10px;">
<input type="button" onclick="submit_frm=1;$('#purchaseordefrm').trigger('submit');" value="Create purchase order" style="font-size:140%">
</div>

</form>
</div>

 
<div style="display:none">
<table id="sl_prod_template">
<tbody>
<tr><td><input type="checkbox" class="sl_sel_prod" value="%pid%"><input type="hidden" class="pid" value="%pid%"></td><td class="psrcstat"><span class="src_stat">%prod_source_stat%</span> <a href=javascript:void(0)" prod_id="%pid%" onclick="upd_prdsourceablestat(this)" nsrc="%prod_source_stat_val%" >Change</a> </td><td>%pid%</td><td class="name" style="width: 400px;text-align: left;"><a  target="_blank" href="<?php echo site_url('admin/product/%pid%')?>">%product%</a></td><td>Rs <span class="mrp">%mrp%</span></td><td class="margin" style="display: none;" >%margin%</td><td>%stock%</td><td ><input type="text" class="i_po_qty" style="width: 40px;" value="%i_po_qty_val%"></td><td class="orders">%orders%</td></tr>
</tbody>
</table>
<table id="p_clone_template">
<tr class="barcode--barcode-- barcodereset rec_type">
<td>%sno%</td>

<td><input type="hidden" name="product[]" value="product_id">
	<a target="_blank" href="<?php echo site_url('admin/product/');?>/product_id">product_name</a></td>
<td><input type="text" class="qty inp" size="2" name="qty[]" value="%qty%"></td>
<td>%orders%</td>
<td><input type="text" class="mrp inp" size="8" name="mrp[]" value="mrpvalue"></td>
<td style="display:none"><input type="text" class="margin inp readonly" size="6" readonly="readonly" name="margin[]" value="%margin%"></td>
<td><input type="text" class="sdiscount inp" size="6" name="sch_discount[]" value="0"></td>
<td><select class="stype" name="sch_type[]">
<option value="1">percent</option>
<option value="2">value</option>
</select></td>
<td><input type="text" class="inp pprice readonly" size=4 readonly="readonly" name="price[]" value=""></td>
<td><input type="text" class="inp tpprice readonly" size=4 readonly="readonly" name="tprice[]" value=""></td>
<td><input type="checkbox" class="inp" name="%foc%" value="1"></td>
<td><input type="checkbox" class="inp" name="%offer%" value="1"></td>
<td><input type="text" class="inp" name="note[]" value=""></td>
<td><a href="javascript:void(0)" onclick='$(this).parent().parent().remove();'>remove</a></td>
</tr>
</table>


</div>


<div id="sl_products" title="Choose and add to current order">
	<span style="float: right">
			<b>Show</b> :
			<select name="stk_prod_disp">
				<option value="1">All Products</option>
				<option value="2">Orders With no Stock</option>
			</select> 
	</span>
	<br><br>
	<div class="datagrid_cont">
		<table class="datagrid datagridsort" width="100%">
			<thead>
				<tr><th><input type="checkbox" class="chk_all"></th><th>Source</th><th>Product ID</th><th>Product</th><th>Mrp</th><th style="display: none;">Margin</th><th>Stock</th><th>PO Qty</th><th>Orders[90 Days]</th></tr>
			</thead>
			<tbody></tbody>
		</table>
		<div>
		<br><br>
</div>
	</div>
	
 <form id="src_form" action="<?=site_url("admin/jx_mark_src_products")?>" method="post">
<input type="hidden" name="pids" class="pids">
<input type="hidden" name="action" class="action" value="1">
</form>

<form id="unsrc_form" action="<?=site_url("admin/jx_mark_unsrc_products")?>" method="post">
<input type="hidden" name="pids" class="pids">
<input type="hidden" name="action" class="action" value="1">
</form>
</div>

<style>
.src{
	background:#afa;
}
.nsrc{
	background:#faa;
}
#sl_products td {text-align: center;}

.psrcstat a{font-size: 10px;color: blue;}
</style>

<div id="addvenbrandfrm_dlg" title="Add Brand">
	 
		<table width="100%">
			<tr>
				<td><b>Brand</b></td>
				<td><select style="width: 200px;" name="newbrand" data-placeholder="Choose"></select></td>
			</tr>
			<tr>
				<td><b>Margin</b></td>
				<td><input type="text" size="10" value="0" name="newbrandmarg"></td>
			</tr>
		</table>
	 
</div>

<script>
$(".chk_all").click(function(){
	if($(this).attr("checked"))
		$(".sl_sel_prod").attr("checked",true);
	else
		$(".sl_sel_prod").attr("checked",false);
});



$('.datetimepick').datepicker({minDate:new Date()});
$('#addvenbrandfrm_dlg select[name="newbrand"]').chosen();

$('#addvenbrandfrm_dlg').dialog({
									autoOpen:false,
									width:400,
									height:500,
									autoResize:true,
									modal:true,
									open:function(){
										var dlgEle = $(this);
											$('#addvenbrandfrm_dlg select[name="newbrand"]').html("").trigger("liszt:updated");
											$.post(site_url+'/admin/jx_ven_unavail_brands','ven_id='+$('#vendorsel').val(),function(resp){
												if(resp.status == 'error')
												{
													alert("No brands found");
													dlgEle.dialog('close');	
												}else
												{
													var selOpts = '<option value="">Choose</option>';
														$.each(resp.brandlist,function(a,b){
															selOpts += '<option value="'+b.brandid+'">'+b.brandname+'</option>';
														});
														$('#addvenbrandfrm_dlg select[name="newbrand"]').html(selOpts).trigger("liszt:updated");
												}
											},'json');
									},
									buttons:{
										'Submit': function(){
											var newbrandname = $('#addvenbrandfrm_dlg select[name="newbrand"] option:selected').text();
											var newbrandid = $('#addvenbrandfrm_dlg select[name="newbrand"]').val();
											var newbrandmarg = $('#addvenbrandfrm_dlg input[name="newbrandmarg"]').val();
												if(isNaN(newbrandmarg*1))
												{
													alert("Invalid Margin Entered");
												}else
												{
													$.post(site_url+'/admin/jx_upd_venbrandlink','bid='+newbrandid+'&bmarg='+(newbrandmarg*1)+'&ven_id='+$('#vendorsel').val(),function(resp){
														if(resp.status == 'error')
														{
															alert(resp.error);
														}else
														{
															alert("Brand Linked");
															if($('#sl_sel_brand').length)
															{
																var selOpts = '';
																	$.each(resp.linked_brands,function(a,b){
																		selOpts += '<option value="'+b.brandid+'">'+b.brandname+'</option>';
																	});
																$('#sl_sel_brand').html(selOpts);
															}
															$('#addvenbrandfrm_dlg').dialog('close');
															$('.overview_ven_brandlist').append(newbrandname+', ');
														}
													},'json');
												}
										},
										'Cancel':function(){
										
										}
									}
								});							
function show_addvenbranddlg()
{
	$('#addvenbrandfrm_dlg').dialog('open');
}

var submit_frm = 0; 
$("#load_unavail").click(function(){
	$(this).attr("disabled",true);
	$.post("<?=site_url("admin/jx_load_unavail_products")?>",{hash:<?=time()?>,vid:$("#vendoridhidden").val()},function(data){
		os=$.parseJSON(data); 
		$.each(os,function(i,o){
			var po_qty = 0;
			if((o.available-o.qty) < 0)
				po_qty = (o.qty-o.available);
			else
				po_qty = 0;
				
			addproduct(o.product_id, o.product_name, o.mrp,o.brand_margin,0,po_qty);
		});
 	});
}).attr("disabled",false);

$('select[name="stk_prod_disp"]').change(function(){
	var type = $(this).val();
	if(type == 1)
		$('#sl_products .datagrid tbody tr').show();
	else 
		$('#sl_products .datagrid tbody tr').hide();
	
		if(type == 2)
			$('#sl_products .datagrid tbody tr.ORDERNOSTOCK').show();
			else if(type == 3)
				$('#sl_products .datagrid tbody tr.ORDERSTOCK').show();
				else if(type == 4)
					$('#sl_products .datagrid tbody tr.STOCKNOORDER').show();
					else if(type == 5)
						$('#sl_products .datagrid tbody tr.NOSTOCKNOORDER').show();
});

var added_po=[];

function addproduct(id,name,mrp,margin,orders,qty)
{
	if($.inArray(id,added_po)!=-1)
	{
		alert("Product already added to the current Order");
		return;
	}
	$.post("<?=site_url("admin/jx_productdetails")?>",{id:id},function(data){
		o=$.parseJSON(data);
		i=added_po.length;
		$("#po_prod_list").hide();
		template=$("#p_clone_template tbody").html();
		template=template.replace("product_id",id);
		template=template.replace("product_id",id);
		template=template.replace("product_name",name);
		template=template.replace("mrpvalue",mrp);
		template=template.replace(/%sno%/g,i+1);
		template=template.replace(/%margin%/g,margin);
		template=template.replace(/%foc%/g,"foc"+i);
		
		template=template.replace(/%qty%/g,qty);
		
		template=template.replace(/%orders%/g,o.orders);
		template=template.replace(/%offer%/g,"offer"+i);
		template=template.replace(/--barcode--/g,o.barcode);
		$("#pprods tbody").append(template);
		added_po.push(id);

		$("#pprods tbody tr .qty").trigger('change');
		
	});
}


function calc_total_pov()
{
	total=0;
	$("#pprods .sdiscount").each(function(i,o){
		$p=$(o).parents("tr").get(0);
		qty=parseInt($(".qty",$p).val());
		mrp=parseInt($(".mrp",$p).val());
		stype=parseInt($(".stype",$p).val());
		sdiscount=parseFloat($(".sdiscount",$p).val());
		if(isNaN(sdiscount))
		{
			sdiscount=0;
			$(".sdiscount",$p).val("0");
		}
		margin=parseInt($(".margin",$p).val());
		price=mrp-(mrp*margin/100);
		if(stype==1)
			price=price-(mrp*sdiscount/100);
		else
			price=price-sdiscount;
		total+=(price*qty);
	});
	$("#total_po_value").html(Math.round(total,2));
}

$(function(){

	$("#srch_barcode").keyup(function(e){
		if(e.which==13)
		{
			$(".barcodereset").removeClass("highlightprow");
			if($(".barcode"+$(this).val()).length==0)
			{
				alert("Product not found on rising PO");
				return;
			}
			$(".barcode"+$(this).val()).addClass("highlightprow");
			$(document).scrollTop($(".barcode"+$(this).val()).offset().top);
		}
	});

	$("#pprods .sdiscount, #pprods .stype, #pprods .qty").live("change",function(){
		$p=$(this).parents("tr").get(0);
		qty=parseInt($(".qty",$p).val());
		mrp=parseInt($(".mrp",$p).val());
		stype=parseInt($(".stype",$p).val());
		sdiscount=parseFloat($(".sdiscount",$p).val());
		if(isNaN(sdiscount))
		{
			sdiscount=0;
			$(".sdiscount",$p).val("0");
		}
		margin=parseInt($(".margin",$p).val());
		price=mrp-(mrp*margin/100);
		if(stype==1)
			price=price-(mrp*sdiscount/100);
		else
			price=price-sdiscount;
		$(".pprice",$p).val(price);
		$(".tpprice",$p).val(price*qty);
		calc_total_pov();
	});
	


	$("#po_search").keyup(function(e){
		e.preventDefault();
		q=$(this).val();
		if(q.length<3)
			return true;
		if(jHR!=0)
			jHR.abort();
		window.clearTimeout(search_timer);
		search_timer=window.setTimeout(function(){
		jHR=$.post("<?=site_url("admin/getvendorproducts")?>",{q:q,v:$("#vendoridhidden").val()},function(data){
			$("#po_prod_list").html(data).show();
		});
		},200);
	}).focus(function(){
		if($("#po_prod_list a").length==0)
			return;
		$("#po_prod_list").show();
	}).click(function(e){
		e.stopPropagation();
	});

	$("#purchaseordefrm").submit(function(){
		if(submit_frm)
		{
			if($("#pprods tbody tr").length==0)
			{
				alert("Add products please");
				return false;
			}
			return true;
		}
		return false;
		
	});
	
	$("#vendorsel").change(function(){
		showvendor($(this).val());
	}).val("0").attr("disabled",false);
	$("#choosevendor").click(function(){
		$(".showaftvendor,#barcode_floater").show();
		$("#vendorsel,#choosevendor").attr("disabled",true);
		$("#vendoridhidden").val($("#vendorsel").val());
		$.post("<?=site_url("admin/jx_getbrandsforvendor_json")?>",{vid:$("#vendorsel").val()},function(json){
			data=$.parseJSON(json);
			str='<select id="sl_sel_brand">';
			$.each(data,function(i,v){
				str=str+'<option value="'+v.id+'">'+v.name+'</option>';
			});
			str=str+'</select>';
			$("#load_brands").html(str);
		});
	}).attr("disabled",false);
	$("#sl_show").click(function(){
		$("#sl_products").dialog('open');
	});
});
var brand_prods=[];
var search_timer=0,jHR=0;
function showvendor(vid)
{
	$(".v_disp_container").show();
	$(".v_disp").html("LOADING>>>>");
	$.post("<?=site_url("admin/jx_show_vendor_details")?>",{v:vid},function(data){
		$(".v_disp").html(data);
	});
}

function loadbrandproducts()
{
	$(".sl_sel_prod:checked").each(function(){
		tr=$($(this).parents("tr").get(0));
		addproduct($(".pid",tr).val(),$(".name",tr).html(),$(".mrp",tr).html(),$(".margin",tr).html(),$(".orders",tr).html(),$(".i_po_qty",tr).val());
	});
	$("#sl_products .datagrid tbody").html("");
	$("#sl_products").dialog('close');
}

$("#sl_products").dialog({
							width:973,
							resizable:true,
							modal:true,
							height:550,
							autoOpen:false,
							open: function(event, ui) {
						        $(event.target).dialog('widget')
					            .css({ position: 'fixed' })
					            .position({ my: 'center', at: 'center', of: window });
					            
										$("#sl_products .datagrid tbody").html("<tr><td colspan='8' align='left'>Loading .....</td></tr>");
										bid=$("#load_brands select").val();
										vid=$("#vendoridhidden").val();
										$("#loading_bar").show();
										$.post(site_url+'/admin/jx_getproductsforbrand',{bid:bid,vid:vid},function(json){
											$("#loading_bar").hide();
											data=$.parseJSON(json);
											brand_prods=data;
											$("#sl_products .datagrid tbody").html("");
											if(data.length)
											{
											$.each(data,function(i,p){
													template=$("#sl_prod_template tbody").html();
													template=template.replace(/%id%/g,i);
													template=template.replace(/%pid%/g,p.id);
													template=template.replace(/%product%/g,p.product);
													template=template.replace(/%stock%/g,p.stock);
													template=template.replace(/%margin%/g,p.margin);
													template=template.replace(/%mrp%/g,p.mrp);
													template=template.replace(/%orders%/g,p.orders);
													template=template.replace(/%prod_source_stat%/g,((p.src==1)?'Yes':'No'));
													template=template.replace(/%prod_source_stat_val%/g,p.src);
													var po_ord_qty = 0;
													if(p.pen_ord_qty > p.stock)
														po_ord_qty = p.pen_ord_qty-p.stock;
													else if(p.pen_ord_qty)
														po_ord_qty = 0; 
														
													template=template.replace(/%i_po_qty_val%/g,po_ord_qty);
													$("#sl_products .datagrid tbody").append(template);
													if(p.src==1)
														$("#sl_products .datagrid tbody tr:last").addClass("src");
													else
														$("#sl_products .datagrid tbody tr:last").addClass("nsrc");
													
														
													
													rec_type = '';
										
													if(p.stock && p.orders)
														rec_type += ' ORDERSTOCK ';
													else if(!p.stock && p.orders)
															rec_type += ' ORDERNOSTOCK ';
														else if(p.stock && !p.orders)
															rec_type += ' STOCKNOORDER ';
															else if(!p.stock && !p.orders)
																rec_type += ' NOSTOCKNOORDER ';
										
													$("#sl_products .datagrid tbody tr:last").addClass(rec_type);
													
												});
											$("#sl_products .datagrid").trigger("update");
											$("table").trigger("sorton",[[[1,0]]]); 
											}
											else
											{
												$("#sl_products .datagrid tbody").html("<tr><td colspan='8' align='left'>No products found</td></tr>");
											}
											
										});
							},
							buttons:{
								'Load Selected Products' : function(){
																loadbrandproducts();	
														},

								'Mark as Sourcable' :function()
								{
								
										var dlg = $(this);
										var pids_arr=[];
										$(".sl_sel_prod:checked").each(function(){
											if($(".sl_sel_prod:checked").length)
											{
												pids_arr.push($(this).val());
											}
										});
										pids=pids_arr.join(",");
										$('#src_form input[name="pids"]').val(pids);
										var frm_checksubmit = $("#src_form",this);
										 if($(".sl_sel_prod:checked").length!=0){
											 $.post(site_url+'/admin/jx_mark_src_products',{pids:pids},function(resp){
										            $("#sl_products").dialog('close');
							                      	$("#sl_products").dialog('open');
							                });
							            }
							          
							   
								},

								'Mark as not Sourcable':function()
								{ 
									var dlg = $(this);
									var pids=[];
									$(".sl_sel_prod:checked").each(function(){
										pids.push($(this).val());
										
									});
									pids=pids.join(",");
									$('#unsrc_form input[name="pids"]').val(pids);
									var frm_checksubmit = $("#unsrc_form",this);
									 if($(".sl_sel_prod:checked").length!=0){
										 $.post(site_url+'/admin/jx_mark_unsrc_products',{pids:pids},function(resp){

									            $("#sl_products").dialog('close');
						                      	$("#sl_products").dialog('open');
										 
											 
								                   
						                },'json');
						            }
								},
								'Close':function(){
										$("#sl_products").dialog('close');
								}
							}
						});
$("#sl_products .datagrid").tablesorter({sortList: [[1,0]]});


function upd_prdsourceablestat(ele)
{
	nsrc = $(ele).attr('nsrc');

	prod_id = $(ele).attr('prod_id');
	
	if(confirm("Are you sure want to mark this product "+((nsrc==1)?'Not':'')+' Sourceable ?'))
	{

		$.post(site_url+'/admin/jx_upd_prodsrcstatus',{pid:prod_id,stat:nsrc},function(resp){
			if(resp.status)
			{
				var src_disp_ele = $(ele).parent().find('.src_stat');
				
					if(nsrc==1)
					{
						$(ele).parent().parent().removeClass('src').addClass('nsrc');
						src_disp_ele.text("No");	
						$(ele).attr('nsrc',0);
					}else
					{
						$(ele).parent().parent().removeClass('nsrc').addClass('src');
						src_disp_ele.text("Yes"); 
						$(ele).attr('nsrc',1);	
					}	
			}
		},'json');
			
	}
}
 

</script>
<style>
.v_disp_container{
margin-top:10px;
background:#eee;
padding:5px;
display:none;
}
.v_disp{
min-width:400px;
border:1px solid #ffaa00;
background:#F7EFB9;
padding:5px;
}
.showaftvendor
{
display:none;
}
#po_prod_list{
display:none;
position:absolute;
width:500px;
max-height:230px;
overflow:auto;
background:#eee;
border:1px solid #aaa;
}
#po_prod_list a{
display:block;
padding:5px;
}
#po_prod_list a:hover{
background:blue;
color:#fff;
}
/*
#sl_products{
position:absolute;
width:800px;
min-height:300px;
max-height:500px;
overflow:auto;
top:130px;
left:200px;
border:1px solid #aaa;
display:none;
background:#fff;
padding:10px;
}
#sl_products .datagrid_cont{
margin-bottom:10px;
width:780px;
height:420px;
overflow:auto;
}
*/
.highlightprow{
background:#ff9900;
}
#loading_bar{
display:none;
background:#ff9900;
color:#000;
font-size:16px;
padding:5px 10px;
position:fixed;
top:400px;
right:50px;
}
</style>
<?php
