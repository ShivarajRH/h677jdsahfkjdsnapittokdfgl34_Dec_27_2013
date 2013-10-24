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
                <th>Stock</th>
                <th>Orders</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php   echo "<pre>".$last_qry."</pre>";
            foreach($transactions as $i=>$trans_arr) { $i+=1;
                
                ?>
            <tr>
                <td style="width:15px"><?php echo $i; ?></td>
                <td style="width:180px"><?php echo $trans_arr['str_time']; ?></td>
                <td><?php echo $trans_arr['transid']; ?></td>
                <td><?php echo $trans_arr['batch_status']; ?></td>
                <td style="padding:0px !important;">
                   
                     <table class="subdatagrid" cellpadding="0" cellspacing="0">
                        
                        <?php $trans_orders = $this->db->query("SELECT if((si.available_qty)=0,'No stock',if(o.quantity<=si.available_qty, 'ready' ,'partial')) as batch_status,
                                                            o.id as orderid,o.itemid,o.quantity,o.status,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount
                                                            ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled
                                                            ,di.id as itemid,di.name as dealname,di.price,di.available,di.pic
                                                            ,pdl.itemid,pdl.product_id,pdl.product_mrp,pdl.qty
                                                            ,si.stock_id,si.product_id,si.available_qty,si.location_id,si.rack_bin_id,si.product_barcode
                                                            ,if(p.is_sourceable=1,'yes','no') as sorceable
                                                            from king_orders o
                                                            join king_transactions tr on tr.transid=o.transid
                                                            join king_dealitems di on di.id=o.itemid
                                                            join m_product_deal_link pdl on pdl.itemid=o.itemid
                                                            join t_stock_info si on si.product_id=pdl.product_id
                                                            join m_product_info p on p.product_id=pdl.product_id
                                                            join king_deals deal on deal.dealid=di.dealid
                                                            WHERE tr.transid=?
                                                            group by o.id order by o.actiontime DESC",$trans_arr['transid'])->result_array();
                       
                                    echo "<pre>"; echo $this->db->last_query();echo "</pre>";
                        ?>
                        <tr>
                            <th>Order id</th>
                            <th>Prdt Id</th>
                            <th>Prdt Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Available Stk</th>
                            <th>Sourceable</th>
                        </tr>
                        <?php
                        foreach($trans_orders as $order) {
                        ?>
                                <tr>
                                    <td style="width:100px"><?php echo $order['orderid']; ?></td>
                                    <td style="width:50px"><?php echo $order['product_id']; ?></td>
                                    <td style="width:360px"><?php echo $order['dealname']; ?></td>
                                    <td style="width:50px"><?php echo $order['quantity']; ?></td>
                                    <td style="width:100px"><?php echo $order['batch_status']; ?></td>
                                    <td style="width:50px"><?php echo $order['available_qty']; ?></td>
                                    <td style="width:50px"><?php echo $order['sorceable']; ?></td>
                                </tr>
                        <?php 
                            }
                        ?>
                        
                    </table>
                <td><?php echo "Process Delete"; ?></td>
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
