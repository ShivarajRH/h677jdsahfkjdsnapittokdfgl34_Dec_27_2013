<style>
.viewbrand h4{
margin-bottom:0px;
}
</style>
<div class="container viewbrand">
<div style="float:left;width:40%;">
<h2><?=ucfirst($brand['name'])?> Brand Details</h2>

<table class="datagrid">
<thead>
<tr>
<th>Brand Name</th><th>Allotted Rack n bins</th>
</tr>
</thead>
<tbody>
<tr>
<td><?=$brand['name']?> <a href="<?=site_url("admin/editbrand/{$brand['id']}")?>" class="link">edit</a></td>
<td><?php foreach($rbs as $rb){?>
<div><?=$rb['rack_name']?><?=$rb['bin_name']?></div>
<?php }?>
</td>
</tr>
</tbody>
</table>
</div>

<div style="padding-left:20px;float:left">
<h4>Vendors for <?=$brand['name']?> products (<?=count($vendors)?>)</h4>
<table class="datagrid">
<thead>
<tr>
<th>Vendor</th><th>Margin</th><th>City</th>
</tr>
</thead>
<tbody>
<?php foreach($vendors as $v){?>
<tr>
<td><a class="link" href="<?=site_url("admin/vendor/{$v['vendor_id']}")?>"><?=$v['vendor_name']?></a></td>
<td><?=$v['brand_margin']?>%</td>
<td><?=$v['city_name']?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>

<div class="clear"></div>

<div style="float:left;width:40%;">
<h4>Products of <?=$brand['name']?> (<?=count($products)?>)</h4>
<div style="max-height:300px;overflow:auto;">
<table class="datagrid" width="100%">
<thead>
<tr>
<th>Product Name</th><th>MRP</th><th>Barcode</th>
</tr>
</thead>
<tbody>
<?php foreach($products as $p){?>
<tr>
<td><a class="link" href="<?=site_url("admin/editproduct/{$p['product_id']}")?>"><?=$p['product_name']?></a></td>
<td><?=$p['mrp']?></td>
<td><?=$p['barcode']?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>
</div>

<div style="float:left;width:40%;margin-left:20px;">
<h4>Deals of <?=$brand['name']?> (<?=count($deals)?>)</h4>
<div style="max-height:300px;overflow:auto;">
<table class="datagrid" width="100%">
<thead>
<tr><th>Deal Name</th><th>URL</th><th>MRP</th><th>Price</th></tr>
</thead>
<tbody>
<?php foreach($deals as $p){?>
<tr>
<td><a class="link" href="<?=site_url("admin/edit/{$p['dealid']}")?>"><?=$p['name']?></a></td>
<td><a class="link" href="<?=site_url("{$p['url']}")?>">site</a></td>
<td><?=$p['orgprice']?></td>
<td><?=$p['price']?></td>
</tr>
<?php }?>
</tbody>
</table>
</div>
</div>

</div>
<?php
