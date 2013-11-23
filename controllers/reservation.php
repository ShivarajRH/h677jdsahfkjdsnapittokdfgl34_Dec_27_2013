<?php
/**
 * Transaction/Orders Reservation functions
 * @contact Shivaraj@storeking.in
 */
include APPPATH.'/controllers/voucher.php';
class Reservation extends Voucher {
    /**
     * Suggest menu id under batch groupid
     * @param type $batchgroupid
     */
    function jx_suggest_menus_groupid($batchgroupid) {
        $this->erpm->auth();$output=array();
        $rslt = $this->db->query("select assigned_menuid,batch_size,assigned_uid from m_batch_config where id=?",$batchgroupid)->row_array();
//        if(count($rslt)>0) {
//            $output['assigned_menuid'] = explode(",",$rslt['assigned_menuid']);
//        }
        echo json_encode($rslt);
    }
    function manage_reservation_create_batch_form() {
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
        foreach ($p_invoice_ids as $p_inv_id) {
            //echo '<br>'.$p_inv_id;
            $data['prods'][] = $this->reservations->product_proc_list_for_invoice($p_inv_id);
        }
        $this->load->view("admin/body/product_proc_list_pinvoice",$data);
    }
    
    /**
     * Check and reserve available stock for all transactions
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function reserve_avail_stock_all_transaction($updated_by,$batch_remarks='By transaction reservation system') {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        if($user) {
            $rslt_for_trans = $this->db->query("select * from (select a.transid,count(a.id) as num_order_ids,sum(a.status) as orders_status
                    from king_orders a
                    join king_transactions tr on tr.transid = a.transid
                    where a.status in (0,1) and tr.batch_enabled=1 #and a.transid=@tid
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
        echo $this->reservations->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
    }
       
    /**
     * Get transaction list by batch type, Like, Ready for processing or pending or not ready transaction
     * @param type $batch_type
     * @param type $from
     * @param type $to
     * @param type $pg
     * @param type $limit
     */
    function jx_manage_trans_reservations_list($batch_type,$from,$to,$terrid=0,$townid=0,$franchiseid=0,$menuid=0,$brandid=0,$limit=1,$pg=0) {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        $this->load->model("reservation_model");
        if($from != '') {
                $s=date("Y-m-d", strtotime($from));
                $e=date("Y-m-d", strtotime($to));
                            //die($s.'<br>'.$e);
        }
        else {
                $s=date("Y-m-d",strtotime("last month")); 
                $e=date("Y-m-d",strtotime("today"));
        }
        
        $data['user']=$user;
        $data['batch_type']=$batch_type;
        $data['pg']=$pg;
        $data['limit']=$limit;
        $data['s']=$s;
        $data['e']=$e;
        $data['terrid']=$terrid;
        $data['townid']=$townid;
        $data['franchiseid']=$franchiseid;
        $data['menuid']=$menuid;
        $data['brandid']=$brandid;

        $this->load->view("admin/body/jx_manage_trans_reservations_list",$data);
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
    function cancel_reserved_proforma_invoice($p_invoice,$update_by=1,$msg="Proporma Cancelled by reservation system")
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
