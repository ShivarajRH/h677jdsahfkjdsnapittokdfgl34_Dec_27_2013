<style>
    .leftcont { display: none;}
    /*table.datagridsort tbody td { padding: 4px; }
    .datagrid td { padding: 1px; }
    .datagrid td:hover { background-color: rgb(248, 249, 250) !important; }
    .datagrid th { background: #443266;color: #C3C3E5; }
    .subdatagrid {    width: 100%; }
    .subdatagrid th {padding: 4px 0 2px 4px !important;font-size: 11px !important;color: #130C09;background-color: rgba(112, 100, 151, 0.51);}
    .subdatagrid td {padding: 4px !important;}*/
    
    .loading { display: block; padding-left: 20px;}
</style>
<div class="page_wrap container">
	
	<div class="page_topbar" >
		<h2 class="page_title fl_left">Manage Acknowledgements</h2>
                <div style="clear:both">&nbsp;</div>
                <div class="fl_left">
                    <select id="sel_territory" name="sel_territory" style="width:200px;">
                        <option value="00">All Territory</option>
                        <?php foreach($pnh_terr as $terr):?>
                                <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
                        <?php endforeach;  ?>
                    </select>
                </div>
                
		<div class="page_action_buttons fl_left" align="right">
                    <form id="trans_date_form" name="trans_date_form" method="post" action="<?php echo site_url("admin/print_invoice_acknowledgementbydate"); ?>">
                        <table cellpadding="5" cellspacing="0" border="0">

                            <tr>
                                <td><label for="date_from">From:</label></td><?php //date('Y-m-01',time()-60*60*24*7*4); ?>
                                <td><input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" size="10" /></td>
<!--                                <td><label for="date_to">To:</label></td>
                                <td><input type="text" id="date_to" name="date_to" value="<?php // echo $date_to; ?>"  size="10"/></td>-->
                                <td colspan="4" align="right"><input type="submit" value="Submit"></td>
                            </tr>
<!--                            <tr>
                                <td colspan="4" align="left"><label for="consider_printed_ack">Do you want to consider already <br> printed acknowledgements?</label><input type="checkbox" id="consider_printed_ack" name="consider_printed_ack" value="y" checked/></td>
                            </tr>-->
                        </table>
                    </form>
		</div>
	</div>
	<div style="clear:both">&nbsp;</div>
	<div class="page_content">
            <div class="block_list_acknowledgements">
                <?php
                    if(!isset($terr_info)) {?>
                        <h2>No records found change date range</h2>
                <?php }
                    else {
                ?>
                    <form action='' name='' id=''>
                        <table class='datagrid' cellspacing='4' cellpadding='4' width='100%'>
                            <tr>
                                <th width="25">#</th>
                                <th>Territory Name</th>
                                <th>
                                    <abbr title='Territory Manager'>Territory Manager</abbr>
                                </th>
                                <th>
                                    <abbr title='Business Executive'>Business Executives</abbr>
                                </th>
                                <th width="50">
                                    <abbr title='Total Franchises'>Franchises</abbr>
                                </th>
                                <th width="200"><?=$set1["date_from"]; ?> - <?=$set1["date_to"];?></th>
                                <th width="200"> <?=$set2["date_from"]; ?> - <?=$set2["date_to"];?></th>
                                <th width="200"><?=$set3["date_from"]; ?> - <?=$set3["date_to"];?> >></th>
                                <th width="50"><input type='checkbox' name='' id='' title='Pick for printing' class='chk_all_terr_print'>
                                </th>
                            </tr>
                            <?php 
                            foreach($terr_info as $i=>$terr) { ?>
                                <tr>
                                    <td><?=++$i;?></td>
                                    <td><?=$terr['territory_name'];?></td>
                                    <td>
                                        <?php
                                            $new_arr = $terr_manager_info[$terr['territory_id']];
                                            foreach($new_arr as $tm) { ?>
                                                    <div class=""><a target="_blank" href="<?=site_url('admin/view_employee/'.$tm['employee_id'])?>"><?=$tm['name'];?></a></div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                            $new_arr2 = $busi_executive_info[$terr['territory_id']];
                                            foreach($new_arr2 as $be) { ?>
                                                    <div class=""><a target="_blank" href="<?=site_url('admin/view_employee/'.$be['employee_id'])?>"><?=$be['name'];?></a></div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                         <b><?=$terr['ttl_franchises']; ?></b> <br> 
                                    </td>
                                    <td>
                                        <div class="ack_det">
                                            <b><?=$set1[$terr['territory_id']]['ttl_invs'];?></b> Invoices
<!--                                            <span class="label_fld">
                                                <a href="">Generate</a>
                                            </span>-->
                                        </div>
                                   </td>
                                   <td>
                                        <div class="ack_det">
                                            <b><?=$set2[$terr['territory_id']]['ttl_invs'];?></b> Invoices
                                        </div>
                                   </td>
                                   <td>
                                        <div class="ack_det">
                                           
                                            <b><?=$set3[$terr['territory_id']]['ttl_invs'];?></b> Invoices
                                        </div>
                                   </td>
                                    
                                    <td><input type='checkbox' name='' id='' class='chk_terr_print'></td>
                                </tr>

                            <?php } ?>

                                <tr>
                                    <td colspan='8' align='right'><input type='submit' name='' id='' class='btn_generate_inv' value='Generate'></td>
                                </tr>
                            </table>
                   </form>
            </div>
            <?php } ?>
        </div>
        
</div>

<script type="text/javascript">
// <![CDATA[
    $(".chk_all_terr_print").bind("click",function() {
        var elt = $(this);
        if(elt.is(":checked")) {
            $(".chk_terr_print").attr("checked",true);
        }
        else {
            $(".chk_terr_print").attr("checked",false);
        }
    });
    
    $(".btn_generate_inv").click(function(e){
        e.preventDefault();
        var total_terr =  $(".chk_terr_print").length;
        var selected_terr = $(".chk_terr_print:checked").length;
        //alert(total_terr+""+selected_terr);
        if(selected_terr == 0) {
            alert("Select territories for picklist."); return false;
        }
    });
    
   $(document).ready(function() {
        //FIRST RUN
        $( "#date_from").datepicker({
             changeMonth: true,
             dateFormat:'yy-mm-dd',
             numberOfMonths: 1,
             maxDate:0,// minDate: new Date(reg_date),
               onClose: function( selectedDate ) {
                 $( "#date_to" ).datepicker( "option", "minDate", selectedDate ); //selectedDate
             }
           });
        $( "#date_to" ).datepicker({
            changeMonth: true,
             dateFormat:'yy-mm-dd',// numberOfMonths: 1,
             maxDate:0,
             onClose: function( selectedDate ) {
               $( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
             }
        });
    });
    
    $("#sel_territory").live("change",function(e) {
        loadAcknowledgementList(0);
    });

    function loadAcknowledgementList() {
            var sel_territory = $("#sel_territory").find(":selected").val();
            var consider_printed_ack = 0//$("#consider_printed_ack").prop("checked")?'y':'n';
            var date_from = $("#date_from").val();
            var date_to = $("#date_to").val();
            var postData = {sel_territory:sel_territory,date_from:date_from,date_to:date_to,consider_printed_ack:consider_printed_ack};

            //$(".block_list_acknowledgements").html("<div class='loading'>Loading...</div>");
            //$.post(site_url+'admin/jx_get_acknowledgement_list/',postData,function(resp) {
                //var rdata='';
                //if(resp.status == "success") {
                   
                /*}
                else {
                    rdata +="Error : <b>"+resp.response+"<b>";
                }*/
    }
    loadAcknowledgementList(0);
// ]]>
</script>