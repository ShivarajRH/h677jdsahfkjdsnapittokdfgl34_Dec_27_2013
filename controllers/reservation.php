<?php
/**
 * Description of reservation
 *
 * @contact Shivaraj@storeking.in
 */
include APPPATH.'/controllers/analytics.php';
class Reservation extends Analytics {
    
    /**
     * @access public
     * @param type $transid
     */
    function jx_batch_enable_disable($transid,$flag=1)
    {
            $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
            //if($this->db->query("select batch_enabled from king_transactions where transid=?",$transid)->row()->batch_enabled==1)
            //        $flag=0;
            
            $this->db->query("update king_transactions set batch_enabled=? where transid=? limit 1",array($flag,$transid));
            $this->erpm->do_trans_changelog($transid,"Transaction ".($flag==1?"ENABLED":"DISABLED")." for batch process");
            
            echo "Transaction ".$transid." ".($flag==1?"enabled":"disabled")." for batch process";
    }
 
    /******** Orders Reservation**************/
    /**
     * Check and reserve available stock for all transactions
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function reserve_avail_stock_all_transaction($updated_by,$batch_remarks='By transaction reservation system') {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        
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
            $this->load->model("reservation_model");
            $this->reservation_model->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
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
        $this->load->model("reservation_model");
        $this->reservation_model->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
    }
       
    /**
     * Get transaction list by batch type, Like, Ready for processing or pending or not ready transaction
     * @param type $batch_type
     * @param type $from
     * @param type $to
     * @param type $pg
     * @param type $limit
     */
    function jx_get_transaction_list($batch_type,$from,$to,$terrid=0,$townid=0,$franchiseid=0,$menuid=0,$brandid=0,$limit=1,$pg=0) {
        //$limit = $_POST["limit"]; //
        
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

        $this->load->view("admin/body/jx_get_transaction_list",$data);
    }

    
    /**
     * Dispaly and process transaction batch status as 
     */
    function manage_trans_reservations() {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        /*$data['pnh_menu'] = $this->db->query("select * from pnh_menu order by name")->result_array();
        $data['pnh_terr'] = $this->db->query("select * from pnh_m_territory_info order by territory_name")->result_array();
        $data['pnh_towns']=$this->db->query("select id,town_name from pnh_towns order by town_name")->result_array();
        $data['pnh_menu'] = $this->db->query("select mn.id,mn.name from pnh_menu mn
                                                    join king_deals deal on deal.menuid=mn.id
                                                    where mn.status=1 
                                                    group by mn.id
                                                    order by mn.name")->result_array();
        $data['pnh_brands'] = $this->db->query("select br.id,br.name from king_brands br
                                    join king_orders o on o.brandid=br.id
                                    group by br.id order by br.name")->result_array();
        $data['s']=date("d/m/y",$from);
        $data['e']=date("g:ia d/m/y",$to);*/
        $data['user']=$user;
        $data['page']='manage_trans_reservations';
        $this->load->view("admin",$data);
    }
    
    
    
        function reservation_cancel_proforma_invoice($p_invoice,$update_by=1,$msg="Proporma Cancelled")
        {
		$invoice=$this->db->query("select transid,order_id,p_invoice_no,p_invoice_no as invoice_no from proforma_invoices where p_invoice_no=? and invoice_status=1",$p_invoice)->result_array();
		if(empty($invoice))
			show_error("Proforma Invoice not found or Invoice already cancelled");
		$transid=$invoice[0]['transid'];
		$oids=array();
		foreach($invoice as $i)
			$oids[]=$i['order_id'];
			
		$orders=$this->db->query("select quantity as qty,itemid,id from king_orders where id in ('".implode("','",$oids)."') and transid = ? ",$transid)->result_array();
		
		$batch_id = $this->db->query("select batch_id from shipment_batch_process_invoice_link where p_invoice_no=?",$p_invoice)->row()->batch_id;

		$proforma_inv_det = $this->db->query("select id,is_b2b from proforma_invoices where p_invoice_no=? ",$p_invoice)->row_array();
		
		$proforma_inv_id = $proforma_inv_det['id'];
		$is_pnh = $proforma_inv_det['is_b2b'];
		
		
		foreach($orders as $o)
		{
			$pls=$this->db->query("select qty,pl.product_id,p.mrp,p.brand_id from m_product_deal_link pl join m_product_info p on p.product_id=pl.product_id where itemid=?",$o['itemid'])->result_array();
			
			$pls2=$this->db->query("select pl.qty,p.product_id,p.mrp,p.brand_id 
						from products_group_orders pgo
						join king_orders o on o.id = pgo.order_id 
						join m_product_group_deal_link pl on pl.itemid=o.itemid 
						join m_product_info p on p.product_id=pgo.product_id 
						where pgo.order_id=? and o.transid = ? ",array($o['id'],$transid))->result_array();
								
			$pls=array_merge($pls,$pls2);
			 
			foreach($pls as $p)
			{
				
				/** Default rack bin used if brand is not linked for loc **/ 
				$p['location'] = 1;
				$p['rackbin'] = 10;
				$loc_det_res = $this->db->query("select location_id,rack_bin_id from m_rack_bin_brand_link a join m_rack_bin_info b on a.rack_bin_id = b.id where brandid = ? limit 1 ",$p['brand_id']);
				if($loc_det_res->num_rows())
				{
					$loc_det = $loc_det_res->row_array();	
					$p['location'] = $loc_det['location_id'];
					$p['rackbin'] = $loc_det['rack_bin_id'];
				}
				
				$p_reserv_qty = 0;
				//$reserv_stk_res = $this->db->query('select id,release_qty,extra_qty,stock_info_id,qty from t_reserved_batch_stock where batch_id = ? and p_invoice_no = ? and status = 1 and order_id = ? and product_id = ? ',array($batch_id,$p_invoice,$o['id'],$transid,$p['product_id']));
				
				$reserv_stk_res = $this->db->query('select a.id,a.release_qty,a.extra_qty,a.stock_info_id,a.qty 
										from t_reserved_batch_stock a
										join king_orders b on a.order_id = b.id  
										where batch_id = ? and p_invoice_no = ? 
										and a.status = 0 and a.order_id = ? and b.transid = ? and product_id = ?',array($batch_id,$p_invoice,$o['id'],$transid,$p['product_id']));
													
				 
                                $stk_movtype=1; //IN
				if($reserv_stk_res->num_rows())
				{
					foreach($reserv_stk_res->result_array() as $reserv_stk_det)
					{
						$rqty = $reserv_stk_det['qty']-$reserv_stk_det['release_qty']+$reserv_stk_det['extra_qty'];
						$p_reserv_qty += $rqty;
						//$sql="update t_stock_info set available_qty=available_qty+? where product_id=? and stock_id = ? limit 1";
						//$this->db->query($sql,array($rqty,$p['product_id'],$reserv_stk_det['stock_info_id']));
						$this->db->query("update t_reserved_batch_stock set status=3 where id = ? ",array($reserv_stk_det['id']));
						//$this->erpm->do_stock_log(1,$rqty,$p['product_id'],$proforma_inv_id,false,true,true,-1,0,0,$reserv_stk_det['stock_info_id']);
						
						
						$stk_info = $this->db->query("select * from t_stock_info where stock_id = ? ",$reserv_stk_det['stock_info_id'])->row_array();
						
						if($stk_info)
						{
							 //($prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg='')
							$this->erpm->_upd_product_stock($stk_info['product_id'],$stk_info['mrp'],$stk_info['product_barcode'],$stk_info['location_id'],$stk_info['rack_bin_id'],0,$rqty,$update_by,$stk_movtype,$proforma_inv_id,-1,$msg);	
						}else
						{
							 
							$this->erpm->_upd_product_stock($p['product_id'],$p['mrp'],'',$p['location'],$p['rackbin'],0,$rqty,$update_by,$stk_movtype,$proforma_inv_id,-1,$msg);
						}
					}
					
				}else{
                                        //($prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg='')
                                        $this->erpm->_upd_product_stock($p['product_id'],$p['mrp'],'',$p['location'],$p['rackbin'],0,($p['qty']*$o['qty']),$update_by,$stk_movtype,$proforma_inv_id,-1,$msg);
					
					/*
					$new_stock_entry = true;
					$sp_det_res = $this->db->query("select product_id,mrp from t_stock_info where product_id = ? ",$p['product_id']);
					if($sp_det_res->num_rows())
					{
						foreach($sp_det_res->result_array() as $sp_row)
							if($sp_row['mrp'] == $p['mrp'])
								$new_stock_entry = false;
					}
					if($new_stock_entry)
						 $this->db->query("insert into t_stock_info(product_id,location_id,rack_bin_id,mrp,available_qty,product_barcode,created_on) values(?,?,?,?,?,?,now())",array($p['product_id'],$p['location'],$p['rackbin'],$p['mrp'],0,''));
					
					$stk_ref_id = @$this->db->query("select stock_id from t_stock_info where product_id=? and mrp=? limit 1 ",array($p['product_id'],$p['mrp']))->row()->stock_id;
					if($stk_ref_id)
					{
						$sql="update t_stock_info set available_qty=available_qty+? where stock_id=? limit 1";
						$this->db->query($sql,array($p['qty']*$o['qty'],$stk_ref_id));
						$this->erpm->do_stock_log(1,$p['qty']*$o['qty'],$p['product_id'],$proforma_inv_id,false,true,true,-1,0,0,$stk_ref_id);
					}
					**/
				}
				
				
				
			}
		
		}
 
 		 
		$this->db->query("update king_orders set status=0 where id in ('".implode("','",$oids)."') and transid=? ",$transid);
		$this->db->query("update proforma_invoices set invoice_status=0 where p_invoice_no=? and transid = ? ",array($p_invoice,$transid));
		$this->erpm->do_trans_changelog($transid,"Proforma Invoice no $p_invoice cancelled");
		$this->session->set_flashdata("erp_pop_info","Proforma Invoice cancelled");
		$bid=$this->db->query("select batch_id from shipment_batch_process_invoice_link where p_invoice_no=?",$p_invoice)->row()->batch_id;

		$is_batch_open = $this->db->query("select count(*) as t from (
			select  a.batch_id,a.p_invoice_no,sum(1) as total,sum(b.invoice_status) as ttl_active,sum(if(a.invoice_no,1,0)) as ttl_invoiced 
				from shipment_batch_process_invoice_link a
				join proforma_invoices b on a.p_invoice_no = b.p_invoice_no 
				join shipment_batch_process c on c.batch_id = a.batch_id 
				where a.batch_id = ?  
			group by a.batch_id
			having ttl_active != ttl_invoiced 
			) as g ",$bid)->row()->t;
	
			if($is_batch_open)
				$this->db->query("update shipment_batch_process set status=1 where batch_id=? limit 1",$bid);
			else
				$this->db->query("update shipment_batch_process set status=2 where batch_id=? limit 1",$bid);
		
		//redirect("admin/proforma_invoice/$p_invoice");
	}
	
        
    /********End Orders Reservation**************/
}

?>
