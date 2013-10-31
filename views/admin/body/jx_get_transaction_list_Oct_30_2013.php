<?php
$output = ''; $cond = '';
$from=strtotime($s);
$to=strtotime("23:59:59 $e");
if( $batch_type == "ready") 
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

if($menuid!=0) {
     $cond .= ' and d.menuid='.$menuid; 
 }
if($brandid!=0) {
     $cond .= ' and d.brandid='.$brandid;
 }
if($terrid!=0) {
     $cond .= ' and f.territory_id='.$terrid;
 }
 if($townid!=0) {
     $cond .= ' and f.town_id='.$townid;
 }
 if($franchiseid!=0) {
     $cond .= ' and f.franchise_id='.$franchiseid;
 }
 
//echo "<br>".$s."++".$e."++".$batch_type."++".$cond;
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
                    
        
    $output .= '<div class="trans_pagination pagi_top">'.$trans_pagination.' </div>
        <table class="datagrid" width="100%">
        <thead>
            <tr>
                <th>Slno</th>
                <th>Ordered On</th>
                <th>Transaction Id</th>
                <th>Reservation Status</th>
                <th>Item details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';
        $slno = $pg;
        foreach($transactions as $i=>$trans_arr) 
        {  
            $slno+=1;
            $ord_stat_txt = '';$action_str = '';
            
            
            

            if($trans_arr['batch_enabled'] == 0 ) {
                    $batch_status_msg='<div class="batch_msg_disabled clear" class="batch_status_msg_'.$trans_arr['transid'].'" onclick="batch_enable_disable(\''.$trans_arr['transid'].'\',1)">Enable for batch</div>';
                    $ord_stat_txt = 'disabled';
                    $b_status = '--'.$trans_arr['batch_status'];
                    $action_str =$batch_status_msg;
            } 
            else {
                $batch_status_msg='<div class="batch_msg_enabled clear" class="batch_status_msg_'.$trans_arr['transid'].'" onclick="batch_enable_disable(\''.$trans_arr['transid'].'\',0)">Disable for batch</div>';
                
                

                if($trans_arr['status'] == 0) { // partial ready
                        $ord_stat_txt = 'pending';
                        $msg="Ready";
                }
                else if($trans_arr['status'] == 1) { //ready
                        $ord_stat_txt = 'processed';
                        $msg='';
                }
                /*else if($trans_arr['status'] == 2) { //ready
                        $ord_stat_txt = 'shipped';
                }
                else if($trans_arr['status'] == 3) { //Not ready
                        $ord_stat_txt = 'cancelled';
                        $action_str = '<a href="">Enable</a>';
                }
                 */
                 
                /*$proform_list = $this->db->query("select sb.status,sb.batch_id,sb.num_orders from shipment_batch_process_invoice_link sbp
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
                            $action_str ='<a href="batch/'.$p_list['batch_id'].'"> Create invoice</a>'; 
                        }
                        elseif($p_list['status'] == 3 and  $ord_stat_txt == 'pending') {
                            $action_str ='<a href="batch/'.$p_list['batch_id'].'"> Enable for batch process</a>'; 
                        }
                        elseif($p_list['status'] == 1) {
                            $action_str ='<a href="batch/'.$p_list['batch_id'].'">Re-Process for packing</a>'; 
                        }
                        elseif($p_list['status'] == 2 and $ord_stat_txt == 'processed') {
                            $action_str ='<a href="batch/'.$p_list['batch_id'].'" target="_blank">View</a>'; 
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
                } */
                
            }

        

            $output .= '<tr class="'.$ord_stat_txt.'_ord">
                <td style="width:15px">'.$slno.'</td>
                <td style="width:180px">'.$trans_arr['str_time'].'</td>
                <td style="width:100px"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</a><br>'.$batch_status_msg.'</td>
                <td style="width:100px">'.ucfirst($ord_stat_txt).'</td>
                <td style="padding:0px !important;">';
                    $is_cancelled = ($trans_arr['status']==3)?1:0;
                    /*$trans_orders = $this->db->query("SELECT 
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
                                                group by o.id order by o.actiontime DESC",$trans_arr['transid'])->result_array();*/
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
            
                        $trans_orders = $this->db->query("select e.invoice_no,sd.packed,sd.shipped,e.invoice_status,sd.shipped_on,a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount 
                                                                        from king_orders a
                                                                        join king_dealitems b on a.itemid = b.id
                                                                        join king_deals dl on dl.dealid = b.dealid
                                                                        join king_transactions t on t.transid = a.transid   
                                                                        left join proforma_invoices c on c.order_id = a.id 
                                                                        left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
                                                                        left join king_invoice e on e.invoice_no = sd.invoice_no
                                                                where a.transid = ?
                                                                    $order_cond order by c.p_invoice_no desc",$trans_arr['transid'])->result_array();
                        $trans_ttl_shipped = 0;
                        $trans_ttl_cancelled = 0;
                        $processed_oids = array();
                        
                        foreach($trans_orders as $j=>$order_i) 
                        { $j+=1;
                        
                                                    //=================
                                                        if(!isset($processed_oids[$order_i['id']]))
                                                                $processed_oids[$order_i['id']] = 1;
                                                        else
                                                                continue;

                                                        $ord_status_color = '';
                                                        $is_shipped = 0;
                                                        $is_cancelled = ($order_i['status']==3)?1:0;
                                                        if($is_cancelled)
                                                        {
                                                                $trans_ttl_cancelled += 1;
                                                                $ord_status_color = 'cancelled_ord';
                                                        }else
                                                        {
                                                                
                                                                $ship_dets = array(); 
                                                                $invoice_block='';
                                                                $is_shipped = ($order_i['shipped'])?1:0;;
                                                                if($order_i['shipped'] && $order_i['invoice_status'])
                                                                {
                                                                        $trans_ttl_shipped += 1;
                                                                        $ship_dets[$order_i['invoice_no']] = format_date($order_i['shipped_on']);//format_date(date('Y-m-d',$order_i['shipped_on']));
                                                                        
                                                                        $ord_status_color = 'shipped_ord';
                                                                }else if($order_i['status'] == 0)
                                                                {
                                                                        $ord_status_color = 'pending_ord';
                                                                }
                                                                $invoice_block='<div style="font-size:10px;color:green;">';
                                                                foreach($ship_dets as $s_invno =>$s_shipdate)
                                                                {
                                                                        $status_mrp= $this->db->query("select round(sum(nlc*quantity)) as amt from king_invoice a join king_orders b on a.order_id = b.id where a.invoice_no = '".$s_invno."' ")->row()->amt;

                                                                        $invoice_block.='<div style="margin:3px 0;"><a target="_blank" href="'.site_url('admin/invoice/'.$s_invno).'">'.$s_invno.'-'.$s_shipdate.'</a> - Rs.'.$status_mrp.' </div>';
                                                                }
                                                                $invoice_block.='</div>';
                                                        }
                                                        $is_shipped = ($is_shipped && $order_i['invoice_status']) ?'Yes':'No';
                                                        $amount=round($order_i['i_orgprice']-($order_i['i_coup_discount']+$order_i['i_discount']),2);
                                                       
                                                        

                                                        /*$resonse.='<tr class="'.$ord_status_color.'">
                                                                <td width="40">'.$order_i['id'].'</td>
                                                                <td>'.anchor('admin/pnh_deal/'.$order_i['itemid'],$order_i['name'],'  target="_blank" ').'</td>
                                                                <td width="20">'.$order_i['quantity'].'</td>
                                                                <td width="40">'.$order_i['i_orgprice'].'</td>
                                                                <td width="40">'.$amount.'</td>
                                                                <td width="40" align="center">'.$is_shipped.'</td>
                                                                <td align="center">'.$invoice_block.'</td>
                                                            </tr>';*/

                                                    
                                                    //==============
                        
                                $output .= '<tr class="'.$ord_stat_txt.'_ord">
                                    <td style="width:25px">'.$j.'</td>
                                    <td style="width:50px">'.$order_i['orderid'].'</td>
                                    <td style="width:50px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['id'].'</a></td>
                                    <td style="width:200px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['name'].'</a></td>
                                    <td style="width:50px">'.$order_i['quantity'].'</td>
                                    <td style="width:50px">'.$order_i['i_orgprice'].'</td>
                                    <td style="width:50px">'.$amount.'</td>
                                    <td width="40" align="center">'.$is_shipped.'</td>
                                    <td width="80" align="center">'.$invoice_block.'</td>
                                </tr>';

                        }

                        $output .= '</table>
                    </td>
                    <td>'.$action_str.'</td>
                </tr>';
            }
            $output .= '</tbody></table><div class="trans_pagination">'.$trans_pagination.' </div>';

    $endlimit=($pg+1*$limit);
    $endlimit=($endlimit>$total_trans_rows)?$total_trans_rows : $endlimit;

    $output.='<script>$(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' to '.$endlimit.'</strong> of <strong>'.$total_trans_rows.'</strong> transactions");</script>
        <script>$(".log_display").html("Orders from '.$s.' to '.$e.'");</script>';

}
echo $output;
    