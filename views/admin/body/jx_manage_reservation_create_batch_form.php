<div>
    <select name="batch_group_name" id="batch_group_name">
        <option value="00">Select Batch Group Name</option>
        <?php
        foreach ($batch_conf as $conf) {?>
            <option value="<?=$conf['id'];?>"><?=$conf['batch_grp_name'];?></option>
        <? }
        
        ?>
    </select>
    
    <input name="assigned_menuids" id="assigned_menuids" type="text" value="" />
    <input name="batch_size" id="batch_size" type="text" value="" />
    <input name="assigned_uid" id="assigned_uid" type="text" value="" />
    
    <?php /*<option value="00">Select</option>
    foreach ($batch_conf as $conf) {
        $arr_menuids = explode(",",$conf['assigned_menuid']);
        foreach($arr_menuids as $menuid) {
        ?>
        <option value="<?=$menuid;?>"><?=$menuid;?></option>
    <?php }
    }</select>*/
    ?>
    
    <?php /*
    <select id="dlg_sel_territory" name="dlg_sel_territory" >
        <option value="00">All Territory</option>
        <?php  foreach($pnh_terr as $terr): ?>
                <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
        <?php endforeach; ?>
    </select> */ ?>
    <?php/*<select id="dlg_sel_town" name="dlg_sel_town">
        <option value="00">All Towns</option>
         foreach($pnh_towns as $town): ?>
                <option value="<?php echo $town['id'];?>"><?php echo $town['town_name'];?></option>
        <?php endforeach; 
    </select>*/ ?>
   <?php /* <select id="dlg_sel_franchise" name="dlg_sel_franchise" style="width: 204px;">
        <option value="00">All Franchise</option>
    </select>*/
    ?>
</div>
