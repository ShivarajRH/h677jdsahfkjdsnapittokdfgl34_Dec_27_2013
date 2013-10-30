<html>
<head>
<title>Product procurement list</title>
</head>
<body style="font-family:arial;font-size:14px;">
<h2>Product procurement list for BATCH<?=$this->uri->segment(3)?></h2>
<table border=1 style="font-family:arial;font-size:13px;" cellpadding=3>
<tr style="background:#aaa">
<th>Product ID</th><th>Product Name</th><th>Qty</th><Th>MRP</Th><th>Location</th>
</tr>
<?php $i=0; foreach($prods as $p){?>
<tr <?php if($i%2==0){?>style="background:#eee;"<?php }?>>
<td><a target="_blank" href="<?php echo site_url('admin/product/'.$p['product_id'])?>"><?=$p['product_id']?></a></td>
<td><?=$p['product']?></td>
<td><?=$p['qty']?></td>
<?php list($loc,$mrp) = explode('::',$p['location']);?>
<td><?=$mrp?></td>
<td><?=$loc?>&nbsp;</td>
</tr>
<?php $i++;}?>
</table>
</body>
</html>
<?php
