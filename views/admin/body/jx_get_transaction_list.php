<?php
$output = $cond = $cond_2 = '';
$from=strtotime($s);
$to=strtotime("23:59:59 $e");
if( $batch_type == "ready") 
{
    $pg_trans_description='Following transactions are ready for shipping.';
    $cond_2 = ' g.is_pending = g.total_trans ';
}
if( $batch_type == "partial") 
{
    $pg_trans_description='Following transactions are partial ready.';
    $cond_2 = ' g.`is_pending` < g.`total_trans` and g.`is_pending` <> 0 ';
}
if( $batch_type == "pending") 
{
    $pg_trans_description='Following transactions are not ready or pending.';
    $cond_2 = ' g.`is_pending` = 0 ';
}

if($menuid!=0) {
     $cond .= ' and dl.menuid='.$menuid; 
 }
if($brandid!=0) {
     $cond .= ' and dl.brandid='.$brandid;
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
//echo $batch_type."<br>".$cond_2."<br>".$cond; die();
$sql="select * from (
select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_trans,tr.transid
		,sum(o.status) as is_pending,o.status,o.id,o.itemid,o.brandid,o.quantity,o.time,o.i_orgprice,o.bill_person,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
		,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                
		from king_transactions tr
		left join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
        
		left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                  left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 
        
            WHERE tr.actiontime between $from and $to and o.status in (0,1) and tr.batch_enabled=1 and i.id is null $cond
            group by o.transid order by tr.actiontime desc) as g where $cond_2 group by transid";
// and i.invoice_status=1 and sd.shipped=1 
//echo "<p><pre>".$sql.'</pre></p>';die(); 
$transactions_src=$this->db->query($sql);

if(!$transactions_src->num_rows()) 
{
    $output.='<script>$(".ttl_trans_listed").html("");
                        $(".log_display").html("");
                        $(".pagination_top").html("");
            </script>';
    $output .= '<div><h3 style="margin:2px;" align="center">No transactions found for selected criteria.</h3>';
    
}
else 
{
    $total_trans_rows=$transactions_src->num_rows();
    $transactions=$this->db->query($sql." limit $pg,$limit")->result_array();

//   PAGINATION
            $this->load->library('pagination');
            $config['base_url'] = site_url("admin/jx_get_transaction_list/".$batch_type.'/'.$s.'/'.$e.'/'.$terrid.'/'.$townid.'/'.$franchiseid.'/'.$menuid.'/'.$brandid."/".$limit); 
            $config['total_rows'] = $total_trans_rows;
            $config['per_page'] = $limit;
            $config['uri_segment'] = 12; 
            $config['num_links'] = 5;
            $config['cur_tag_open'] = '<span class="curr_pg_link">';
            $config['cur_tag_close'] = '</span>';
            $this->config->set_item('enable_query_strings',false); 
            $this->pagination->initialize($config); 
            $trans_pagination = $this->pagination->create_links();
            $this->config->set_item('enable_query_strings',TRUE);
//   PAGINATION ENDS
    $endlimit=($pg+1*$limit);
    $endlimit=($endlimit>$total_trans_rows)?$total_trans_rows : $endlimit;

    $output .= '<table class="datagrid" width="100%">
        <thead>
            <tr>
                <th>Slno</th>
                <th>Ordered On</th>
                <th>Transaction Id</th>
                <th>Item details</th>
                <th><input type="checkbox" value="chk_all"/></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';
        $slno = $pg;
        foreach($transactions as $i=>$trans_arr) 
        {
            // chk if trans is pending or part 
            
            //if($this->erpm->is_transaction_invoiced($trans_arr['transid'])) 
            //    continue;
            
            //$total_trans[$trans_arr['transid']] = true;
            $batch_ids = array();
            $invoice_infos=array();
            $trans_action_msg = '';
            
            $is_shipped = ($trans_arr['shipped'])?1:0; 
            $is_shipped = ($is_shipped && $trans_arr['invoice_status']) ?'Yes':'No';
            
            $slno+=1;
            $ord_stat_txt = '';$action_str = '';
            
            $arr_fran = $this->reservation_model->fran_experience_info($f['f_created_on']);
            
            $output .= '<tr class="'.$ord_stat_txt.'_ord">
                <td style="width:15px">'.$slno.'</td>
                <td style="width:180px">'.$trans_arr['str_time'].'</td>
                <td style="width:160px">
                    <span class="info_links"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</span><br></a>
                    <span class="info_links"><a href="'.site_url("admin/pnh_franchise/{$trans_arr['franchise_id']}").'"  target="_blank">'.$trans_arr['bill_person'].'</a><br></span>
                    <span class="info_links">'.$trans_arr['territory_name'].'<br></span>
                    <span class="info_links">'.$trans_arr['town_name'].'<br></span>
                    <span class="fran_experience" style="background-color:'.$arr_fran['f_color'].';color: #ffffff;">'.$arr_fran['f_level'].'</span>
                </td>
                <td style="padding:0px !important;">';
            $output .='<table class="subdatagrid" cellpadding="0" cellspacing="0">
                        <tr>
                            <th>Slno</th>
                            <th>Order id</th>
                            <th>Deal Name</th>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Amount</th>
                            <th>Alloted?|Shipped?</th>
                        </tr>';
                        $trans_orders = $this->db->query("select e.invoice_no,sd.packed,sd.shipped,e.invoice_status,sd.batch_id,sd.shipped_on,a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount,c.p_invoice_no 
                                                                        from king_orders a
                                                                        join king_dealitems b on a.itemid = b.id
                                                                        join king_deals dl on dl.dealid = b.dealid
                                                                        join king_transactions t on t.transid = a.transid   
                                                                        left join proforma_invoices c on c.order_id = a.id and c.invoice_status = 1 
                                                                        left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
                                                                        left join king_invoice e on e.invoice_no = sd.invoice_no
                                                                where a.transid = ? and a.status in (0,1)
                                                                order by c.p_invoice_no desc",$trans_arr['transid'])->result_array();
                        $trans_ttl_shipped = 0;
                        $trans_ttl_cancelled = 0;
                        $processed_oids = array();
                        
                        #$invoice_infos=array();
                        $trans_pending=0; 
                        $k=0;
                        foreach($trans_orders as $j=>$order_i) 
                        {
                                
                                                   $output .='<input type="hidden" size="2" class="'.$trans_arr['transid'].'_total_orders" value="'.count($trans_orders).'" />';
                                        
                                                    //=================
                                                        
                                                        $k+=1;
                                                        $ship_dets = array(); 
                                                        $invoice_block='';
                                                        $is_shipped = ($order_i['shipped'])?1:0;
                                                        if($order_i['shipped'] && $order_i['invoice_status'])
                                                        {
                                                                $trans_ttl_shipped += 1;
                                                                $ship_dets[$order_i['invoice_no']] = format_date($order_i['shipped_on']);//format_date(date('Y-m-d',$order_i['shipped_on']));
                                                        }
                                                        #else if($order_i['status'] == 0) {//$ord_status_color = 'pending_ord';}
                                                        $invoice_block='<div style="font-size:10px;color:green;">';
                                                        foreach($ship_dets as $s_invno =>$s_shipdate)
                                                        {
                                                                $status_mrp= $this->db->query("select round(sum(nlc*quantity)) as amt from king_invoice a join king_orders b on a.order_id = b.id where a.invoice_no = '".$s_invno."' ")->row()->amt;

                                                                $invoice_block.='<div style="margin:3px 0;"><a target="_blank" href="'.site_url('admin/invoice/'.$s_invno).'">'.$s_invno.'-'.$s_shipdate.'</a> - Rs.'.$status_mrp.' </div>';
                                                        }
                                                        $invoice_block.='</div>';
                                                      
                                                        $is_shipped = ($is_shipped && $order_i['invoice_status']) ?'Yes':'No';
                                                        $amount=round($order_i['i_orgprice']-($order_i['i_coup_discount']+$order_i['i_discount']),2);
                                                       
                                                        $invoice_infos[]=$order_i['p_invoice_no'];
                                                        
                                                    
                                $output .= '<tr class="'.$ord_stat_txt.'_ord">
                                    <td style="width:25px">'.$k.'</td>
                                    <td style="width:50px"><span class="info_links"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['id'].'</a></span></td>
                                    <td style="width:200px"><span class="info_links"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['name'].'</a></span></td>
                                    <td style="width:50px">'.$order_i['quantity'].'</td>
                                    <td style="width:50px">'.$order_i['i_orgprice'].'</td>
                                    <td style="width:50px">'.$amount.'</td>
                                    <td style="width:20px">'.($order_i['status']?"Yes":"No").'|('.$is_shipped.')</td>
                                </tr>';//
                                if($order_i['batch_id']!=null) {
                                    $batch_ids[]=$order_i['batch_id'];
                                }
                                if($order_i['status'] == 0) {$trans_pending += 1;}
                        }
                        
                        $batch_ids = array_unique($batch_ids);
                        $invoice_infos = array_unique($invoice_infos);
                        
                      // print_r($invoice_info);// die();
//                       $trans_action_msg='';
                        if($trans_pending == 0) {
                            $order_status_msg = '<span class="partial_ready">Ready</span>';
                            
                            
                            foreach ($invoice_infos as $invoice_info ) {
                                if($invoice_info != '') {
                                    $trans_action_msg .= '<div>
                                                        <a class="proceed_link clear" href="pack_invoice/'.$invoice_info.'" target="_blank">Generate invoice</a><br>
                                                        <a class="danger_link clear" href="javascript:void(0)" onclick="cancel_proforma_invoice(\''.$invoice_info.'\','.$pg.')" class="">De-Allot</a>
                                                    </div>
                                                                                                <!--<div><a class="batch_msg_disabled clear" href="batch/'.$trans_orders[0]['batch_id'].'" target="_blank">Process for Shipping</a></div>-->';
                                    
//                                    $trans_action_msg .= '<a class="danger_link clear" onclick="cancel_proforma_invoice(\''.$invoice_info.'\','.$pg.')" class="">Cancel Proforma Invoice</a>';
                                    
                                }
                            }
                            if( $is_shipped == "Yes") {
                                $order_status_msg = 'Done';
                                $trans_action_msg = '<div><a class="proceed_link clear" href="javascript:void(0)">Done</a></div>';
                            }
                        }
                        elseif($trans_pending == count($trans_orders)) {
                            $order_status_msg = '<span class="partial_ready">Not Ready</span>';
                            $trans_action_msg .= '<a href="javascript:void(0);" class="retry_link" onclick="return reserve_stock_for_trans('.$user['userid'].',\''.trim($trans_arr['transid']).'\','.$pg.');">Re-Allot</a>';
                        }
                        elseif($trans_pending < count($trans_orders)) {
                            $order_status_msg = '<span class="partial_ready">Partial Ready</span>';
                            
                                       foreach ($invoice_infos as $invoice_info ) {
                                            if($invoice_info != '') {
                                                $trans_action_msg .= '<div>
                                                                    <a class="proceed_link clear" href="pack_invoice/'.$invoice_info.'" target="_blank">Generate invoice</a><br>
                                                                    <a class="danger_link clear" href="javascript:void(0)" onclick="cancel_proforma_invoice(\''.$invoice_info.'\','.$pg.')" class="">De-Allot</a>
                                                                </div>';
                                            }
                                        }
                            
                        }
                        //'.($order_status_msg).'
                        $output .= '</table></td>
                            <td style="width:20px"><input type="checkbox" value="" name="" class="chk_'.$trans_arr['transid'].'" /></td>
                            <td width="200">'.$trans_action_msg.'</td>
                        </tr>';
                        
                $fil_territorylist[$trans_arr['territory_id']] = $trans_arr['territory_name'];
                $fil_townlist[$trans_arr['town_id']] = $trans_arr['town_name'];
                $fil_menulist[$trans_arr['menuid']] = $trans_arr['menu_name'];
                $fil_brandlist[$trans_arr['brandid']] = $trans_arr['brand_name'];
                $fil_franchiselist[$trans_arr['franchise_id']] = $trans_arr['franchise_name'];
            }
            $output .= '</tbody></table><div class="trans_pagination">'.$trans_pagination.' </div>';
            
            $output .= '<script>
                            $(".pagination_top").html(\''.($trans_pagination).'\');
                            $(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' - '.$endlimit.'</strong> / <strong>'.$total_trans_rows.'</strong> transactions from <strong>'.$s.'</strong> to <strong>'.$e.'</strong>");
                        </script>';
            
//          $output.='<script>//$(".log_display").html("Orders from ");$(".page_trans_description").html(\''.($pg_trans_description).'\');</script>';

}
echo $output;
//===========
#Other functions
$resonse2='';
if(count($fil_menulist) && $menuid==0) {
    asort($fil_menulist);
    $menulist = '<option value="00">All Menu</option>';
    foreach($fil_menulist as $fmenu_id=>$fmenu_name) {
        $menulist .= '<option value="'.$fmenu_id.'">'.$fmenu_name.'</option>';   
    }
}
if(count($fil_brandlist) && $brandid==0) {
    asort($fil_brandlist);
    $brandlist = '<option value="00">All Brands</option>';
    foreach($fil_brandlist as $fbrandid=>$fbrand_name) {
        $brandlist .= '<option value="'.$fbrandid.'">'.$fbrand_name.'</option>';   
    }
}
if(count($fil_territorylist) && $terrid==0) {
    asort($fil_territorylist);
    $territory_list = '<option value="00">All Territory</option>';
    foreach($fil_territorylist as $fterrid=>$fterritory_name) {
        $territory_list .= '<option value="'.$fterrid.'">'.$fterritory_name.'</option>';   
    }
}
if(count($fil_townlist) && $townid==0) {
    asort($fil_townlist);
    $town_list = '<option value="00">All Towns</option>';
    foreach($fil_townlist as $ftownid=>$ftown_name) {
        $town_list .= '<option value="'.$ftownid.'">'.$ftown_name.'</option>';   
    }   
}
if(count($fil_franchiselist) && $franchiseid==0) {
    asort($fil_franchiselist);
    $franchise_list = '<option value="00">All Franchise</option>';
    foreach($fil_franchiselist as $franchiseid=>$franchise_name) {
        $franchise_list .= '<option value="'.$franchiseid.'">'.$franchise_name.'</option>';   
    }
}
$resonse2.='<script>$("#sel_menu").html(\''.$menulist.'\');
                    $("#sel_brands").html(\''.$brandlist.'\');
                    $("#sel_territory").html(\''.$territory_list.'\');
                    $("#sel_town").html(\''.$town_list.'\');
                    $("#sel_franchise").html(\''.$franchise_list.'\');
            </script>';
echo $resonse2;