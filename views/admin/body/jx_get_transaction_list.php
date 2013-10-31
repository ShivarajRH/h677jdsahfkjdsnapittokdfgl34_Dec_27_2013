<?php
$output = '';
$output_header='';
$output_body='';
$output_body_top='';
$output_body_bottom='';
$cond = '';
$from=strtotime($s);
$to=strtotime("23:59:59 $e");

/*if( $batch_type == "ready") 
{
    $cond .= ' and tr.batch_enabled=1 and o.status IN (1,2) ';
}
if( $batch_type == "partial_ready") 
{
    $cond .= ' and tr.batch_enabled=1 and o.status = 0 ';
}
if( $batch_type == "not_ready") 
{
    $cond .= ' and tr.batch_enabled=0 and o.status = 3 ';
}
*/

if($menuid!=0) {
     $cond .= ' and dl.menuid='.$menuid; 
 }
if($brandid!=0) {
     $cond .= ' and dl.brandid='.$brandid;
 }
if($terrid!=0) {
     $cond .= ' and f.territory_id='.$terrid;
     $con_msg_log = " and for this territory ";
 }
 if($townid!=0) {
     $cond .= ' and f.town_id='.$townid;
     $con_msg_log = " and for this town ";
 }
 if($franchiseid!=0) {
     $cond .= ' and f.franchise_id='.$franchiseid;
     $con_msg_log = " and for this franchise ";
 }
 
//echo "<br>".$s."++".$e."++".$batch_type."++".$cond;
$sql="select 
            from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time
            ,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled,tr.franchise_id
            from king_transactions tr
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
            WHERE tr.actiontime between $from and $to and batch_enabled=1
            order by tr.actiontime desc ";
 
//echo "<p><pre>".$sql.'</pre></p>';die();
$transactions_src=$this->db->query($sql);

if(!$transactions_src->num_rows()) 
{
    $output.='<script>$(".ttl_trans_listed").html("");</script><script>$(".log_display").html("");</script>';
    $output .= '<div><h3 style="margin:2px;" align="center">No transactions found for selected dates range '.$s.' to '.$e.' </h3>';
}
else 
{
                
    $total_trans_rows=$transactions_src->num_rows();
    $transactions=$this->db->query($sql." limit $pg,$limit")->result_array();
    
    
//   PAGINATION
            $this->load->library('pagination');

            $config['base_url'] = site_url("admin/jx_get_transaction_list/".$batch_type.'/'.$s.'/'.$e.'/'.$terrid.'/'.$townid.'/'.$franchiseid.'/'.$menuid.'/'.$brandid); 
            $config['total_rows'] = $total_trans_rows;
            $config['per_page'] = $limit;
            $config['uri_segment'] = 11; 
            $config['num_links'] = 5;

            $this->config->set_item('enable_query_strings',false); 
            $this->pagination->initialize($config); 
            $trans_pagination = $this->pagination->create_links();
            $this->config->set_item('enable_query_strings',TRUE);
//   PAGINATION ENDS
                    
//    $output_header .= '<div class="trans_pagination pagi_top">'.$trans_pagination.' </div>';
    $output_header .= '<table class="datagrid" width="100%">
        <thead>
            <tr>
                <!--<th><abbr title="Some transactions are skipped for transactions are not having any orders">Slno*</abbr></th>-->
                <th>Ordered On</th>
                <th>Transaction Id</th>
                <th>Item details</th>
                <th>Reservation Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';
        #$slno = $pg;
        foreach($transactions as $i=>$trans_arr) 
        {  
            #$slno+=1;
           
                        $trans_orders = $this->db->query("select e.invoice_no,sd.packed,sd.shipped,e.invoice_status,sd.shipped_on,a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount 
                                                                    from king_orders a
                                                                    join king_dealitems b on a.itemid = b.id
                                                                    join king_deals dl on dl.dealid = b.dealid
                                                                    join king_transactions t on t.transid = a.transid   
                                                                    left join proforma_invoices c on c.order_id = a.id 
                                                                    left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
                                                                    left join king_invoice e on e.invoice_no = sd.invoice_no
                                                                        left join pnh_m_franchise_info  f on f.franchise_id=t.franchise_id
                                                                        left join pnh_m_territory_info ter on ter.id = f.territory_id 
                                                                        left join pnh_towns twn on twn.id=f.town_id
                                                            where a.status in (0,1) and a.transid = ? $cond
                                                            order by c.p_invoice_no desc",$trans_arr['transid'])->result_array();
                       # echo '<pre>'; print_r($this->db->last_query()); die();
                        
                        if(!count($trans_orders)) { 
                            $output_body_top='';
                            $output_body_item='';
                            $output_body_bottom='';
                        }
                        else {
                            $output_body_top = '<tr class="'.$ord_stat_txt.'_ord">
                                <!--<td style="width:15px">'.$slno.'</td>-->
                                <td style="width:180px">'.$trans_arr['str_time'].'</td>
                                <td style="width:100px"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</a><br>'.$batch_status_msg.'</td>
                                <td style="padding:0px !important;">
                                    <input type="hidden" size="2" class="'.$trans_arr['transid'].'_total_orders" value="'.count($trans_orders).'" />
                                        <table class="subdatagrid" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <th>Slno</th>
                                                <th>Order id</th>
                                                <th>Deal Id</th>
                                                <th>Deal Name</th>
                                                <th>Quantity</th>
                                                <th>Status</th>
                                                <th>MRP</th>
                                                <th>Amount</th>
                                            </tr>';
                            
                                $trans_pending = 0;
                                $output_body_item='';
                                foreach($trans_orders as $j=>$order_i) 
                                { $j+=1;


                                        if($order_i['status'] == 0) {
                                            $trans_pending += 1;
                                        }
                                        //elseif($order_i['status'] == 1) { $processed += 1; }

                                        $amount=round($order_i['i_orgprice']-($order_i['i_coup_discount']+$order_i['i_discount']),2);
                                        $output_body_item .= '<tr class="'.$ord_stat_txt.'_ord">
                                            <td style="width:25px">'.$j.'</td>
                                            <td style="width:50px">'.$order_i['id'].'</td>
                                            <td style="width:50px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['id'].'</a></td>
                                            <td style="width:200px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['name'].'</a></td>
                                            <td style="width:50px">'.$order_i['quantity'].'</td>
                                            <td style="width:50px">'.$order_i['status'].'</td>
                                            <td style="width:50px">'.$order_i['i_orgprice'].'</td>
                                            <td style="width:50px">'.$amount.'</td>
                                        </tr>';

                                        

                                }
                                
                                
                                $trans_action_msg='';
                                if($trans_pending == 0) {
                                    $order_status_msg = 'Processed';
                                    $trans_action_msg = '<div class="batch_msg_disabled clear" class="batch_status_msg_'.$trans_arr['transid'].'" onclick="_enable_disable(\''.$trans_arr['transid'].'\',1)">Process for packing</div>
                                                            <div class="batch_msg_disabled clear" class="batch_status_msg_'.$trans_arr['transid'].'" onclick="_enable_disable(\''.$trans_arr['transid'].'\',1)">Disable from batch</div>';
                                }
                                elseif($trans_pending == count($trans_orders)) {
                                    $order_status_msg = 'Not Ready';
                                    $trans_action_msg = '<a href="javascript:void(0);" onclick="return trans_enable_batch(\''.trim($trans_arr['transid']).'\');">Process to Batch</a>';
                                }
                                elseif($trans_pending < count($trans_orders)) {
                                    $order_status_msg = 'Partial Ready';
                                }
                        
                                $output_body_bottom = '</table>
                                                    </td>
                                                    <td style="width:100px">'.($order_status_msg).'</td>
                                                    <td>'.$trans_action_msg.'</td>
                                                </tr>';
                                
                               
                        }
                         $output_body .= $output_body_top.$output_body_item.$output_body_bottom;
                        
                        
            }
            
            if($output_body == '') {
                
                $output.='<script>$(".ttl_trans_listed").html("");</script><script>$(".log_display").html("");</script>';
                
                $output_body='<div><h3 style="margin:2px;" align="center">No transactions found for selected dates range '.$s.' to '.$e.' </h3>'; 
                $output_footer = '';
            }
            else {
                 $endlimit=($pg+1*$limit);
                $endlimit=($endlimit>$total_trans_rows)?$total_trans_rows : $endlimit;

                $output.='<script>$(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' to '.$endlimit.'</strong> of <strong>'.$total_trans_rows.'</strong> transactions");</script>
                    <script>$(".log_display").html("Orders from '.$s.' to '.$e.'");</script>';
                $output_footer = '</tbody></table><div class="trans_pagination">'.$trans_pagination.' </div>';
            }
            
            
            
            $output .= $output_header.$output_body.$output_footer;

   

}
echo $output;
    