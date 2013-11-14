<?php
/**
 * Description of reservation_model
 *
 * @author Shivaraj@storeking.in
 * @access public
 */
class reservation_model extends Model
{
	
    function __construct()
    {
            parent::__construct();
    }
    function put_town_courier_priority() {
        foreach(array("townid","courier_priority_1","courier_priority_2","courier_priority_3","delivery_hours_1","delivery_hours_2","delivery_hours_3","userid") as $i) {
                $$i= $this->input->post($i);
        }
        
        $is_active = 1;
        $townid_exits = $this->db->query("select town_id from `pnh_town_courier_priority_link` where town_id=?",$townid);
        //echo "=".$townid_exits."=".$this->db->last_query(); die();
        if($townid_exits->num_rows() <= 0) {
            $created_on = date("Y-m-d H:i:s",time('now'));
            $created_by = $userid;

            $this->db->query("insert into `pnh_town_courier_priority_link`
                (`town_id`,`courier_priority_1`,`courier_priority_2`,`courier_priority_3`,`delivery_hours_1`,`delivery_hours_2`,`delivery_hours_3`,`is_active`,`created_on`,`created_by`) 
                values (?,?,?,?,?,?,?,?,?,?)",array($townid,$courier_priority_1,$courier_priority_2,$courier_priority_3,$delivery_hours_1,$delivery_hours_2,$delivery_hours_3,$is_active,$created_on,$created_by));
        }
        else {
            $modified_on = date("Y-m-d H:i:s",time('now'));
            $modified_by = $userid;
            $arr_details=array("town_id"=>$townid,"courier_priority_1"=>$courier_priority_1,"courier_priority_2"=>$courier_priority_2
                ,"courier_priority_3"=>$courier_priority_3,"delivery_hours_1"=>$delivery_hours_1,"delivery_hours_2"=>$delivery_hours_2
                ,"delivery_hours_3"=>$delivery_hours_3,"is_active"=>$is_active,"modified_on"=>$modified_on,"modified_by"=>$modified_by);
            $this->db->update("pnh_town_courier_priority_link",$arr_details,array("town_id"=>$townid));
        }
        return true;
    }
    /**
     * Get franchise experience information based on created_time
     * @param type $f_created_on
     * @return type array
     */
    function fran_experience_info($f_created_on) {
        //$f_created_on =$f_created_on; //$f['f_created_on'];
        $fr_reg_diff = ceil((time()-$f_created_on)/(24*60*60));
	 
        if($fr_reg_diff <= 30)
        {
                $fr_reg_level_color = '#cd0000';
                $fr_reg_level = 'Newbie';
        }
        else if($fr_reg_diff > 30 && $fr_reg_diff <= 60)
        {
                $fr_reg_level_color = 'orange';
                $fr_reg_level = 'orange';//'Mid Level';
        }else if($fr_reg_diff > 60)
        {
                $fr_reg_level_color = 'green';
                $fr_reg_level = 'Experienced';
        }
        return array("f_level"=>$fr_reg_level,"f_color"=>$fr_reg_level_color);
    }
    
    /**
     * function to check if transaction is fully invoiced or not 
     * @param type $transid
     * @return boolean 
     */
    function is_transaction_invoiced($transid) 
    {
        $rslt = $this->db->query("select a.id,b.invoice_no 
                            from king_orders a 
                            left join king_invoice b on a.id = b.order_id and invoice_status = 1 
                            where a.transid = ? and b.id is null 
                            group by a.id ",$transid);
        
        // if resultset has atleast one record then pending orders for invoice is available 
        // else all orders in transactions are invoiced 
        return (($rslt->num_rows()>0)?false:true);
    }
    
    
    /**
     * Process the transactions to batch and generates  performa invoice for Batch enabled trans
     * @param type $transid
     * @param type $ttl_num
     * @param type $batch_remarks
     * @param type $updated_by
     * @depends do_pnh_offline_order(), and reservation controller functions
     */
    function do_batching_process($transid,$ttl_num,$batch_remarks="PNH Offline Order Placed.",$updated_by)
    {
            ini_set('memory_limit','512M');
            $i_transid=false;
            $num=$ttl_num;

            $user = $this->erpm->auth();
            $output='';

            if(empty($num))
                    show_error("Enter no of orders to process");
            $i_transid=$transid;//$this->input->post("transid");


            $down_import_summ = 0;
            $cond = '';
            $is_pnh=1;
           
            $trans=array();
            $itemids=array();
           
            
            if($i_transid)
            {
                    $cond = '';
                    $is_pnh = $this->db->query("select is_pnh from king_transactions where transid = ? ",$i_transid)->row()->is_pnh;
            }


            if($is_pnh)
            {
                    $fr_list = array();
                    $fr_list_res = $this->db->query("select franchise_id  from pnh_m_franchise_info where is_suspended = 0 ")->result_array();
                    foreach($fr_list_res as $fr_det)
                            $fr_list[] = $fr_det['franchise_id'];

                    $cond .= ' and t.franchise_id in ('.implode(',',$fr_list).')';
            }


            if($i_transid)
                    $raw_trans=$this->db->query("select o.* from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=1 and t.transid=?",$i_transid)->result_array();
            /*else
                    $raw_trans=$this->db->query("select o.*,t.partner_reference_no from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 join king_dealitems di on di.id = o.itemid join king_deals d on d.dealid = di.dealid  where t.batch_enabled=1 and t.is_pnh=$is_pnh $cond order by t.priority desc, t.init asc")->result_array();
                    */


            $v_transids=array();
            foreach($raw_trans as $t)
            {
                    $transid=$t['transid'];
                    if(!isset($trans[$transid]))
                            $trans[$transid]=array();
                    $trans[$transid][]=$t;
                    $itemids[]=$t['itemid'];
                    $v_transids[]=$t['transid'];
            }
            if(empty($trans))
                    show_error("No orders to process");

            $itemids=array_unique($itemids);
            $raw_prods=$this->db->query("select itemid,qty,product_id from m_product_deal_link where itemid in ('".implode("','",$itemids)."')")->result_array();
            $products=array();
            $productids=array();
            $partials=$not_partials=array();
            foreach($raw_prods as $p)
            {
                    $itemid=$p['itemid'];
                    if(!isset($products[$itemid]))
                            $products[$itemid]=array();

                    $products[$itemid][]=$p;
                    $productids[]=$p['product_id'];
            }
            $productids=array_unique($productids);

            $raw_prods=$this->db->query("select * from products_group_orders where transid in ('".implode("','",$v_transids)."')")->result_array();

            foreach($raw_prods as $r)
            {
                    $itemid=$this->db->query("select itemid from king_orders where id=? and transid = ? ",array($r['order_id'],$r['transid']))->row()->itemid;
                    $qty=$this->db->query("select l.qty from products_group_pids p join m_product_group_deal_link l on l.group_id=p.group_id where p.product_id=? and itemid = ? ",array($r['product_id'],$itemid))->row()->qty;

                    if(!isset($products[$itemid]))
                            $products[$itemid]=array();

                    $products[$itemid][]=array("itemid"=>$itemid,"qty"=>$qty,"product_id"=>$r['product_id'],"order_id"=>$r['order_id']);
                    $productids[]=$r['product_id'];

            }

            $to_process_orders=array();
            $raw_stock=$this->db->query("select product_id,sum(available_qty) as stock from t_stock_info where product_id in ('".implode("','",$productids)."') and available_qty > 0 and mrp > 0 group by product_id")->result_array();
            $stock=array();
            foreach($productids as $p)
                    $stock[$p]=0;
            foreach($raw_stock as $s)
            {
                    $pid=$s['product_id'];
                    $stock[$pid]=$s['stock'];
            }


            $total_orders_process=0;
            foreach($trans as $transid=>$orders)
            {
                    $total_pending[$transid]=count($orders);
                    $possible[$transid]=0;
                    $not_partial_flag=true;
                    $same_order=array();

                    $rem_prod_stock = array(); 
                    foreach($orders as $order)
                    {
                            $itemid=$order['itemid'];

                            if(!isset($products[$itemid]))
                                    continue;

                            $pflag=true; //process flag
                            foreach($products[$itemid] as $p)
                            {
                                    $process_stk_chk = 0;
                                    if(isset($p['order_id']))
                                    {
                                            if($p['order_id'] == $order['id'])
                                                    $process_stk_chk = 1;
                                    }else
                                    {
                                            $process_stk_chk = 1;
                                    }		
                                    if($process_stk_chk)
                                    {
                                            if(!isset($rem_prod_stock[$p['product_id']]))
                                                    $rem_prod_stock[$p['product_id']] = $stock[$p['product_id']];

                                            //echo ($stock[$p['product_id']]).'-'.$p['product_id'].' - '.$p['qty'].' - '.$order['quantity'].' ';
                                            if($rem_prod_stock[$p['product_id']]<$p['qty']*$order['quantity'])
                                            {
                                                    $pflag=false;
                                                    break;
                                            }else
                                                    $rem_prod_stock[$p['product_id']] -= $p['qty']*$order['quantity'];
                                    }	
                            }

                            if($pflag)
                            {
                                    $possible[$transid]++;
                                    if($process_partial)
                                    {
                                            $to_process_orders[]=$order['id'];
                                            $same_order[]=$order['id'];
                                    }
                            }
                            else
                                    $not_partial_flag=false;

                    }

                    if($not_partial_flag)
                    {
                            $same_order=array();
                            //$to_process_orders=array();
                            foreach($orders as $order)
                            {
                                    $to_process_orders[]=$order['id'];
                                    $same_order[]=$order['id'];
                            }
                            $not_partials[]=$transid;
                    }else
                            $partials[]=$transid;

                    if(!empty($same_order))
                    {
                            $total_orders_process++;
                            foreach($orders as $order)
                            {
                                    if(in_array($order['id'],$same_order))
                                    {
                                            if(!isset($products[$itemid]))
                                                    continue;

                                            foreach($products[$order['itemid']] as $p)
                                            {
                                                    if($stock[$p['product_id']] >= ($p['qty']*$order['quantity']))
                                                    {
                                                            $stock[$p['product_id']]-=$p['qty']*$order['quantity'];
                                                            $to_process_orders[] = $order['id'];
                                                    }
                                                    else
                                                            break ;
                                            }
                                    }
                            }
                    }

                    if($total_orders_process>=$ttl_num)
                            break;
            }
            
            $orders=array_unique($to_process_orders);

            /*
            print_r($productids);
            print_r($orders);
            print_r($stock);
            exit;
            */

            $p_invoices=$this->erpm->do_proforma_invoice($orders);

            $batch_id=0;
            $batch_inv_link = array();

            $ttl_invoices = count($p_invoices);
            if($ttl_invoices > $num)
                    $ttl_batchs = ceil($ttl_invoices/$num);
            else 
                    $ttl_batchs = 1;

            //$batch_remarks = $this->input->post('batch_remarks');

            if(!empty($p_invoices))
            {
                    for($b=0;$b<$ttl_batchs;$b++)
                    {
                            $s = $b*$num;
                            $ttl_inbatch = ((($s+$num) > $ttl_invoices)?$ttl_invoices-$s:$num);

                            $this->db->query("insert into shipment_batch_process(num_orders,batch_remarks,created_on) values(?,?,?)",array($ttl_inbatch,$batch_remarks,date('Y-m-d H:i:s')));
                            $batch_id=$this->db->insert_id();
                            for($k=$s;$k<$s+$ttl_inbatch;$k++)
                            {
                                    $inv = $p_invoices[$k];
                                    $batch_inv_link[$inv] = $batch_id;
                                    $cid=0;
                                    $awb="";

                                    // generate entries for processing splitted orders to invoice no with one proforma invoice

                                    $p_ttl_inv_ords = @$this->db->query("select sum(t) as t from ((select count(*) as t 
                                                                                    from proforma_invoices a
                                                                                    join king_orders b on a.order_id = b.id and a.transid = b.transid 
                                                                                    where a.p_invoice_no = ? and a.invoice_status = 1 and is_ordqty_splitd = 1 
                                                                                    group by is_ordqty_splitd 
                                                                                    )union 
                                                                                    (
                                                                                    select 1 as t 
                                                                                    from proforma_invoices a
                                                                                    join king_orders b on a.order_id = b.id and a.transid = b.transid 
                                                                                    where a.p_invoice_no = ? and a.invoice_status = 1 and is_ordqty_splitd = 0  
                                                                                    group by is_ordqty_splitd 
                                                                                    )) as g ",array($inv,$inv))->row()->t; 

                                    $p_ttl_inv_ords = $p_ttl_inv_ords*1;					

                                    for($k1=0;$k1<$p_ttl_inv_ords;$k1++) 
                                            $this->db->query("insert into shipment_batch_process_invoice_link(batch_id,p_invoice_no,courier_id,awb) values(?,?,?,?)",array($batch_id,$inv,$cid,$awb));

                            }
                    }
            }


            $down_summary = array(); 
            foreach($orders as $o)
            {
                    $pinv_det=@$this->db->query("select id,p_invoice_no,transid from proforma_invoices where order_id=? and invoice_status = 1 order by id desc ",$o)->row_array();
                    if(!$pinv_det)
                            continue;

                    $invid = $pinv_det['id'];
                    $pinvno = $pinv_det['p_invoice_no'];
                    $ptransid = $pinv_det['transid'];

                    $s_prods=$this->db->query("select t.partner_reference_no,o.transid,o.id,p.product_id,p.qty,o.quantity,o.i_orgprice,o.id as order_id from king_orders o join m_product_deal_link p on p.itemid=o.itemid join king_transactions t on t.transid  = o.transid  where o.id=? and o.transid = ? order by o.sno asc",array($o,$ptransid))->result_array();

                    $s_prods_1=$this->db->query("select t.partner_reference_no,o.transid,o.id,go.product_id,p.qty,o.quantity,o.i_orgprice,o.id as order_id 
                                                                                            from king_orders o 
                                                                                            join king_transactions t on t.transid  = o.transid 
                                                                                            join products_group_orders go on go.order_id = o.id 
                                                                                            join m_product_group_deal_link p on p.itemid=o.itemid 
                                                                                            where o.id=? and o.transid = ?  order by o.sno asc",array($o,$ptransid))->result_array();

                    $s_prods = array_merge($s_prods,$s_prods_1);

                    // compute stock reduction by order quantity and record stock info ids 
                    foreach($s_prods as $p)
                    {
                            $omrp = $p['i_orgprice'];
                            $total_qty = $p['quantity']*$p['qty'];
                            $order_id = $p['order_id'];

//                            if($down_import_summ)
//                                    $down_summary[$p['partner_reference_no']] = $pinvno;

                            /*
                            for($i=1;$i<=$p['quantity']*$p['qty'];$i++)
                                    $this->db->query("update t_stock_info set available_qty=available_qty-1 where product_id=? and available_qty>=0 order by stock_id asc limit 1",$p['product_id']);
                            */
                            $alloted_stock = array();
                            $alloted_stock2 = array();

                            $pen_qty = $total_qty;

                            // query to fetch stock product ordered by exact mrp and followed by asc mrp.  	

                            $sql = "select product_barcode,stock_id,product_id,available_qty,location_id,rack_bin_id,mrp,if((mrp-$omrp),1,0) as mrp_diff 
                                            from t_stock_info where mrp > 0  and product_id = ? and available_qty > 0 
                                            order by product_id desc,mrp_diff,mrp ";


                            $stk_prod_list = $this->db->query($sql,$p['product_id']);

                            if($stk_prod_list->num_rows())
                            {
                                    // iterate all stock product 
                                    foreach($stk_prod_list->result_array() as $stk_prod)
                                    {
                                            $reserv_qty = 0; 
                                            if($stk_prod['available_qty'] < $pen_qty )
                                                    $reserv_qty = $stk_prod['available_qty'];
                                            else
                                                    $reserv_qty = $pen_qty;

                                                    $tmp = array();
                                                    $tmp['p_invoice_no'] = $pinvno;
                                                    $tmp['stock_info_id'] = $stk_prod['stock_id'];
                                                    $tmp['product_id'] = $stk_prod['product_id'];
                                                    $tmp['batch_id'] = $batch_id;
                                                    $tmp['order_id'] = $order_id;
                                                    $tmp['qty'] = $reserv_qty;
                                                    $tmp['reserved_on'] = time();
                                                    
                                                    array_push($alloted_stock,$tmp);
                                                    
                                                    $tmp['mrp'] = $stk_prod['mrp'];//by S
                                                    $tmp['product_barcode'] = $stk_prod['product_barcode'];//by S
                                                    $tmp['location_id'] = $stk_prod['location_id'];//by S
                                                    $tmp['rack_bin_id'] = $stk_prod['rack_bin_id'];//by S
                                                    array_push($alloted_stock2,$tmp);
                                                    
                                                    $pen_qty = $pen_qty-$reserv_qty;

                                            // if all qty updated 
                                            if(!$pen_qty)
                                                    break;
                                            
                                    }
                            }

                            if(count($alloted_stock))
                            {
                                    foreach($alloted_stock as $allot_stk)
                                            $this->db->insert("t_reserved_batch_stock",$allot_stk);
                                    
                                    foreach($alloted_stock2 as $stk_prod)
                                    {
                                            $stk_movtype=0;
                                            //$prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg=''
                                            if($this->erpm->_upd_product_stock($stk_prod['product_id'],$stk_prod['mrp'],$stk_prod['product_barcode'],$stk_prod['location_id'],$stk_prod['rack_bin_id'],$stk_prod['stock_info_id'],$stk_prod['qty'],$updated_by,$stk_movtype,12312,-1,$batch_remarks)) {
                                                //Stock log updated.
                                                $stk_movtype_msg = ($stk_movtype)?' De-Alloted. ': " Alloted. ";
                                                
                                                $output .= "\nProduct (".$stk_prod['product_id'].') with '.$stk_prod['qty'].' quantity is '.$stk_movtype_msg."";
                                            }
                                            else {
                                                $output .= "\nStock log not updated.";
                                            }
                                             
                                    }
                                    
                            }
                    }

            }
            echo ($output);
    }

}

?>
