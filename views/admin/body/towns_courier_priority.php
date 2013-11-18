<style>
/*   select { background: #C09292; color: #fff; }*/
.leftcont { display: none;}
.loading {
    text-align: center;
    margin: 2% 0 0 4%;
    visibility: visible; 
    font-size: 15px; 
}
.response_status { color: #029332; }

.datagrid td { padding: 7px; }
.datagrid th { background: #443266;color: #C3C3E5; }
.nodata { margin-top: 130px; text-align:center; }
.delivery_type {padding: 5px 0 4px 2px; }
.delivery_type span{ font-size: 11px;}
.delivery_type input[type="radio"] { width: 11px;
padding-top: 10px;
margin: 2px 3px 0 2px;}
</style>
<div class="container">
    <h2>Manage Towns Courier Priority</h2>
    <div style="float:right;">
        <select id="sel_territory" name="sel_territory" >
            <option value="">Choose Territory</option>
            <?php  foreach($pnh_terr as $terr): 
                if(isset($terr_selected['territory_id']) and $terr_selected['territory_id'] == $terr['id']) {?>
                    <option value="<?php echo $terr['id'];?>" selected><?php echo $terr['territory_name'];?></option>
                <?php
                }
                else {
                ?>
                    <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
            <?php }
            endforeach;  ?>
        </select>
    </div>
   
    <div class="towns_courier_priority_list">
        <?php
        if(count($towns_courier_priority) == 0 ) {
            echo '<h3 class="nodata">No towns available</h3>'; 
        }
        else 
            {
        
        if(isset($terr_selected['territory_id'])) { ?>
            <h3>Territory: <?php echo $terr_selected['territory_name']; ?></h3>
        <?php
            }
        ?>
        <table width="100%" class="datagrid">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Town List</th>
                    <th>Priority 1 Courier <span class="red_star">*</span></th>
                    <th>Delivery In <span class="small_text">Hours</span><span class="red_star">*</span></th>
                    <th>Priority 2 Courier</th>
                    <th>Delivery In</th>
                    <th>Priority 3 Courier</th>
                    <th>Delivery In</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                
                foreach($towns_courier_priority as $i=>$town): 
                    $townid = $town['townid'];
                    $st_hrs=array('24','48','72');
                    ?>
                    <tr>
                        <td>
                            <?=++$i?>
                            <form name="form_<?=$town['id'];?>" method="post" onsubmit="return save_courier_priority(<?=$townid?>);">
                        </td>
                        <td><input type="hidden" value="<?=$townid?>" /><a href="javascript:void(0);" onclick="return disp_franchise(<?=$townid?>);"><?php echo $town['town_name'];?></a></td>
                        <td>
                            <select id="courier_priority_1_<?=$townid?>" name="courier_priority_1_<?=$townid?>" class="courier_priority_1">
                                <option value="00">Select courier</option>
                                <?php foreach($courier_providers as $courier) {
                                        if($town['courier_priority_1'] == $courier['courier_id'] ) { ?>
                                            <option value="<?php echo $courier['courier_id'];?>" selected><?php echo $courier['courier_name'];?></option>
                                        <? }
                                        else {
                                        ?>
                                            <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                                <?php   }
                                    }   ?>
                            </select>
                            <div class="delivery_type">
                                <input type="radio" name="delivery_type_priority1" id="delivery_type_priority1_<?=$townid?>" value="0" <?php echo ($town['delivery_type_priority1']=="0")?'checked="checked"':''; ?> /><span> By Hand</span>
                                <input type="radio" name="delivery_type_priority1" id="delivery_type_priority1_<?=$townid?>" value="1" <?php echo ($town['delivery_type_priority1']=="1")?'checked="checked"':''; ?> /><span>To Door</span>
                            </div>
                        </td>
                        <td>
                            <select id="delivery_hours_1_<?=$townid?>" name="delivery_hours_1_<?=$townid?>" class="delivery_hours_1">
                                <option value="00">Choose</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_1'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?> Hrs</option>
                                    
                                <?php }
                                }
                                ?>
                            </select>
                        </td>
                        <td><select id="courier_priority_2_<?=$townid?>" name="courier_priority_2_<?=$townid?>" class="courier_priority_2">
                                <option value="00">Select courier</option>
                                <?php foreach($courier_providers as $courier) {
                                        if($town['courier_priority_2'] == $courier['courier_id'] ) {?>
                                            <option value="<?php echo $courier['courier_id'];?>" selected><?php echo $courier['courier_name'];?></option>
                                        <?}
                                        else {
                                        ?>
                                            <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                                <?php   }
                                    }  ?>
                            </select>
                            <div class="delivery_type">
                                <input type="radio" name="delivery_type_priority2" id="delivery_type_priority2_<?=$townid?>" value="0" <?php echo ($town['delivery_type_priority2']=="0")?'checked="checked"':''; ?>/><span> By Hand</span>
                                <input type="radio" name="delivery_type_priority2" id="delivery_type_priority2_<?=$townid?>" value="1" <?php echo ($town['delivery_type_priority2']=="1")?'checked="checked"':''; ?>/><span>To Door</span>
                            </div>
                        </td>
                        <td>
                            <select id="delivery_hours_2_<?=$townid?>" name="delivery_hours_2_<?=$townid?>" class="delivery_hours_2">
                                <option value="00">Choose</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_2'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?> Hrs</option>
                                    
                                <?php }
                                }
                                ?>
                            </select>
                        </td>
                        <td><select id="courier_priority_3_<?=$townid?>" name="courier_priority_3_<?=$townid?>" class="courier_priority_3">
                                <option value="00">Select courier</option>
                                <?php foreach($courier_providers as $courier) {
                                        if($town['courier_priority_3'] == $courier['courier_id'] ) {?>
                                            <option value="<?php echo $courier['courier_id'];?>" selected><?php echo $courier['courier_name'];?></option>
                                        <?}
                                        else {
                                        ?>
                                            <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                                <?php   }
                                    }  ?>
                            </select>
                            <div class="delivery_type">
                                <input type="radio" name="delivery_type_priority3" id="delivery_type_priority3_<?=$townid?>" value="0" <?php echo ($town['delivery_type_priority3']=="0")?'checked="checked"':''; ?>/><span> By Hand</span>
                                <input type="radio" name="delivery_type_priority3" id="delivery_type_priority3_<?=$townid?>" value="1" <?php echo ($town['delivery_type_priority3']=="1")?'checked="checked"':''; ?>/><span>To Door</span>
                            </div>
                        </td>
                        <td>
                            <select id="delivery_hours_3_<?=$townid?>" name="delivery_hours_3_<?=$townid?>" class="delivery_hours_3">
                                <option value="00">Choose</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_3'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?> Hrs</option>
                                    
                                <?php }
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" name="submit" id="submit_<?=$townid;?>" value="Update"/>
                            </form>
                            <div class="response_status" id="response_status_<?=$townid;?>"></div>
                            <div id="show_fran_info_block_<?=$townid;?>" style="display: none;">Hi</div>
                        </td>
                    </tr>
                
                <?php endforeach;  ?>
            </tbody>
        </table>
        <?php
                }
        ?>
    </div>
    
</div>
<script>
    //$(".courier_priority_1").chosen();
    //$(".delivery_hours_1").chosen();
    function save_courier_priority(townid) {
        //validate delivery courier priority
         var courier_1_id=$("#courier_priority_1_"+townid).find(":selected").val();
         if(courier_1_id == '00') {
             alert("Please Select Priority 1 Courier"); return false;
         }
         //validate delivery type
         if($("#delivery_type_priority1_"+townid+":checked").length <= 0){ 
             alert("Please select delivery type for priority 1"); return false;
         }
         var delivery_type_priority1=$("#delivery_type_priority1_"+townid+":checked").val();
         
        var delivery_type_priority2=0;
        var delivery_type_priority3=0;
        if($("#delivery_type_priority2_"+townid+":checked").length > 0){ 
            var delivery_type_priority2=$("#delivery_type_priority2_"+townid+":checked").val();
        }
        if($("#delivery_type_priority3_"+townid+":checked").length > 0){ 
            var delivery_type_priority3=$("#delivery_type_priority3_"+townid+":checked").val();
        }
        //if($("#delivery_type_priority2_"+townid+":checked").length <= 0){ alert("Please select delivery type for priority 2"); return false;}
        //if($("#delivery_type_priority3_"+townid+":checked").length <= 0){ alert("Please select delivery type for priority 3"); return false;}
         
        //validate delivery Hours
        var delivery_hours_1_id=$("#delivery_hours_1_"+townid).find(":selected").val();
        if(delivery_hours_1_id == '00') {
            alert("Please Select Delivery Hours"); return false;
        }
        var courier_2_id=$("#courier_priority_2_"+townid).find(":selected").val(); if(courier_2_id == '00') courier_2_id=0;
        var delivery_hours_2_id=$("#delivery_hours_2_"+townid).find(":selected").val(); if(delivery_hours_2_id == '00') delivery_hours_2_id=0;
        var courier_3_id=$("#courier_priority_3_"+townid).find(":selected").val(); if(courier_3_id == '00') courier_3_id=0;
        var delivery_hours_3_id=$("#delivery_hours_3_"+townid).find(":selected").val(); if(delivery_hours_3_id == '00') delivery_hours_3_id=0;

        //Process
        $("#submit_"+townid).attr("disabled","disabled");//.val("Loading");
        $("#response_status_"+townid).html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>").slideDown("slow");
        var postdata = {townid:townid
                            ,courier_priority_1:courier_1_id,delivery_hours_1:delivery_hours_1_id
                            ,courier_priority_2:courier_2_id,delivery_hours_2:delivery_hours_2_id
                            ,courier_priority_3:courier_3_id,delivery_hours_3:delivery_hours_3_id
                            ,delivery_type_priority1:delivery_type_priority1
                            ,delivery_type_priority2:delivery_type_priority2
                            ,delivery_type_priority3:delivery_type_priority3};

        $.post(site_url+"admin/jx_put_courier_priority/",postdata,function(rdata) {
                $("#response_status_"+townid).html(rdata);
                $("#submit_"+townid).removeAttr("disabled"); //.val("Change");
                $("#response_status_"+townid).delay(3000).slideUp("slow");
                return true;
        },"html").done(done).fail(fail);
         
        return false;
    }
    function disp_franchise(townid) {
        $.post(site_url+"admin/get_franchisebytwn_id",{townid:townid},function(rdata){
           if(rdata.status == 'success') {
               var franchiselist_html='';
               $.each(rdata.franchise_list,function(i,itm) {
                   franchiselist_html+='<div style="white-space:nowrap;margin-bottom:3px;margin-right:3px;padding:3px;background:#eee;cursor:pointer;">'
                        +'<input type="hidden" name="fids[]" value="'+itm.franchise_id+'"/>'
                        +itm.franchise_name
                        +'</div>';
                   
                   //
               });
                  //output = ""+franchiselist_html;
                  $("#show_fran_info_block_"+townid).html(franchiselist_html)
                  .dialog({
                        autoOpen: true,
                          height: 300,
                          width: 350,
                          modal: true
                    });
           }
           else {
               //alert("Error: "+rdata.message);
               $("#show_fran_info_block_"+townid).html(rdata.message)
                .dialog({
                      autoOpen: true,
                        height: 300,
                        width: 350,
                        modal: true
                  });
           }
        },"json");
    }
//ONCHANGE Territory
$("#sel_territory").live("change",function() {
    var terrid=$(this).find(":selected").val();//text(); 
    window.location=site_url+"/admin/towns_courier_priority/"+terrid;
    //var pathname = window.location.pathname; alert(pathname);return false;
    //document.URL    window.status = "message";
});
function done(data) { }
function fail(xhr,status) { print("Error: "+xhr.responseText+" "+xhr+" | "+status);}
function success(resp) {
        //$('#trans_list_replace_block').html(resp);
}
</script>