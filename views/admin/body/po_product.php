<link type="text/css" rel="stylesheet" href="<?php echo base_url();?>css/po_product.css" />

<div class="container">
	<h2>Purchase Order - Productwise</h2>

	

	<table width="100%">
		<tr>
			<td>
				<input type="text" name="s" id="po_search" class="prd_blk inp" placeholder="Search &amp; Add" > 
				<div class="prd_srch_result closeonclick" id="po_prod_list"></div>
			</td>
			
			<td>
				<a href="javascript:void(0)" id="load_unavail" class="loadbutton small blue" style="float:right;" onclick="show()">Load</a>
				<form name="tcol" onsubmit="return false" style="float:right;margin:7px;">
					<b>Show Offer</b> : <input type="checkbox" name="col1" onclick="toggleVis(this.name)"> 
				</form>
				
			</td>
		</tr>
	</table>

	<div class="po_filters">
		  <span title="Toggle Filter Block" class="close_filters">
	  			<span class="close_btn"><b>Show</b></span>
	            <h3 class="filter_heading">Filters:</h3>
	      </span>
	 	 <div id="filter_prods" >
			<span>
				<b>Menu&nbsp;</b> <select name="fil_menu"></select> 
			</span> &nbsp; 
			
			<b>Brand &nbsp;</b>
			<select name="fil_brand"></select> &nbsp; 
				<span id="load_othrunavlibleprod">
					<input type="button" value="Show & load" id="sl_show">
				</span> 
				
				<span>
					<b>Vendor&nbsp; </b> 
					<select name="fil_vendor"></select>
				</span> 
				
				<!--<span>
					<b>Order by&nbsp; </b>
					<select name="fil_partner"></select>
				</span>-->
			</div>
	</div>

	<form method="post" id="poprodfrm" autocomplete="off">
		<!--<input type="button" value="show offer" style="font-size: 10px;" id="offer_details">-->
	
		<div style="margin-bottom:22px;clear:both;">
			<table class="datagrid datagridsort" id="pprods" width="100%" cellpadding="8">
				<thead>
					<tr>
						<th width="10px">S.No</th>
						<th width="320px">Product</th>
						<th width="10px"></th>
						<th width="80px">MRP</th>
						<th width="80px">DP Price</th>
						<th width="80px">Margin</th>
						<th width="230px" style=""> Qty & price Details</th>
						<th width="130px">Purchase Price </th>
						<th >Vendor </th>
						<th name="tcol1" id="tcol1" class="bold" style="text-align: center;display:none;" >Offer</th>
						<th  width="20px" style="text-align: center">Actions</th> 
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<table id="show_submit" width="100%" >
			<tr>
				<td>
			<!--  <td valign="top"><b>Expected Date of Delivary</b>:<input type="text" name="po_deliverydate" id="po_deliverydate"  style="width:207px;height:21px;">
			<b>Remarks:</b>
			<input type="text" name="po_remarks"  style="width:500px;height:21px;">&nbsp;&nbsp;
			<input type="checkbox" value="1" checked="checked" name="sendpo_mail"><b>Notify Vendors By Mail</b> &nbsp;&nbsp;-->
					
					<button type="submit" class="loadbutton small green" style="float: right;">Place PO</button>
					
				</td>
			</tr>
		</table>
	</form>
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
		<br><br>
	</div>
</div>

<form id="src_form" action="<?=site_url("admin/mark_src_products")?>" method="post">
	<input type="hidden" name="pids" class="pids">
	<input type="hidden" name="action" class="action" value="1">
</form>

<div style="display:none">
	<table id="sl_prod_template">
		<tbody>
			<tr>
				<td>
					<input type="checkbox" class="sl_sel_prod" value="%pid%">
					<input type="hidden" class="pid" value="%pid%">
				</td>
				
				<td class="psrcstat">
					<span class="src_stat">%prod_source_stat%</span> 
					<a href=javascript:void(0)" prod_id="%pid%" onclick="upd_prdsourceablestat(this)" nsrc="%prod_source_stat_val%" >Change</a> 
				</td>
				
				<td>%pid%</td>
				
				<td class="name" style="width: 400px;text-align: left;">
					<a  target="_blank" href="<?php echo site_url('admin/product/%pid%')?>">%product%</a>
				</td>
				
				<td>Rs <span class="mrp">%mrp%</span></td><td class="margin" style="display: none;" >%margin%</td>
				
				<td>%stock%</td>
				
				<td ><input type="text" class="i_po_qty" style="width: 40px;" value="%i_po_qty_val%"></td>
				
				<td class="orders">%orders%</td>
			</tr>
		</tbody>
	</table>
	
	<table id="p_clone_template" width="100%">
		<tr class="barcode--barcode-- barcodereset filbrand_%brand_id% filmenu_%menuid% filvendor_%vendor_id% filpartner_%partner_id%" prod_id="%product_id%" brandid="%brand_id%" brandname="%brand_name%" menuid=%menuid% menuname="%menu%" >
			<td>%sno%</td>
				<td width="200px">
					<input type="hidden" class="product" name="product[]" value="%product_id%">
					<input type="hidden" class="brand" name="brand[]" value="%brand_id%">
					<input type="hidden" class="menu" name="menu[]" value="%menuid%">
					<input type="hidden" class="brand" name="brandid[]" value="%brand_id%">
					<input type="hidden" class="cat" name="catid[]" value="%cat_id%">
					<input type="hidden" class="menu" name="menuid[]" value="%menuid%">
					<input type="hidden" class="menu" name="menuname[]" value="%menu%">
					<input type="hidden" class="menu" name="brandname[]" value="%brand_name%"> 
					<input type="hidden" class="vendor" name="sel_vendor[]" value="%vendor_id%">
					<input type="hidden" class="partner" name="partner[]" value="%partner_id% %is_pnh%">
					
					<a target="_blank" class="pprod_name" href="<?php echo site_url('admin/product/%product_id%') ?>">%product_name%</a> &nbsp;<b>(%product_brand%)</b>
					<br/>
					<a href="javascript:void(0)" class="pprod_name_subwrap" onclick="view_orderdet(%product_id%)">order details&nbsp;,&nbsp;</a><a href="javascript:void(0)" class="pprod_name_subwrap" onclick="view_purchasepattern(%product_id%)">purchase pattern</a>
			
					<!--  <br/>
					Date:%last_orderdon% &nbsp;Transid:<a target="_blank" href="<?php echo site_url('admin/trans/%transid%')?>">%transid%</a></p>-->
				</td>
				
				<td></td>
				<td><input type="text" class="inp calc_pp mrp" size=7 name="mrp[]" value="%mrpvalue%"></td>
				<td><input type="text" title="Change/Update DP Price on change" class="inp calc_pp has_dp_price dp_price" size=5 name="dp_price[]" value="%dp_price%"></td>
				<td><input type="text" class="inp calc_pp margin readonly" size="7" name="margin[]" readonly="readonly" value="%margin%" ></td>
				
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
						<span><input type="text" class="inp calc_pp discount" size=7 name="sch_discount[]" value="0" style="width:72px;border:1px solid #FF0000;"></span>	
					</div>
					
					<div>
						<span>Discount type :</span>
						<span>
							<select class="calc_pp type" name="sch_type[]" style="width:82px;margin-bottom: ">
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
							<span class="marg_prc_preview" style="display: none">
								<input type="text" name="marg_prc" value="" readonly="readonly" class="inp marg_prc readonly">
								%
							</span>
						</div>
					</div>
				</td>
				
				<!--  <td><input type="text" class="inp calc_pp qty" id="prod_qty_%product_id%"  size=4 name="qty[]" value="%require_qty%" style="border:2px solid #000"></td>-->
				
				<td>
					<select class="vendor" name="vendor[]">%vendorlist%</select>
				</td>
				<!-- class="offer_tdcont" -->
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
				
				<td style="text-align: center"><!--  <select class="partner" name="partner[]">%partnerlist%</select>-->
				&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick='remove_prod_selection(this)'><img  src="<?php echo base_url().'images/remove.png'?>"></a></td>
			</tr>
		</table>
	</div>
	
	<div id="orderd_productdet" title="Order Details">
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
	
	<div id="modal" style="display:none;align="center">
		<div id="loading" align="center">
			Loading.Please wait...<br /><br />
			<img src="<?php echo base_url().'images/jx_loading.gif'?>" alt="" />
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

<div style="display:none">
	<div id="dlg_openpolist" title="Open PO List"></div>
</div>

<script>


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

if (document.all) showMode='block';

function toggleVis(btn){
	// First isolate the checkbox by name using the
	// name of the form and the name of the checkbox
	document.forms[0];
	btn   = document.forms['tcol'].elements[btn];

	// Next find all the table cells by using the DOM function
	// getElementsByName passing in the constructed name of
	// the cells, derived from the checkbox name
	cells = document.getElementsByName('t'+btn.name);

	// Once the cells and checkbox object has been retrieved
	// the show hide choice is simply whether the checkbox is
	// checked or clear
	mode = btn.checked ? 'block' : 'none';
	// Apply the style to the CSS display property for the cells
	for(j = 0; j < cells.length; j++) cells[j].style.display = mode;
}

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

$('#dlg_openpolist').dialog({'width':800,autoOpen:false,height:600,modal:true,open:function(){
	var pid = $(this).data('pid');
	$('#dlg_openpolist').html("<h3 align='center'>Loading...</h3>");
	$.post(site_url+'/admin/jx_getopenpolistbypid/'+pid,{},function(resp){
		var html = '<h3 class="pname">'+resp.product_name+'</h3>';
			html += '	<div class="pttl">Total open qty : <b>'+resp.ttl_open_qty+'</b></div>';
			html += '	<div id="openpolist_tbl">';
			html += '	<table width="100%" class="datagrid" cellpadding="5" cellspacing="0">';
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
	'Cancel selected product in PO':function()
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

function show() {
    document.getElementById("modal").style.display="block";
    setTimeout("hide()", 38000);  // 3 seconds
}

function hide() {
    document.getElementById("modal").style.display="none";
}
var psubmit= false;   
$('#poprodfrm').submit(function(){
	if(!psubmit)
	{
		var block_frm_submit = 0;
		var qty_pending = 0;
		var ven_pending = 0;
		var marg_pending = 0;
		var frmEle = $(this);
		$('.datagrid tbody tr',frmEle).addClass('row_p');
			$('.datagrid tbody tr:visible',frmEle).each(function(){
				qty = $('input[name="qty[]"]',this).val()*1;
				ven = $('select[name="vendor[]"]',this).val()*1;
				marg = $('input[name="margin[]"]',this).val()*1;
				
				if(isNaN(marg) )
					marg_pending += 1;
					
				
				if(qty==0)
					qty_pending += 1;
	
				if(ven==0)
					ven_pending += 1;
				$(this).removeClass('row_p');
			});

			$('.datagrid tbody tr.row_p',frmEle).remove();
			
			if(marg_pending)
			{
				alert("Invalid Margins entered,Please check and update margins");
				return false;
			}
			
			if(qty_pending || ven_pending){
				alert("Unable to submit request, please choose vendor or qty is missing");
				return false;
			}
	
		
			if(confirm('Are You sure want to place PO?'))
			{
				return true;
			}
			else
				return false;
		
	}
});


function loadbrandproducts()
{
	$(".sl_sel_prod:checked").each(function(){
	$r=$(this).parents("tr").get(0);
	addproduct($(".pid",$r).val(),$(".name",$r).html(),$(".mrp",$r).html(),$(".i_po_qty",$r).val());
	});
	$("#sl_products .datagrid tbody").html("");
	$("#sl_products").dialog('close');
}



var p_brand_list = [];
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

function addproduct(id,name,mrp,require)
{
	if(!id)
		return;
	
	require = (typeof require === "undefined") ? "" : require;
	$("#po_prod_list").hide();
	if($.inArray(id,added_po)!=-1)
	{
		alert("Product already added to the current Order");
		return;
	}

	$.post("<?=site_url("admin/jx_productdetails")?>",{id:id},function(data){
		o=$.parseJSON(data);
		i=added_po.length;

	 
		
	if(data.length && data!=undefined && o.product_id != undefined)
	{

		
		
		template=$("#p_clone_template tbody").html();
		template=template.replace(/%sno%/g,i+1);
		template=template.replace(/%require_qty%/g,require?require:o.require_qty);
		template=template.replace(/%product_id%/g,o.product_id);
		template=template.replace(/%brand_id%/g,o.brand_id);

		template=template.replace(/%cat_id%/g,o.catid);
		
		template=template.replace(/%menuid%/g,o.menuid);
		template=template.replace(/%brand_name%/g,o.brand_name);
		template=template.replace(/%menu%/g,o.menu);
		template=template.replace(/%vendor_id%/g,o.vendor_id);
	
		template=template.replace(/%product_name%/g,o.product_name);
		template=template.replace(/%last_orderdon%/g,o.last_orderdon);
		template=template.replace(/%transid%/g,o.transid);
		template=template.replace(/%product_brand%/g,o.brand_name);
		template=template.replace(/--barcode--/g,o.barcode);
		template=template.replace(/%mrpvalue%/g,o.mrp);
		template=template.replace(/%dp_price%/g,o.dp_price);
		
		//o.margin = 0;
		
		template=template.replace(/%margin%/g,o.margin);
		template=template.replace(/%foc%/g,"foc"+i);
		template=template.replace(/%offer%/g,"offer"+i);
		
		mrp=parseFloat(o.mrp);

		
	
	 	var mrgin=$('input[name="margin[]"],tr',this).val()*1;
	 	var sch_type=$('select[name="sch_type[]"],tr',this).val();
	 	var extra_mrgin=$('input[name="sch_discount[]"],tr',this).val()*1;
		
	/*	if(extra_mrgin.length)
			o.margin=o.margin+parseFloat(extra_mrgin);
		else
			o.margin=o.margin;*/

		
		if(!o.dp_price.length)
		{
			if(sch_type == 1)
			{
				
				
				pprice=mrp-(mrp*parseFloat(o.margin+extra_mrgin)/100);
			}
			else
			{
				
				pprice=mrp-(mrp*(parseFloat(o.margin)/100));
				pprice=pprice-parseFloat(extra_mrgin);

			}
		}

		
		if(o.dp_price.length)
		{
			if(sch_type == 1)
			
				pprice=o.dp_price-(o.dp_price*(parseFloat(o.margin+extra_mrgin)/100));
				
			else
				pprice=o.dp_price-(o.dp_price*(parseFloat(o.margin)/100));
				pprice=pprice-parseFloat(extra_mrgin);
		}
		if(pprice.length)
		{
			template=template.replace(/%pprice%/g,pprice);
		}
		if(o.purchase_price)
		{
			template=template.replace(/%pprice%/g,o.purchase_price);
		}
		if(o.mrp && !o.purchase_price)
		{
			template=template.replace(/%pprice%/g,o.mrp);
		}
		vendors="";
		$.each(o.vendors,function(i,v){
			vendors=vendors+'<option value="'+v.vendor_id+'">'+v.vendor+'</option>';
		});

		
		
		
		partners="";
		$.each(o.partners,function(i,v){

			if(v.partner_id==0 && v.is_pnh==1)
			{
				v.partner_id='PNH';
				v.partner_name='PayNearHome';
			}
			if(v.partner_id==0 && v.is_pnh==0)
			{
				v.partner_id='SNP';
				v.partner_name='Snapittoday';
			}
			partners=partners+'<option value="'+v.partner_id+'">'+v.partner_name+'</option>';


			if(!$('select[name="fil_partner"] option#partner_'+v.partner_id).length){
				if(v.partner_id != undefined)
				{
					if(v.is_pnh==1 && v.partner_id==0)
					{
						v.partner_id='PNH';
						v.partner_name='PayNearHome';
					}
				 if(v.is_pnh==0 && v.partner_id==0)
					{
					 	v.partner_id='SNP';
						v.partner_name='Snapittoday';
					}
					$('select[name="fil_partner"]').append('<option id="partner_'+v.partner_id+'" value="'+v.partner_id+'">'+v.partner_name+'</option>');
				}
			}
			
		});
		template=template.replace(/%vendorlist%/g,vendors);

		template=template.replace(/%partnerlist%/g,partners);
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
		
		if(!o.dp_price.length)
		{
			$('#pprods tbody .has_dp_price:last').css('visibility','hidden');	
		}else
		{
			$('#pprods tbody .has_dp_price:last').css('visibility','visible');
		}
		
		added_po.push(id);

		if(!$('select[name="fil_brand"] option#brand_'+o.brand_id).length){
			if(o.brand_id != undefined){
			$('select[name="fil_brand"]').append('<option id="brand_'+o.brand_id+'" value="'+o.brand_id+'">'+o.brand_name+'</option>');
			}
		}
		if(!$('select[name="fil_menu"] option#menu_'+o.menuid).length){
			if(o.menuid!=undefined){
				$('select[name="fil_menu"]').append('<option id="menu_'+o.menuid+'" value="'+o.menuid+'">'+o.menu+'</option>');
			}
		}
		if(!$('select[name="fil_vendor"] option#vendor_'+o.vendor_id).length){
			if(o.vendor_id != undefined){
			$('select[name="fil_vendor"]').append('<option id="vendor_'+o.vendor_id+'" value="'+o.vendor_id+'">'+o.vendor_name+'</option>');
			}
		}

		
		

	}
	});
}

var search_timer=0;
var jHR=0;
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

	
	$("#load_unavail").click(function(){
		$(this).hide();
		$.post("<?=site_url("admin/jx_load_unavail_products")?>",{hash:<?=time()?>,oldest_order:'asc'},function(data){
			os=$.parseJSON(data);
			if(data.status=='error')
			{
				alert(data.msg);
			}
			else
			{
			
			$('.po_filters').show();
			$('select[name="fil_brand"]').html('<option value="">Choose</option>');
			$('select[name="fil_menu"]').html('<option value="">Choose</option>');
			$('select[name="fil_vendor"]').html('<option value="">Choose</option>');
			$('select[name="fil_partner"]').html('<option value="">Choose</option>');

			
				$.each(os,function(i,o){
					addproduct(o.product_id, "", "",0);
				});
								

			var brand_list = [];
				$('select[name="fil_brand"] option').each(function(){
					brand_list[$(this).text().replace(' ','_')]=$(this).val();
				});
				$('select[name="fil_brand"]').html('<option value="">Choose</option>');
				$.each(brand_list,function(a,b){
					$('select[name="fil_brand"]').append('<option id="brand_'+a+'" value="'+a+'">'+b+'</option>');
				});
			var menu_list = [];
				$('select[name="fil_menu"] option').each(function(){
					menu_list[$(this).text().replace(' ','_')]=$(this).val();
				});
				$.each(menu_list,function(a,b){
					$('select[name="menu_list"]').append('<option id="menu_'+a+'" value="'+a+'">'+b+'</option>');
				});
			var vendor_list = [];
				$('select[name="fil_vendor"] option').each(function(){
					vendor_list[$(this).text().replace(' ','_')]=$(this).val();
				});
				$.each(vendor_list,function(a,b){
					$('select[name="vendor_list"]').append('<option id=vendor_'+a+'" value="'+a+'">'+b+'</option>');
				});

				var partner_list = [];
				$('select[name="fil_partner"] option').each(function(){
					partner_list[$(this).text().replace(' ','_')]=$(this).val();
				});
				$.each(partner_list,function(a,b){
					$('select[name="partner_list"]').append('<option id=partner_'+a+'" value="'+a+'">'+b+'</option>');
					
				});
				
			$('#filter_prods').show();
			$("#load_othrunavlibleprod").hide();
		
			
			
	}
		});
	}).attr("disabled",false);

	$('select[name="fil_brand"]').change(function(){
		
 		if($(this).val() == '')
		{
			$('#pprods tbody tr').show();
		}
		else
		{
			var bid=$(this).val();

			$('#pprods tbody tr').hide();
			$('#pprods tbody tr.filbrand_'+$(this).val()).show();
			$("#load_othrunavlibleprod").show();
		}


		var v_vendors = new Array();
			$('#pprods tbody tr:visible select.vendor option').each(function(){
				v_vendors[$(this).attr('value')]= $(this).text();
			});
 
		$('select[name="fil_vendor"]').html("<option value=''>choose</option>");
		for(var vid in v_vendors)
		{
			$('select[name="fil_vendor"]').append("<option value='"+vid+"'>"+v_vendors[vid]+"</option>");	
		}
	});

	$('select[name="fil_menu"]').change(function(){
	
		if($(this).val() == '')
		{
			$('#pprods tbody tr').show();
		}else
		{
			$('#pprods tbody tr').hide();
			$('#pprods tbody tr.filmenu_'+$(this).val()).show();
		}

		var v_brands = new Array();
			$('#pprods tbody tr:visible').each(function(){
				v_brands[$(this).attr('brandid')]= $(this).attr('brandname');
			});
 
			$('select[name="fil_brand"]').html("<option value=''>choose</option>");
			for(var bid in v_brands)
			{
				$('select[name="fil_brand"]').append("<option value='"+bid+"'>"+v_brands[bid]+"</option>");	
			}
		
		var v_vendors = new Array();
			$('#pprods tbody tr:visible select.vendor option').each(function(){
				v_vendors[$(this).attr('value')]= $(this).text();
			});
	
			$('select[name="fil_vendor"]').html("<option value=''>choose</option>");
			for(var vid in v_vendors)
			{
				$('select[name="fil_vendor"]').append("<option value='"+vid+"'>"+v_vendors[vid]+"</option>");	
			}

		var v_partners = new Array();
			$('#pprods tbody tr:visible select.partner option').each(function(){
				v_partners[$(this).attr('value')]= $(this).text();
			});
	
			$('select[name="fil_partner"]').html("<option value=''>choose</option>");
			for(var partnerid in v_partners)
			{
				$('select[name="fil_partner"]').append("<option value='"+partnerid+"'>"+v_partners[partnerid]+"</option>");	
			}
		
	});
	
		
	 $('select[name="fil_partner"]').change(function(){
		
 		if($(this).val() == '')
		{
			$('#pprods tbody tr').show();
		}
		else
		{
			var bid=$(this).val();

			$('#pprods tbody tr').hide();
			$('#pprods tbody tr.filpartner_'+$(this).val()).show();
			$("#load_othrunavlibleprod").show();
		}
	
	 });
	 });
	
	$("#po_search").keyup(function(){
		q=$(this).val();
		if(jHR!=0)
			jHR.abort();
		clearTimeout(search_timer);
		search_timer=setTimeout(function(){
		jHR=$.post("<?=site_url("admin/jx_searchproducts")?>",{q:q},function(data){
			$("#po_prod_list").html(data).show();
		});});
	}).focus(function(){
		if($("#po_prod_list a").length==0)
			return;
		$("#po_prod_list").show();
	}).click(function(e){
		e.stopPropagation();
	});


/*	$('input[name="sch_type[]"] tr').live('change',function(){
		 	sch_type=$(this).val();
		
		if(mrgin.length)
			o.margin=mrgin;
		else
			o.margin=o.margin;

		
		if(!o.dp_price.length)
		{
			if(sch_type == 1)
				pprice=mrp-(mrp*parseFloat(o.margin)/100);
			else
				pprice=mrp-parseFloat(o.margin);
		}
	
	 });*/



	
	$('select[name="fil_vendor"]').live("change",function(){
		var vendorid=$(this).val();
		
		var vtext=$('select[name="fil_vendor"] option:selected').html();
		
		//alert(vtext);
		$('select[name="vendor[]"]:visible').find('option').each(function(){
			if($(this).val()==vendorid)
			{
				$r=$(this).parents("tr:visible");
			    $(this).attr('selected','selected');
			    $.post("<?=site_url("admin/jx_getbrandmargin")?>",{v:$(this).val(),b:$(".brand",$r).val(),c:$(".cat",$r).val()},function(data){
					$(".margin",$r).val(data).change();
				});
			}
		
	});
});

	$('select[name="fil_partner"]').live("change",function(){
		var partnerid=$(this).val();
		
		$('select[name="partner[]"]:visible').find('option').each(function(){
			if($(this).attr("value") == partnerid)
			{
				$r=$(this).parents("tr:first");
				$(this).attr('selected','selected');
				$.post("<?=site_url("admin/jx_getqtybypartnerorder")?>",{partnerid:$(this).val(),prodid:$(".product",$r).val()},function(data){
					$("#prod_qty_"+data.prod_id).val(data.qty).change();
				},'json');
			}
		});

	});
	
	$("#pprods .calc_pp").live("change",function(){
		$r=$(this).parents("tr").get(0);
		
		dp_price=parseFloat($(".dp_price",$r).val());
		
		dp_price = isNaN(dp_price)?'-1':dp_price;

		sch_type= $(".type",$r).val();

		
		mrp=parseFloat($(".mrp",$r).val());
		margin=parseFloat($(".margin",$r).val());
		discount=parseFloat($(".discount",$r).val())*1;
		if(dp_price == -1)
		{
				if(sch_type == 1)
				{
					$('.marg_prc_preview',$r).hide();
					mmrp=mrp-(mrp*parseFloat(margin+discount)/100);
				}
				else
				{
					mmrp=mrp-(mrp*parseFloat(margin)/100)-parseFloat(discount);
					margin_prc=(1-(mmrp/mrp))*100;
					$('.marg_prc_preview',$r).show();
					$('.marg_prc',$r).val(margin_prc);
				}
		}
		else
		{
			
				if(sch_type == 1)
				{
					$('.marg_prc_preview',$r).hide();
					mmrp=dp_price-(dp_price*parseFloat(margin+discount)/100);
					
				}
				else
				{
					mmrp=dp_price-(dp_price*parseFloat(margin)/100)-parseFloat(discount);
					margin_prc=(1-(mmrp/dp_price))*100;
					$('.marg_prc_preview',$r).show();
					$('.marg_prc').val(margin_prc);
				}
			
			if(dp_price > mrp)
				alert("Error:: Please note DP Price cannot be greater than MRP");
					
		}
			
		if(isNaN(mmrp))
			mmrp="-";
		$(".pprice",$r).val(mmrp);
	});
	
	
	$("#pprods .vendor").live("change",function(){
		$r=$(this).parents("tr").get(0);
		$.post("<?=site_url("admin/jx_getbrandmargin")?>",{v:$(this).val(),b:$(".brand",$r).val(),c:$(".cat",$r).val()},function(data){
				$(".margin",$r).val(data).change();
		});
	});
	
	
	$("#pprods .partner").live("change",function(){
		$r=$(this).parents("tr").get(0);
		$.post("<?=site_url("admin/jx_getqtybypartnerorder")?>",{partnerid:$(this).val(),prodid:$(".product",$r).val()},function(data){
				$("#prod_qty_"+data.prod_id).val(data.qty).change();
		},'json');
	
});


function view_orderdet(pid)
{
	$("#orderd_productdet").data('product_id',pid).dialog('open');
}

$('#orderd_productdet').dialog({
	modal:true,
	autoOpen:false,
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
$('.leftcont').hide();



$("#sl_show").click(function(){
	$("#sl_products").dialog('open');
});

$("#sl_products").dialog({
	width:970,
	resizable:false,
	modal:true,
	height:550,
	autoOpen:false,
	open: function(event, ui) {
        $(event.target).dialog('widget')
        .css({ position: 'fixed' })
        .position({ my: 'center', at: 'center', of: window });
        
				$("#sl_products .datagrid tbody").html("<tr><td colspan='8' align='left'>Loading .....</td></tr>");
				bid=$('select[name="fil_brand"]').val();
				//vid=$("#vendoridhidden").val();
				$("#loading_bar").show();
				$.post(site_url+'/admin/jx_getproductsforbrand',{bid:bid},function(json){
					$("#loading_bar").hide();
					data=$.parseJSON(json);
					brand_prods=data;
					$("#sl_products .datagrid tbody").html("");
					if(data.length )
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
								po_ord_qty = p.pen_ord_qty; 
								
								
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
$(".chk_all").click(function(){
	if($(this).attr("checked"))
		$(".sl_sel_prod").attr("checked",true);
	else
		$(".sl_sel_prod").attr("checked",false);
});


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
$("#filter_prods").hide();
$(".close_filters").toggle(function() {
    $(".close_filters .close_btn").html("Hide");
    $("#filter_prods").slideDown();

},function() {
    $("#filter_prods").slideUp();
    
    $(".close_filters .close_btn").html("Show");
   
});
$('.po_filters').hide();
$(".offer_tdcont").hide();

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
