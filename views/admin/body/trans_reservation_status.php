<style>
    table.datagridsort tbody td { padding: 7px; }
    .datagrid td { padding: 7px; }
    .datagrid th { background: #443266;color: #C3C3E5; }
    .subdatagrid {
        width: 100%;
    }
    .subdatagrid td {
        padding: 3px;
        font-size: 12px;
    }
    .subdatagrid td a {
        color: #121213;
    }
</style>
<div class="container" id="account_grn_present">
    <h2>Stock Overview and Process</h2>
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
    <table class="datagrid datagridsort" width="100%">
        <thead>
            <tr>
                <th>Transaction id</th>
                <th>Order id</th>
                <th>Product Name</th>
                <th>Ordered Items</th>
                <th>Status</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($transactions as $trans_arr) { 
     
                ?>
            <tr>
                <td><?php print($trans_arr['transid']); ?></td>
                <td><?php print($trans_arr['orderid']); ?></td>
                <td><?php print($trans_arr['name']); ?></td>
                <td>
                   
                     <table class="subdatagrid">
                        
                        <?php //o.quantity,o.status,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount
                       $trans_orders = $this->db->query("SELECT if(o.quantity<=si.available_qty, 'ready' ,'partial') as batch_status,
                                                            o.id as orderid,o.itemid,o.quantity,o.status,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount
                                                            ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled
                                                            ,di.id as itemid,di.name as dealname,di.price,di.available,di.pic
                                                            ,pdl.itemid,pdl.product_id,pdl.product_mrp,pdl.qty
                                                            ,si.stock_id,si.product_id,si.available_qty,si.location_id,si.rack_bin_id,si.product_barcode
                                                            from king_orders o
                                                            join king_transactions tr on tr.transid=o.transid
                                                            join king_dealitems di on di.id=o.itemid
                                                            join m_product_deal_link pdl on pdl.itemid=o.itemid
                                                            join t_stock_info si on si.product_id=pdl.product_id
                                                            join king_deals deal on deal.dealid=di.dealid
                                                            WHERE tr.transid=?
                                                            group by o.id order by o.actiontime DESC",$trans_arr['transid'])->result_array();
                       
                        //echo "<pre>";print_r($trans_orders); print $this->db->last_query(); die();
                        ?>
                        <tr>
                            <th>Transaction id</th>
                            <th>Order id</th>
                            <th>Product Name</th>
                            <th>Ordered Items</th>
                            <th>Status</th>
                            
                            <th>Date</th>
                        </tr>
                        <?php
                        /*foreach($orders as $id=>$order_arr) {*/
                            foreach($trans_orders as $order) {
                        ?>
                                <tr>
                                    <td><?php print($order['transid']); ?></td>
                                    <td><?php print($order['orderid']); ?></td>
                                    <td><?php print($order['dealname']); ?></td>
                                    <td><?php print($order['available']); ?></td>
                                    <td><?php print($order['batch_status']); ?></td>
                                    <td><?php print($order['str_time']); ?></td>

                                </tr>
                    <?php 
                        }
                   /* }*/
                        ?>
                        
                    </table>
                    
                </td>
                <td><?php print($trans_arr['batch_status']); ?></td>
                <td><?php print("Process Delete"); ?></td>
                <td><?php print($trans_arr['str_time']); ?></td>

            </tr>
            <?php } ?>
        </tbody>
    </table>
 <?php 
               
                /*
                 * [PNHMMK31572] => Array
                    (
                        [0] => Array
                            (
                                [str_time] => 23rd September 07:10:40 2013
                                [id] => 5389
                                [userid] => 81390
                                [itemid] => 866577478746
                                [quantity] => 1
                                [status] => 0
                                [bill_person] => Banashankari PayNearHome - 11feet ecommerce Pvt Ltd
                                [transid] => PNHMMK31572
                                [amount] => 292
                                [paid] => 292
                                [init] => 1379665403
                                [actiontime] => 1379665403
                                [is_pnh] => 1
                                [franchise_id] => 17
                                [batch_enabled] => 1
                                [trans_created_by] => 0
                                [deal] => Lakme Enrich Satin Lip Color - 138
                                [price] => 221
                                [available] => 0
                                [pic] => 4ja9pk73af3h640
                                [product_id] => 413
                                [product_mrp] => 225.0000
                                [qty] => 1
                                [is_active] => 1
                                [created_on] => 
                                [created_by] => 0
                                [modified_on] => 2013-10-10 15:31:27
                                [modified_by] => 28
                                [tmp_pnh_itemid] => 
                                [stock_id] => 1567
                                [location_id] => 1
                                [rack_bin_id] => 25
                                [mrp] => 225.0000
                                [available_qty] => 3
                                [product_barcode] => 8901030324161
                                [in_transit] => 0
                                [tmp_brandid] => 0
                    )
                 * 
                 */
             ?>
    </div>
</div>

<script>
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
</script>

<?php
