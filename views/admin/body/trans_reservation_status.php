<style type="text/css">
.leftcont { display: none;}
table.datagridsort tbody td { padding: 4px; }
.datagrid td { padding: 1px; }
.datagrid th { background: #443266;color: #C3C3E5; }
.subdatagrid {    width: 100%; }
.subdatagrid th {
    padding: 4px !important;
    font-size: 11px !important;
    color:#080808;
    background-color:#C5C796; /*#F1F0FFrgba(51, 47, 43, 0.61);*/
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
.pagi_top {
    padding-top: 0px;
    margin: 0 40px 6px 0;
}
.trans_filters_block {
    clear: both;
}
select {
    margin: 10px 0 15px 5px;
    float: left;
}
label {
    margin:10px 0 15px 5px;

}
</style>
<div class="container" id="account_grn_present">
    <h2>Transaction Reservation Status</h2>
    <div class="trans_filters_block">
        
            
        <table width="100%">
            <tr>
                <td width="55%">

                    <select id="sel_territory" name="sel_territory" >
                        <option value="00">All Territory</option>
                        <?php foreach($pnh_terr as $terr):?>
                                <option value="<?php echo $terr['id'];?>"><?php echo $terr['territory_name'];?></option>
                        <?php endforeach;  ?>
                    </select>
                    <select id="sel_town" name="sel_town">
                        <option value="00">All Towns</option>
                        <?php foreach($pnh_towns as $town): ?>
                                <option value="<?php echo $town['id'];?>"><?php echo $town['town_name'];?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="sel_franchise" name="sel_franchise">
                        <option value="00">All Franchise</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <select id="sel_menu" name="sel_menu" colspan="2">
                        <option value="00">Select Menu</option>
                         <?php foreach($pnh_menu as $menu): ?>
                                <option value="<?php echo $menu['id'];?>"><?php echo $menu['name'];?></option>
                        <?php endforeach; ?>
                    </select> &nbsp;
                    <select id="sel_brands" name="sel_brands">
                        <option value="00">Select Brands</option>
                         <?php foreach($pnh_brands as $brand): ?>
                                <option value="<?php echo $brand['id'];?>"><?php echo $brand['name'];?></option>
                        <?php endforeach; ?>

                    </select>
                </td>
                <td width="45%"><div class="ttl_trans_listed dash_bar"></div>
                    <br><span class="log_display" style="font-weight:bold;"></span></td>
            </tr>
        
            <tr>
                
                <td>
                    <label for="batch_type">Batch Type:</label>
                    <select id="batch_type">
                        <option value="00">All</option>
                        <option value="ready">Batch Ready</option>
                        <option value="partial_ready">Partial Batch Ready</option>
                        <option value="not_ready">Not Ready</option>
                    </select>
                </td>
                
                <td align="right"><div >
                    <form id="trans_date_form" method="post">
                            <b>Show transactions</b> :
                            <label for="date_from">Batch Type:From :</label><input type="text" style="width: 90px;" id="date_from"
                                    name="date_from" value="<?php echo date('Y-m-d',time()-60*60*24*7*4)?>" />
                            <label for="date_to">Batch Type:To :</label><input type="text" style="width: 90px;" id="date_to"
                                    name="date_to" value="<?php echo date('Y-m-d',time())?>" /> 
                            <input type="submit" value="Submit">
                    </form>
                    </div>
                </td>
            </tr>
            
        </table>
    </div>

    <div style="padding:10px 0px;" id="trans_list_replace_block"></div>
</div>

<script>
// <![CDATA[
    $(document).ready(function() {
        loadTransactionList(0);
        
    });
    $("#trans_date_form").submit(function() {
        loadTransactionList(0);
        return false;
    });
    $("#batch_type").live("change",function() {
        loadTransactionList(0);
        return false;
    });
    $(".trans_pagination a").live("click",function(e) {
        e.preventDefault();
        $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
        $.post($(this).attr("href"),{},function(rdata) {
            $("#trans_list_replace_block").html(rdata);
            
        });
        return false;
    });
    $("#sel_menu").live("change",function() {
            var menuid=$(this).find(":selected").val();//text();
            
            var url="<?php echo site_url("admin/jx_get_brandsbymenuid"); ?>"+"/"+menuid;
            $.post(url,function(resp) {
                    if(resp.status=='success') {
                         var obj = jQuery.parseJSON(resp.brands);
                        $("#sel_brands").html(objToOptions_brands(obj));
                    }
                    else {
                        $("#sel_brands").val($("#sel_brands option:nth-child(0)").val());
                        //$(".sel_status").html(resp.message);
                    }
                },'json').done(done).fail(fail);

        loadTransactionList(0);
        return false;
    });
    $("#sel_brands").live("change",function() {
        loadTransactionList(0);
        return false;
    });
    
    function trans_enable_batch(transid) {
        $("#action_refresh_"+transid).refresh();return false;
        if(!confirm("Are you sure you want to process \nthis transaction for batch?")) {
            return false;
            //var batch_remarks=prompt("Enter remarks?");
        }
        var ttl_num_orders=$("."+transid+"_total_orders").val();
        var batch_remarks='';
        var updated_by = "<?=$user['userid']?>";
        
        $.post('batching_process/'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'+updated_by+'',"",function(rdata) {
            loadTransactionList(0);
        });
        
        return false;
    }
    
    function loadTransactionList(pg) 
    {
        var batch_type= ($("#batch_type").val() == "00")?0: $("#batch_type").val();
        var date_from= $("#date_from").val();
        var date_to= $("#date_to").val();
        
        //alert(batch_type+date_from+date_to+pg);
        $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
        $.post('jx_get_transaction_list/'+batch_type+'/'+date_from+'/'+date_to+"/"+pg,"",function(rdata) {
            $("#trans_list_replace_block").html(rdata);
            
        });
        
        
    }
//]]>
</script>

<script>
// <![CDATA[
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
// ]]>
</script>
<style type="text/css">

.datagrid tr.processed_ord td { color: green  !important; }
.datagrid tr.processed_ord td a { color: green !important; }
.subdatagrid tr.processed_ord td { text-decoration: line-through;color: green  !important; }
.subdatagrid tr.processed_ord td a { text-decoration: line-through;color: green  !important; }

.datagrid tr.shipped_ord td{ color: #BDB5AB  !important;}
.datagrid tr.shipped_ord td a{ color: #BDB5AB !important;}
.subdatagrid tr.shipped_ord td{ color: #BDB5AB  !important;}
.subdatagrid tr.shipped_ord td a{color: #BDB5AB !important;}

.datagrid tr.pending_ord td{color: rgb(221, 148, 14) !important;}
.datagrid tr.pending_ord td a{color: rgb(221, 148, 14) !important;}
.subdatagrid tr.pending_ord td{color: rgb(221, 148, 14) !important;}
.subdatagrid tr.pending_ord td a{color: rgb(221, 148, 14) !important;}

.datagrid tr.cancelled_ord td{color: #cd0000 !important;}
.datagrid tr.cancelled_ord td a{color: #cd0000 !important;}
.subdatagrid tr.cancelled_ord td{text-decoration: line-through;color: #cd0000 !important;}
.subdatagrid tr.cancelled_ord td a{text-decoration: line-through;color: #cd0000 !important;}

</style>
<?php
