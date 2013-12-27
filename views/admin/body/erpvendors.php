<div class="container">

<h2>Vendors <?=isset($brand)?" for $brand products":""?></h2>
<div width="100%">
<span style="float: right;margin-right: 8px;"><a href="<?=site_url("admin/addvendor")?>">Add New</a></span> 

<span style="float: left;padding:5px;">
View by brand : <select id="vend_disp_brands">
<option value="0">select</option>
<?php foreach($this->db->query("select distinct(b.id),b.name from m_vendor_info v join m_vendor_brand_link vb on vb.vendor_id=v.vendor_id join king_brands b on b.id=vb.brand_id order by b.name asc")->result_array() as $b){?>
<option value="<?=$b['id']?>"><?=$b['name']?></option>
<?php }?>
</select>
</span>
</div>
<div class="clear"></div>
<table class="datagrid datagridsort" width="100%">
<thead>
<tr>
<th>Name</th>
<th>Contact Details</th>
<th>Brands Supported</th>
<th>Total POs raised</th>
<th>Total PO value</th>
<th>Active</th>
<th width="134px">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($vendors as $v){?>
<tr>
<td><?=$v['vendor_name']?><br><?=$v['city_name']?></td>
<td><?=$v['contact_name']?><br><?=$v['mobile_no_1']?><br><?=$v['email_id_1']?></td>
<td><?php foreach($this->erpm->getbrandsforvendor($v['vendor_id']) as $i=>$b){?><?=$i>0?", ":""?><a href="<?=site_url("admin/viewbrand/{$b['id']}")?>"><?=$b['name']?></a><?php }?></td>
<td><?=$v['pos']?></td>
<td>Rs <?=number_format($v['total_value'])?></td>
<td><?=$v['is_active']?"YES":"NO"?></td>
<td>
<a class="link" href="<?=site_url("admin/vendor/{$v['vendor_id']}")?>">view</a> &nbsp;
<a class="dbllink" href="<?=site_url("admin/editvendor/{$v['vendor_id']}")?>">edit</a>&nbsp;
<a href="<?php echo site_url("admin/purchaseorder/{$v['vendor_id']}")?>" target="_blank" >Create PO</a>
</td>
</tr>
<?php }?>
</tbody>
</table>

</div>
<script>
$(".datagrid").tablesorter({sortList: [[0,0]]});
$(function(){
	$("#vend_disp_brands").change(function(){
		v=$(this).val();
		if(v!="0")
			location='<?=site_url("admin/vendorsbybrand")?>/'+v;
	});
});
</script>
<style>
.leftcont{display:none;}
</style>
<?php
