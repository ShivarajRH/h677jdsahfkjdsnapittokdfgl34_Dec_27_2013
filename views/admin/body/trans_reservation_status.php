<style type="text/css">
.leftcont { display: none;}
table.datagridsort tbody td { padding: 4px; }
.datagrid td { padding: 1px; }
.datagrid th { background: #443266;color: #C3C3E5; }
.subdatagrid {
    width: 100%;
}
.subdatagrid th {
    padding: 1px !important;
    font-size: 11px !important;
    color:#080808;
    background-color:#F1F0FF; /*rgba(51, 47, 43, 0.61);*/
}
.subdatagrid td {
    padding: 1px;
    font-size: 11px;
}
.subdatagrid td a {
    color: #121213;
}
.subdatagrid tr.processed_ord td,.subdatagrid tr.shipped_ord td{ text-decoration: line-through;color: green  !important;}
.subdatagrid tr.processed_ord td a,.subdatagrid tr.shipped_ord td a{text-decoration: line-through;color: green !important;}
.subdatagrid tr.cancelled_ord td{text-decoration: line-through;color: #cd0000 !important;}
.subdatagrid tr.cancelled_ord td a{text-decoration: line-through;color: #cd0000 !important;}

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
                <th>Date</th>
                <th>Transaction Id</th>
                <th>Process</th>
                <th>Orders</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php   //echo "<pre>".$last_qry."</pre>";
            foreach($transactions as $i=>$trans_arr) { $i+=1;
                    $o_item_list = $this->db->query("select a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount from king_orders a
                            join king_dealitems b on a.itemid = b.id 
                            where a.transid = ? order by a.status 
                    ",$trans_arr['transid'])->result_array();
                    $oi = 0;
                        foreach($o_item_list as $o_item)
                        {
                                $is_cancelled = ($o_item['status']==3)?1:0;
                                $ord_stat_txt = '';
                                if($o_item['status'] == 0)
                                        $ord_stat_txt = 'pending';
                                else if($o_item['status'] == 1)
                                        $ord_stat_txt = 'processed';
                                else if($o_item['status'] == 2)
                                        $ord_stat_txt = 'shipped';
                                 else if($o_item['status'] == 3)
                                        $ord_stat_txt = 'cancelled';

                        }
                        
                ?>
            <tr>
                <td style="width:15px"><?php echo $i; ?></td>
                <td style="width:180px"><?php echo $trans_arr['str_time']; ?></td>
                <td><?php echo $trans_arr['transid']; ?></td>
                <td><?=$ord_stat_txt;?></td>
                <td style="padding:0px !important;">
                   
                     <table class="subdatagrid" cellpadding="0" cellspacing="0">
                        
                        <?php $trans_orders = $this->db->query("SELECT 
                                                            o.id as orderid,o.itemid,o.quantity,o.status,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount
                                                            ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled
                                                            ,di.id as itemid,di.name as dealname,di.price,di.available,di.pic
                                                            from king_orders o
                                                            join king_transactions tr on tr.transid=o.transid
                                                            join king_dealitems di on di.id=o.itemid
                                                            join king_deals deal on deal.dealid=di.dealid
                                                            WHERE tr.transid=?
                                                            group by o.id order by o.actiontime DESC",$trans_arr['transid'])->result_array();
                       
                                    //echo "<pre>"; echo $this->db->last_query();echo "</pre>";
                        ?>
                        <tr>
                            <th>Order id</th>
                            <th>Product Id</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Available Stk</th>
                            <th>Sourceable</th>
                        </tr>
                        <?php
                        foreach($trans_orders as $order) {
                            
                        ?>
                                <tr class="<?php echo $ord_stat_txt.'_ord'?>">
                                    <td style="width:100px"><?php echo $order['orderid']; ?></td>
                                    <td style="width:50px"><?php echo $order['product_id']; ?></td>
                                    <td style="width:360px"><?php echo $order['dealname']; ?></td>
                                    <td style="width:50px"><?php echo $order['quantity']; ?></td>
                                    <td style="width:50px"><?php echo $order['available']; ?></td>
                                    <td style="width:50px"><?php echo $order['sorceable']; ?></td>
                                </tr>
                        <?php 
                            }
                        ?>
                        
                    </table>
                <td><?php echo '<a href="">Process for packing</a>'; ?> </td>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
 
    </div>
</div>

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

<?php
