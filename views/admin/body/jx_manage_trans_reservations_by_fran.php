<?php
$output = $cond = $cond_batch = $inner_loop_cond = $re_allot_all_block='';
$from=strtotime($s);
$to=strtotime("23:59:59 $e");

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
 if(!isset($arr_trans_set)) {
        $arr_trans_set = $this->reservations->get_trans_list($batch_type,$from,$to);
        $total_trans_rows=count($arr_trans_set['result']);
 }
if($total_trans_rows == 0 ) {
    $output.='<script>$(".ttl_trans_listed").html("");
                                $(".pagination_top").html("");
                                $(".re_allot_all_block").css({"padding":"0"});
                    </script>
                    <h3 class="heading_no_results">No franchise found for selected criteria.</h3>';
}
else 
{

        //echo '<pre>'.$arr_trans_set['last_query'];
         foreach ($arr_trans_set['result'] as $i=>$arr_trans) { $all_trans[$i] = "'".$arr_trans['transid']."'";  }
         $str_all_trans = implode(",",$all_trans);

        //echo '<pre>'.$str_all_trans;die();
        
        $sql="select distinct o.transid,count(distinct tr.transid) as ttl_trans,
                        f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
                        ,ter.territory_name
                        ,twn.town_name
                from king_transactions tr
                        join king_orders o on o.transid=tr.transid
                        join king_dealitems di on di.id=o.itemid
                        join king_deals dl on dl.dealid=di.dealid
                        join pnh_menu m on m.id = dl.menuid
                        join king_brands bs on bs.id = o.brandid
                join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
                join pnh_m_territory_info ter on ter.id = f.territory_id 
                join pnh_towns twn on twn.id=f.town_id
                WHERE o.transid in ($str_all_trans) and o.status in (0,1) $cond
                group by f.franchise_id order by f.territory_id,f.town_id,f.franchise_id desc";
 
            
                $transactions_src=$this->db->query($sql);
            //echo "<p><pre>".$this->db->last_query().'</pre></p>';die(); 
            
        if(!$transactions_src->num_rows()) 
        {
            $output.='<script>$(".ttl_trans_listed").html("");
                                $(".pagination_top").html("");
                                $(".re_allot_all_block").css({"padding":"0"});
                    </script>
                    <h3 class="heading_no_results">No franchise found for selected criteria.</h3>';
        }
        else 
        {
            $total_trans_rows=$transactions_src->num_rows();
            $transactions=$this->db->query($sql." limit $pg,$limit")->result_array();

            $output .= '
            <select name="sel_terr_id" id="sel_terr_id"></select>    
            <table class="datagrid" width="100%">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th style="width:150px">Territory Name</th>
                                <th style="width:150px">Town Name</th>
                                <th style="width:160px">Franchise</th>
                                <th style="width:150px">Actions</th>
                                <th style="">Total Orders</th>
                            <tr>
                        </thead>
                        <tbody>';
 
        
    
            foreach($transactions as $i=>$trans_arr) 
            {

                $output .= '<tr class="filter_terr_'.$trans_arr['territory_id'].' filter_terr">
                                <td>'.++$i.'</td>
                                <td>'.$trans_arr['territory_name'].'</td>
                                <td>'.$trans_arr['town_name'].'</td>
                                <td>'.$trans_arr['franchise_name'].'</td>';
                

                        if( $batch_type == "ready") 
                        {
                                    $output .= '<td>';
                                    $arr_pinv_ids =array();
                                    foreach ($arr_trans_set['result'] as $arr_trans) { 
                                        if($trans_arr['franchise_id'] == $arr_trans['franchise_id']) {
                                            $arr_pinv_ids[] = $arr_trans['p_inv_nos'];

                                        }
                                    }
                                    $str_pinv_ids = implode(",", array_unique($arr_pinv_ids));
                                    $output .= '<a class="proceed_link clear" href="javascript:void(0)" onclick="process_pinvoices_by_fran(this,'.$trans_arr['franchise_id'].')" p_invoice_ids="'.$str_pinv_ids.'">Generate invoice</a>
                                          <form action="'.site_url('admin/pack_invoice_by_fran').'" method="post" id="pinvoices_form_'.$trans_arr['franchise_id'].'" target="_blank">
                                                <input type="hidden" value="'.$str_pinv_ids.'" name="p_invoice_ids"/>
                                                <input type="hidden" value="'.$trans_arr['franchise_id'].'" name="franchise_id"/>
                                           </form>';
                                    
                                    $output .= '<br><a class="proceed_link clear" href="javascript:void(0)" onclick="process_picklist_by_fran(this,'.$trans_arr['franchise_id'].')" p_invoice_ids="'.$str_pinv_ids.'">Generate Picklist</a>
                                          <form action="'.site_url("admin/p_invoice_for_picklist").'" method="post" id="picklist_by_fran_form_'.$trans_arr['franchise_id'].'" target="_blank">
                                                <input type="hidden" value="'.$str_pinv_ids.'" name="pick_list_trans"/>
                                                <input type="hidden" value="'.$trans_arr['franchise_id'].'" name="franchise_id"/>
                                           </form> ';

                                    $output .= '</td>
                                        <td>'.$trans_arr['ttl_trans'].'
                                                <div class="view_all_orders"><a href="javascript:void(0);" class="view_all_link" onclick="return show_orders_list('.$trans_arr['franchise_id'].',\''.$from.'\',\''.$to.'\',\''.$batch_type.'\')" >View Orders</a></div>
                                                <div class="orders_info_block_'.$trans_arr['franchise_id'].'" class="orders_info_block" style="display:none;"></div>
                                        </td>';



                        }
                        elseif( $batch_type == "partial")
                        {

                                    $output .= '<td>';
                                    $arr_pinv_ids =array();
                                    foreach ($arr_trans_set['result'] as $arr_trans) { 
                                        if($trans_arr['franchise_id'] == $arr_trans['franchise_id']) {
                                            $arr_pinv_ids[] = $arr_trans['p_inv_nos'];

                                        }
                                    }
                                    $str_pinv_ids = implode(",", array_unique($arr_pinv_ids));
                                    $output .= '<a class="proceed_link clear" href="javascript:void(0)" onclick="process_pinvoices_by_fran(this,'.$trans_arr['franchise_id'].')" p_invoice_ids="'.$str_pinv_ids.'">Generate invoice</a>
                                          <form action="'.site_url('admin/pack_invoice_by_fran').'" method="post" id="pinvoices_form_'.$trans_arr['franchise_id'].'" target="_blank">
                                                <input type="hidden" value="'.$str_pinv_ids.'" name="p_invoice_ids"/>
                                                <input type="hidden" value="'.$trans_arr['franchise_id'].'" name="franchise_id"/>
                                           </form> ';

                                    $output .= '</td>
                                        <td>'.$trans_arr['ttl_trans'].'
                                                <div class="view_all_orders"><a href="javascript:void(0);" class="view_all_link" onclick="return show_orders_list('.$trans_arr['franchise_id'].',\''.$from.'\',\''.$to.'\',\''.$batch_type.'\')" >View Orders</a></div>
                                                <div class="orders_info_block_'.$trans_arr['franchise_id'].'" class="orders_info_block" style="display:none;"></div>
                                        </td>';

                        }
                        elseif( $batch_type == "pending") 
                        {

                                        $output .= '<td><a href="javascript:void(0);" class="retry_link" onclick="return reserve_stock_for_trans('.$user['userid'].',\''.trim($trans_arr['transid']).'\','.$pg.');">Re-Allot</a></td>';
                                        $output .= '</td>
                                                 <td>'.$trans_arr['ttl_trans'].'
                                                         <div class="view_all_orders"><a href="javascript:void(0);" class="view_all_link" onclick="return show_orders_list('.$trans_arr['franchise_id'].',\''.$from.'\',\''.$to.'\',\''.$batch_type.'\')" >View Orders</a></div>
                                                         <div class="orders_info_block_'.$trans_arr['franchise_id'].'" class="orders_info_block" style="display:none;"></div>
                                                 </td>';

                        }
                $output .= '</tr>'; 
                
                $fil_territorylist[$trans_arr['territory_id']] = $trans_arr['territory_name'];
            }

            $output .='</tbody>
                        </table>';
            
           
    
    }
}

    echo ''.$output;
    
    
if(count($fil_territorylist) && $terrid==0) {
    asort($fil_territorylist);
    $territory_list = '<option value="00">All Territory</option>';
    foreach($fil_territorylist as $fterrid=>$fterritory_name) {
        $territory_list .= '<option value="'.$fterrid.'">'.$fterritory_name.'</option>';   
    }
    $resonse2.='<script>$("#sel_terr_id").html(\''.$territory_list.'\');</script>';
}
echo ''.$resonse2;
?>