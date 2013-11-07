<style type="text/css">
.leftcont { display: none;}
table.datagridsort tbody td { padding: 4px; }
.datagrid td { padding: 1px; }
.datagrid th { background: #443266;color: #C3C3E5; }
.subdatagrid {    width: 100%; }
.subdatagrid th {
    padding: 2px 0 2px 4px !important;
    font-size: 9px !important;
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
.log_display {font-weight:bold;margin-right: 90px;}
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
    padding: 5px;
    margin: 5px;
    border-radius: 6px;
}
.batch_msg_enabled { margin-top:20px; cursor: pointer;color: #EC0009 !important; }
.batch_msg_disabled { margin-top: 20px;
    cursor: pointer;
    color: #f4f4f4;
    background-color: #ff7777 !important;
    padding: 5px;
    margin: 5px;
    border-radius: 6px; }
.proceed_link { margin-top: 20px;
    cursor: pointer;
    color: #f4f4f4;
    background-color: #36EC4C !important;
    padding: 5px;
    margin: 5px;
    border-radius: 6px; }
.info_links a {
    color: #1B0510;
/*   _links background-color: #36EC4C !important;*/
}
</style>
<div class="container" id="account_grn_present">
    <h2>Manage Transaction Reservations</h2>
    <div class="trans_filters_block">
        
            
        <table width="100%">
            <tr>
                <td width="65%">

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
                    
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
                </td>
                <td width="35%" align="right">
                   <form id="trans_date_form" method="post">
                            <b>Show transactions</b> :
                            <label for="date_from">From :</label><input type="text" style="width: 90px;" id="date_from"
                                    name="date_from" value="<?php echo date('Y-m-1',time()-60*60*24*7*4)?>" />
                            <label for="date_to">To :</label><input type="text" style="width: 90px;" id="date_to"
                                    name="date_to" value="<?php echo date('Y-m-d',time()-60*60*24*7*2)?>" /> 
                            <input type="submit" value="Submit">
                    </form>
                </td>
            </tr>
            <tr>
                
                <td>
                    <label for="batch_type" style="float:left;">Batch Type:</label>
                    <select id="batch_type">
                        <option value="00">All</option>
                        <option value="ready">Batch Ready</option>
                        <option value="partial_ready">Partial Batch Ready</option>
                        <option value="not_ready">Not Ready</option>
                    </select>
                    <label style="float:left;font-weight: bold; padding: 4px !important;min-width: inherit;" class="dash_bar"><a href="javascript:void(0);" onclick="reallot_stock_for_all_transaction(<?=$pg?>);">Re-Allot all transactions</a></label>
                    <span class="working_status"></span>
                </td>
                <td align="right"> 
                    <span class="log_display"></span>
                    <span class="ttl_trans_listed dash_bar"></span>
                </td>
            </tr>
        </table>
    </div>
    <div style="padding:1px 0px;" id="trans_list_replace_block"></div>
</div>

<script>
// <![CDATA[
    //By default load lists
    loadTransactionList(0);
    var pg=0;
    
    function reallot_stock_for_all_transaction(pg) {
        if(!confirm("Are you sure you want to reserve available stock for all pending or partial transactions?")) {
            return false;
            //var batch_remarks=prompt("Enter remarks?");
        }
        /*
        var batch_remarks='';'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'*/
        var updated_by = "<?=$user['userid']?>";
        $(".working_status").html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
        $.post('reserve_avail_stock_all_transaction/'+updated_by,"",function(rdata) {
            loadTransactionList(0);
        });
        $(".working_status").html("");
        
        
        return false;
    }
    
    function cancel_proforma_invoice(p_invoice_no,pg) {
        if(!confirm("Are you sure you want to cancel proforma invoice?")) {
            return false;
        }
        $.post(site_url+"admin/cancel_proforma_invoice/"+p_invoice_no,{},function() {
            loadTransactionList(pg);
        });
        return false;
    }
    
    function batch_enable_disable(transid,flag,pg) {
        var d_msg=(flag==1)?"enable":"disable";
        if(confirm("Are you sure you want to "+d_msg+" for batch?")) {
            $.post(site_url+"admin/jx_batch_enable_disable/"+transid+"/"+flag,{},function(rdata) {
                alert(rdata);
//                $(".pg").val(pg);
                
                loadTransactionList(pg);
                
            }).done(done).fail(fail);
        }
    }
    
    function reserve_stock_batch(transid,pg) {
        if(!confirm("Are you sure you want to process \nthis transaction for batch?")) {
            return false;
            //var batch_remarks=prompt("Enter remarks?");
        }
        var ttl_num_orders=$("."+transid+"_total_orders").val();
        var batch_remarks='';
        var updated_by = "<?=$user['userid']?>";
        
        $.post('reserver_batch_process/'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'+updated_by+'',"",function(rdata) {
            loadTransactionList(pg);
        });
        
        return false;
    }
    //Show between date ranges
    $("#trans_date_form").submit(function() {
        loadTransactionList(0);
        return false;
    });
    //ONCHANGE Batch_type
    $("#batch_type").live("change",function() {
        loadTransactionList(0);
        return false;
    });
    //Paginations
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
            
            $.post("<?php echo site_url("admin/jx_get_brandsbymenuid"); ?>"+"/"+menuid,{},function(resp) {
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
    $("#sel_franchise").live("change",function() {
                /*var franchiseid=($("#sel_franchise").val()=='00')? 00 :$("#sel_franchise").val();
                if(franchiseid==00) {
                    $(".sel_status").html("");
                }   $.post("<?php echo site_url("admin/jx_franchise_creditnote"); ?>"+"/"+franchiseid,{},function(resp) {
                    if(resp.status=='success') {
                         $(".sel_status").html(resp);
                    }
                    else {
                        $(".sel_status").html(resp);
                    }
                }).done(done).fail(fail);*/
                
        loadTransactionList(0);
        return false;
    });
    
    //ENTRY 6
    $("#sel_town").live("change",function() { 
        var townid=$(this).find(":selected").val();//text();
        var terrid=$("#sel_territory").find(":selected").val();//text();
        $.post("<?php echo site_url("admin/jx_suggest_fran"); ?>"+"/"+terrid+"/"+townid,function(resp) {
                if(resp.status=='success') {
                     var obj = jQuery.parseJSON(resp.franchise);
                    $("#sel_franchise").html(objToOptions_franchise(obj));
                }
                else {
                    $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
                    //$(".sel_status").html(resp.message);
                }
            },'json').done(done).fail(fail);
        
        loadTransactionList(0);
        return false;
    });
    
    
    //ONCHANGE Territory
    $("#sel_territory").live("change",function() {
        var terrid=$(this).find(":selected").val();//text();
//        if(terrid=='00') {          $(".sel_status").html("Please select territory."); return false;        }
        
       // $("table").data("sdata", {terrid:terrid});
        
        $.post("<?php echo site_url("admin/jx_suggest_townbyterrid"); ?>/"+terrid,function(resp) {
            if(resp.status=='success') {
                 //print(resp.towns);
                 var obj = jQuery.parseJSON(resp.towns);
                $("#sel_town").html(objToOptions_terr(obj));
            }
            else {
                $("#sel_town").val($("#sel_town option:nth-child(0)").val());
                $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
                            //$(".sel_status").html(resp.message);
            }
        },'json').done(done).fail(fail);
        loadTransactionList(0);
        return false;
    });
    

    
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
        $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
        $.post('jx_get_transaction_list/'+batch_type+'/'+date_from+'/'+date_to+'/'+terrid+'/'+townid+'/'+franchiseid+'/'+menuid+'/'+brandid+"/"+pg,"",function(rdata) {
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
    
    function done(data) { }
    function fail(xhr,status) { $('#trans_list_replace_block').print("Error: "+xhr.responseText+" "+xhr+" | "+status);}
    function success(resp) {
            $('#trans_list_replace_block').html(resp);
    }

   function objToOptions_brands(obj) {
        var output='';
            output += "<option value='00' selected>All Brands</option>\n";
        $.each(obj,function(key,elt){
            if(obj.hasOwnProperty(key)) {
                output += "<option value='"+elt.id+"'>"+elt.name+"</option>\n";
            }
        });
        return(output);
    }
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
    function objToOptions_franchise(obj) {
        var output='';
            output += "<option value='00' selected>All Franchise</option>\n";
        $.each(obj,function(key,elt){
            if(obj.hasOwnProperty(key)) {
                output += "<option value='"+elt.franchise_id+"'>"+elt.franchise_name+"</option>\n";
            }
        });
        return(output);
    }
// ]]>
</script>

<?php
