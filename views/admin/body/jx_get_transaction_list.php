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
     $cond .= ' and o.menuid='.$menuid; 
 }
if($brandid!=0) {
     $cond .= ' and o.brandid='.$brandid;
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
$sql="select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.franchise_id,tr.batch_enabled,tr.franchise_id
		,o.*
		from king_transactions tr
		left join king_orders o on o.transid=tr.transid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
            WHERE tr.actiontime between $from and $to and o.status in (0,1) and batch_enabled=1 $cond
            group by tr.transid order by tr.actiontime desc ";

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
                <th>Item details</th>
                <th>Reservation Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';
        $slno = $pg;
        foreach($transactions as $i=>$trans_arr) 
        {  
            $slno+=1;
            $ord_stat_txt = '';$action_str = '';
            
            $output .= '<tr class="'.$ord_stat_txt.'_ord">
                <td style="width:15px">'.$slno.'</td>
                <td style="width:180px">'.$trans_arr['str_time'].'</td>
                <td style="width:100px"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</a></td>
                <td style="padding:0px !important;">';
                    $is_cancelled = ($trans_arr['status']==3)?1:0;
                   

            
            
            $output .='<table class="subdatagrid" cellpadding="0" cellspacing="0">
                        <tr>
                            <th>Slno</th>
                            <th>Order id</th>
                            <th>Deal Name</th>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Shipped</th>
                            
                        </tr>';
            
                        $trans_orders = $this->db->query("select e.invoice_no,sd.packed,sd.shipped,e.invoice_status,sd.batch_id,sd.shipped_on,a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount,c.p_invoice_no 
                                                                        from king_orders a
                                                                        join king_dealitems b on a.itemid = b.id
                                                                        join king_deals dl on dl.dealid = b.dealid
                                                                        join king_transactions t on t.transid = a.transid   
                                                                        left join proforma_invoices c on c.order_id = a.id 
                                                                        left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
                                                                        left join king_invoice e on e.invoice_no = sd.invoice_no
                                                                where a.transid = ? and a.status in (0,1)
                                                                order by c.p_invoice_no desc",$trans_arr['transid'])->result_array();
                        $trans_ttl_shipped = 0;
                        $trans_ttl_cancelled = 0;
                        $processed_oids = array();
                        $batch_ids = array();
                        $invoice_infos=array();
                        $trans_pending=0; $j=0;
                        foreach($trans_orders as $j=>$order_i) 
                        { 
                        
                                                $output .='<input type="hidden" size="2" class="'.$trans_arr['transid'].'_total_orders" value="'.count($trans_orders).'" />';
                                        
                                                    //=================
                                                        if(!isset($processed_oids[$order_i['id']]))
                                                                $processed_oids[$order_i['id']] = 1;
                                                        else
                                                                continue;
                                                        $j+=1;
                                                        
                                                        if($order_i['status'] == 0) {
                                                            $trans_pending += 1;
                                                            
                                                        }
                                                        
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
                                                       
                                                        
                                                        
                                                    
                                                    //==============
                        
                                $output .= '<tr class="'.$ord_stat_txt.'_ord">
                                    <td style="width:25px">'.$j.'</td>
                                    <td style="width:50px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['id'].'</a></td>
                                    <td style="width:200px"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['name'].'</a></td>
                                    <td style="width:50px">'.$order_i['quantity'].'</td>
                                    <td style="width:50px">'.$order_i['i_orgprice'].'</td>
                                    <td style="width:50px">'.$amount.'</td>
                                    <td style="width:50px">'.$order_i['status'].'</td>
                                    <td width="40" align="center">'.$is_shipped.'</td>
                                    <td width="80" align="center">'.$invoice_block.'</td>
                                </tr>';
                                if($order_i['batch_id']!=null) {
                                    $batch_ids[]=$order_i['batch_id'];
                                }
                                
                                $invoice_infos[]=$order_i['p_invoice_no'];
                                
                                
                        }
                        
                        $batch_ids = array_unique($batch_ids);
                        $invoice_infos = array_unique($invoice_infos);
                        
                       // print_r($invoice_info);// die();
                        $trans_action_msg='';
                        if($trans_pending == 0) {
                            $order_status_msg = 'Ready';
                            $trans_action_msg = '<div><a class="batch_msg_disabled clear" href="batch/'.$trans_orders[0]['batch_id'].'" target="_blank">Process for Shipping</a></div>';
                        }
                        elseif($trans_pending == count($trans_orders)) {
                            $order_status_msg = 'Not Ready';
                            $trans_action_msg = '<a href="javascript:void(0);" class="retry_link" onclick="return reserve_stock_batch(\''.trim($trans_arr['transid']).'\','.$pg.');">Re-Allot</a>';
                        }
                        elseif($trans_pending < count($trans_orders)) {
                            $order_status_msg = '<span style="color:#cd0000;font-weight:bold; padding:4px 6px; ">'.ucfirst('Partial Ready').'</span>';
                            foreach($batch_ids as $batch_id) {
                                $trans_action_msg .= '<div><a class="proceed_link clear" href="batch/'.$batch_id.'" target="_blank">Process for packing</a> </div>';
                            }
                            foreach ($invoice_infos as $invoice_info ) {
                                if($invoice_info != '')
                                    $trans_action_msg .= '<a class="danger_link clear" onclick="cancel_proforma_invoice(\''.$invoice_info.'\','.$pg.')" class="">Cancel Proforma Invoice</a>';
                            }
                            
                        }
                        
                        
                        $output .= '</table>
                    </td>
                    <td style="width:100px">'.($order_status_msg).'-'.$trans_arr['total_ords'].'</td>
                    <td>'.$trans_action_msg.'</td>
                </tr>';
            }
            $output .= '</tbody></table><div class="trans_pagination">'.$trans_pagination.' </div>';

    $endlimit=($pg+1*$limit);
    $endlimit=($endlimit>$total_trans_rows)?$total_trans_rows : $endlimit;

    $output.='<script>$(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' to '.$endlimit.'</strong> of <strong>'.$total_trans_rows.'</strong> transactions");</script>
        <script>$(".log_display").html("Orders from '.$s.' to '.$e.'");</script>';

}
echo $output;
    