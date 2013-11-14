<style>
/*   select { background: #C09292;
                color: #fff; }*/
.loading {
    text-align: center;
    margin: 2% 0 0 4%;
    visibility: visible; 
    font-size: 15px; 
}
.response_status { color: #029332; }
.datagrid td { padding: 7px; }
.datagrid th { background: #443266;color: #C3C3E5; }
</style>
<div class="container">
    <h2>Manage Towns Courier Priority</h2>
    <?php /*
    <div>
    <fieldset>
        <form name="courior_priority_form" id="courior_priority_form">
            <select id="sel_territory" name="sel_territory" >
                <option value="00">All Territory</option>
                <?php  foreach($pnh_terr as $terr):?>
                        <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
                <?php endforeach;  ?>
            </select>
            <select id="sel_town" name="sel_town">
                <option value="00">All Towns</option>
                <?php foreach($pnh_towns as $town): ?>
                        <option value="<?php echo $town['id'];?>"><?php echo $town['town_name'];?></option>
                <?php endforeach;  ?>
            </select>

            <div class="clear"></div>
<!--("town_id","courier_priority_1","courier_priority_2","courier_priority_3","delivery_hours_1","delivery_hours_2","delivery_hours_3")-->
            <label>Priority 1:</label>
            <select id="courier_priority_1" name="courier_priority_1">
                <option value="00">Select courier</option>
                <?php foreach($courier_providers as $courier): ?>
                        <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                <?php endforeach;  ?>
            </select>
            <div class="clear"></div>
            <label>Priority 2:</label>
            <select id="courier_priority_2" name="courier_priority_2">
                <option value="00">Select courier</option>
                <?php foreach($courier_providers as $courier): ?>
                        <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                <?php endforeach;  ?>
            </select>
            <div class="clear"></div>
            <label>Priority 3:</label>
            <select id="courier_priority_3" name="courier_priority_3">
                <option value="00">Select courier</option>
                <?php foreach($courier_providers as $courier): ?>
                        <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                <?php endforeach;  ?>
            </select>
            <label>Delivery in:</label>
            <select id="delivery_hours_1" name="delivery_hours_1">
                <option value="24">24</option>
                <option value="48">48</option>
                <option value="72">72</option>
            </select>Hours.

            
            <label>Delivery in:</label>
            <select id="delivery_hours_2" name="delivery_hours_2">
                <option value="24">24</option>
                <option value="48">48</option>
                <option value="72">72</option>
            </select>Hours.
            
            <label>Delivery in:</label>
            <select id="delivery_hours_3" name="delivery_hours_3">
                <option value="24">24</option>
                <option value="48">48</option>
                <option value="72">72</option>
            </select>Hours.
            <div align="right" style="padding:5px 0px;">
                    <input type="submit" value="Submit Return" style="padding:5px 10px;">
            </div>
        </form>
    </fieldset>
    </div>
    */ ?>
    <div style="float:right;">
        <select id="sel_territory" name="sel_territory" >
            <option value="00">All Territory</option>
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
   <?php
        if(isset($terr_selected['territory_id'])) { ?>
            <h3>Territory: <?php echo $terr_selected['territory_name']; ?></h3>
    <?php
        }
    ?>
    <div class="towns_courier_priority_list">
        <table width="100%" class="datagrid">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Town List</th>
                    <th>Priority 1 Courier <span class="red_star">*</span></th>
                    <th>Delivery In <span class="small_text">Hours</span><span class="red_star">*</span></th>
                    <th>Priority 2 Courier</th>
                    <th>Delivery In Hours</th>
                    <th>Priority 3 Courier</th>
                    <th>Delivery In Hours</th>
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
                        <td><input type="hidden" value="<?=$townid?>" /><?php echo $town['town_name'];?></td>
                        <td>
                            <select id="courier_priority_1_<?=$townid?>" name="courier_priority_1_<?=$townid?>" class="courier_priority_1">
                                <option value="00">Select courier</option>
                                <?php foreach($courier_providers as $courier) {
                                        if($town['courier_priority_1'] == $courier['courier_id'] ) {?>
                                            <option value="<?php echo $courier['courier_id'];?>" selected><?php echo $courier['courier_name'];?></option>
                                        <?}
                                        else {
                                        ?>
                                            <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                                <?php   }
                                    }  ?>
                            </select>
                        </td>
                        <td>
                            <select id="delivery_hours_1_<?=$townid?>" name="delivery_hours_1_<?=$townid?>" class="delivery_hours_1">
                                <option value="00">0</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_1'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?></option>
                                    
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
                        </td>
                        <td>
                            <select id="delivery_hours_2_<?=$townid?>" name="delivery_hours_2_<?=$townid?>" class="delivery_hours_2">
                                <option value="00">0</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_2'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?></option>
                                    
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
                        </td>
                        <td>
                            <select id="delivery_hours_3_<?=$townid?>" name="delivery_hours_3_<?=$townid?>" class="delivery_hours_3">
                                <option value="00">0</option>
                                <?php
                                foreach ($st_hrs as $hrs) {
                                    if($town['delivery_hours_3'] == $hrs ) {?>
                                    <option value="<?php echo $hrs;?>" selected><?php echo $hrs;?></option>
                                <?} else { ?>
                                    <option value="<?php echo $hrs;?>"><?php echo $hrs;?></option>
                                    
                                <?php }
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" name="submit_<?=$townid;?>" value="Change"/>
                            </form>
                            <div class="response_status" id="response_status_<?=$townid;?>"></div>
                        </td>
                    </tr>
                
                <?php endforeach;  ?>
            </tbody>
        </table>
    </div>
    
</div>
<script>
    //$(".courier_priority_1").chosen();
    //$(".delivery_hours_1").chosen();

//    $(".courier_priority_2").chosen();
//    $(".delivery_hours_2").chosen();

//    $(".courier_priority_3").chosen();
//    $(".delivery_hours_3").chosen();
    function save_courier_priority(townid) {
         var courier_1_id=$("#courier_priority_1_"+townid).find(":selected").val();
         if(courier_1_id == '00') {
             alert("Please Select Priority 1 Courier");
             return false;
         }
         var delivery_hours_1_id=$("#delivery_hours_1_"+townid).find(":selected").val();
         if(delivery_hours_1_id == '00') {
             alert("Please Select Delivery Hours");
             return false;
         }
         var courier_2_id=$("#courier_priority_2_"+townid).find(":selected").val(); if(courier_2_id == '00') courier_2_id=0;
         var delivery_hours_2_id=$("#delivery_hours_2_"+townid).find(":selected").val(); if(delivery_hours_2_id == '00') delivery_hours_2_id=0;
         
         var courier_3_id=$("#courier_priority_3_"+townid).find(":selected").val(); if(courier_3_id == '00') courier_3_id=0;
         var delivery_hours_3_id=$("#delivery_hours_3_"+townid).find(":selected").val(); if(delivery_hours_3_id == '00') delivery_hours_3_id=0;
         
         var postdata = {townid:townid
                            ,courier_priority_1:courier_1_id,delivery_hours_1:delivery_hours_1_id
                            ,courier_priority_2:courier_2_id,delivery_hours_2:delivery_hours_2_id
                            ,courier_priority_3:courier_3_id,delivery_hours_3:delivery_hours_3_id};
         $.post(site_url+"admin/put_courier_priority/",postdata,function(rdata) {
                $("#response_status_"+townid).html('<div class="loading">$nbsp;</div>').slideDown("slow").html(rdata).delay(3000).slideUp("slow");
                
         },"html").done(done).fail(fail);
         
         return false;
    }
    /*$("#courior_priority_form").submit(function() {
        var terrid=$("#sel_territory").find(":selected").val();
        if(terrid == '00') {
            alert("Please select territory");
            return false;
        }
        var townid=$("#sel_town").find(":selected").val();
        if(townid == '00') {
            alert("Please select town");
            return false;
        }
        var couriourid=$("#courier_priority_1").find(":selected").val();
        if(couriourid == '00') {
            alert("Please select first couriour priority 1.");
            return false;
        }
        var couriourid=$("#delivery_hours_1").find(":selected").val();
        if(couriourid == '00') {
            alert("Please select delevery hours1.");
            return false;
        }
        
        
        return true;
    });*/
$("#sel_town").live("change",function() { 
    var townid=$(this).find(":selected").val();//text();
    var terrid=$("#sel_territory").find(":selected").val();//text();
    /*$.post(site_url+"admin/jx_suggest_fran/"+terrid+"/"+townid,function(resp) {
            if(resp.status=='success') {
                 var obj = jQuery.parseJSON(resp.franchise);
                $("#sel_franchise").html(objToOptions_franchise(obj));
            }
            else {
                $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
                //$(".sel_status").html(resp.message);
            }
        },'json').done(done).fail(fail);

    //loadTransactionList(0);*/
    return false;
});


//ONCHANGE Territory
$("#sel_territory").live("change",function() {
    var terrid=$(this).find(":selected").val();//text();
//        if(terrid=='00') {          $(".sel_status").html("Please select territory."); return false;        }
    //document.URL
    window.location=site_url+"/admin/towns_courier_priority/"+terrid;
    
   // $("table").data("sdata", {terrid:terrid});
   
    /*$.post(site_url+"admin/jx_suggest_townbyterrid/"+terrid,function(resp) {
        if(resp.status=='success') {
             //print(resp.towns);
             var obj = jQuery.parseJSON(resp.towns);
            $("#sel_town").html(objToOptions_terr(obj));
        }
        else {
            $("#sel_town").val($("#sel_town option:nth-child(0)").val());
        }
    },'json').done(done).fail(fail);
    //loadTransactionList(0);
    return false;*/
});
function objToOptions_terr(obj) {
    var output='';
        output += "<option value='00' selected>All Towns</option>\n";
    $.each(obj,function(key,elt){
        if(obj.hasOwnProperty(key)) {
            output += "<option value='"+elt.id+"'>"+elt.town_name+"</option>\n";
        }
    });
    return(output);
}
function done(data) { }
function fail(xhr,status) { print("Error: "+xhr.responseText+" "+xhr+" | "+status);}
function success(resp) {
        //$('#trans_list_replace_block').html(resp);
}
</script>