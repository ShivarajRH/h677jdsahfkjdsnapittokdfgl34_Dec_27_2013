<?php
$output = ''; $cond = '';
        


$from=strtotime($s);
$to=strtotime("23:59:59 $e");




if( $batch_type == "ready") 
{
    $cond .= ' and o.status in(1,2)';
}
if( $batch_type == "partial_ready") 
{
    $cond .= ' and o.status = 0';
}
if( $batch_type == "not_ready") 
{
    $cond .= ' and o.status = 3';
}

if($menuid!=0) {
     $cond .= ' and deal.menuid='.$menuid; 
 }
if($brandid!=0) {
     $cond .= ' and deal.brandid='.$brandid;
 }
if($terrid!=0) {
     $cond .= ' and d.territory_id='.$terrid;
 }
 if($townid!=0) {
     $cond .= ' and d.town_id='.$townid;
 }
 if($franchiseid!=0) {
     $cond .= ' and d.franchise_id='.$franchiseid;
 }

 
//echo "<br>".$s."++".$e."++".$batch_type."++".$cond;
$log_display="Showing orders between ".$s." to ".$e;

$sql="select 
                    #if(sum(si.available_qty)=0,'No stock',if(o.quantity<=si.available_qty, if(sum(o.quantity)<=0,'Not Ready','Batch Ready'),'Partial Ready')) as batch_status,
                    from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time
                    ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled
                    ,o.id as orderid,o.itemid,o.status,o.quantity,o.shipped,o.ship_person,o.ship_address,o.ship_city,o.quantity,o.ship_pincode,o.ship_state,o.ship_email,o.ship_phone
                    from king_orders o
                    #join m_product_deal_link pdl on pdl.itemid=o.itemid
                    #join t_stock_info si on si.product_id=pdl.product_id
                    join king_transactions tr on tr.transid=o.transid
                    join king_dealitems di on di.id=o.itemid 
                    WHERE tr.actiontime between $from and $to $cond
                    group by o.transid order by tr.actiontime desc";
$sql="select 
                    from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time
                    ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled,tr.franchise_id
                    ,o.id as orderid,o.itemid,o.status,o.quantity,o.shipped,o.ship_person,o.ship_address,o.ship_city,o.quantity,o.ship_pincode,o.ship_state,o.ship_email,o.ship_phone
		,d.brandid,d.menuid
		#,f.franchise_id	
                    from king_orders o
                    join king_transactions tr on tr.transid=o.transid
                    join king_dealitems di on di.id=o.itemid 
		join king_deals d on d.dealid=di.dealid
		join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
		join pnh_m_territory_info ter on ter.id = f.territory_id 
		join pnh_towns twn on twn.id=f.town_id
                    WHERE tr.actiontime between $from and $to $cond
                    group by o.transid order by tr.actiontime desc ";
//echo "<p><pre>".$sql.'</pre></p>';
$transactions_src=$this->db->query($sql);

if(!$transactions_src->num_rows()) 
{
        $output .= '<div><h3 style="margin:2px;" align="center">No transactions found for selected dates range '.$s.' to '.$e.' </h3>';
}
else 
{
    
                $total_trans_rows=$transactions_src->num_rows();
                $transactions=$this->db->query($sql." limit $pg,$limit")->result_array();
        
//                    PAGINATION
                    
                    $this->load->library('pagination');
                   
                    $config['base_url'] = site_url("admin/jx_get_transaction_list/".$batch_type.'/'.$s.'/'.$e); 
                    $config['total_rows'] = $total_trans_rows;
                    $config['per_page'] = $limit;
                    $config['uri_segment'] = 6; 
                    $config['num_links'] = 5;
                    
                    $this->config->set_item('enable_query_strings',false); 
                    $this->pagination->initialize($config); 
                    $trans_pagination = $this->pagination->create_links();
                    $this->config->set_item('enable_query_strings',TRUE);
//                  PAGINATION ENDS
                    
        
        $output .= '<div class="trans_pagination pagi_top">'.$trans_pagination.' </div>
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
            <tbody>';
           //echo "<pre>".$last_qry."</pre>";
           
                foreach($transactions as $i=>$trans_arr) 
                {  $i+=1;

                    $ord_stat_txt = '';
                    $action_str = '';

                    if($trans_arr['status'] == 0) { // partial ready
                            $ord_stat_txt = 'pending';
                            

                    }
                    else if($trans_arr['status'] == 1) { //ready
                            $ord_stat_txt = 'processed';

                    }
                    else if($trans_arr['status'] == 2) { //ready
                            $ord_stat_txt = 'shipped';
                    }
                    else if($trans_arr['status'] == 3) { //Not ready
                            $ord_stat_txt = 'cancelled';
                            $action_str = '<a href="">Enable</a>';
                    }


                                    $proform_list = $this->db->query("select sb.status,sb.batch_id from shipment_batch_process_invoice_link sbp
                                            left join proforma_invoices pi on pi.p_invoice_no=sbp.p_invoice_no
                                            left join shipment_batch_process sb on sb.batch_id=sbp.batch_id
                                            where pi.transid=?
                                            order by sb.created_on desc",array($trans_arr['transid']))->result_array();


                    if(!empty($proform_list))
                    {

                        $batch_status = array('PENDING','PARTIAL','CLOSED');
                        foreach($proform_list as $p_list) {

                            $b_status = $batch_status[$p_list['status']]; 

                            if($p_list['status'] == 0) {
                                $action_str ='<a href="batch/'.$p_list['batch_id'].'"> Re-process for packing</a>'; 
                            }
                            elseif($p_list['status'] == 1) {
                                $action_str ='<a href="batch/'.$p_list['batch_id'].'">Process for packing</a>'; 
                            }
                            elseif($p_list['status'] == 2) {
                                $action_str ='Done packing'; 
                            }


                        }
                    }
                    elseif($ord_stat_txt = 'pending' and empty($proform_list)) {
                        $b_status = '--';
                        $action_str = '<a href="javascript:void(0);" onclick="return trans_enable_batch(\''.trim($trans_arr['transid']).'\');">Process to Batch</a>';
                    }
                    else {
                        $b_status = '--';
                        $action_str ='<a href="javascript:void(0);" onclick="return trans_enable_batch(\''.trim($trans_arr['transid']).'\');">Re-process to batch</a>';
                    }
                  
            
                $output .= '<tr class="'.$ord_stat_txt.'_ord" id="action_refresh_'.trim($trans_arr['transid']).'">
                    <td style="width:15px">'.$i.'</td>
                    <td style="width:180px">'.$trans_arr['str_time'].'</td>
                    <td style="width:100px"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</a></td>
                    <td style="width:100px">'.ucfirst($ord_stat_txt).'</td>
                    <td style="width:100px">'.ucfirst($b_status).'</td>
                    <td style="padding:0px !important;">';
                
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
                                //$output .= "<pre>"; echo $this->db->last_query();echo "</pre>";
                        
                    $output .='<input type="hidden" size="2" class="'.$trans_arr['transid'].'_total_orders" value="'.count($trans_orders).'" />
                        <table class="subdatagrid" cellpadding="0" cellspacing="0">
                                <tr>
                                    <th>Slno</th>
                                    <th>Order id</th>
                                    <th>Deal Id</th>
                                    <th>Deal Name</th>
                                    <th>Quantity</th>
                                    <th>MRP</th>
                                    <th>Amount</th>
                                </tr>';
                                
                                foreach($trans_orders as $j=>$order) 
                                { $j+=1;

                                        $output .= '<tr class="'.$ord_stat_txt.'_ord">
                                            <td style="width:25px">'.$j.'</td>
                                            <td style="width:50px">'.$order['orderid'].'</td>
                                            <td style="width:50px"><a href="pnh_deal/'.$order['itemid'].'" target="_blank">'.$order['dealid'].'</a></td>
                                            <td style="width:200px"><a href="pnh_deal/'.$order['itemid'].'" target="_blank">'.$order['dealname'].'</a></td>
                                            <td style="width:50px">'.$order['quantity'].'</td>
                                            <td style="width:50px">'.$order['i_orgprice'].'</td>
                                            <td style="width:50px">'.round($order['i_orgprice']-($order['i_coup_discount']+$order['i_discount']),2).'</td>
                                        </tr>';
                                
                                }
                                
                        $output .= '</table>
                    </td>
                    <td>'.$action_str.'</td>
                </tr>';
            }
            $output .= '</tbody>
        </table>
        <div class="trans_pagination">'.$trans_pagination.' </div>';
            
        $endlimit=($pg+1*$limit);
        $endlimit=($endlimit>$total_trans_rows)?$total_trans_rows : $endlimit;
    
        $output.='
            <script>$(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' to '.$endlimit.'</strong> of <strong>'.$total_trans_rows.'</strong> transactions");</script>
            <script>$(".log_display").html("Orders from '.$s.' to '.$e.'");</script>';
    }
    
    
    echo $output;
    