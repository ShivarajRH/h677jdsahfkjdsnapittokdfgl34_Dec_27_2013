<?php
/**
 * Transaction/Orders Reservation functions
 * @contact Shivaraj@storeking.in
 */
include APPPATH.'/controllers/voucher.php';
class Reservation extends Voucher {
    function pack_invoice_by_fran() {
        $user=$this->auth(ORDER_BATCH_PROCESS_ROLE|OUTSCAN_ROLE|INVOICE_PRINT_ROLE);
        
        if(isset($_POST['pids'])) {
            $this->erpm->do_pack();
            die();
        }
        
        foreach(array("p_invoice_ids","franchise_id") as $i) 
            $$i=$this->input->post($i);
            //$result = $this->reservations->do_pack_invoice_by_fran();$data['invoice'] = $invoices = $this->reservations->get_packing_details($franchise_id,$p_invoice_ids);
            $data['invoice'] = $invoices = $this->erpm->getinvoiceforpacking($p_invoice_ids);

        //$data['batch']=$this->erpm->getbatch($bid);$data['invoices']=$this->erpm->getbatchinvoices($bid);$data['bid']=$bid;
        $data['page']="pack_invoice";
        $this->load->view("admin",$data);
    }
    
    function process_batch_by_fran($bid)
    {
        
        $user=$this->auth(ORDER_BATCH_PROCESS_ROLE|OUTSCAN_ROLE|INVOICE_PRINT_ROLE);
        $data['batch']=$this->erpm->getbatch($bid);
        $data['invoices']=$this->erpm->getbatchinvoices($bid);
        $data['bid']=$bid;
        $data['page']="process_batch_by_fran";
        $this->load->view("admin",$data);
    }
    
    function jx_get_franchise_orders($franchise_id,$from=0,$to=0,$batch_type) {
        $output = $picklist_btn_msg= '';
        $user=$this->auth(ORDER_BATCH_PROCESS_ROLE|OUTSCAN_ROLE|INVOICE_PRINT_ROLE);
        
         if($from!=0 and $to != 0) {
            $cond .= '  and tr.actiontime between '.$from.' and '.$to.' ';
         }else {
             $from=$to=0;
         }
        $arr_trans_set = $this->reservations->get_trans_list($batch_type,$from,$to,$franchise_id);
       
        foreach ($arr_trans_set['result'] as $i=>$arr_trans) { $all_trans[$i] = "'".$arr_trans['transid']."'";  }
        $str_all_trans = implode(",",$all_trans);
        
        $rslt = $this->db->query("select DISTINCT o.*,tr.transid,tr.amount,tr.paid,tr.init,tr.is_pnh,tr.franchise_id,di.name
                                ,o.status,pi.p_invoice_no,o.quantity
                                ,f.franchise_id,pi.p_invoice_no
                                ,sd.batch_id
                                from king_orders o
                                join king_transactions tr on tr.transid = o.transid and o.status in (0,1) and tr.batch_enabled = 1
                                join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                                left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                                left join proforma_invoices `pi` on pi.order_id = o.id and pi.invoice_status = 1
                                left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = pi.p_invoice_no 
                                join king_dealitems di on di.id = o.itemid 
                                where f.franchise_id = ? $cond and i.id is null and tr.transid in ($str_all_trans)
                                order by tr.init desc",array($franchise_id,$from,$to))->result_array();
                                            #,di.name
        $output = '<table width="100" class="subdatagrid">
                            <thead>
                            <tr>
                                <th style="width:25px">#</th>
                                <th style="width:100px">Trans</th>
                                <th style="width:100px">Batch ID</th>
                                <th style="width:100px">Order ID</th>
                                <th style="width:250px">Order Name</th>
                                <th style="width:25px"><input type="checkbox" value="" name="pick_all_fran" id="pick_all_fran_'.$franchise_id.'" class="pick_list_trans_grp_fran" title="Select all invoices" onclick="chkall_fran_orders('.$franchise_id.')" />PickList</th>
                                <th style="width:100px">Action</th>
                            </tr>
                            </thead>
                            <tbody>';
        //echo '<pre>'.$this->db->last_query(); die();
        $arr_tmp=array();
        foreach ($rslt as $i=>$row) {
                $invoice_action =$batch_id_msg = '--';
                $batch_id=$row['batch_id'];
                
                if(!in_array($row['transid'],$arr_tmp)) {
                    $arr_tmp[]=$row['transid'];
                    $transid_msg='<a href="'.site_url('admin/trans/'.$row['transid']).'" target="_blank">'.$row['transid'].'</a>';
                    
                    if($batch_type == 'pending') {
                            $invoice_action = '<a href="javascript:void(0);" class="retry_link" onclick="return reserve_stock_for_trans('.$user['userid'].',\''.trim($row['transid']).'\',0);">Re-Allot</a>';
                    }
                    else {
                            if($batch_id != GLOBAL_BATCH_ID) {
                                $invoice_action = '<a class="proceed_link clear" href="pack_invoice/'.$row['p_invoice_no'].'" target="_blank">Generate invoice</a>
                                    <a class="danger_link clear" href="javascript:void(0)" onclick="cancel_proforma_invoice(\''.$row['p_invoice_no'].'\','.$user['userid'].',0)" class="">De-Allot</a>';
                                $picklist_btn_msg = '<input type="checkbox" value="'.$row['p_invoice_no'].'" name="chk_pick_list_by_fran[]" id="chk_pick_list_by_fran" class="chk_pick_list_by_fran_'.$franchise_id.'" title="Select this for picklist" />';
                                
                            }
                    }
                }
                else {
                    
                    $transid_msg=' --||--';//'-second</a>';
                }
                if(isset($batch_id)) $batch_id_msg = '<a href="'.site_url("admin/batch/".$batch_id).'" target="_blank">B'.$batch_id.'</a>';
                $output .= '<tr>
                                <td>'.++$i.'</td>
                                <td align="center">'.$transid_msg.'</td>
                                <td align="center">'.$batch_id_msg.'</td>
                                <td align="center">'.$row['id'].'</td>
                                <td><span class="info_links"><a href="pnh_deal/'.$row['itemid'].'" target="_blank">'.$row['name'].'</a></span></td>
                                <td align="center">'.$picklist_btn_msg.'</td>
                                <td align="center">'.$invoice_action.'</td>
                         </tr>';
        }
        $output .= '</tbody></table>';
        echo $output;
    }

    /**
     * creates a new batch by select menuids,batch_size
     */
    function create_batch_by_group_config() {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE); $output=array();
        echo $this->reservations->do_create_batch_by_group_config();
    }
    /**
     * Suggest menu id under batch groupid
     * @param type $batchgroupid
     */
    function jx_suggest_menus_groupid($batchgroupid) {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);$output=array();
        $rslt = $this->db->query("select assigned_menuid,batch_size,group_assigned_uid from m_batch_config where id=?",$batchgroupid)->row_array();
        if(count($rslt)>0) {
            $output['status'] = 'success';
            $output['assigned_menuid'] = $rslt['assigned_menuid'];
            $output['batch_size'] = $rslt['batch_size'];
                $arr =array();
                $arr_uids = explode(",",$rslt['group_assigned_uid']);
                foreach($arr_uids as $i=>$userid) {
                    $arr[$i]["userid"] = $userid;
                    $arr[$i]["username"] = $this->db->query("select username from king_admin where id=?",$userid)->row()->username;
                }
            $output['group_assigned_uid'] = json_encode($arr);
        }
        else {
            $output['status'] = 'fail';
            $output['response'] = 'No data found.';
        }
        echo json_encode($output);
    }
    function manage_reservation_create_batch_form() {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);$output=array();
        $data['batch_conf']=  $this->reservations->getBatchGroupConfig();
        $data['pnh_terr'] = $this->db->query("select * from pnh_m_territory_info order by territory_name")->result_array();
        $data['pnh_towns']=$this->db->query("select id,town_name from pnh_towns order by town_name")->result_array();
        $this->load->view("admin/body/jx_manage_reservation_create_batch_form",$data);
    }
    /**
     * Process proforma ids selected 
     */
    function p_invoice_for_picklist() {
        $user=$this->auth(INVOICE_PRINT_ROLE);
        $p_invoice_ids = explode(",",$_POST['pick_list_trans']);
        
        $tmp_arr=array();
        foreach ($p_invoice_ids as $p_inv_id) {
            
            $arr_rslt = $arr_prod = $this->reservations->product_proc_list_for_invoice($p_inv_id);
            foreach($arr_rslt as $row) {
                if(!in_array($row['menuid'],$tmp_arr)) {
                    $tmp_arr[]=$row['menuid'];
                    $rslt_arr[$row['menuid']][] = $row;
                    $rslt_arr[$row['menuid']]['menuname'] = $row['menuname'];
                }
                else {
                    $rslt_arr[$row['menuid']][] = $row;
                    $rslt_arr[$row['menuid']]['menuname'] = $row['menuname'];
                }
            }
            
        }
//        echo '<pre>';print_r($rslt_arr);die();
        $data['prods']=$rslt_arr;
        $this->load->view("admin/body/product_proc_list_pinvoice",$data);
    }
    
    /**
     * Check and reserve available stock for all transactions
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function jx_reallot_frans_all_trans($updated_by,$batch_remarks='By transaction reservation system') {
        
        $user= $this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        
        $all_trans = $_POST['all_trans'];
        //die($all_trans);
        if($user) {
            $rslt_for_trans = $this->db->query("select * from (select a.transid,count(a.id) as num_order_ids,sum(a.status) as orders_status
                    from king_orders a
                    join king_transactions tr on tr.transid = a.transid and tr.is_pnh=1
                    where a.status in (0,1) and tr.batch_enabled=1 and a.transid in ".$all_trans."
                    group by a.transid) as ddd
                    where ddd.orders_status=0")->result_array() or die("Error");
            foreach($rslt_for_trans as $rslt) {
                $transid = $rslt['transid'];
                $ttl_num_orders = $rslt['num_order_ids'];

                //echo ("$transid,$ttl_num_orders,$batch_remarks,$updated_by <br>");
                // Process to batch this transaction
                $this->reservations->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
            }
        }
        else {
            echo 'You dodn\'t have access permission to do this acation';
        }
        //$this->output->set_output($output);
    }
    /**
     * Check and reserve available stock for all transactions
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function jx_reserve_avail_stock_all_transaction($updated_by,$batch_remarks='By transaction reservation system') {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE); $output=array();
        if($user) {
            $rslt_for_trans = $this->db->query("select * from (select a.transid,count(a.id) as num_order_ids,sum(a.status) as orders_status
                    from king_orders a
                    join king_transactions tr on tr.transid = a.transid
                    where a.status in (0,1) and tr.batch_enabled=1 and tr.is_pnh=1  #and a.transid=@tid
                    group by a.transid) as ddd
                    where ddd.orders_status=0")->result_array() or die("Error");
            foreach($rslt_for_trans as $rslt) {
                $transid = $rslt['transid'];
                $ttl_num_orders = $rslt['num_order_ids'];

                //echo ("$transid,$ttl_num_orders,$batch_remarks,$updated_by <br>");
                // Process to batch this transaction
                $output['result'][] = $this->reservations->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
                
            }
            
//                $count_alloted = count($arr_result["alloted"]);
//                $count_nostock = count($arr_result["nostock"]);
//                $output['result'] =array("alloted"=>$count_alloted);
        }
        else {
            $output = array("status"=>"fail","resp"=>'You dodn\'t have access permission to do this acation');
        }
        //$this->output->set_output($output);
        echo json_encode($output);
    }
    
    /**
     * Make transaction enabled for batch and allot stock
     * @param type $trans
     * @param type $ttl_num_orders
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function reserve_stock_for_trans($transid,$ttl_num_orders,$updated_by,$batch_remarks='') {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        $batch_remarks=$batch_remarks=='' ? 'by transaction reservation system' : $batch_remarks ;
        
        // Process to batch this transaction
        $output['result'] = $this->reservations->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
        
        echo json_encode($output);
    }
       
    /**
     * Get transaction list by batch type, Like, Ready for processing or pending or not ready transaction
     * @param type $batch_type
     * @param type $from
     * @param type $to
     * @param type $pg
     * @param type $limit
     */
    function jx_manage_trans_reservations_list($batch_type,$from=0,$to=0,$terrid=0,$townid=0,$franchiseid=0,$menuid=0,$brandid=0,$showbyfrangrp=0,$batch_group_type=0,$latest=1,$limit=10,$pg=0) 
    {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        $this->load->model("reservation_model");
        
        if($from != 0 and $to !=0) {
                $s=date("Y-m-d", strtotime($from));
                $e=date("Y-m-d", strtotime($to));
        }
        else {
            $s=$e=0;
        }
        /*else {                $s=date("Y-m-d",strtotime("last month"));                 $e=date("Y-m-d",strtotime("today"));        }*/
        
        $data['user']=$user;
        $data['batch_type']=$batch_type;
        $data['s']= $s;
        $data['e'] = $e;
        $data['terrid']=$terrid;
        $data['townid']=$townid;
        $data['franchiseid']=$franchiseid;
        $data['menuid']=$menuid;
        $data['brandid']=$brandid;
        $data['showbygrp']=$showbyfrangrp;
        $data['batch_group_type']=$batch_group_type;
        $data['latest']= $latest;
        $data['limit']=$limit;
        $data['pg']=$pg;
//        echo '<pre>';    print_r($data);        //die();
        if(!$showbyfrangrp)
            $this->load->view("admin/body/jx_manage_trans_reservations_list",$data);
        else 
            $this->load->view("admin/body/jx_manage_trans_reservations_by_fran",$data);
        
    }

    
    /**
     * Dispaly and process transaction batch status as 
     */
    function manage_trans_reservations() {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        /*$data['pnh_terr'] = $this->db->query("select * from pnh_m_territory_info order by territory_name")->result_array();
        $data['pnh_towns']=$this->db->query("select id,town_name from pnh_towns order by town_name")->result_array();
        $data['pnh_menu'] = $this->db->query("select mn.id,mn.name from pnh_menu mn join king_deals deal on deal.menuid=mn.id where mn.status=1 group by mn.idorder by mn.name")->result_array();
        $data['pnh_brands'] = $this->db->query("select br.id,br.name from king_brands br join king_orders o on o.brandid=br.id group by br.id order by br.name")->result_array();
        $data['s']=date("d/m/y",$from);
        $data['e']=date("g:ia d/m/y",$to);*/
        $data['user']=$user;
        $data['page']='manage_trans_reservations';
        $this->load->view("admin",$data);
    }
    /**
     * Cancel stock reserved and invoice generated trasactions and update the stock tables
     * @param type $p_invoice
     * @param type $update_by
     * @param type $msg
     */
    function cancel_reserved_proforma_invoice($p_invoice,$update_by=1,$msg="Proforma cancelled by reservation system")
    {
        echo $this->reservations->reservation_cancel_proforma_invoice($p_invoice,$update_by,$msg);
    }

    /********End Orders Reservation**************/
     
    /**
     * Enable/Disable the transaction batch status
     * @access public
     * @param type $transid
     */
    /*function jx_batch_enable_disable($transid,$flag=1)
    {
            $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
            //if($this->db->query("select batch_enabled from king_transactions where transid=?",$transid)->row()->batch_enabled==1)
            $this->db->query("update king_transactions set batch_enabled=? where transid=? limit 1",array($flag,$transid));
            $this->erpm->do_trans_changelog($transid,"Transaction ".($flag==1?"ENABLED":"DISABLED")." for batch process");
            echo "Transaction ".$transid." ".($flag==1?"enabled":"disabled")." for batch process";
    }*/
}

?>
