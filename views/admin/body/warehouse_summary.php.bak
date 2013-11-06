<div class="container">
<h2>Warehouse Summary</h2>

<div class="dash_bar">Total Products in Stock :<span><?=$this->db->query("select count(distinct p.product_id) as n from m_product_info p join t_stock_info s on s.product_id=p.product_id where available_qty>0")->row()->n?></span></div>

<?php 
	if($this->erpm->auth(true,true)){
?>
<div class="dash_bar">Total Value : <span>Rs <?=number_format($this->db->query("select sum(p.mrp*s.available_qty) as n from m_product_info p join t_stock_info s on s.product_id=p.product_id where s.available_qty>0")->row()->n,2)?></span></div>
<?php } ?>

<div class="dash_bar">Brands in stock : <span><?=$this->db->query("select count(distinct p.brand_id) as n from m_product_info p join t_stock_info s on s.product_id=p.product_id where s.available_qty>0")->row()->n?></span></div>

<div class="clear"></div>

<div class="dash_bar">View Stock products by brand : <select id="brand"><option value="0">select</option>
<?php foreach($this->db->query("select name,id from king_brands where id in (select p.brand_id from m_product_info p join t_stock_info s on s.product_id=p.product_id where s.available_qty>0) order by name asc")->result_array() as $b){?>
<option value="<?=$b['id']?>" <?php if($this->uri->segment(4)==$b['id']){?>selected<?php }?>><?=$b['name']?></option>
<?php }?>
</select></div>
<div class="clear"></div>
<?php if(isset($products)){?>
<h3><?=$pagetitle?></h3>
<table class="datagrid">
<thead><tr><Th>Sno</Th><th>Product Name</th><th>MRP</th><th>Stock Qty</th><th>MRP Value</th><th>Avg Purchase Price</th><th>Avg Total purchase</th></tr></thead>
<tbody>
<?php $t_sv=$t_avg=0;$i=1; foreach($products as $p){?>
<tr>
<td><?=$i++?></td>
<td><a class="link" href="<?=site_url("admin/product/{$p['product_id']}")?>"><?=$p['product_name']?></a></td>
<td>Rs <?=$p['mrp']?></td>
<td><?=$p['stock']?></td>
<td>Rs <?=$p['stock_value']?></td>
<td>Rs <?php $avg=round($this->db->query("select avg(purchase_price) as a from t_grn_product_link where product_id=?",$p['product_id'])->row()->a,2); echo $avg;?></td>
<td>Rs <?=number_format($p['stock']*$avg,2)?></td>
</tr>
<?php $t_sv+=$p['stock_value']; $t_avg+=$p['stock']*$avg; }?>
<tr><td colspan="4" align="right">Total :</td><td>Rs <?=number_format($t_sv)?></td><td></td><td><?=number_format($t_avg)?></td></tr>
</tbody>

</table>

<?php }?>

<script>

$(function(){
	$("#brand").change(function(){
		if($(this).val()=="0")
			return;
		location="<?=site_url("admin/warehouse_summary/1")?>/"+$(this).val();
	});
});

</script>


</div>
<?php
