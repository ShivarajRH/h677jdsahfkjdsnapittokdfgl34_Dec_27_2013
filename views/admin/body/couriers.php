<div class="container">
<h2>Couriers</h2>
<a href="<?=site_url("admin/add_courier")?>">Add new courier</a>
<table class="datagrid">
<thead>
<tr><th>Courier Name</th><th>Enabled for pincodes</th><th>Edit pincodes</th><th>Current AWB</th><th>Remaining AWBs</th><th>Update AWB series</th></tr>
</thead>
<tbody>
<?php foreach($couriers as $c){?>
<tr>
<td><?=$c['courier_name']?></td>
<td><?=$c['pincodes']?></td>
<td><a href="<?=site_url("admin/edit_pincodes/{$c['courier_id']}")?>">view/edit pincodes</a></td>
<td><?=$c['awb']?></td>
<td><?=$c['rem_awb']?></td>
<td><a href="<?=site_url("admin/update_awb/{$c['courier_id']}")?>">Update AWB</a></td>
</tr>
<?php }?>
</tbody>
</table>
</div>
<?php
