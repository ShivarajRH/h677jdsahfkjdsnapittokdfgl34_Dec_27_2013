<?php
/**
 * Description of reservation_model
 *
 * @author Shivaraj@storeking.in
 * @access public
 */
class reservation_model extends Model
{
	
    function __construct() {
            parent::__construct();
    }
    function do_create_batch_by_group_config () {
        $output = '';
        
        foreach(array("batch_group_name","assigned_menuids","batch_size","assigned_uid") as $i) {
            $$i=$this->input->post($i);
            //echo "$batch_group_name, $assigned_menuids, $batch_size,$assigned_uid <br>";
           // 112,118, 2,37
        }
        //die();
        $rslt = $this->db->query("select d.menuid,sd.id,sd.batch_id,sd.p_invoice_no,from_unixtime(tr.init) from king_transactions tr
                                join king_orders as o on o.transid=tr.transid
                                join proforma_invoices as `pi` on pi.order_id = o.id
                                join shipment_batch_process_invoice_link sd on sd.p_invoice_no =pi.p_invoice_no
                                join king_dealitems dl on dl.id = o.itemid
                                join king_deals d on d.dealid = dl.dealid  and d.menuid in (?)
                                where sd.batch_id=5000
                                order by tr.init asc
                                limit 0,$batch_size",array($assigned_menuids))->result_array();
        $output.= "1.".  $this->db->last_query().'<br>';
        $batch_remarks = 'By Transaction Reservation System';
        $ttl_inbatch = count($rslt);
        
        if($ttl_inbatch>0) {
            
            //$this->db->query("insert into shipment_batch_process(num_orders,batch_remarks,created_on) values(?,?,?)",array($ttl_inbatch,$batch_remarks,date('Y-m-d H:i:s')));
            
            //$batch_id = $this->db->insert_id();
            
            //$output.= "2.".  $this->db->last_query().'<br>'.$batch_id;
            
            $batch_id = 5015;
            foreach ($rslt as $row) {
                
                $arr_set = array("batch_id"=>$batch_id,"assigned_userid"=>$assigned_uid);
                $arr_where =array("id"=>$row['id']);
                $output.= ''.implode(",",$arr_set).'<br>'.implode(",",$arr_where);
               /*     
                
                $this->db->update("update `shipment_batch_process_invoice_link` set `batch_id` = $batch_id and `assigned_userid` = $assigned_uid where `id`= ".$row['id']."");
                
                $output.= "3.".  $this->db->last_query().'<br>';
               
                $output.= 'Batch '.$batch_id." is updated to ".$row['id'].'<br>';
                */
            }
            $output.= 'Batch created'.'<br>';
        }
        else {
            $output.= 'No transactions found.'.'<br>';
        }
        return '<pre>'.$output.'</pre>';
    }
    
    function getBatchGroupConfig() {
        return $this->db->query("select * from m_batch_config")->result_array();
    }
    function product_proc_list_for_invoice($p_invoiceid) {
        //$data['prods']=$this->erpm->getprodproclist($bid);
        $arr_rslt = $this->db->query("select product_id,product,location,sum(rqty) as qty from ( 
                select rbs.product_id,pi.product_name as product,concat(concat(rack_name,bin_name),'::',si.mrp) as location,rbs.qty as rqty 
                        from t_reserved_batch_stock rbs 
                        join t_stock_info si on rbs.stock_info_id = si.stock_id 
                        join m_product_info pi on pi.product_id = si.product_id 
                        join m_rack_bin_info rak on rak.id = si.rack_bin_id 
                        join shipment_batch_process_invoice_link e on e.p_invoice_no = rbs.p_invoice_no and invoice_no = 0 
                        where e.p_invoice_no=? 
                group by rbs.id  ) as g 
                group by product_id,location",$p_invoiceid)->result_array();#e.batch_id in (?)
        return $arr_rslt;
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

            if(empty($num)) {
                    $output.=("Enter no of orders to process"); return $output;
            }
            
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
            else
                    $raw_trans=$this->db->query("select o.*,t.partner_reference_no from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 join king_dealitems di on di.id = o.itemid join king_deals d on d.dealid = di.dealid  where t.batch_enabled=1 and t.is_pnh=$is_pnh $cond order by t.priority desc, t.init asc")->result_array();
                    

            
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
            if(empty($trans)) {
                    $output .= ("No orders to process"); return $output;
            }

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

            /*echo '<pre>';
            print_r($productids);
            print_r($orders);
            print_r($stock);
            exit;*/
            

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

                            //$this->db->query("insert into shipment_batch_process(num_orders,batch_remarks,created_on) values(?,?,?)",array($ttl_inbatch,$batch_remarks,date('Y-m-d H:i:s')));
                            $batch_id='5000';//$this->db->insert_id();
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

                            if($down_import_summ)
                                    $down_summary[$p['partner_reference_no']] = $pinvno;

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
                                    
                                    $tot_qty=array();
                                    foreach($alloted_stock2 as $stk_prod)
                                    {
                                            $stk_movtype=0;
                                            //$prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg=''
                                            $rdata=$this->erpm->_upd_product_stock($stk_prod['product_id'],$stk_prod['mrp'],$stk_prod['product_barcode'],$stk_prod['location_id'],$stk_prod['rack_bin_id'],$stk_prod['stock_info_id'],$stk_prod['qty'],$updated_by,$stk_movtype,12312,-1,$batch_remarks);
                                            if($rdata) {
                                                //Stock log updated.
                                                $stk_movtype_msg = ($stk_movtype)?' De-Alloted. ': " Alloted. ";
                                                $tot_prdt[$stk_prod['product_id']]=true;
                                                $success = "<br>Product-".$stk_prod['product_id']." and stock-".$stk_prod['stock_info_id'].' with '.$stk_prod['qty'].' quantity is '.$stk_movtype_msg."";
                                            }
                                            elseif(is_array($rdata)) {
                                                $output .= "<br>".$rdata;
                                                
                                            }else {
                                                $output .= "<br>Transaction Stock log not updated.";
                                            }
                                             
                                    }
                                    
                                    $output .= '<br>STOCK ALLOTED - '.$i_transid.' with '.count($alloted_stock).' product'.$stk_movtype_msg.'';
                            }
                           
                    }
                    
            }
            if($down_import_summ)
            {
			 
				//$csv_oids = array();
				//$csv_oids[] = '"Partner Reference no","Batch ID","Invoice no"';
				//$p_oids = $this->input->post('p_oids');
				//$p_oid_arr = explode(',',$p_oids);
				/*foreach($p_oid_arr as $p_oid)
				{
					$tmp = array();
					$tmp['partner_reference_no'] = $p_oid;
					$tmp['batch_id'] = '';
					$tmp['p_invoice_no'] = '';
					if(isset($down_summary[$p_oid]))
					{
						$tmp['p_invoice_no'] = isset($down_summary[$p_oid])?$down_summary[$p_oid]:'';
						$tmp['batch_id'] = isset($batch_inv_link[$tmp['p_invoice_no']])?$batch_inv_link[$tmp['p_invoice_no']]:'';	
					}else
					{
						//$pinv_det = $this->db->query("select ");
					}
					
					array_push($csv_oids,'"'.implode('","',$tmp).'"');
				}
				
				header('Content-Type: application/csv');
				header('Content-Disposition: attachment; filename=import_stat.csv');
				header('Pragma: no-cache');

				echo implode("\r\n",$csv_oids);
				exit;*/
                
                               
					$tmp = array();
					//$tmp['partner_reference_no'] = $p_oid;
					$tmp['batch_id'] = '';
					$tmp['p_invoice_no'] = '';
					if(isset($down_summary[$p_oid]))
					{
						$tmp['p_invoice_no'] = isset($down_summary[$p_oid])?$down_summary[$p_oid]:'';
						$tmp['batch_id'] = isset($batch_inv_link[$tmp['p_invoice_no']])?$batch_inv_link[$tmp['p_invoice_no']]:'';	
					}
					
					$data = implode('","',$tmp);
				
                                
                                        $output .= "<br>>Transaction $i_transid with ".$data.'.';
                                
		}else
		{
			if(!count($batch_inv_link))
				$output.="<br>INSUFFICIENT STOCK - $i_transid";
		}
                echo ($output);
    }
    
    function reservation_cancel_proforma_invoice($p_invoice,$update_by=1,$msg) {
            $output='';
            $invoice=$this->db->query("select transid,order_id,p_invoice_no,p_invoice_no as invoice_no from proforma_invoices where p_invoice_no=? and invoice_status=1",$p_invoice)->result_array();
            
            if(empty($invoice)) {
                    return "Proforma Invoice not found or Invoice already cancelled"; 
            }
                    
                    
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
//                $this->session->set_flashdata("erp_pop_info","Proforma Invoice cancelled");
            
            
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
            
            $output .= "Proforma Invoice-$p_invoice cancelled under $transid transaction";
            return $output;
            //redirect("admin/proforma_invoice/$p_invoice");
    }
    
}

?>
