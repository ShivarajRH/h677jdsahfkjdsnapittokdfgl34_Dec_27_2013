<link type="text/css" rel="stylesheet" href="<?php echo base_url();?>css/manage_reservations_style.css" />

<div class="container">
    <div>
        <h2>Manage Transaction Reservations</h2>
        <div class="high_link"><a href="javascript:void(0);" onclick="reallot_stock_for_all_transaction(<?=$user['userid'];?>);">Re-Allot all pending transactions</a></div>
    </div>
    <div class="clear"></div>
    <div id="list_wrapper">
        <table width="100%" >
                <tr>
                        <td width="60%">
                                <div class="tab_list" style="clear: both;">
                                            <ol>
                                                    <li><a class="load_type selected" id="ready" href="javascript:void(0)" title="Transactions are ready for shipping">READY</a><div class="ready_pop"></div></li>
                                                    <li><a class="load_type" id="partial" href="javascript:void(0)" title="Transactions are partial ready for shipping">PARTIAL</a><div class="partial_pop"></div></li>
                                                    <li><a class="load_type" id="pending" href="javascript:void(0)" title="Transactions are pending for shipping">PENDING</a><div class="pending_pop"></div></li>
                                            </ol>
                                    </div>
                    </td>
                </tr>
        </table>
    </div>
    <!--<p class="page_trans_description"></p>-->
    <div class="level1_filters">
        <fieldset>
            <span title="Toggle Filter Block" class="close_filters"><span class="close_btn">Show</span>
            <h3 class="filter_heading">Filters:</h3>
            </span>
                <div class="filters_block">
                        <div class="date_filter">
                            <form id="trans_date_form" method="post">
                                    <b>Show transactions : </b>
                                    <label for="date_from">From :</label><input type="text" id="date_from"
                                            name="date_from" value="<?php echo date('Y-m-01',time()-60*60*24*7*4)?>" />
                                    <label for="date_to">To :</label><input type="text" id="date_to"
                                            name="date_to" value="<?php echo date('Y-m-d',time())?>" /> 
                                    <input type="submit" value="Submit">
                            </form>
                        </div>
                        <div class="group_filter">
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
                        <div class="clear"></div>
                        <div>
                            <span class="limit_display_block">
                                Show
                                    <select name="limit_filter" id="limit_filter">
                                        <option value="20">20</option>
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                    </select>
                                items per page.
                            </span>
                        </div>
                        
                </div>
                <input type="hidden" name="pg_num" class="page_num" value="0" size="3"/>
        </fieldset>
    </div>
    <div class="clear"></div>
    <div class="level2_filters">
            <div class="trans_pagination pagination_top"></div>
            
            <span class="ttl_trans_listed dash_bar"></span>
<!--            <span class="log_display"></span>-->
    </div>        
        <div id="trans_list_replace_block"></div>

</div>
<script type="text/javascript" src="<?=base_url()?>js/manage_trans_reservations_script.js"></script>
<script>
// <![CDATA[
    //By default load lists
    loadTransactionList(0);
    var pg=0;
    
    function loadTransactionList(pg) 
    {
        $(".pagination_top").html("");
        $(".ttl_trans_listed").html("");
        $(".log_display").html("").dis;
        

        var batch_type = $('.tab_list .selected').attr('id');
//        var batch_type= ($("#batch_type").val() == "00")?0: $("#batch_type").val();
        var terrid= ($("#sel_territory").val()=='00')?0:$("#sel_territory").val();
        var townid=($("#sel_town").val()=='00')?0:$("#sel_town").val();
        var franchiseid=($("#sel_franchise").val()=='00')?0:$("#sel_franchise").val();
        var menuid=($("#sel_menu").val()=='00')?0:$("#sel_menu").val();
        var brandid=($("#sel_brands").val()=='00')?0:$("#sel_brands").val();
         
        var date_from= $("#date_from").val();
        var date_to= $("#date_to").val();
        
        
        var limit= $("#limit_filter").val();
        if(typeof pg != 'undefined') { 
            $(".page_num").val=pg;
        }
        pg = (typeof pg== 'undefined') ? $(".page_num").val() : $(".page_num").val();

        //alert(batch_type+"/"+date_from+"/"+date_to+"/"+pg);
        $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>"); 
        $.post('jx_get_transaction_list/'+batch_type+'/'+date_from+'/'+date_to+'/'+terrid+'/'+townid+'/'+franchiseid+'/'+menuid+'/'+brandid+"/"+limit+"/"+pg+"",{},function(rdata) {
            $("#trans_list_replace_block").html(rdata);
            
        });
   }
    
$(document).ready(function() {
        //FIRST RUN
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
        //prepare_daterange('date_from','date_to');
    });
    
    function done(data) { }
    function fail(xhr,status) { $('#trans_list_replace_block').print("Error: "+xhr.responseText+" "+xhr+" | "+status);}
    function success(resp) {
            $('#trans_list_replace_block').html(resp);
    }
// ]]>
    
</script>

<?php
