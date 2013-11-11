<style type="text/css">
    h2 {        width: 788px; float: left;   }
.leftcont { display: none;}
table.datagridsort tbody td { padding: 4px; }
.datagrid td { padding: 1px; }
.datagrid th { background: #443266;color: #C3C3E5; }
.subdatagrid {    width: 100%; }
.subdatagrid th {
    padding: 4px 0 2px 4px !important;
    font-size: 11px !important;
    color: #130C09;
    background-color: rgba(112, 100, 151, 0.51);
}
.subdatagrid td {
        /*font-size: 11px !important;*/
        padding: 4px !important;
}
.loading {
    text-align: center;
    margin: 5% 0 0 40%;
    visibility: visible; font-size: 16px; 
}
.trans_pagination {
    float: right;
    font-size: 20px;
    margin: 10px 40px 6px 0;
}
.trans_pagination a {
    color: #606EBD;
    background: #C3C3E5;
    padding: 1px 5px;
}
.trans_pagination a:hover {
    background: #EFEFF5;
}
.pagi_top {
    padding-top: 0px;
    margin: 0 40px 6px 0;
}
.trans_filters_block {
    clear: both;
}
select {
    margin: 10px 0 5px 5px;
}
label {
    margin:10px 0 15px 5px;
    
}
.log_display {font-weight:bold;margin-right: 10px; margin-bottom: 15px;}
/*Start*/
/*.subdatagrid tr.processed_ord a { color: #290715  !important; text-decoration: line-through; }*/
.datagrid tr.processed_ord a { color: #290715 !important; }
/*.datagrid tr.processed_ord { background-color: #80C280  !important; }*/
/*.subdatagrid tr.processed_ord { background-color: #80C280  !important; text-decoration: line-through;}*/

.datagrid tr.shipped_ord td{ color: #BDB5AB  !important;}
.datagrid tr.shipped_ord td a{ color: #BDB5AB !important;}
.subdatagrid tr.shipped_ord td{ color: #BDB5AB  !important;}
.subdatagrid tr.shipped_ord td a{color: #BDB5AB !important;}

/*.datagrid tr.pending_ord td { background-color: rgba(247, 190, 190, 0.51) !important; }*/
.datagrid tr.pending_ord td a{color: rgba(20, 2, 2, 0.75) !important}
.subdatagrid tr.pending_ord td {/*background-color: rgba(247, 190, 190, 0.51) !important*/}
.subdatagrid tr.pending_ord td a{color: rgba(20, 2, 2, 0.75) !important}

.datagrid tr.cancelled_ord td{color: #cd0000 !important;} 
.datagrid tr.cancelled_ord td a{color: #cd0000 !important;}
.subdatagrid tr.cancelled_ord td{text-decoration: line-through;color: #cd0000 !important;}
.subdatagrid tr.cancelled_ord td a{text-decoration: line-through;color: #cd0000 !important;}

.datagrid tr.disabled_ord td{color: #D8D3D2 !important;}
.datagrid tr.disabled_ord td a{color: #D8D3D2 !important;}
.subdatagrid tr.disabled_ord td{text-decoration: none;color: #D8D3D2 !important;}
.subdatagrid tr.disabled_ord td a{text-decoration: none;color: #D8D3D2 !important;}


/*   End*/
.retry_link {
    margin-top: 20px;
    cursor: pointer;
    color:#f4f4f4;
    background-color: #3B3BB9 !important;
    padding: 5px 10px;
    margin: 5px;
    border-radius: 3px;
}
.batch_msg_enabled { margin-top:20px; cursor: pointer;color: #EC0009 !important; }
.batch_msg_disabled { margin-top: 20px;
    cursor: pointer;
    color: #f4f4f4;
    background-color: #ff7777 !important;
    padding: 5px 10px;
    margin: 5px;
    border-radius: 3px; }
.proceed_link { margin-top: 20px;
    cursor: pointer;
    color: #f4f4f4;
    background-color: #447049 !important;
    padding: 5px 10px;
    margin: 5px;
    border-radius: 3px; }
.info_links a {
    color: #4318B3;
    /*background-color: #36EC4C !important;*/
}
.small_link { font-size: 80%; color: #455566;}
.danger_link {
    margin: 7px;
}
.high_link {
    float:left;font-weight: bold; padding: 4px 10px !important;min-width: inherit;background: #116428;
}
.high_link a  { color: #F1EFEE; }
.high_link:hover,.high_link a:hover { background-color: #28B14E; }

.ttl_trans_listed {float: right;margin: 0 65px 10px 0;}
.level1_filters { float:right;margin-top: 7px;margin-right: 5px; }
.fran_experience {
    font-weight:bold;font-size:10px;padding: 2px 3px;border-radius: 3px; margin-top:5px;
}
</style>


<div class="container" id="account_grn_present">
    <h2>Manage Transaction Reservations</h2>
    <div class="level1_filters">
        <select id="sel_menu" name="sel_menu" colspan="2">
                        <option value="00">Select Menu</option>
                         <?php /*foreach($pnh_menu as $menu): ?>
                                <option value="<?php echo $menu['id'];?>"><?php echo $menu['name'];?></option>
                        <?php endforeach;*/ ?>
                    </select> &nbsp;
                    <select id="sel_brands" name="sel_brands">
                        <option value="00">Select Brands</option>
                         <?php /* foreach($pnh_brands as $brand): ?>
                                <option value="<?php echo $brand['id'];?>"><?php echo $brand['name'];?></option>
                        <?php endforeach; */?>
                    </select>
                    
                    
                    <select id="sel_territory" name="sel_territory" >
                        <option value="00">All Territory</option>
                        <?php /* foreach($pnh_terr as $terr):?>
                                <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
                        <?php endforeach; */ ?>
                    </select>
                    <select id="sel_town" name="sel_town">
                        <option value="00">All Towns</option>
                        <?php /*foreach($pnh_towns as $town): ?>
                                <option value="<?php echo $town['id'];?>"><?php echo $town['town_name'];?></option>
                        <?php endforeach; */ ?>
                    </select>
                    <select id="sel_franchise" name="sel_franchise">
                        <option value="00">All Franchise</option>
                    </select>
    </div>
    <div class="trans_filters_block">
        <table width="100%">
            <tr>
                <td width="65%"><label for="batch_type" style="float:left;">Batch Type:</label>
                                <select id="batch_type">
                                    <option value="00">All</option>
                                    <option value="ready">Batch Ready</option>
                                    <option value="partial_ready">Partial Batch Ready</option>
                                    <option value="not_ready">Not Ready</option>
                                </select>
                </td>
                <td width="35%" align="right">
                   <form id="trans_date_form" method="post">
                            <b>Show transactions : </b>
                            <label for="date_from">From :</label><input type="text" style="width: 90px;" id="date_from"
                                    name="date_from" value="<?php echo date('Y-m-1',time()-60*60*24*7*4)?>" />
                            <label for="date_to">To :</label><input type="text" style="width: 90px;" id="date_to"
                                    name="date_to" value="<?php echo date('Y-m-d',time())?>" /> 
                            <input type="submit" value="Submit">
                    </form>
                </td>
            </tr>
            <tr>
                <td>
                                
                    <span class="working_status"></span>
                </td>
                <td align="right"> 
                    <div class="log_display"></div>
                    <div class="loading_log"></div>
<!--                    <span class="ttl_trans_listed dash_bar"></span>-->
                </td>
            </tr>
        </table>
    </div>
    <div style="padding:1px 0px;" id="trans_list_replace_block"></div>
</div>
<script type="text/javascript" src="<?=base_url()?>js/manage_trans_reservations_script.js"></script>
<script>
// <![CDATA[
    //By default load lists
    loadTransactionList(0);
    var pg=0;
    
    function loadTransactionList(pg) 
    {
        var batch_type= ($("#batch_type").val() == "00")?0: $("#batch_type").val();
        
        var terrid= ($("#sel_territory").val()=='00')?0:$("#sel_territory").val();
         var townid=($("#sel_town").val()=='00')?0:$("#sel_town").val();
         var franchiseid=($("#sel_franchise").val()=='00')?0:$("#sel_franchise").val();
         var menuid=($("#sel_menu").val()=='00')?0:$("#sel_menu").val();
         var brandid=($("#sel_brands").val()=='00')?0:$("#sel_brands").val();
         
        var date_from= $("#date_from").val();
        var date_to= $("#date_to").val();
        
        //var pg= ($(".pg_num").val()== 'undefined')?$(".pg_num").val():pg;
        
        //alert(batch_type+"/"+date_from+"/"+date_to+"/"+pg);
        $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>"); //trans_list_replace_block
        $.post('jx_get_transaction_list/'+batch_type+'/'+date_from+'/'+date_to+'/'+terrid+'/'+townid+'/'+franchiseid+'/'+menuid+'/'+brandid+"/"+pg,"",function(rdata) {
            $("#trans_list_replace_block").html(rdata);
            
        });
        
        
    }
//var GM_TIMING_END_CHUNK1=(new Date).getTime();
$(document).ready(function() {
        //FIRST RUN
        var reg_date = "<?php echo date('m/d/Y',  time()*60*60*24);?>";
        
        $( "#date_from").datepicker({
             changeMonth: true,
             dateFormat:'yy-mm-dd',
             numberOfMonths: 1,
             maxDate:0,
//             minDate: new Date(reg_date),
               onClose: function( selectedDate ) {
                 $( "#date_to" ).datepicker( "option", "minDate", selectedDate ); //selectedDate
             }
           });
        $( "#date_to" ).datepicker({
            changeMonth: true,
             dateFormat:'yy-mm-dd',
//             numberOfMonths: 1,
             maxDate:0,
             onClose: function( selectedDate ) {
               $( "#date_from" ).datepicker( "option", "maxDate", selectedDate );
             }
        });

        prepare_daterange('date_from','date_to');
    });
    
    function done(data) { }
    function fail(xhr,status) { $('#trans_list_replace_block').print("Error: "+xhr.responseText+" "+xhr+" | "+status);}
    function success(resp) {
            $('#trans_list_replace_block').html(resp);
    }

// ]]>
</script>

<?php
