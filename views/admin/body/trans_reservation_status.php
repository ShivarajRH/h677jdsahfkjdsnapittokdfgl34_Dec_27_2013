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
</style>
<div class="container" id="account_grn_present">
    <h2>Transaction Reservation Status</h2>
    <div>
        <table class="" width="100%">
                <tr>
                    <td></td>
                    <td>
                        Batch Type:
                        <select>
                            <option>All</option>
                            <option>Batch Ready</option>
                            <option>Partial Batch Ready</option>
                            <option>Not Ready</option>
                        </select>
                    </td>
                    <td><strong><?=$log_display;?></strong></td>
                    <td align="right"><div >
                        <form id="ord_list_frm" method="post">
                                <input type="hidden" value="all" name="type" name="type">
                                <b>Show Orders </b> :
                                From :<input type="text" style="width: 90px;" id="date_from"
                                        name="date_from" value="<?php echo date('Y-m-d',time()-60*60*24)?>" />
                                To :<input type="text" style="width: 90px;" id="date_to"
                                        name="date_to" value="<?php echo date('Y-m-d',time())?>" /> 
                                <input type="submit" value="Submit">
                        </form>
                        </div>
                    </td>
                </tr>
            
        </table>
    </div>

    <div style="padding:20px 0px;">
    <table class="datagrid" width="100%">
        <thead>
            <tr>
                <th>Slno</th>
                <th>Ordered On</th>
                <th>Transaction Id</th>
                <th>Process to Batch</th>
                <th>Shipment from Batch</th>
                <th>Orders</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php   //echo "<pre>".$last_qry."</pre>";
            
            foreach($transactions as $i=>$trans_arr) { $i+=1;
            
                $ord_stat_txt = '';
                $action_str = '';
                
                if($trans_arr['status'] == 0) {
                        $ord_stat_txt = 'pending';
                        $action_str = '<a href="javascript:void(0);" onclick="return trans_enable_batch("'.trim($trans_arr['transid']).'");"> Batch Enable</a>';
                        
                        

                }
                else if($trans_arr['status'] == 1) {
                        $ord_stat_txt = 'processed';
                        
                }
                else if($trans_arr['status'] == 2) {
                        $ord_stat_txt = 'shipped';
                }
                 else if($trans_arr['status'] == 3) {
                        $ord_stat_txt = 'cancelled';
                        $action_str = '<a href="">Enable</a>';
                 }
                 
                 
                 $proform_list = $this->db->query("select sb.status,sb.batch_id from shipment_batch_process_invoice_link sbp
                                        left join proforma_invoices pi on pi.p_invoice_no=sbp.p_invoice_no
                                        left join shipment_batch_process sb on sb.batch_id=sbp.batch_id
                                        where pi.transid=?
                                        order by sb.created_on desc",array($trans_arr['transid']))->result_array();
                                
                                
                if(!empty($proform_list)) {

                    $batch_status = array('PENDING','PARTIAL','CLOSED');
                    foreach($proform_list as $p_list) {

                        $b_status=$batch_status[$p_list['status']]; //$p_list['status'];
                        
                        if($p_list['status'] == 0) {
                            $action_str ='<a href="batch/'.$p_list['batch_id'].'"> Process for packing</a>'; 
                        }
                        
                        
                    }
                }
                else {
                    $b_status='--';
                    $action_str ='<a  href="javascript:void(0);" onclick="return trans_enable_batch("'.trim($trans_arr['transid']).'">Re - Process to batch</a>'; 
                }
                  
            ?>
            <tr class="<?php echo $ord_stat_txt.'_ord'?>">
                <td style="width:15px"><?=$i;?></td>
                <td style="width:180px"><?php echo $trans_arr['str_time']; ?></td>
                <td style="width:100px"><?php echo '<a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</a>'; ?></td>
                <td style="width:100px"><?=ucfirst($ord_stat_txt);?></td>
                <td style="width:100px"><?=ucfirst($b_status);?></td>
                <td style="padding:0px !important;">
                   
                     <table class="subdatagrid" cellpadding="0" cellspacing="0">
                        
                        <?php 
                        
                        $is_cancelled = ($o_item['status']==3)?1:0;
                        
                        $trans_orders = $this->db->query("SELECT 
                                                            o.id as orderid,o.itemid,o.quantity,o.status,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount
                                                            ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled
                                                            ,di.id as dealid,di.name as dealname,di.price,di.available,di.pic
                                                            #,pdl.product_id
                                                            from king_orders o
                                                            join king_transactions tr on tr.transid=o.transid
                                                            join king_dealitems di on di.id=o.itemid
                                                            join king_deals deal on deal.dealid=di.dealid
                                                            #join m_product_deal_link pdl on pdl.itemid=o.itemid
                                                            WHERE tr.transid=?
                                                            group by o.id order by o.actiontime DESC",$trans_arr['transid'])->result_array();
                       
                                    //echo "<pre>"; echo $this->db->last_query();echo "</pre>";
                                    
                        ?>
                         <input type="hidden" size="2" class="<?=$trans_arr['transid']?>_total_orders" value="<?=count($trans_orders)?>" />
                        <tr>
                            <th>Slno</th>
                            <th>Order id</th>
                            <th>Deal Id</th>
                            <th>Deal Name</th>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Amount</th>
                        </tr>
                        <?php
                        foreach($trans_orders as $j=>$order) { $j+=1;
                            
                        ?>
                                <tr class="<?php echo $ord_stat_txt.'_ord'?>">
                                    <td style="width:25px"><?=$j; ?></td>
                                    <td style="width:50px"><?php echo $order['orderid']; ?></td>
                                    <td style="width:50px"><?php echo $order['dealid']; ?></td>
                                    <td style="width:200px"><?php echo '<a href="pnh_deal/'.$order['itemid'].'" target="_blank">'.$order['dealname'].'</a>'; ?></td>
                                    <td style="width:50px"><?php echo $order['quantity']; ?></td>
                                    <td style="width:50px"><?php echo $order['i_orgprice']; ?></td>
                                    <td style="width:50px"><?php echo round($o_item['i_orgprice']-($o_item['i_coup_discount']+$o_item['i_discount']),2) ?></td>
                                </tr>
                        <?php 
                            }
                        ?>
                        
                    </table>
                </td>
                <td><?php echo $action_str; ?> </td>
                
            </tr>
            <?php } ?>
        </tbody>
    </table>
 
    </div>
</div>

<script>
// <![CDATA[
    function trans_enable_batch(transid) {
        var ttl_num_orders=$("."+transid+"_total_orders").val();
        var batch_remarks=prompt("Enter remarks?");
        var updated_by = "<?=$user['userid']?>";
        
        $.post('batching_process/'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'+updated_by+'',"",function(rdata) {
            console.log(rdata);
        });
        
        return false;
    }
//]]>
</script>

<script>
// <![CDATA[
var GM_TIMING_END_CHUNK1=(new Date).getTime();
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
        //loadTableData(0);
        
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
