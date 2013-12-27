<?php 
	$v=false;
	if(isset($vendor))
		$v=$vendor;
	$vendor_id=$v['vendor_id'];
?>
<form method="post" id="venfrm"  autocomplete="off">
<div class="container">
<h2><?php echo $v['vendor_name']?></h2>&nbsp;<b style="font-size: 11px;" ><?=$v?'<input type="submit" value="Save" style="float:right;margin-left: 188px;margin-top: -41px;" class="save_pobtn">':"New" ?></b>



<div class="tab_view">

<ul>
<li><a href="#v_details">Basic Details</a></li>
<li><a href="#v_financials">Finance Details</a></li>
<li><a href="#v_extra">Extra Details</a></li>
<li><a href="#v_contacts">Contacts</a></li>
<li><a href="#v_linkbrands" class="link_br">Link Brands</a></li>

</ul>

<div id="v_details">
<table>
<tr><td>Name <span class="red_star">*</span> :</td><td><input type="text" name="name" class="inp val_req" value="<?=$v?"{$v['vendor_name']}":""?>"></td></tr>
<tr><td>Address Line 1 :</td><td><input type="text" name="addr1" class="inp" size="40" value="<?=$v?"{$v['address_line1']}":""?>"></td></tr>
<tr><td>Address Line 2 :</td><td><input type="text" name="addr2" class="inp" size="30" value="<?=$v?"{$v['address_line2']}":""?>"></td></tr>
<tr><td>Locality :</td><td><input type="text" name="locality" class="inp" value="<?=$v?"{$v['locality']}":""?>"></td></tr>
<tr><td>Landmark :</td><td><input type="text" name="landmark" class="inp" value="<?=$v?"{$v['landmark']}":""?>"></td></tr>
<tr><td>City <span class="red_star">*</span> :</td><td><input type="text" name="city" class="inp val_req" value="<?=$v?"{$v['city_name']}":""?>"></td></tr>
<tr><td>State :</td><td><input type="text" name="state" class="inp" value="<?=$v?"{$v['state_name']}":""?>"></td></tr>
<tr><td>Country :</td><td><input type="text" name="country" class="inp" value="<?=$v?"{$v['country']}":""?>"></td></tr>
<tr><td>Postcode :</td><td><input type="text" name="postcode" class="inp" value="<?=$v?"{$v['postcode']}":""?>"></td></tr>
</table>
</div>
<div id="v_financials">
<table>
<tr><td>Ledger ID :</td><td><input type="text" name="ledger" class="inp" value="<?=$v?"{$v['ledger_id']}":""?>"></td></tr>
<tr><td>Credit Limit :</td><td><input type="text" name="credit_limit" class="inp" value="<?=$v?"{$v['credit_limit_amount']}":""?>"></td></tr>
<tr><td>Credit Days :</td><td><input type="text" name="credit_days" class="inp" value="<?=$v?"{$v['credit_days']}":""?>"></td></tr>
<tr><td>Payment Advance :</td><td><input type="text" name="advance" class="inp" size=3 value="<?=$v?"{$v['require_payment_advance']}":""?>">%</td></tr>
<tr><td>CST :</td><td><input type="text" name="cst" class="inp" value="<?=$v?"{$v['cst_no']}":""?>"></td></tr>
<tr><td>PAN :</td><td><input type="text" name="pan" class="inp" value="<?=$v?"{$v['pan_no']}":""?>"></td></tr>
<tr><td>VAT :</td><td><input type="text" name="vat" class="inp" value="<?=$v?"{$v['vat_no']}":""?>"></td></tr>
<tr><td>Service Tax :</td><td><input type="text" name="stax" class="inp" value="<?=$v?"{$v['service_tax_no']}":""?>"></td></tr>
<tr><td>Average TAT :</td><td><input type="text" name="tat" class="inp" value="<?=$v?"{$v['avg_tat']}":""?>"></td></tr>
</table>
</div>
<div id="v_extra">
<table>
<tr><td>Return Policy :</td><td><textarea class="inp" name="rpolicy"><?=$v?"{$v['return_policy_msg']}":""?></textarea></td></tr>
<tr><td>Payment Terms :</td><td><textarea class="inp" name="payterms"><?=$v?"{$v['payment_terms_msg']}":""?></textarea></td></tr>
<tr><td>Remarks :</td><td><textarea class="inp" name="remarks"><?=$v?"{$v['remarks']}":""?></textarea></td></tr>
</table>
</div>
<div id="v_contacts">
<input type="button" value="+ new contact" onclick='clone_vcnt()'>
<div id="v_contact_cont">
<?php if($v){foreach($contacts as $c){?>

<table>
<tr><td>Name : </td><td><input type="text" class="inp" name="cnt_name[]" value="<?=$c['contact_name']?>"></td>
<td>Designation : </td><td><input type="text" class="inp" name="cnt_desgn[]" value="<?=$c['contact_designation']?>"></td>
</tr>
<tr>
<td>Mobile 1 : </td><td><input type="text" class="inp" name="cnt_mob1[]" value="<?=$c['mobile_no_1']?>"></td>
<td>Mobile 2 : </td><td><input type="text" class="inp" name="cnt_mob2[]" value="<?=$c['mobile_no_2']?>"></td>
</tr>
<tr>
<td>Telephone : </td><td><input type="text" class="inp" name="cnt_telephone[]" value="<?=$c['telephone_no']?>"></td>
<td>FAX : </td><td><input type="text" class="inp" name="cnt_fax[]" value="<?=$c['fax_no']?>"></td>
</tr>
<tr>
<td>Email 1 : </td><td><input type="text" class="inp" name="cnt_email1[]" value="<?=$c['email_id_1']?>"></td>
<td>Email 2 : </td><td><input type="text" class="inp" name="cnt_email2[]" value="<?=$c['email_id_2']?>"></td>
</tr>
</table>
<?php } }?>
</div>
</div>

<div id="v_linkbrands">
	
	
	<div class="po_filter_wrap2">
		<div style="width:49%;float:left">
			<span><b style="margin:3px 5px;float: left">Filter by : </b></span> 
			<select name="fil_cat" class="fil_cat" style='width:200px;' data-placeholder='Category'>
			</select>
		</div>
		
		<div style="width:49%;float:right">
			<span><b style="margin:3px 5px;float: left">Filter by : </b></span>
			<select name='fil_brand' class='fil_brand' style="width:200px;" data-placeholder='Brand'>
				
			</select>
		</div>
	</div>
	
	<div>Category : <button type="button" class="add_pop">Add</button></div>
	<div id="po_filter_wrap1" style="display:none;" title="Add Category">
		<div class="po_filter_blk">
			<div id="filter_prods">
			<table cellspacing='10'>
					<tr><td><b style="float:left;margin:3px 5px">Category : </b></td><td><select name="select_cat" class="select_cat" style='width:230px;'>
							<option value="">Select Category</option>
							<?php $cats=$this->db->query("select id,name from king_categories  GROUP BY name order by name asc")->result_array();?>
							<?php foreach($cats as $c){?>
								<option value="<?php echo $c['id']?>"><?php echo $c['name']?></option>
							<?php }?></select></td></tr>
							
					
					
					<tr class='show_brand'>
					<td><b style="float:left;margin:3px 15px">Brand : </b></td>
					<td><select name='select_brand' class='select_brand' style="width:220px;height:300px;" multiple="true" data-placeholder='Select Brand'><option value=''>select brand</option></select></td>
					</tr>
					</table>
			</div>	
		</div>
	</div>
	
	
	
	
	

<table class="datagrid v_lbtable" width="100%">
<thead>
<tr>
<th>
	&nbsp;
</th>
<th>Brand</th>
<th>Category</th>

<th>Margin %</th>
<th>Applicable From</th>
<th colspan=2>Applicable Until</th>
</tr>
</thead>
<tbody>
<?php if($v){ foreach($cat_brands as $i=>$b){
	
?>
<tr class="brands_cat_det">
<td><input type="checkbox" class="edit_vblink_chk" ></td>
<td><input type="hidden" disabled="disabled" class="inp" name="l_brand[]" value="<?=$b['brand_id']?>"><?=$b['brand_name']?></td>
<td>
<input type="hidden" disabled="disabled" class="inp" name="l_catid[]" value="<?=$b['cat_id']?>"><?=$b['category_name']? $b['category_name'] :'All'?>&nbsp;<a style=" font-size: 10px;"id="show_cat" href="javascript:void(0)" onclick="load_allcatsofbrand(<?php echo $b['brand_id']?>)">edit cat</a></td>
<td><input type="text" disabled="disabled"  class="inp" name="l_margin[]" value="<?=$b['brand_margin']?>"></td>
<td><input type="text" disabled="disabled"  class="inp datepic lb_date lb_date<?=$i?>" name="l_from[]" value="<?php echo $b['applicable_from']!=0 ? $b['applicable_from']:0;?>" ></td>
<td><input type="text" disabled="disabled"  class="inp datepic lb_date lb_date<?=$i?>t" name="l_until[]" value="<?php echo $b['applicable_till']!=0 ? $b['applicable_till']:0;?>"></td>
<td>
	<a href="javascript:void(0)" onclick="remove_vblink(this)" >remove</a>
</tr>
<?php }}?>
</tbody>
</table>
<!--  <input type="submit" value="Submit" style="float:right;">-->

</div>
</div>
</div>
</form>


<div id="all_catdiv" title="Categories" style="display:none;">
<form id="update_cat_margin"  method="post" action="<?php echo site_url('admin/to_update_brand_marg_bycat/'.$vendor_id)?>">
<table class="datagrid" id="cat_brandtable" width="100%">
<tr><td><input type="hidden" name="brandid" id="brandid" value=""></td></tr>
<thead>
<th>
	&nbsp;
</th>
<th>Brand</th>
<th>Category</th>

<th>Margin %</th>
<th>Applicable From</th>
<th colspan=2>Applicable Until</th>
</tr>
</thead>
<tbody></tbody>
</table>
</form>
</div>

</div>

<div style="display:none">
<table id="lb_template" class="datagrid">
<tbody>
<tr>
<td>&nbsp;</td>
<td><input type="hidden" name="l_brand[]" value="%brandid%">%brand%</td>
<td><input type="hidden" name="l_cat[]" value="%catid%">%cat%</td>

<td><input type="text" class="inp" name="l_margin[]" value="10"></td>
<td><input type="text" class="inp lb_date lb_date%di%" name="l_from[]"></td>
<td><input type="text" class="inp lb_date lb_date%di%t" name="l_until[]"></td>
<td><a href="javascript:void(0)" onclick="remove_vblink(this)" >remove</a>
</tr>
</tbody>
</table>
</div>

<table id="cnt_clone">
<tr><td>Name : </td><td><input type="text" class="inp" name="cnt_name[]"></td>
<td>Designation : </td><td><input type="text" class="inp" name="cnt_desgn[]"></td>
</tr>
<tr>
<td>Mobile 1 : </td><td><input type="text" class="inp" name="cnt_mob1[]"></td>
<td>Mobile 2 : </td><td><input type="text" class="inp" name="cnt_mob2[]"></td>
</tr>
<tr>
<td>Telephone : </td><td><input type="text" class="inp" name="cnt_telephone[]"></td>
<td>FAX : </td><td><input type="text" class="inp" name="cnt_fax[]"></td>
</tr>
<tr>
<td>Email 1 : </td><td><input type="text" class="inp" name="cnt_email1[]"></td>
<td>Email 2 : </td><td><input type="text" class="inp" name="cnt_email2[]"></td>
</tr>
</table>


<style>
#cnt_clone{
display:none;
}
#v_contact_cont table{
margin:10px;
border:1px solid #ccc;
padding:5px;
}
#v_searchres{
display:none;
position:absolute;
width:200px;
height:80px;
overflow:auto;
background:#eee;
border:1px solid #aaa;
}
#v_searchres a{
display:block;
padding:5px;
}
.add_pop
{
	margin-top: 8px 8px 8px 0;
}
.show_brand
{
	margin-top:15px;
}
#v_searchres a:hover{
background:blue;
color:#fff;
}
td.selected{
	background: #b4defe !important;
}
.required_inp{border:1px solid #cd0000 !important;}
.red_star{color: #cd0000;font-size: 20px;font-weight: bold;margin-left: 5px;}
#show_cat{display:none;}
.popclose_button.b-close, .popclose_button.bClose {
    border-radius: 7px;
    box-shadow: none;
    font: bold 131% sans-serif;
   padding: 6px 10px 5px;
    position: absolute;
    right: -7px;
    top: -7px;
}
.popclose_button > span {
    font-size: 84%;
}
.popclose_button {
    background-color: #2B91AF;
    border-radius: 10px;
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3);
    color: #FFFFFF;
    cursor: pointer;
    display: inline-block;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
}
/* .po_filter_wrap1 {
    min-height: 250px;
}
.po_filter_wrap1{
    background-color: #FFFFFF;
    border-radius: 10px;
    box-shadow: 0 0 25px 5px #999999;
    color: #111111;
    display: none;
    min-width: 450px;
    padding: 25px;
} */
</style>
<script>
$('#load_brnd_catlink').hide();
$('.show_brand').hide();
$('.select_cat ').chosen();
$(".select_brand ").chosen();
$('.fil_cat').chosen();
$('.fil_brand').chosen();
//$('.po_filter_blk').hide();


$('.add_pop').click(function(){
	$('#po_filter_wrap1').dialog('open');
});

$('#po_filter_wrap1').dialog({
modal:true,
	autoOpen:false,
	width:'519',
	height:'300',
	open:function(){
	},
buttons:{
	'Submit':function(){
			update_new_cat();
			$(this).dialog('close');
	},
	'Cancel':function(){
		$(this).dialog('close');
	}
}
});
$('.fil_cat').change(function(){
	var catid=$(this).val();
	var ven_id = '<?php echo $this->uri->segment(3);?>';
	$(".fil_brand").html('').trigger("liszt:updated");
	$.post(site_url+'/admin/jx_load_brand_byvendor_bycatid',{catid:catid,vid:ven_id},function(resp){
		var brands_html='';
		if(resp.status=='error')
		{
			alert(resp.message);
		}
		else
		{
			brands_html+='<option value=""></option>';
			brands_html+='<option value="0">All</option>';
			$.each(resp.brd_list,function(i,b){
			brands_html+='<option value="'+b.brandid+'">'+b.brand_name+'</option>';
			});
		}
		 $('.fil_brand').html(brands_html).trigger("liszt:updated");
	},'json');
	
	$.post(site_url+'/admin/jx_load_vendor_cat',{catid:catid,vid:ven_id},function(resp){
		if(resp.status=="success")
		{
			$(this).attr('disabled',true);
			var template='';
			$.each(resp.res_list,function(i,c){
				template +=	"<tr>"
							+"<td><input type='checkbox' class='edit_vblink_chk' checked='checked' ></td>"
							+"<td><input type='hidden'  class='inp' name='l_brand[]' value='"+c.brandid+"'>"+c.brand_name+"</td>"
							+"<td><input type='hidden'  class='inp' name='l_catid[]' value='"+c.catid+"'>"+c.category_name+"</td>"
							+"<td><input type='text'   class='inp' name='l_margin[]' value='"+c.brand_margin+"'></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_from[]' value='"+c.applicable_from+"'></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_until[]' value='"+c.applicable_till+"'></td>"
							+"<td><a href='javascript:void(0)' onclick='remove_vblink(this)' >remove</a>"
							+"</tr>";
				
			});
			$('.v_lbtable tbody').html(template);
			$('.v_lbtable .lb_date').each(function(i,dpEle){
				if(!$(this).hasClass('hasDatepicker'))
					$(this).datepicker();
			});
			
		}else
		{
			alert(resp.msg);
			$(this).attr('disabled',false);
		}
	},'json');
});

$('.link_br').click(function(){
	var ven_id = '<?php echo $this->uri->segment(3);?>';
	if(ven_id)
	{
		$('.po_filter_wrap2').show();
		var ven_id = '<?php echo $this->uri->segment(3);?>';
		$(".fil_cat").html('').trigger("liszt:updated");
		$.post(site_url+'/admin/jx_load_cat_byvendor',{ven_id:ven_id},function(resp){
			var cat_html='';
			if(resp.status=='error')
			{
				alert(resp.message);
			}
			else
			{
				cat_html+='<option value=""></option>';
				cat_html+='<option value="0">All</option>';
				$.each(resp.ct_list,function(i,c){
				cat_html+='<option value="'+c.catid+'">'+c.category_name+'</option>';
				});
			}
			 $('.fil_cat').html(cat_html).trigger("liszt:updated");
		},'json');
		
		$.post(site_url+'/admin/jx_load_brand_byvendor',{ven_id:ven_id},function(resp){
			var brand_html='';
			if(resp.status=='error')
			{
				alert(resp.message);
			}
			else
			{
				brand_html+='<option value=""></option>';
				brand_html+='<option value="0">All</option>';
				$.each(resp.br_list,function(i,c){
				brand_html+='<option value="'+c.brandid+'">'+c.brand_name+'</option>';
				});
			}
			 $('.fil_brand').html(brand_html).trigger("liszt:updated");
		},'json');
	}
	else
	{
		$('.po_filter_wrap2').hide();
	}
	
});

$('.fil_brand').change(function(){
	var catid=$('select[name="fil_cat"]').val();
	var brand_id=$('select[name="fil_brand"]').val();
	var ven_id = '<?php echo $this->uri->segment(3);?>';
	
	$.post(site_url+'/admin/jx_load_vendordet_bybrand',{catid:catid,brand_id:brand_id,ven_id:ven_id},function(resp){
		if(resp.status=="success")
		{
			$(this).attr('disabled',true);
			var temp='';
			$.each(resp.res_list,function(i,c){
				temp += "<tr>"
							+"<td><input type='checkbox' class='edit_vblink_chk' checked='checked' ></td>"
							+"<td><input type='hidden'  class='inp' name='l_brand[]' value='"+c.brandid+"'>"+c.brand_name+"</td>"
							+"<td><input type='hidden'  class='inp' name='l_catid[]' value='"+c.catid+"'>"+c.category_name+"</td>"
							+"<td><input type='text'   class='inp' name='l_margin[]' value='"+c.brand_margin+"'></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_from[]' value='"+c.applicable_from+"'></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_until[]' value='"+c.applicable_till+"'></td>"
							+"<td><a href='javascript:void(0)' onclick='remove_vblink(this)' >remove</a>"
							+"</tr>";
			});
			$('.v_lbtable tbody').html(temp);
			$('.v_lbtable .lb_date').each(function(i,dpEle){
				if(!$(this).hasClass('hasDatepicker'))
					$(this).datepicker();
			});
			
		}else
		{
			alert(resp.msg);
			$(this).attr('disabled',false);
		}
	},'json');
});

$('.select_cat').change(function(){
	
	var sel_catid=$(this).val();
	if(sel_catid!='0')
	{
		$('.show_brand').show();
		$('#load_brnd_catlink').show();
		$(".select_brand").html('').trigger("liszt:updated");
		$.getJSON(site_url+'/admin/jx_load_allbrandsbycat/'+sel_catid,'',function(resp){
		var brands_html='';
		if(resp.status=='error')
		{
			alert(resp.message);
		}
		else
		{
			brands_html+='<option value=""></option>';
			brands_html+='<option value="0">All</option>';
			$.each(resp.brand_list,function(i,b){
			brands_html+='<option value="'+b.brandid+'">'+b.name+'</option>';
			});
		}
		 $('.select_brand').html(brands_html).trigger("liszt:updated");
		
		});
		
	}
	
});
$('#venfrm').submit(function()
{
	$('.val_req',this).each(function(){
		$(this).val($.trim($(this).val()));
		if($(this).val())
			$(this).removeClass('required_inp');
		else
			$(this).addClass('required_inp');
	});


	if($('.edit_vblink_chk').attr("checked"))
	{
		var margin=$('input[name="l_margin[]"]').val();
		
		if(isNaN(margin*1))
		{
			alert('Margin need to be filled');
				return false;
		}
		
	}
	if($('.datepic',this).val()==0)
	{
		alert("Applicable from date must be filled");
		return false;
	}

	if($('.required_inp',this).length)
	{
		alert("Vendor name and city is required");
		return false;
	}

	if(!$('.v_lbtable tbody tr',this).length)
	{
		alert("Link atlease one brand for this vendor");
		return false;
	}


});

var p_added=[],b_added=[];
var ven_id = '<?php echo $this->uri->segment(3);?>';
<?php if($v){foreach($brands as $b){?>
b_added.push(<?=$b['brand_id']?>);
<?php }}?>

//$('#load_brnd_catlink').click(function(){
function update_new_cat(){	
	var select_brand=$('.select_brand').val();
	var cat_id=$('.select_cat').val();
	var ven_id = '<?php echo $this->uri->segment(3);?>';
	$.post(site_url+'/admin/update_vendor_catgory_brand',{catid:cat_id,brandid:select_brand,vendorid:ven_id},function(resp){
		if(resp.status=="success")
		{
			$(this).attr('disabled',true);
			
			$.each(resp.brnd_cat_res,function(i,c){
				var template=''
							+"<tr>"
							+"<td><input type='checkbox' class='edit_vblink_chk' checked='checked' ></td>"
							+"<td><input type='hidden'  class='inp' name='l_brand[]' value='"+c.brandid+"'>"+c.brand_name+"</td>"
							+"<td><input type='hidden'  class='inp' name='l_catid[]' value='"+c.catid+"'>"+c.category_name+"</td>"
							+"<td><input type='text'   class='inp' name='l_margin[]' value=''></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_from[]' value=''></td>"
							+"<td><input type='text'   class='inp datepic lb_date' name='l_until[]' value=''></td>"
							+"<td><a href='javascript:void(0)' onclick='remove_vblink(this)' >remove</a>"
							+"</tr>"
				$(template).prependTo(".v_lbtable tbody");
				
			});

			$('.v_lbtable .lb_date').each(function(i,dpEle){
				if(!$(this).hasClass('hasDatepicker'))
					$(this).datepicker();
			});
			
		}else
		{
			alert(resp.msg);
			$(this).attr('disabled',false);
		}
	},'json');
}

	
$('.edit_vblink_chk').live('change',function(){
	var tds = $(this).parent().parent().find('td');
	if($(this).attr("checked"))
	{
		tds.addClass('selected');
		tds.find(".inp").attr("disabled",false);
		var catid_val=tds.find('input[name="l_catid[]"]').val();
		if(catid_val == 0)
			tds.find('#show_cat').show();
		else
			tds.find('#show_cat').hide();
	}else
	{
		tds.removeClass('selected');
		tds.find(".inp").attr("disabled",true);
		tds.find('#show_cat').hide();
		
	}
});

function remove_vblink(ele){
	
	var trEle = $(ele).parent().parent();
	if(ven_id)
	{
		
		if(confirm("Want to remove "+$('td:eq(1)',trEle).text()+" from this vendor ?"))
		{
			brand_id = $('input[name="l_brand[]"]',trEle).val();
			$.post(site_url+'/admin/jx_remove_vendor_brand_link','vendor_id='+ven_id+'&brand_id='+brand_id,function(resp){
				if(resp.status == 'error')
				{
					alert(resp.error);
				}else
				{
					trEle.fadeOut().remove();
				}
			},'json');
		}
	}
	else
	{
		trEle.fadeOut().remove();
	}		
}

function clone_vcnt()
{
	$("#v_contact_cont").append("<table>"+$("#cnt_clone").html()+"</table>");
}
function addproduct(id,name,mrp,tax)
{
	$("#v_searchres").hide();
	if($.inArray(id,p_added)!=-1)
	{
		alert("Product already added");
		return;
	}
	p_added.push(id);
	template='<tr><td><input type="hidden" name="pproduct[]" value="'+id+'">'+name+'</td><td><input class="inp" type="text" name="pmrp[]" value="'+mrp+'"></td><td><input type="text" class="inp" name="pprice[]"></td><td><input type="text" class="inp" name="ptax[]" value="'+tax+'"></td><td><input type="text" class="inp" name="pminorder[]"></td><td><input type="text" name="ptat" class="inp"></td><td><input type="text" class="inp" name="premarks[]"></td></tr>';
	$("#v_lptable").append(template);
	$("#v_lpsearch").val("");
}


$(function(){
	
	$(".lb_date").each(function(){
		$(this).datepicker();
	});
	
	if(b_added.length==0)
	clone_vcnt();
	$("#v_lpsearch").keyup(function(){
		$.post("<?=site_url("admin/searchproducts")?>",{q:$(this).val()},function(data){
			$("#v_searchres").html(data).show();
		});
	});

	$("#v_lbsearch").keyup(function(){
		$.post("<?=site_url("admin/jx_searchcategory")?>",{q:$(this).val()},function(data){
			$("#v_searchresb").html(data).show();
		});
	}).focus(function(){
		if($("#v_searchresb").html().length!=0)
			$("#v_searchresb").show();
	});
	
});


function load_allcatsofbrand(brandid)
{
	$('#all_catdiv').data('bid',brandid).dialog('open');
}

$("#all_catdiv").dialog({
	modal:true,
	autoOpen:false,
	 width:'800',
	height:'400',
		open:function(){
			dlg = $(this);
			$('#brandid').val("");
			 $('#categories').html("").trigger("liszt:updated");
			
			$(".selected_brand b").val("");
			//var cat_tbl_html="";
			$('input[name="brandid"]').val(dlg.data('bid'));
			 $("#cat_brandtable tbody").html("");
			$.post(site_url+'/admin/getbrand_cat_details',{brandid:dlg.data('bid'),vendorid:'<?php echo $vendor_id ;?>'},function(resp){
			
				$('.selected_brand').val(resp.brand_name);
				
				if(resp.status == 'success')
				{
					$.each(resp.cat_list,function(i,c){
						
						var	cat_tbl_html =""
										+"<tr>"
										+"<td><input type='checkbox' checked='checked' class='edit_vblink_chk'></td>"
										+"<td><input type='hidden' class='inp' name='l_brand[]' value='"+c.brandid+"'>"+resp.brand_name+"</td>"
										+"<td><input type='hidden'  class='inp' name='l_catid[]' value='"+c.catid+"'>"+c.cat_name+"</td>"
										+"<td><input type='text'    class='inp' name='l_margin[]' value='"+resp.brand_margin+"'></td>"
										+"<td><input type='text'  class='inp datepic from_date' name='l_from[]' value='"+resp.from_dt+"'></td>"
										+"<td><input type='text'   class='inp datepic to_date' name='l_until[]' value='"+resp.to_dt+"'></td>"
										+"<td><a href='javascript:void(0)' onclick='remove_vblink(this)' >remove</a>"
										+"</tr>"
										$("#cat_brandtable tbody").append(cat_tbl_html);
							 //$(cat_tbl_html).appendTo("#cat_brandtable tbody");	
									
						});

					$('#cat_brandtable .from_date').each(function(i,dpEle){
						if(!$(this).hasClass('hasDatepicker'))
							$(this).datepicker();
						
						if(!$('#cat_brandtable .to_date:eq('+i+')').hasClass('hasDatepicker'))
							$('#cat_brandtable .to_date:eq('+i+')').datepicker();
						
					});
					
				}
			},'json');
			},
			buttons:{
				'Cancel' :function(){
					$(this).dialog('close');
				},
				'Submit':function(){
					if($('input[name="l_from[]"]').val()==null || $('input[name="l_until[]"]').val()==null)
					{
						alert('Enter Date Range');
						return false;
					}
					else
					{
						$('#update_cat_margin').submit();
						$(this).dialog('close');
					}
				}
			}
});

$('.tab_view').tabs();

 

$(".close_btn").click(function() {

	if($("#filter_prods").is(':visible'))
	{
		$(".po_filter_head .close_btn").html("<img src='<?php echo IMAGES_URL?>acc_plus.png'>");
		$("#filter_prods").slideUp();	
	}else
	{
		$(".po_filter_head .close_btn").html("<img src='<?php echo IMAGES_URL?>acc_minus.png'>");
	    $("#filter_prods").slideDown();
	}
});

$(".close_brcbtn").click(function() {

	if($("#brnd_cat_fltrs").is(':visible'))
	{
		$(".po_br_cat_filter_head .close_brcbtn").html("<img src='<?php echo IMAGES_URL?>acc_plus.png'>");
		$("#brnd_cat_fltrs").slideUp();	
	}else
	{
		$(".po_br_cat_filter_head .close_brcbtn").html("<img src='<?php echo IMAGES_URL?>acc_minus.png'>");
	    $("#brnd_cat_fltrs").slideDown();
	}
});


</script>

<style>
.po_filters {
	background-color: rgb(223, 224, 240);
	 
  	padding:10px 0px;
	 
}
.po_head_blk
{
	color: #000000;
    font-size: 13px;
    font-weight: bold;
}
.po_filter_wrap2
{
	float: right;
	width:52%;
	margin-bottom:10px;
}

.po_filter_head
 {
    border-bottom: 1px solid #777777;
    padding: 7px 0 7px 12px;
}
.po_br_cat_filter_head
{
	border-bottom: 1px solid #777777;
    padding: 7px 0 7px 12px;
}

.close_btn {
    float: right;
    margin-right: 8px;
    cursor:pointer;
    color:#D50C0C;
    font-weight: bold;
    font-size: 11px;
}
#filter_prods{
	padding: 19px 35px 0 52px;
}
h3.filter_heading { margin-bottom: 0px; margin-top: 0;width: 788px;font-size: 11px;}


/* .po_br_cat_filter_head {
    display: table;
    width: 99%;
    float: right;
    cursor:pointer;
    background-color: #DFE0F0;
    padding: 2px 7px 2px 6px;
} */
.close_brcbtn {
    float: right;
    margin-right: 8px;
    cursor:pointer;
    color:#D50C0C;
    font-weight: bold;
    font-size: 11px;
}
#brnd_cat_fltrs{
	padding:7px 10px 9px;
}
.save_pobtn {
	background-color: rgb(227, 227, 227);
    background-image: linear-gradient(to bottom, rgb(239, 239, 239), rgb(216, 216, 216));
    border: 1px solid rgba(0, 0, 0, 0.4);
    border-radius: 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1), 0 1px 1px rgba(255, 255, 255, 0.8) inset;
    color: rgb(76, 76, 76);
    display: inline-block;
    font-size: 13px;
    margin: 0;
    outline: medium none;
    padding: 3px 12px;
    text-align: center;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.5);

}
</style>
<?php
