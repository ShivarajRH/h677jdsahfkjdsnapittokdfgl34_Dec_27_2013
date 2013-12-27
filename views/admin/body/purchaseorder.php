<?php $vid=$this->uri->segment(3); if($vid){$vname=$this->db->query("select vendor_name from m_vendor_info where vendor_id=?",$vid)->row()->vendor_name;}?>

<link type="text/css" rel="stylesheet" href="<?php echo base_url();?>css/po_product.css" />
<link type="text/css" rel="stylesheet" href="<?php echo base_url();?>css/purchaseorder.css" />
<div class="container">
 
<div id="loading_bar">
Loading...
</div>
<h2>Purchase Order:Vendorwise</h2>
<form method="post" id="purchaseordefrm"  autocomplete="off">
<input type="hidden" id="vendoridhidden" name="vendor">
<div style="float: right;margin-top:-35px;">
<b>Select Vendor :</b> <select id="vendorsel" width="150px" name="vendorsel" >

<?php if($vid==0){?>
<option value="0">select</option>
<?php foreach($vendors as $v){ ?>
<option value="<?=$v['vendor_id']?>"><?=$v['vendor_name']?></option>
<?php } }else{?>
$selected = set_select('vendorsel',$v['vendor_id'],($vid==$v['vendor_id']));
<?php echo '<option value="'.$vid.'" '.$selected.' >'.$vname.'</option>';	?>				
													
<?php }?>

</select><input type="button" value="Choose vendor" id="choosevendor" disabled="false">
</div>
<div class="block">
<div class="block-heading">
<span class="vendor_det" style="float: left;margin-left: 13px;">Vendor Details</span><span class="show_vdet" style="float: right;margin-right: 13px;">Show</span>
</div> 

<div class="v_disp_container">

<table class="gridtable ">
<tr>
	<td class="span_title_wrap">Contact Details</td>
	<td id="contact_det">
		<div></div>
	</td>
</tr>

<tr>
	<td class="span_title_wrap">Supported Brands</td>
	<td id="brand_det">
		<div></div>
	</td>
</tr>


<tr>
	<td class="span_title_wrap">PO Details</td>
	<td id="po_det">
		<div></div>
	</td>
</tr>

<tr>
	<td class="span_title_wrap">Latest PO</td>
	<td id="latest_po">
		<div></div>
	</td>
</tr>
</table>

</div>
</div>
<div class="clear">	</div>


				
<div class="showaftvendor">
	<div style="padding:5px;">
		<div style="float:right">
			<span id="load_brands"></span>
			<input type="button" value="Show & load" id="sl_show" class="loadbutton small grey">
			<input type="button" class="loadbutton small blue" value="Load Unavailable Products" id="load_unavail">
		</div>
			<input type="text" class="prd_blk inp " id="po_search" placeholder="Search &amp; Add">
		<div id="po_prod_list" class="prd_srch_result closeonclick"></div>
	</div>

	<h4>Purchase Products</h4>
	<table class="datagrid datagridsort" id="pprods" width="100%" cellpadding="8">
	<thead>
		<tr>
			<th width="10px">S.No</th>
			<th width="300px">Product</th>
			<th width="10px"></th>
			<th width="80px">MRP</th>
			<th width="80px">DP Price</th>
			<th width="80px">Margin</th>
			<th width="180px" > Qty & price Details</th>
			<th width="130px">Unit price </th>
			<th width="130px">Sub total</th>
			<th name="tcol1" id="tcol1" class="bold" style="text-align: center;display:none;" >Offer</th>
			<th  width="20px" style="text-align: center">Actions</th> 
		</tr>
	</thead>
		<tbody>
		</tbody>
	</table>
	
	

	

</div>

<div class="show_after_vendorsel">
<h3 style='float:right;margin-right:164px;'>Total PO Value :</b>Rs <span id="total_po_value" >0</span></h3>
</div>


<table class="show_after_vendorsel">
<!--  <h4>Purchase Order Details</h4>-->
<tr>
	<td><b>Date of Delivery</b></td>
	<td><input type="text"  name="e_dod" class="datetimepick" value="" ></td>
</tr>
<tr>
	<td style="text-align:right;"><b>Remarks</b></td>
	<td><textarea name="remarks" value=""></textarea></td>
</tr>
</table>


<div style="margin-top:10px;" class="show_after_vendorsel">
<input type="button" class="loadbutton small green" onclick="submit_frm=1;$('#purchaseordefrm').trigger('submit');" value="Create purchase order" style="font-size:140%;float:right;margin-top:-53px;" >
</div>
</form>
</div>


 
<div style="display:none">
<table id="sl_prod_template">

<tbody>
<tr class="brand_%brandid%" brandid="%brandid%"><td><input type="checkbox" class="sl_sel_prod" value="%pid%"><input type="hidden" class="pid" value="%pid%"></td><td class="psrcstat"><span class="src_stat">%prod_source_stat%</span> <a href=javascript:void(0)" prod_id="%pid%" onclick="upd_prdsourceablestat(this)" nsrc="%prod_source_stat_val%" >Change</a> </td><td>%pid%</td><td class="name" style="width: 400px;text-align: left;"><a  target="_blank" href="<?php echo site_url('admin/product/%pid%')?>">%product%</a></td><td>Rs <span class="mrp">%mrp%</span></td><td class="margin" style="display: none;" >%margin%</td><td>%stock%</td><td ><input type="text" class="i_po_qty" style="width: 40px;" value="%i_po_qty_val%"></td><td class="orders">%orders%</td></tr>
</tbody>
</table>

<div>
<table id="p_clone_template" width="100%">
<tr class="barcode--barcode-- barcodereset rec_type brand_%brandid%" brandid="%brandid%">
<td>%sno%</td>

<td width="200px">
<div>
<input type="hidden" class="product" name="product[]" value="%product_id%">
<a target="_blank"  class="pprod_name" href="<?php echo site_url('admin/product/');?>/%product_id%">product_name</a>&nbsp;<b>(%product_brand%)</b>
</div>
<div>
<b>PNH Product ID:%product_id%</b>
</div>

</td>
<td></td>
<td><input type="text" class="mrp calc_pp inp" size="8" name="mrp[]" value=mrpvalue></td>

<td>
	<div style="visibility: %dp_price_inp% "><input type="text" class="dp_price inp" size="8" name="dp_price[]" value="%dp_price%"></div>
</td>

<td><input type="text" class="margin inp calc_pp readonly" size="8" name="margin[]" value="%margin%"></td>

<td class="qty_price_blk" style="width:150px;font-size: 10px;" >
<div style="margin-bottom: 2px">
<span>Open PO qty : </span>
<b id="is_po_raised_%product_id%" style="font-size: 12px;margin-left: 4px"></b>	
</div>

<div>
	<span>Required qty : </span>
	<span><input type="text" class="inp calc_pp qty" id="prod_qty_%product_id%"  size=4 name="qty[]" value="%require_qty%" style="border:1px solid #FF0000;width:72px;"></span>
</div>

<div>
	<span>Extra Margin :</span>
	<span><input type="text" class="inp calc_pp sdiscount" size=7 name="sch_discount[]" value="0" style="width:72px;border:1px solid #FF0000;"></span>	
</div>

<div>
	<span>Discount type :</span>
	<span>
		<select class="calc_pp stype" name="sch_type[]" style="width:82px;margin-bottom: ">
				<option value="1">percent</option>
				<option value="2">value</option>
			</select>
		</span>	
	</div>
</td>


<td>
<div>
	<div>
		<input type="text" class="inp pprice readonly" readonly="readonly"  name="price[]" value="%pprice%" >
		<span class="marg_prc_preview" style="display:none!important;">
				<input type="text" name="marg_prc" value="" readonly="readonly" class="inp marg_prc readonly">
				%
			</span>
		</div>
		
	</div>
</td>
	<td>
		<div>
			<input type="text" class="inp tpprice readonly"
				readonly="readonly" name="qtyprice[]" value="">
		</div>
	</td>

<td name="tcol1" id="tcol1" class="bold" style="height:104px;display:none">
	<div>
		<span>
		Foc : <input type="checkbox" class="inp" style="top:10px"  name="%foc%" value="1">
		</span>
	</div>

	<div>
		<span>
		Has Offer : <input type="checkbox" class="inp" style="" name="%offer%" value="1">
		</span>
	</div>
</td>

<td><a href="javascript:void(0)" onclick='remove_prod_selection(this)'><img  src="<?php echo base_url().'images/remove.png'?>"></a></td>

</tr>
</table>
</div>

<div id="orderd_productdet" title="Order Details" >
		<form id="orderd_productdet_frm" method="post" data-validate="parsley" action="<?php echo site_url('admin/to_load_orderdprd_details')?>" >
			<input type="hidden" name="po_productid" id="po_productid">
			<table class="datagrid smallheader" width="100%" id="orderd_podet">
				<thead><th align="left">Parter name</th><th align="left">Total Orders</th><th align="left">Last Orderd On</th></thead>
				<tbody>
				
				</tbody>
			</table>
		</form>
</div>
<div id="purchase_productdet" title="Purchase Pattern Details">
		<form id="purchase_productdet_frm" method="post" data-validate="parsley" action="<?php// echo site_url('admin/to_load_purchaseprod_details')?>" >
		<input type="hidden" name="purchase_pid" id="purchase_pid">
			<table class="datagrid smallheader" width="100%" id="last7_pdet">
				<h4>Last 7 days Purchase</h4>
				<thead>
					<th align='left'  width="50%"">Vendor Details</th>
					<th align='left'  width="25%">Quantity</th>
					<th align='left' width="25%" >Margin</th>
				</thead>
				<tbody>
				</tbody>
			</table>
			<table class="datagrid smallheader" width="100%" id="last30_pdet">
				<h4>Last 30 days Purchase</h4>
				<thead>
					<th align='left'  width="50%"">Vendor Details</th>
					<th align='left'  width="25%">Quantity</th>
					<th align='left' width="25%" >Margin</th>
				</thead>
				<tbody>
				</tbody>
			</table>
				
			<table class="datagrid smallheader" width="100%" id="last60_pdet">
				<h4>Last 60 days Purchase</h4>
				<thead>
					<th  align='left'  width="50%"">Vendor Details</th>
					<th align='left' width="25%">Quantity</th>
					<th align='left' width="25%">Margin</th>
				</thead>
				<tbody>
				
				</tbody>
			</table>
			
			<table class="datagrid smallheader" width="100%" id="last90_pdet">
				<h4>Last 90 days Purchase</h4>
				<thead>
					<th align='left'  width="50%"">Vendor Details</th>
					<th align='left' width="25%">Quantity</th>
					<th align='left' width="25%">Margin</th>
				</thead>
				<tbody>
				</tbody>
			</table>
		</form>
	</div>
	
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
	<h3 id="ttl_res">Total Products:</h3>
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


<div style="display:none">
	<div id="dlg_openpolist" title="Open PO List"></div>
</div>

<script>

var pre_selected_vendor_id="<?php echo $vid?>";

function get_unixtimetodate(utime)
{
	var date = new Date(utime * 1000);
	var y=date.getFullYear();
    var m=date.getMonth()+1;
    var d=date.getDate();
    var h=(date.getHours() > 9)?date.getHours()-12:date.getHours();
    var mi=date.getMinutes();
    var s=date.getSeconds();
    var datetime=d+'/'+m+'/'+y;
    return datetime;
}



$(".all_po_chk").live("click", function(){
	if($(this).attr("checked"))
	{
		$(".sl_sel_po").attr("checked",true);
	
	}
	else
	{
		$(".sl_sel_po").attr("checked",false);
		
	}
});
$('.block').hide();

$('.show_after_vendorsel').hide();

$('#sl_sel_brand').chosen();

$('.show_vdet').toggle(function(){
	 $(".block-heading .show_vdet").html("Hide");
	 $(".v_disp_container").slideDown();
},function() {
    $(".v_disp_container").slideUp();
    
    $(".block-heading .show_vdet").html("Show");
    
});



$('#dlg_openpolist').dialog({'width':850,autoOpen:false,'height':500,modal:true,open:function(){
	var pid = $(this).data('pid');
	$('#dlg_openpolist').html("<h3 align='center'>Loading...</h3>");
	$.post(site_url+'/admin/jx_getopenpolistbypid/'+pid,{},function(resp){
		var html = '<h3 class="pname">'+resp.product_name+'</h3>';
			html += '	<div class="pttl">Total open qty : <b>'+resp.ttl_open_qty+'</b></div>';
			html += '	<div id="openpolist_tbl">';
			html += '	<table width="100%" class="datagrid datagridsort" cellpadding="5" cellspacing="0">';
			html += '		<thead>';
			html += '			<th><input type="checkbox" class="all_po_chk"></th><th>Slno</th><th>Remove product in PO</th><th>Vendor</th><th>PO Date</th><th>POID</th><th>Total Qty</th><th>Action</th>';
			html += '		</thead>';
			html += '		<tbody>';
			$.each(resp.vendor_po_list,function(a,b){
				html += '		<tr poid="'+b.po_id+'" productid="'+pid+'"><td><input type="checkbox" class="sl_sel_po" value="'+pid+'" po_id="'+b.po_id+'"></td><td>'+(a*1+1)+'</td><td><a href="javascript:void(0)" onclick="remove_prodfrmpo(this)" poid="'+b.po_id+'" pid="'+pid+'" style="font-size:11px;color:blue;" >Cancel product in PO</a></td><td><a target="_blank" href="'+site_url+'/admin/vendor/'+b.vendor_id+'">'+b.vendor_name+'</a></td><td>'+get_unixtimetodate(b.po_date)+'</td><td>'+b.po_id+'</td><td>'+b.qty+'</td><td><a class="inline_trig" style="color:blue;font-weight:bold" target="_blank" href="'+(site_url+'/admin/viewpo/'+b.po_id)+'" >View</a></td></tr>';	
			});
			html += '		</tbody>';
			html += '	</table>';
			html += '	</div>';
			$('#dlg_openpolist').html(html);
	},'json');
},
buttons:{
	'Cancel product in PO':function()
	{
		var dlg = $(this);
		var poids=[];
		$(".sl_sel_po:checked").each(function(){
			poids.push($(this).attr('po_id'));
			
		});
		poids=poids.join(",");
		var pid=$('.sl_sel_po').val();
		if($(".sl_sel_po:checked").length!=0){
			 $.post(site_url+'/admin/jx_update_poprod_status',{poid:poids,pid:pid},function(resp){
			if(resp.status)
			{
				$("#dlg_openpolist").dialog('close');
           		$("#dlg_openpolist").dialog('open');
          		$("#is_po_raised_"+pid).html('<a href="javascript:void(0)" onclick="load_openpolist('+pid+')" ><b>'+resp.ttl_open_qty+'</b></a>');
   			 
			} 
	                   
            },'json');
        }
	}
}
}).load(function() {
                $(this).dialog("option", "position", ['center', 'center'] );
            });


function load_openpolist(pid)
{
	$('#dlg_openpolist').data('pid',pid).dialog('open');
}
$("#dlg_openpolist .datagrid").tablesorter({sortList: [[2,0]]});

$(".chk_all").click(function(){
	if($(this).attr("checked"))
		$(".sl_sel_prod").attr("checked",true);
	else
		$(".sl_sel_prod").attr("checked",false);
});




$('.datetimepick').datepicker({ minDate: 0 });
	



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
				
			addproduct(o.product_id, o.product_name, o.mrp,o.brand_margin,0,0);
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
function remove_prod_selection(ele)
{
		var trEle = $(ele).parents('tr:first');
		var rmv_prdid = $('input[name="product[]"]',trEle).val();
			trEle.remove();
			
		var po_prod_sel = [];
			for(var i in added_po)
			{
				if(rmv_prdid != added_po[i])
					po_prod_sel.push(added_po[i]);
			}
			added_po = po_prod_sel;
			
			$('#pprods tbody tr').each(function(i,ele){
				$('td:first',this).text(i*1+1);
			});
			
}



function addproduct(id,name,mrp,margin,orders,qty,require)
{
	selected_vendorid=$("#vendorsel").val();

	require = (typeof require === "undefined") ? "" : require;
	
	if($.inArray(id,added_po)!=-1)
	{
		alert("Product already added to the current Order");
		return;
	}
	$.post("<?=site_url("admin/jx_productdetails")?>",{id:id,vid:selected_vendorid},function(data){
		o=$.parseJSON(data);
		i=added_po.length;
		$("#po_prod_list").hide();
		template=$("#p_clone_template tbody").html();
		template=template.replace("product_name",name);
		template=template.replace("mrpvalue",mrp);
		template=template.replace(/%sno%/g,i+1);
		template=template.replace(/%product_id%/g,o.product_id);
		template=template.replace(/%require_qty%/g,require?require:o.require_qty);
		margin = 0;
		template=template.replace(/%margin%/g,o.margin);
		template=template.replace(/%foc%/g,"foc"+i);
		template=template.replace(/%dp_price%/g,o.dp_price);
		
		if(o.is_serial_required*1)
			template=template.replace(/%dp_price_inp%/g,'visible');
		else
			template=template.replace(/%dp_price_inp%/g,'hidden');
		
		template=template.replace(/%qty%/g,qty?qty:o.require_qty);
		template=template.replace(/%orders%/g,o.orders);
		template=template.replace(/%offer%/g,"offer"+i);
		template=template.replace(/--barcode--/g,o.barcode);
		template=template.replace(/%brandid%/g,o.brand_id);
		template=template.replace(/%product_brand%/g,o.brand_name);
		
		$("#pprods tbody").append(template);
		var ttl_openpo = 0;
		if(o.is_po_raised != null)
		{
			is_po_raised_html = "";
			$.each(o.is_po_raised,function(i,p){
				if(p.po_order_qty<0)
					p.po_order_qty=0;
					//is_po_raised_html+="<div><ul><a href='"+site_url+'/admin/viewpo/'+p.po_id+"' target='_blank'>"+p.po_id+"</a>"+'-'+'<input type="text" readonly="readonly" style="background-color: #F1F1F1" size="2" value='+p.po_order_qty+' ></ul></div>';
					ttl_openpo += p.po_order_qty*1;
				});
				
		}
		$("#is_po_raised_"+o.product_id).html('<a href="javascript:void(0)" onclick="load_openpolist('+o.product_id+')" ><b>'+ttl_openpo+'</b></a>');
			
		added_po.push(id);

		$("#pprods tbody tr .qty").trigger('change');
		
	});

	$('.show_after_vendorsel').show();
}


function calc_total_pov()
{
	total=0;
	$("#pprods .sdiscount").each(function(i,o){
		$p=$(o).parents("tr").get(0);
		qty=parseFloat($(".qty",$p).val());
		mrp=parseFloat($(".mrp",$p).val());
		dp_price=parseFloat($(".dp_price",$p).val());
		stype=parseFloat($(".stype",$p).val());
		sdiscount=parseFloat($(".sdiscount",$p).val());
		if(isNaN(sdiscount))
		{
			sdiscount=0;
			$(".sdiscount",$p).val("0");
		}
		margin=parseFloat($(".margin",$p).val());
		

		if(dp_price*1 > 0)
		{
		
			if(stype==1)
			{
				
				price=dp_price-(dp_price*parseFloat(margin+sdiscount)/100);
			}
			else
			{
				
				price=dp_price-(dp_price*parseFloat(margin)/100)-parseFloat(sdiscount);
			

			}
				
		}else
		{
			
			if(stype==1)
			{
				
				price=mrp-(mrp*parseFloat(margin+sdiscount)/100);
			}
			else
			{
				price=mrp-(mrp*parseFloat(margin)/100)-parseFloat(discount);
				
			}
		}
		
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
	
	// update bulk product margin by brand  
	$('#pprods .margin').live('keyup',function(){
		
		if(isNaN($(this).val()*1))
		{
			$(this).val(0);
		}
		else
		{
			var $r = $(this).parents('tr:first');
			var prod_brandid = $r.attr('brandid');
			var mrg = $(this).val();
			$('.qty',$r).trigger('change');
		
		}	
	});

	$("#pprods .sdiscount,#pprods .margin,#pprods .mrp, #pprods .stype, #pprods .qty,#pprods .dp_price,#pprods .calc_pp").live("change",function(){

		$p=$(this).parents("tr").get(0);
		qty=parseFloat($(".qty",$p).val());
		mrp=parseFloat($(".mrp",$p).val());
		dp_price=parseFloat($(".dp_price",$p).val());
		stype=parseFloat($(".stype",$p).val());
		sdiscount=parseFloat($(".sdiscount",$p).val());
		if(isNaN(sdiscount))
		{
			sdiscount=0;
			$(".sdiscount",$p).val("0");
		}
		margin=parseFloat($(".margin",$p).val());

		margin = isNaN(margin)?0:margin;

		
		if(dp_price*1 > 0)
		{
		//	price=dp_price-(dp_price*margin/100);
			if(stype==1)
			{
				$('.marg_prc_preview',$p).hide();
				price=dp_price-(dp_price*parseFloat(margin+sdiscount)/100);
			}
			else
			{
				
				price=dp_price-(dp_price*parseFloat(margin)/100)-parseFloat(sdiscount);
				margin_prc=(1-(price/dp_price))*100;
				$('.marg_prc_preview',$p).show();
				$('.marg_prc').val(margin_prc);

			}
				
		}else
		{
			//price=mrp-(mrp*margin/100);
			if(stype==1)
			{
				$('.marg_prc_preview',$p).hide();
				price=mrp-(mrp*parseFloat(margin+sdiscount)/100);
			}
			else
			{
				price=mrp-(mrp*parseFloat(margin)/100)-parseFloat(sdiscount);
				margin_prc=(1-(price/mrp))*100;
				$('.marg_prc_preview',$p).show();
				$('.marg_prc').val(margin_prc);
			}
		}
	
		qtyprice=qty*price;
		$(".pprice",$p).val(price);
		$(".tpprice",$p).val(price*qty);
		//$(".qtypprice",$p).val()
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
				alert("No products added for creating PO, Please add atleast one product");
				return false;
			}
			
			var block_frm_submit = 0;
			var qty_pending = 0;
			var ven_pending = 0;
			var marg_pending = 0;
				$('#pprods tbody tr:visible',this).each(function(){
					qty = $('input[name="qty[]"]',this).val()*1;
					marg = $('input[name="margin[]"]',this).val()*1;
					
					if(isNaN(marg) || marg == 0)
						marg_pending += 1;
					
					if(qty==0)
						qty_pending += 1;
					
				});
				
				if(marg_pending)
				{
					alert("Invalid Margins entered,Please check and update margins");
					return false;
				}
				
				if(qty_pending || ven_pending){
					alert("Unable to submit request, please enter valid qty for purchase");
					return false;
				}
				if(!$('input[name="e_dod"]').val())
				{
					alert('"Date of Delivery" is mandatory');
					return false;
				}
				if($('textarea[name="remarks"]').val().length == 0)	
				{
					alert('Please input Remarks');
					return false;
				}
				if(confirm("Are you sure want to create this PO ?"))
					return true;
				else
					return false;
		}
		return false;
	});

	$("#vendorsel").change(function(){
		showvendor($(this).val());
	}).val("0").attr("disabled",false);


	
	$("#choosevendor").click(function(){
		if($("#vendorsel").val()!=0)
		{
			$(".showaftvendor,#barcode_floater").show();
			$('.v_disp_container').slideUp();
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
		}else
		{
			alert("Choose vendor");
		}
	}).attr("disabled",false).trigger('click');

	$("#sl_show").click(function(){
		$("#sl_products").dialog('open');
	});

	
});


var brand_prods=[];
var search_timer=0,jHR=0;
function showvendor_old(vid)
{
	$(".v_disp_container").show();
	$(".v_disp").html("LOADING>>>>");
	$.post("<?=site_url("admin/jx_show_vendor_details")?>",{v:vid},function(data){
		$(".v_disp").html(data);
	});
}

function showvendor(vid)
{
	$('.block').show();
	$(".v_disp_container").show();
	$(".v_disp").html("LOADING>>>>");
	$('.gridtable contact_det tbody td').html('');
	$.post("<?=site_url("admin/jx_show_vendor_details")?>",{v:vid},function(resp){
		if(resp.status=='success')
		{
			$('#contact_det div').html("<span><b><a href='"+site_url+'/admin/vendor/'+resp.vcontact.vendor_id+"' target='_blank'>"+resp.vcontact.vendor_name+'</a></b></span><span>'+resp.vcontact.contact_name+'</span><span>'+resp.vcontact.mobile_no_1+' , '+resp.vcontact.mobile_no_2+' , '+resp.vcontact.telephone_no+'</span><span>'+resp.vcontact.address_line1+'</span> <span>'+resp.vcontact.address_line2+'</span> <span>'+resp.vcontact.city_name);
			
			var b_html='';
			$.each(resp.vbrands,function(i,b){
				b_html +='<span>'+b.brand+'</span>';
			});
			$('#brand_det div').html(b_html);

			if(resp.ttl_po.ttl_val==null)
				resp.ttl_po.ttl_val=0
			if(resp.partial_po.ttl_val==null)
				resp.partial_po.ttl_val=0;
			if(resp.cancelled_po.ttl_val==null)
				resp.cancelled_po.ttl_val=0;
			if(resp.complete_po.ttl_val==null)
				resp.complete_po.ttl_val=0;
			if(resp.complete_po.ttl_open_po==null)
				resp.complete_po.ttl_val=0;
			if(resp.recent_po_det == undefined)
				resp.recent_po_det='no data found';
				$('#po_det div').html('<span class="span_count_wrap"><b>Total('+''+resp.ttl_po.ttl+')</b> : Rs. '+resp.ttl_po.ttl_val+'</span><span class="span_count_wrap"><b>Open('+''+resp.ttl_open_po.ttl+')</b> : Rs. '+resp.ttl_open_po.ttl_val+'</span><span class="span_count_wrap"><b>Partial('+''+resp.partial_po.ttl+')</b> : Rs. '+resp.partial_po.ttl_val+'</span><span class="span_count_wrap"><b>Complete('+''+resp.complete_po.ttl+')</b> : Rs. '+resp.complete_po.ttl_val+'</span><span class="span_count_wrap"><b>Cancelled('+''+resp.cancelled_po.ttl+')</b> : Rs. '+resp.cancelled_po.ttl_val+'</span>');
				$('#latest_po div').html('<span class="span_count_wrap"><b>Total Value </b> : Rs. '+resp.recent_po_det.total_value+'</span><span class="span_count_wrap"><b>Created on</b> : '+resp.recent_po_det.created_on+'</span>');
		}
	},'json');
}

function loadbrandproducts()
{
	$(".sl_sel_prod:checked").each(function(){
		tr=$($(this).parents("tr").get(0));
		addproduct($(".pid",tr).val(),$(".name",tr).html(),$(".mrp",tr).html(),$(".margin",tr).html(),$(".orders",tr).html(),$(".i_po_qty",tr).val(),$("#vendorsel").val());
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
						        $('.ui-dialog-buttonpane').find('button:contains("Mark as Sourcable")').css( "background-color","#AAFFAA");
						        $('.ui-dialog-buttonpane').find('button:contains("Mark as not Sourcable")').css( "background-color","#FFAAAA");
						       
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
												$('#ttl_res').append(data.length);
											$.each(data,function(i,p){
													template=$("#sl_prod_template tbody").html();
													template=template.replace(/%id%/g,i);
													template=template.replace(/%pid%/g,p.id);
													template=template.replace(/%product%/g,p.product);
													template=template.replace(/%stock%/g,p.stock);
													template=template.replace(/%margin%/g,p.margin);
													template=template.replace(/%mrp%/g,p.mrp);
													template=template.replace(/%orders%/g,p.orders);
													template=template.replace(/%brandid%/g,bid);
													template=template.replace(/%prod_source_stat%/g,((p.src==1)?'Yes':'No'));
													template=template.replace(/%prod_source_stat_val%/g,p.src);
													var po_ord_qty = 0;
													if(p.pen_ord_qty > p.stock)
														po_ord_qty = (p.pen_ord_qty-(p.stock+p.order_qty));
														//po_ord_qty = p.pen_ord_qty-p.stock;
													else if(p.pen_ord_qty)
														po_ord_qty = 0; 
														
													template=template.replace(/%i_po_qty_val%/g,po_ord_qty<0?0:po_ord_qty);
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

								'Load Selected Products' : function(){
									loadbrandproducts();	
							},

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



function view_purchasepattern(pid)
{
	$("#purchase_productdet").data('purchase_pid',pid).dialog('open');
}

$("#purchase_productdet").dialog({
	modal:true,
	autoOpen:false,
	width:900,
	height:450,
	autoResize:true,
	open:function(){
		var	dlg = $(this);
		$('#purchase_productdet_frm input[name="purchase_pid"]',this).val(dlg.data('purchase_pid'));
		var product_id=$('#purchase_productdet input[name="purchase_pid"]',this).val(dlg.data('purchase_pid'));
		$("#last7_pdet tbody").html("");
		$("#last30_pdet tbody").html("");
		$("#last60_pdet tbody").html("");
		$("#last90_pdet tbody").html("");
		$.post(site_url+'/admin/to_load_purchase_pattern_details',{pid:dlg.data('purchase_pid')},function(result){
			/*if(result.status=='error')
			{
				alert('No Data Found');
				 $("#purchase_productdet").dialog('close');
			}
			else*/
			if(result.status=='success')
			{
				var pofr_orderdrow = '';

				if(result.last_7daydet != undefined)
				{
					$.each(result.last_7daydet,function(p,m){
						pofr_orderdrow +=
							
							"<tr>"	
							
							+"<td text-align='right'>"+m.vendor_name+"</td>"
							+"<td text-align='right'>"+m.ttl_qty+"</td>"
							+"<td text-align='right'>"+m.margin+"</td>"
							+"</tr>";
					});
					
				}else
				{
					pofr_orderdrow +="<b style='align:center;'>No Record Found</b>";
				}
				$("#last7_pdet tbody").html(pofr_orderdrow);
				var pofr_orderdrow = '';

				if(result.last_30daydet != undefined)
				{
					$.each(result.last_30daydet,function(p,m){
						pofr_orderdrow +=
							
							"<tr>"	
							//+"<td align='center'>"+'Last 30 Days'+"</td>"
							+"<td text-align='right'>"+m.vendor_name+"</td>"
							+"<td text-align='right'>"+m.ttl_qty+"</td>"
							+"<td text-align='right'>"+m.margin+"</td>"
							+"</tr>";
					});
					
				}else
				{
					pofr_orderdrow +="<b style='align:center;'>No Record Found</b>";
				}
				
				$("#last30_pdet tbody").html(pofr_orderdrow);

				var pomid_orderdrow = '';
				if(result.last_60daydet != undefined)
				{
					$.each(result.last_60daydet,function(p,m){
						pomid_orderdrow +=
							"<tr >"	
							//+"<td align='center'>"+'Last 60 Days'+"</td>"
							+"<td text-align='right'>"+m.vendor_name+"</td>"
							+"<td text-align='right'>"+m.ttl_qty+"</td>"
							+"<td text-align='right'>"+m.margin+"</td>"
							+"</tr>";
					});
					
				}
				else
				{
					pomid_orderdrow +="<b style='align:center;'>No Record Found</b>";
				}
				$("#last60_pdet tbody").html(pomid_orderdrow);

				var polast_orderdrow = '';
				if(result.last_90daydet != undefined)
				{
					$.each(result.last_90daydet,function(p,m){
						polast_orderdrow +=
							"<tr>"	
							//	+"<td align='center'>"+'Last 90 Days'+"</td>"
							+"<td text-align='right'>"+m.vendor_name+"</td>"
							+"<td text-align='right'>"+m.ttl_qty+"</td>"
							+"<td text-align='right'>"+m.margin+"</td>"
							+"</tr>";
					});
					
				}else
				{
					polast_orderdrow +="<b style='align:center;'>No Record Found</b>";
				}
				$("#last90_pdet tbody").html(polast_orderdrow);
				
			}
		},'json');
	},
buttons:{
	'Close' :function(){
	 $(this).dialog('close');
	},
	
}
});


$('.partner_det a.tgl_viewtransids').live("click",function(e){
	e.preventDefault();
	var ele  = $(this);
	var productid = $(this).attr('productid');
	var partnerid = $(this).attr('partnerid');
	if($(this).attr('status') == 1)
	{
		var qcktiphtml='';
		$(this).attr('status',0);
		$(this).text('close');
	
		$.getJSON(site_url+'/admin/jx_to_load_patner_orddet/'+productid+'/'+partnerid,function(resp){
			if(resp.status == 'error')
			{
				$('.partner_det',ele.parent().parent()).html("No Details found").hide();
			
			}
			else
			{
				 qcktiphtml += '<div style="max-height:200px;overflow:auto;clear:both">';
				qcktiphtml += '<table width="100%" border=1 class="datagrid" cellpadding=3 cellspacing=0>';
				qcktiphtml += '<thead><tr><th>Transids</th><th>Order From</th><th>Quantity</th><th>Orderd On</th></tr></thead><tbody>';
				$.each(resp.partner_transdet,function(a,b){
					if(b.is_pnh==0)
						b.is_pnh='Customer';
					else
						b.is_pnh=b.bill_person;
					qcktiphtml+="<tr>"
					qcktiphtml+="<td><a  href='"+site_url+'/admin/trans/'+b.transid+"' target='_blank'>"+b.transid+"</a></td>";
					qcktiphtml+="<td>"+b.is_pnh+"</td>";
					qcktiphtml+="<td>"+b.ord_qty+"</td>";
					qcktiphtml+="<td>"+b.orderd_on+"</td>";
					qcktiphtml+="</tr>";
				});
				qcktiphtml += '</tbody></table></div>';
				$('.partner_det',ele.parent().parent()).html(qcktiphtml).show();
			}
		});
		$('.partner_det',ele.parent().parent()).html("Loading...").hide(); 
	
	
	}
	else
	{
		$(this).attr('status',1);
		$(this).text('view transids');
		$('.partner_det',ele.parent().parent()).html(qcktiphtml).hide(); 
	}
});


function view_orderdet(pid)
{
	$("#orderd_productdet").data('product_id',pid).dialog('open');
		//alert(pid);
}

$("#orderd_productdet").dialog({
	modal:true,
	autoOpen:true,
	width:726,
	height:400,
	autoResize:true,
	open:function(){
		var	dlg = $(this);
		$('#orderd_productdet_frm input[name="po_productid"]',this).val(dlg.data('product_id'));
		var product_id=$('#orderd_productdet_frm input[name="po_productid"]',this).val(dlg.data('product_id'));
		$("#orderd_podet tbody").html("");
		$.post(site_url+'/admin/to_load_orderdprd_details',{pid:dlg.data('product_id')},function(result){
			if(result.status=='error')
			{
				dlg.dialog('close');
				alert(result.msg);
			}
			else
			{
				if(result.product_orderdet !=  undefined)
				{
					var po_orderdrow = '';
					$.each(result.product_orderdet,function(p,o){
						if(o.partner_name == undefined)
						{o.partner_name='Snapittoday'; }
						if(o.is_pnh==1)
						{o.partner_name='Paynearhome'; }
						var productid=o.product_id;
						po_orderdrow +=
							"<tr class='partner_det' partnerid="+o.partner_id+" productid="+o.product_id+">"	
							+"<td text-align='left' v-align='top'>"+o.partner_name+"</td>"
							+"<td text-align='left' v-align='top'>"+o.total+"<div ><a href='javascript:void(0)' class='tgl_viewtransids' status='1' partnerid="+o.partner_id+" productid="+o.product_id+">view transids</a><div class='partner_det'></div></div></td>"
							+"<td text-align='left' v-align='top'>"+o.orderd_on+"</td>"
							+"</tr>";
						
					});
					$("#orderd_podet tbody").html(po_orderdrow);
				}
			}	
		},'json');
	},
	buttons:{
		'Close' :function(){
		 $(this).dialog('close');
		},
		
	}
});

function remove_prodfrmpo(ele)
{
	poid=$(ele).attr('poid');

	pid=$(ele).attr('pid');

	if(confirm("Are you sure want to cancel product from po ?"))
	{

		$.post(site_url+'/admin/jx_update_poprod_status/',{poid:poid,pid:pid},function(resp){
		if(resp.status)
			{
			 	$("#dlg_openpolist").dialog('close');
           		$("#dlg_openpolist").dialog('open');

           		$("#is_po_raised_"+pid).html('<a href="javascript:void(0)" onclick="load_openpolist('+pid+')" ><b>'+resp.ttl_open_qty+'</b></a>');
           		
			}	
		
	},'json');
				
	}

		
}


</script>

<?php
