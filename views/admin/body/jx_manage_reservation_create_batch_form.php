<div>
    <form method="post" action="">
        <table width="100%" border="0" cellspacing="4">
            <tr>
                <td>Batch Type: <span class="mark">*</span></td>
                <td>
                    <select name="batch_group_name" id="batch_group_name" style="width: 204px;">
                        <option value="00">Choose</option>
                        <?php
                        foreach ($batch_conf as $conf) {?>
                            <option value="<?=$conf['id'];?>"><?=$conf['batch_grp_name'];?></option>
                        <? } ?>
                    </select>
                </td>
            </tr>
           <tr>
               <td>Select Territory</td>
               <td>
                    <select id="dlg_sel_territory" name="dlg_sel_territory" style="width: 204px;">
                        <option value="00">All Territory</option>
                        <?php  foreach($pnh_terr as $terr): ?>
                                <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Select Town</td>
                <td>
                    <select id="dlg_sel_town" name="dlg_sel_town" style="width: 204px;">
                        <option value="00">All Towns</option>
                         <?php foreach($pnh_towns as $town): ?>
                                <option value="<?php echo $town['id'];?>"><?php echo $town['town_name'];?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Number of orders:</td>
                <td>
                    <input type="hidden" name="assigned_menuids" id="assigned_menuids" value="" />
                    <input type="text" name="batch_size" id="batch_size" value="" style="width: 30px;margin:10px 0 5px 5px" />
                    <!--<input type="hidden" name="assigned_uid" id="assigned_uid" value="" />-->
                </td>
            </tr>
            <tr>
                <td>Assigned to:</td>
                <td>
                        <select name="assigned_uid" id="assigned_uid" style="width: 204px;"></select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="create_batch_msg_block"></div>
                </td>
            </tr>
            <?php /*
   <select id="dlg_sel_franchise" name="dlg_sel_franchise" style="width: 204px;">
        <option value="00">All Franchise</option>
    </select> */?>
           
        </table>
    </form>
    
</div>
