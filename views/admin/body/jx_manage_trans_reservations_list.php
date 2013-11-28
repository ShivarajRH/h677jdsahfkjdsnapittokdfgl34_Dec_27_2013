<?php
$output = $cond = $cond_batch = $inner_loop_cond = $re_allot_all_block=$orderby_cond='';
$from=strtotime($s);
$to=strtotime("23:59:59 $e");

$chk_box_global = '<input type="checkbox" value="" name="pick_all" id="pick_all" title="Select all transactions" />';

$generate_btn_link = '<div class="show_by_group_block"><label for="show_by_group">Process by franchise:</label> <input type="checkbox" value="by_group" name="show_by_group" id="show_by_group" '.($showbygrp?"checked":"").' title="Click to Show By Group"/></div>';
$generate_btn_link .= '<input type="submit" value="Create Group Batch" name="btn_cteate_group_batch" id="btn_cteate_group_batch" title="Click to Create Group Batch"/>';
$generate_btn_link .= '<input type="submit" value="Generate Pick List" name="btn_generate_pick_list" id="btn_generate_pick_list" title="Click to generate picklist for printing"/>';

$orderby_cond .='g.init';

if( $batch_type == "ready" or $batch_type == "partial") {
        if($batch_group_type == 1) {
            //$cond_batch .= ' g.batch_id <> 5000 '; $orderby_cond = 'g.batch_id';
        }
        elseif($batch_group_type == 2) {
           // $cond_batch .= ' g.batch_id = 5000 ';
        }
        else
           // $cond_batch .= ' g.batch_id > 0 ';
        
        // is batch assigned to user?
        if($user['userid']) {
            //$cond .= ' and sd.assigned_userid = '.$user['userid'].' ';
        }
}
elseif( $batch_type == "pending")
        $chk_box_global = $generate_btn_link='';

$cond_batch = ($cond_batch!='')? " where ".$cond_batch:"";


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
//if(!isset($arr_trans_set)) {
    $arr_trans_set = $this->reservations->get_trans_list($batch_type,$from,$to,0,0,$pg,$limit); //$user['userid']
    $total_trans_rows=count($arr_trans_set['result']);
//}


if($total_trans_rows == 0 ) {
    $output.='<script>$(".ttl_trans_listed").html("");
                                $(".pagination_top").html("");
                                $(".re_allot_all_block").css({"padding":"0"});
                    </script>
                    <h3 class="heading_no_results">No transactions found for selected criteria.</h3>';
}
else 
{
        
                        foreach ($arr_trans_set['result'] as $i=>$arr_trans) { $all_trans[$i] = "'".$arr_trans['transid']."'";  }
                         $str_all_trans = implode(",",$all_trans);


            
            

            $transid = $trans_array['transid'];
            $sql="select * from (
            select distinct from_unixtime(tr.init,'%D %M %Y') as str_date,from_unixtime(tr.init,'%h:%i:%s %p') as str_time, count(tr.transid) as total_trans,tr.transid
                    ,sum(o.status) as is_pending,o.status,o.shipped,o.id,o.itemid,o.brandid,o.quantity,o.time,o.bill_person,o.ship_phone,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
                    ,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
                    ,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
                    ,ter.territory_name
                    ,twn.town_name
                    ,dl.menuid,m.name as menu_name,bs.name as brand_name
                    ,sd.batch_id,sd.assigned_userid
            from king_transactions tr
                    join king_orders o on o.transid=tr.transid
                    join king_dealitems di on di.id=o.itemid
                    join king_deals dl on dl.dealid=di.dealid
                    join pnh_menu m on m.id = dl.menuid
                    join king_brands bs on bs.id = o.brandid
            join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id and f.is_suspended = 0
            join pnh_m_territory_info ter on ter.id = f.territory_id 
            join pnh_towns twn on twn.id=f.town_id
                    left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                    left join proforma_invoices pi on pi.order_id = o.id and pi.invoice_status = 1 
                    left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = pi.p_invoice_no
            WHERE tr.actiontime between $from and $to and o.status in (0,1) and tr.batch_enabled=1 and i.id is null $cond  and tr.transid in ($str_all_trans)
            group by o.transid) as g $cond_batch group by transid order by {$orderby_cond} desc limit $pg,$limit";

             //echo "<p><pre>".$sql.'</pre></p>';die(); 

             $transactions_src=$this->db->query($sql)->result_array();
             //$trans_arr=$this->db->query($sql);
             
                /*          
             if($transactions_src->num_rows() == 0) {
                 
                 $output .= '.';
                 
             }
             else
             {*/
             
              $output .= '
            <form name="p_invoice_for_picklist" id="p_invoice_for_picklist">
            <table class="datagrid" width="100%">
            <thead>
                <tr>
                    <th style="width:15px">Slno</th>
                    <th style="width:120px">Ordered On</th>
                    <th style="width:120px">Batch ID(Group)</th>
                    <th style="width:200px">Transaction Reference</th>
                    <th style="padding:0px !important;">Item details</th>
                    <th align="center">Pick List<br> '.$chk_box_global.'</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>';
            
              $c = $pg;
             foreach($transactions_src as $trans_arr) {
                 
                    $batch_ids = array();
                    $invoice_infos=array();
                    $trans_action_msg = '';
                    $batch_id_msg='--';
                    $pick_list_msg='';$trans_action_msg='';

           
                    $trans_created_by = @$this->db->query("select username from king_admin a join king_transactions b on a.id = b.trans_created_by where transid = ? ",$trans_arr['transid'])->row()->username;
                    if($trans_created_by) 
                            $trans_created_by = '<div class="trans_created_by"> by '.($trans_created_by).'';

                    $arr_fran = $this->reservations->fran_experience_info($f['f_created_on']);

                    if($trans_arr['batch_id'] != '') $batch_id_msg = '<a href="'.site_url("admin/batch/".$trans_arr['batch_id']).'" target="_blank">B'.$trans_arr['batch_id'].'</a>';

                    $output .= '<tr class="'.$batch_type.'_ord">
                        <td>'.++$c.'</td>
                        <td>'.$trans_arr['str_date'].'<div class="str_time">'.($trans_arr['str_time']).'</div>'.$trans_created_by.'</td>
                        <td>'.$batch_id_msg.'</td>
                        <td>
                            <span class="info_links"><a href="trans/'.$trans_arr['transid'].'" target="_blank">'.$trans_arr['transid'].'</span><br></a>
                            <span class="info_links"><a href="'.site_url("admin/pnh_franchise/{$trans_arr['franchise_id']}").'"  target="_blank">'.$trans_arr['bill_person'].'</a><br></span>
                            <span class="info_links">'.$trans_arr['town_name'].'</span>,
                            <span class="info_links">'.$trans_arr['territory_name'].'<br></span>
                            <span>'.$trans_arr['ship_phone'].'<br></span><span class="fran_experience" style="background-color:'.$arr_fran['f_color'].';color: #ffffff;">'.$arr_fran['f_level'].'</span>
                        </td>
                        <td>';
            
            
                    $output .='<table class="subdatagrid" cellpadding="0" cellspacing="0">
                        <tr>
                            <th>Slno</th>
                            <th>Order id</th>
                            <th>Deal Name</th>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Amount</th>
                            <th>Alloted/Status?</th>
                        </tr>';
            
                    
                    $trans_orders = $this->reservations->get_orders_of_trans($trans_arr['transid']);
                
                
                    foreach($trans_orders as $j=>$order_i) 
                    {

                        $output .='<input type="hidden" size="2" class="'.$trans_arr['transid'].'_total_orders" value="'.count($trans_orders).'" />';

                        $status=array("Confirmed","Processed","Shipped","Cancelled","Returned");
                                    $amount=round($order_i['i_orgprice']-($order_i['i_coup_discount']+$order_i['i_discount']),2);
                                    $output .= '<tr class="'.$ord_stat_txt.'_ord">
                                        <td style="width:25px">'.++$k.'</td>
                                        <td style="width:50px"><span class="info_links"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['id'].'</a></span></td>
                                        <td style="width:200px"><span class="info_links"><a href="pnh_deal/'.$order_i['itemid'].'" target="_blank">'.$order_i['name'].'</a></span></td>
                                        <td style="width:50px">'.$order_i['quantity'].'</td>
                                        <td style="width:50px">'.$order_i['i_orgprice'].'</td>
                                        <td style="width:50px">'.$amount.'</td>
                                        <td style="width:20px">'.($order_i['status']?"Yes":"No").' | '.$status[$trans_arr['shipped']].' </td>
                                    </tr>';
                    }
                
                    $output .= '</table></td>';
                
                
                    if( $batch_type == "ready") 
                    {
                        $arr_pinv_ids =array();
                        foreach ($arr_trans_set['result'] as $i=>$arr_trans) { 
                            if($trans_arr['transid'] == $arr_trans['transid']) {
                                $arr_pinv_ids[] = $arr_trans['p_inv_nos'];

                            }
                        }

                        foreach ($arr_pinv_ids as $p_invoice_id ) {
                            if($p_invoice_id != '') {
                                $trans_action_msg .= '<div>
                                                    <a class="proceed_link clear" href="pack_invoice/'.$p_invoice_id.'" target="_blank">Generate invoice</a><br>
                                                    <a class="danger_link clear" href="javascript:void(0)" onclick="cancel_proforma_invoice(\''.$p_invoice_id.'\','.$user['userid'].','.$pg.')" class="">De-Allot</a>
                                                </div>';
                                $pick_list_msg .= '<input type="checkbox" value="'.$p_invoice_id.'" id="pick_list_trans" name="pick_list_trans[]" class="pick_list_trans_ready" title="Select this for picklist" />';
                            }
                        }
                    }
                    elseif( $batch_type == "partial") 
                    {

                            $arr_pinv_ids =array();
                            foreach ($arr_trans_set['result'] as $i=>$arr_trans) { 
                                if($trans_arr['transid'] == $arr_trans['transid']) {
                                    $arr_pinv_ids[] = $arr_trans['p_inv_nos'];

                                }
                            }


                            foreach ($arr_pinv_ids as $p_invoice_id ) {
                                if($p_invoice_id != '') {
                                    $trans_action_msg .= '<div>
                                                        <a class="proceed_link clear" href="pack_invoice/'.$p_invoice_id.'" target="_blank">Generate invoice</a><br>
                                                        <a class="danger_link clear" href="javascript:void(0)" onclick="cancel_proforma_invoice(\''.$p_invoice_id.'\','.$user['userid'].','.$pg.')" class="">De-Allot</a>
                                                    </div>';
                                    $pick_list_msg .= '<input type="checkbox" value="'.$p_invoice_id.'" id="pick_list_trans" name="pick_list_trans[]" class="pick_list_trans_ready" title="Select this for picklist" />';
                                }
                            }

                    }
                    elseif( $batch_type == "pending") 
                    {
                            $trans_action_msg .= '<a href="javascript:void(0);" class="retry_link" onclick="return reserve_stock_for_trans('.$user['userid'].',\''.trim($trans_arr['transid']).'\','.$pg.');">Re-Allot</a>';
                            $pick_list_msg .= '--';

                            $re_allot_all_block = '<a href="javascript:void(0);" onclick="reallot_stock_for_all_transaction('.$user['userid'].','.$pg.');">Re-Allot all pending transactions</a>';
                            $output .= '<script>$(".re_allot_all_block").css({"padding":"4px 10px"});</script>';


                    }

                    $output .= '<td style="width:70px">'.$pick_list_msg.'</td>';
                    $output .= '<td width="200">'.$trans_action_msg.'</td>
                    </tr>';

                    $fil_territorylist[$trans_arr['territory_id']] = $trans_arr['territory_name'];
                    $fil_townlist[$trans_arr['town_id']] = $trans_arr['town_name'];
                    $fil_menulist[$trans_arr['menuid']] = $trans_arr['menu_name'];
                    $fil_brandlist[$trans_arr['brandid']] = $trans_arr['brand_name'];
                    $fil_franchiselist[$trans_arr['franchise_id']] = $trans_arr['franchise_name'];
            
        }
    }
        
    //   PAGINATION
                $this->load->library('pagination');
                $config['base_url'] = site_url("admin/jx_manage_trans_reservations_list/".$batch_type.'/'.$s.'/'.$e.'/'.$terrid.'/'.$townid.'/'.$franchiseid.'/'.$menuid.'/'.$brandid."/".$showbygrp."/".$batch_group_type."/".$limit); 
                $config['total_rows'] = $total_trans_rows;
                $config['per_page'] = $limit;
                $config['uri_segment'] = 14; 
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
            
    $output .= '</tbody>
        </table>
    </form>
    <div class="trans_pagination">'.$trans_pagination.' </div>
        <script>
            $(".pagination_top").html(\''.($trans_pagination).'\');
            $(".ttl_trans_listed").html("Showing <strong>'.($pg+1).' - '.$endlimit.'</strong> / <strong>'.$total_trans_rows.'</strong> transactions from <strong>'.date("m-d-Y",$from).'</strong> to <strong>'.date("m-d-Y",$to).'</strong>");
            $(".btn_picklist_block").html(\''.($generate_btn_link).'\');

            $(".re_allot_all_block").html(\''.($re_allot_all_block).'\');
        </script>';
 //}
        
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
    $resonse2.='<script>$("#sel_menu").html(\''.$menulist.'\');</script>';
}
if(count($fil_brandlist) && $brandid==0) {
    asort($fil_brandlist);
    $brandlist = '<option value="00">All Brands</option>';
    foreach($fil_brandlist as $fbrandid=>$fbrand_name) {
        $brandlist .= '<option value="'.$fbrandid.'">'.$fbrand_name.'</option>';   
    }
    $resonse2.='<script>$("#sel_brands").html(\''.$brandlist.'\');</script>';
}
if(count($fil_territorylist) && $terrid==0) {
    asort($fil_territorylist);
    $territory_list = '<option value="00">All Territory</option>';
    foreach($fil_territorylist as $fterrid=>$fterritory_name) {
        $territory_list .= '<option value="'.$fterrid.'">'.$fterritory_name.'</option>';   
    }
    $resonse2.='<script>$("#sel_territory").html(\''.$territory_list.'\');</script>';
}
if(count($fil_townlist) && $townid==0) {
    asort($fil_townlist);
    $town_list = '<option value="00">All Towns</option>';
    foreach($fil_townlist as $ftownid=>$ftown_name) {
        $town_list .= '<option value="'.$ftownid.'">'.$ftown_name.'</option>';   
    }
    $resonse2.='<script>$("#sel_town").html(\''.$town_list.'\');</script>';
}
if(count($fil_franchiselist) && $franchiseid==0) {
    asort($fil_franchiselist);
    $franchise_list = '<option value="00">All Franchise</option>';
    foreach($fil_franchiselist as $franchiseid=>$franchise_name) {
        $franchise_list .= '<option value="'.$franchiseid.'">'.$franchise_name.'</option>';   
    }
    $resonse2.='<script>$("#sel_franchise").html(\''.$franchise_list.'\');</script>';
}
echo $resonse2;