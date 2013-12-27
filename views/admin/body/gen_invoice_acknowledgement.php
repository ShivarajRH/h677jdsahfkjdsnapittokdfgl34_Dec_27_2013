<?php
	$this->load->plugin('barcode');
?>
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
    .block {
        /*float: left;*/
        margin: 5px 5px;
        padding: 5px 8px;
        background-color: #8D8CB1;
    }
    .block:hover {
        background-color:#706EC7;
    }
    .block_link {
        padding: 3px 3px;
        font-size: 11px;
        background-color: #999C98;
    }
    .block_link:hover {
        background-color:#706EC7;
    }
    .anchor a {
        color: #f1f1f1;
    }
    .anchor a:hover {
        text-decoration: none;
    }
    .label_text {
        margin-top: 10px;
        margin-right: 10px;
        font-size: 16px;
    }
</style>
<div class="page_wrap container">
	
	<div class="page_topbar" >
		<h2 class="page_title fl_left">Manage Acknowledgements</h2>
                <div style="clear:both">&nbsp;</div>
                <div class="fl_left">
                    <select id="sel_territory" name="sel_territory" style="width:200px;">
                        <option value="00">All Territory</option>
                        <?php foreach($terr_info as $terr) {
                                if($terr['id'] == $territory_id) {
                                        echo '<option value="'.$terr['id'].'" selected>'.$terr['territory_name'].'</option>';
                                }
                                else {
                            ?>
                                <option value="<?=$terr['id'];?>"><?=$terr['territory_name'];?></option>
                        <?php   } 
                            }
                        ?>
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
                <div class="fl_right"><?php
                    $l_date_to = $date_to;//
                    if( strtotime($l_date_to) < time() ) {
                    ?>
                        <div class="block fl_right anchor"><a href="<?=site_url("admin/print_invoice_acknowledgementbydate/".$l_date_to);?>" title="Show Next 7 Days Shipment Log"> Next </a></div>
                    <?php
                    }
                    ?>
                    <div class="block fl_right anchor"><a href="<?=site_url("admin/print_invoice_acknowledgementbydate/".date("Y-m-d",strtotime($date_from)-(60*60*24*6)));?>" title="Show Last 7 Days Shipment Log"> Prev </a></div>
                    
                </div>
                <div class="fl_right">
                    <div class="label_text">Showing from <b><?=$date_from;?></b> to <b><?=$date_to;?></b></div>
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
                               
                                <th width="200"><span><?=date("d_M_Y",strtotime($slabs_data['slab1']["date_from"]) ); ?> - <?=date("d_M_Y",strtotime($slabs_data['slab1']["date_to"]) );?></span></th>
                                <th width="200"><span><?=date("d_M_Y",strtotime($slabs_data['slab2']["date_from"]) ); ?> - <?=date("d_M_Y",strtotime($slabs_data['slab2']["date_to"]) );?></span></th>
                                <th width="200"><span><?=date("d_M_Y",strtotime($slabs_data['slab3']["date_from"]) ); ?> - <?=date("d_M_Y",strtotime($slabs_data['slab3']["date_to"]) );?></span></th>
                                <th width="50"><input type='checkbox' name='' id='' title='Pick for printing' class='chk_all_terr_print'></th>
                            </tr>
                            <?php // echo '<pre>';print_r($terr_info);
                            
                            foreach($terr_info as $i=>$terr) { 
                                ?>
                                <tr>
                                    <td><?=++$i;?></td>
                                    <td><?=$terr['territory_name'];?></td>
                                    <td>
                                        <?php
                                            $tm_info = $this->reservations->get_territory_managers($terr['id']);
                                            foreach($tm_info as $tm) { ?>
                                                    <div class=""><a target="_blank" href="<?=site_url('admin/view_employee/'.$tm['employee_id'])?>"><?=$tm['name'];?></a></div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                            $executive_info = $this->reservations->get_town_executives($terr['id']);
                                            foreach($executive_info as $be) { ?>
                                                    <div class=""><a target="_blank" href="<?=site_url('admin/view_employee/'.$be['employee_id'])?>"><?=$be['name'];?></a></div>
                                        <?php } ?>
                                    </td>
                                   <?php /* <td>
                                         <b>$terr['ttl_franchises']; </b> <br> 
                                    </td> */?>
                                    <?php
                                    if(isset($slabs_data)) 
                                    foreach($slabs_data as $slab_name=>$slab) {
                                    ?>
                                    <td>
                                            <div class="ack_det">
                                                <?php
                                                $elt = $slabs_data[$slab_name]['result'][$terr['id']][0];
                                                $total_invs = $elt['ttl_invs'];
                                                $ttl_franchises = $elt['ttl_franchises'];

                                                if($ttl_franchises != 0) {
                                                    echo '<b>'.$ttl_franchises.'</b> Franchises<br>';
                                                
//                                                    if($total_invs != 0) {
                                                        echo '<b>'.$total_invs.'</b> Invoices';

                                                        echo '<span class="label_fld fl_right block_link anchor">
                                                                <a href="javascript:void(0);" onclick="return print_acknowledgement_inoices(this,\''.$terr["id"].'\',\''.$slab_name.'\');">Generate</a>
                                                                <input type="hidden" name="grp_invoices_'.$slab_name.'_'.$terr['id'].'" id="grp_invoices_'.$slab_name.'_'.$terr['id'].'" class="grp_invoices_set" value="'.$elt['invoice_no_str'].'" date_from="'.$slabs_data[$slab_name]["date_from"].'" date_to="'.$slabs_data[$slab_name]["date_to"].'" />
                                                            </span>';
//                                                    }
                                                    
                                                }
                                                else 
                                                {
                                                    echo '--';
                                                }
                                                ?>
                                            
                                            </div>
                                   </td>
                                <?php  }
                                   /* ?>
                                   <td>
                                        <div class="ack_det">
                                            <?php $total_invs = $set3[$terr['territory_id']]['ttl_invs'];?>
                                            <b><?=$total_invs;?></b> Invoices
                                            <?php if($total_invs != 0 ) { ?>
                                            <span class="label_fld fl_right block_link anchor">
                                                <a href="javascript:void(0);" onclick="return print_acknowledgement_inoices(this,'<?=$terr['territory_id']?>','set3');">Generate</a>
                                                <input type="hidden" name="grp_invoices_set3_<?=$terr['territory_id']?>" id="grp_invoices_set3_<?=$terr['territory_id']?>" class="grp_invoices_set" value="<?=$terr['invoice_no_str']?>" date_from="<?=$set3["date_from"]; ?>" date_to="<?=$set3["date_to"];?>" />
                                            </span>
                                            <?php } ?>
                                        </div>
                                   </td>*/?>
                                    <td><input type='checkbox' name='' id='' class='chk_terr_print'></td>
                                </tr>

                        <?php } ?>

                                <tr><td colspan='8' align='right'><input type='submit' name='' id='' class='btn_generate_inv' value='Generate'></td></tr>
                        </table>
                   </form>
            </div>
            <?php } ?>
        </div>
        <div id="dlg_block" name="dlg_block" style="display:none;"></div>
</div>

<script type="text/javascript">
// <![CDATA[
$("#dlg_block").dialog({
    autoOpen: false,
    height: 650,
    width:950,
    position: ['center', 'center'],
    modal: true
});
$("#sel_territory").change(function(e) {
    e.preventDefault();
    var date_from = $("#date_from").val();
    var terr_id = $(this).find(":selected").val();
    if(terr_id!= '00') {
        window.location.href=site_url+"admin/print_invoice_acknowledgementbydate/"+date_from+"/"+terr_id;
    }
    else {
        window.location.href=site_url+"admin/print_invoice_acknowledgementbydate/"+date_from;
    }
    
});

function print_acknowledgement_inoices(e,territory_id,set) {
    var lnk = $("#grp_invoices_"+set+"_"+territory_id)
    var p_invoice_ids_str = lnk.val();
    var date_from = lnk.attr("date_from");
    var date_to = lnk.attr("date_to");
    
    var postData = {p_invoice_ids_str:p_invoice_ids_str,date_from:date_from,date_to:date_to};
    
    $.post(site_url+"admin/jx_get_acknowledgement_list",postData,function(resp) {
            $("#dlg_block").html(resp).dialog("open").dialog('option', 'title', 'Print Acknowledgement list');
    },'html');
    return false;
}

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
   // loadAcknowledgementList(0);
   
// Auto center the dialog boxes
$(window).resize(function() { //on resize window center the dialog
    $("#dlg_block").dialog("option", "position", ['center', 'center']);
});
// ]]>
</script>