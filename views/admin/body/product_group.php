<div class="container">

<h2>Product Group : <?=$group['group_name']?></h2>

<h4 style="margin:0px;margin-top:20px;">Attribute Classes</h4>
<table class="datagrid">
<thead><tr><th>Attributes</th><th>Possible Values</th></tr></thead>
<tbody>
<?php $as=$this->db->query("select group_concat(av.attribute_value) as `values`,a.attribute_name as name from products_group_attributes a join products_group_attribute_values av on av.attribute_name_id=a.attribute_name_id where a.group_id=? group by a.attribute_name_id order by a.attribute_name_id",$group['group_id'])->result_array(); foreach($as as $a){?>
<tr>
<td><?=$a['name']?></td><Td><?=$a['values']?></Td>
</tr>
<?php }?>
</tbody>
</table>


<h4 style="margin:0px;margin-top:20px;">Products Linked</h4>
<table class="datagrid">
<thead><tr><th>Product Name</th><?php foreach($as as $a){?><th><?=$a['name']?></th><?php }?></tr></thead>
<tbody>
<?php foreach($prods as $prod){?>
<tr>
<td><a href="<?=site_url("admin/product/{$prod['product_id']}")?>" class="link"><?=$prod['product_name']?></a></td>
<?php foreach($this->db->query("select av.attribute_value as value from products_group_pids p join products_group_attribute_values av on av.attribute_value_id=p.attribute_value_id where p.group_id=? and p.product_id=?",array($group['group_id'],$prod['product_id']))->result_array() as $a){?>
<td><?=$a['value']?></td>
<?php }?>
</tr>
<?php }?>
</tbody>
</table>

<br>
<h4 style="margin:0px;margin-top:20px;">Deals Linked</h4>
<table class="datagrid">
<thead><tr><th>Sno</th><th>Deal Name</th><th>MRP</th><th>Price</th></tr></thead>
<tbody>
<?php $i=1; foreach($this->db->query("select i.* from king_dealitems i join m_product_group_deal_link l on l.itemid=i.id where l.group_id=?",$group['group_id'])->result_array() as $p){?>
<tr>
<td><?=$i++?></td><td><a href="<?=site_url("admin/deal/{$p['id']}")?>"><?=$p['name']?></a></td><td><?=$p['orgprice']?></td><td><?=$p['price']?></td>
</tr>
<?php }?>
</tbody>
</table>



</div>
<?php
