<style>
    select { width: 204px; }
</style>
<div class="container">
    <div>
    <h2>Manage Towns Courier Priority</h2>
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
    <div class="towns_courier_priority_list">
        <table width="100%" class="datagrid">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Town List</th>
                    <th>Priority 1 Courier</th>
                    <th>Hours</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pnh_towns as $i=>$town): ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><input type="hidden" value="<?php echo $town['id'];?>" /><?php echo $town['town_name'];?></td>
                    <td><select id="courier_priority_1" name="courier_priority_1">
                            <option value="00">Select courier</option>
                            <?php foreach($courier_providers as $courier): ?>
                                    <option value="<?php echo $courier['courier_id'];?>"><?php echo $courier['courier_name'];?></option>
                            <?php endforeach;  ?>
                        </select>
                    </td>
                    <td>
                        <select id="delivery_hours_1" name="delivery_hours_1">
                            <option value="24">24</option>
                            <option value="48">48</option>
                            <option value="72">72</option>
                        </select>Hours.
                    </td>
                    <td><a href="javascript:void(0)">Change</a></td>
                </tr>
                <?php endforeach;  ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    $("#courior_priority_form").submit(function() {
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
    });
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

   // $("table").data("sdata", {terrid:terrid});

    $.post(site_url+"admin/jx_suggest_townbyterrid/"+terrid,function(resp) {
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
    return false;
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
</script>