<?php 

class Erpmodel extends Model
{
	
	function __construct()
	{
		parent::__construct();
	}
        
        //STOCK UPDATE CODE
        
        /**
        * function to update product stock enry and log accordingly
        */
       function _upd_product_stock($prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg='')
       {
           $user = $this->erpm->auth();

           if(!$prod_id)
               show_error("Invalid product 0 given");

           if(!$p_stk_id)
           {
               // check if product stock entry is availble
               if(!$this->db->query("select count(*) as t from t_stock_info where product_id = ? and available_qty >= 0 ",$prod_id)->row()->t)
               {
                   // create stock entry and update log for new insertion
                   $p_stk_id = $this->_create_stock_entry($prod_id,$mrp,$bc,$loc_id,$rb_id);
               }else
               {

                   // process stock info to get most relavent stock entry by barcode,mrp,location
                   $stk_det_res = $this->db->query("select stock_id,product_id,mrp,product_barcode,bc_match,
                                                               length(product_barcode) as bc_len,length(?) as i_bc_len,
                                                               location_id,rack_bin_id,available_qty,sum(mrp_match+bc_match) as r from (
                                                           select a.stock_id,a.product_id,mrp,if(mrp=?,1,0) as mrp_match,
                                                               product_barcode,if(product_barcode=?,1,0) as bc_match,
                                                               location_id,rack_bin_id,available_qty
                                                               from t_stock_info a
                                                               where product_id = ? and available_qty >= 0 and location_id=? and rack_bin_id= ?
                                                               having mrp_match and if(length(?),(length(product_barcode) = 0 or bc_match = 1),1)
                                                               order by mrp_match desc,bc_match desc ) as g
                                                           group by stock_id
                                                           order by r desc",array($bc,$mrp,$bc,$prod_id,$loc_id,$rb_id,$bc));
//                   echo $this->db->last_query();die();
                   
                   // if no data found create new stock entry Considering mrp for the product is not found
                   if(!$stk_det_res->num_rows())
                   {
                       $this->_create_stock_entry($prod_id,$mrp,$bc,$loc_id,$rb_id);
                   }
                   else
                   {
                       // Product stock relavent data fetched which has matching mrp with stock mrp
                       $stk_det = $stk_det_res->row_array();

                       // update stock with the requested qty where best match stock id is known
                       $p_stk_id = $stk_det['stock_id'];

                       if(strlen($bc) > 0 &&  $stk_det['bc_length'] == 0)
                       {
                           // update product stock with the given barcode for suggestion.
                           $this->db->query("update t_stock_info set product_barcode = ?,modified_by=?,modified_on=now() where stock_id = ? ",array($bc,$user['userid'],$p_stk_id));
                       }

                   }
               }
           }

           // update product stock available qty by stock id
           // update stock log

           if(!$qty)
               return $p_stk_id;

           $this->db->query("update t_stock_info set available_qty=available_qty".($stk_movtype==1?"+":"-")."?,modified_by=?,modified_on=now() where stock_id = ? and available_qty >= 0 ",array($qty,$user['userid'],$p_stk_id));

           // if by grn
           $ins_data = array();
           $ins_data['product_id'] = $prod_id;
           $ins_data['update_type'] = $stk_movtype;

           if($update_by == 1)
               $ins_data['p_invoice_id'] = $update_by_refid; // stock update by proforma invoice
           else if($update_by == 2)
               $ins_data['corp_invoice_id'] = $update_by_refid; // stock update by corp invoicing
           else if($update_by == 3)
               $ins_data['invoice_id'] = $update_by_refid; // stock update by invoice cancel
           else if($update_by == 4)
               $ins_data['grn_id'] = $update_by_refid; // stock update by stock intake
           else if($update_by == 5)
               $ins_data['voucher_book_slno'] = $update_by_refid; // stock update by voucher book
           else if($update_by == 6)
               $ins_data['return_prod_id'] = $update_by_refid; // stock update by product return ref id

           $ins_data['qty'] = $qty;
           $ins_data['current_stock'] = $this->_get_product_stock($prod_id);
           $ins_data['msg'] = $msg;
           $ins_data['mrp_change_updated'] = $mrp_change_updated;
           $ins_data['stock_info_id'] = $p_stk_id;
           $ins_data['stock_qty'] = $this->_get_product_stock_bystkid($p_stk_id);
           $ins_data['created_on'] = cur_datetime();
           $ins_data['created_by'] = $user['userid'];

           $this->db->insert("t_stock_update_log",$ins_data);
           if($this->db->affected_rows() == 1)
               return true;

            return false;

        }

       /**
        * function to get product current stock from table
        */
       function _get_product_stock($product_id = 0)
       {
           return @$this->db->query("select sum(available_qty) as t from t_stock_info where product_id = ? and available_qty >= 0 ",$product_id)->row()->t;
       }

       /**
        * function to get product current stock from table
        */
       function _get_product_stock_bystkid($stock_id = 0)
       {
           return @$this->db->query("select available_qty as t from t_stock_info where stock_id = ? and available_qty >= 0 ",$stock_id)->row()->t;
       }

       /**
        * function to create stock entry by product,mrp,location,barcode
        */
       function _create_stock_entry($prod_id=0,$mrp=0,$barcode='',$loc_id=0,$rb_id=0)
       {
           $user = $this->erpm->auth();

           $inp = array();
           $inp['product_id'] = $prod_id;
           $inp['mrp'] = $mrp;
           $inp['location_id'] = $loc_id;
           $inp['rack_bin_id'] = $rb_id;
           $inp['product_barcode'] = $barcode;
           $inp['available_qty'] = 0;
           $inp['created_by'] = $user['userid'];
           $inp['created_on'] = cur_datetime();

           $p_stk_id = $this->db->insert("t_stock_info",$inp);

           return $p_stk_id;
       }
       
        //END STOCK UPDATE CODE
        
        /**
         * Function to save assigned user
         */
        function do_save_assign_user() {
            foreach(array("stream_id","userid") as $i)
                $$i=$this->input->post($i);
            
            $info=$this->db->query("select su.stream_id,su.user_id,su.access,su.created_by from m_stream_users su where su.user_id=? and su.stream_id=?",array($userid,$stream_id))->result_array();
            if(count($info[0])<1) {
                $read=1;$st_status=1;
                $created_by=$user['userid'];
                $created_time=time();
                $this->db->query("insert into m_stream_users(stream_id,user_id,access,is_active,created_by,created_on) values(?,?,?,?,?,?)",array($stream_id,$userid,$read,$st_status,$created_by,$created_time));
            }
        
//                if(isset($_POST['permissions_w'])) { 
//                $write=2;
//                      foreach($_POST['permissions_w'] as $userid) {
//                        $this->db->query("insert into m_stream_users(stream_id,user_id,access,is_active,created_by,created_on) values(?,?,?,?,?,?)",array($streamid,$userid,$write,$status,$created_by,$created_time));
//                      }
//                }
    }
    /**
     * Function to remove assigned user
     */
    function do_remove_assign_user() {
        foreach(array("stream_id","userid") as $i)
            $$i=$this->input->post($i);
        $this->db->query("delete from `m_stream_users` where `stream_id`=? and `user_id`=?",array($stream_id,$userid));
    }

    /**
     * Function to descriptions
     * @param type $value
     * @return type
     */
    function prep($value){
        $magic_quotes_active = get_magic_quotes_gpc();
        if (!$magic_quotes_active){
            $value = addslashes(trim($value));
        }
        return $value;
    }
          
        /**
         * Function to add post replies using postid
         * @param type $postid
         */
        function do_store_post_reply($post_id) {
            //            echo '<pre>'; print_r($_POST); die();
            foreach(array("description","replied_by") as $i)
			$$i=$this->input->post($i);
            $replied_on = time();$status=1;
            $this->db->query("insert into m_stream_post_reply(description,post_id,replied_by,replied_on,status) values(?,?,?,?,?)",array($this->prep($description),$post_id,$replied_by,$replied_on,$status));
        }
        
        /**
         * Function to add post to stream
         */
        function do_stream_post($user) 
        {
        	$this->load->library('email');
			
            foreach(array("description","stream_id","user_id","assigned_to") as $i)
				$$i=$this->input->post($i);
            $created_time = time();$status=1;
            
            $stream_title = ucwords($this->db->query("select title from m_streams where id = ? ",$stream_id)->row()->title);            

            $this->db->query('insert into m_stream_posts(description,stream_id,status,posted_by,posted_on) values(?,?,?,?,?)',array($this->prep($description),$stream_id,$status,$user_id,$created_time));
            $post_id=$this->db->insert_id();
            if(isset($assigned_to)) 
            {
                foreach($assigned_to as $assignuserid) 
                {
                   // if($assignuserid=='00') {
                        $this->db->query("insert into m_stream_post_assigned_users(userid,post_id,streamid,assigned_userid,assigned_on,active) values(?,?,?,?,?,?)",array($user_id,$post_id,$stream_id,$assignuserid,$created_time,$status));
                 //   }
                        $arr_assiuser=$this->db->query("select name,email from king_admin where id=?",$assignuserid)->row_array();
                        $arr_adminuser=$this->db->query("select name,email from king_admin where id=?",$user['userid'])->row_array();
						
						
						$message = '<h3>Hi '.($arr_adminuser['name']).' </h3>
									 
									<p>
										New Message under <b>'.$stream_title.'</b> Stream, posted by <b>'.$arr_adminuser['name'].'</b>
									</p>	
									
									<blockquote style="background:#f7f7f7;padding:10px;">
									'.(nl2br($this->prep($description))).'
									</blockquote>
									<br>
									<br>
									<b>Storeking</b> 
								';
						
                        $this->_notifybymail(array($arr_assiuser['email']),"$stream_title Stream - New Message Posted from ".$arr_adminuser['name'],$message,"Storeking Streams");
						
						$this->db->query("update m_stream_post_assigned_users set mail_sent=1 where userid=? and post_id=? and streamid=? and assigned_userid=?",array($user_id,$post_id,$stream_id,$assignuserid));		
                }
            }
        }


	function _notifybymail($to,$subj,$message,$fromname="Support",$from='support@snapittoday.com')
	{
		/*
		$config['protocol'] = 'sendmail';
		$config['mailpath'] = '/usr/sbin/sendmail';
		$config['mailtype'] = 'html';
		$config['charset'] = 'utf8';
		$config['wordwrap'] = TRUE;
		*/
		
		$this->email->clear();
		
		$config = array(
			    'protocol' => 'smtp',
			    'smtp_host' => 'ssl://email-smtp.us-east-1.amazonaws.com',
			    'smtp_user' => 'AKIAIIIJ7TIURYRQPZTA',
			    'smtp_pass' => 'ArI01zUQ7iwXYnHNfH3GApcBUWSE0v+b5qhhR2zK2B68',
			    'smtp_port' => 465,
			);
		$config['mailtype']="html";	
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($from,$fromname);
		$this->email->to($to); 
		
		$this->email->subject($subj);
		$this->email->message($message);	
		
		$this->email->send();
		
		//echo $this->email->print_debugger();
 
	}


	/**
     * Function to add streams
     * @param type $user
     */
    function do_updatestream($user)
	{
            $modified_by=$user['userid'];
                foreach(array("stream_id","st_title","st_description","st_status") as $i)
			$$i=$this->input->post($i);
                if($st_title=='') die("Please enter title");
		$modified_time = time();
                $this->db->query("update m_streams set title=?,description=?,modified_by=?,modified_time=?,status=? where id=?",array($this->prep($st_title),$this->prep($st_description),$modified_by,$modified_time,$st_status,$stream_id));
                
		$this->session->set_flashdata("erp_pop_info","Stream Updated");
		redirect("admin/streams");
	}
        
        
        /**
         * Function to add streams
         * @param type $user
         */
        function do_addstream($user)
	{
                $created_by=$user['userid'];
                foreach(array("st_title","st_description") as $i)
			$$i=$this->input->post($i);
                if($st_title=='') die("Please enter title");
		$created_time = time();
                $this->db->query("insert into m_streams(title,description,created_by,created_time,status) values(?,?,?,?,?)",array($this->prep($st_title),$this->prep($st_description),$created_by,$created_time,1));
                $streamid=$this->db->insert_id();
                
                $read=1; $write=2; $status=1;
                if(isset($_POST['permissions_r'])) {
                    foreach($_POST['permissions_r'] as $userid)
                        $this->db->query("insert into m_stream_users(stream_id,user_id,access,is_active,created_by,created_on) values(?,?,?,?,?,?)",array($streamid,$userid,$read,$status,$created_by,$created_time));
                }
                if(isset($_POST['permissions_w'])) {
                    foreach($_POST['permissions_w'] as $userid) 
                        $this->db->query("insert into m_stream_users(stream_id,user_id,access,is_active,created_by,created_on) values(?,?,?,?,?,?)",array($streamid,$userid,$write,$status,$created_by,$created_time));
                }
		$this->session->set_flashdata("erp_pop_info","New stream created");
		redirect("admin/streams");
	}
        
	function get_prodpendingorderqty($product_id)
	{
		$sql = "(
						select go.product_id,'grp' as prod_type,sum(quantity*b.qty) as req_qty					
						from king_orders a
						join m_product_group_deal_link b on a.itemid = b.itemid
						join products_group_orders go on a.id = go.order_id 
						where status = 0 and go.product_id = ? 
						having req_qty is not null 
					)union
					(
						select b.product_id,'grp' as prod_type,sum(quantity*b.qty) as req_qty					
						from king_orders a
						left join m_product_deal_link b on a.itemid = b.itemid 
						where status = 0 
						and b.product_id = ? 
						having req_qty is not null 
					)
				";
		$res=$this->db->query($sql,array($product_id,$product_id));
		if($res->num_rows())
		{
			return $res->row()->req_qty;
		}
		return 0;
	}
	
	function searchvendorproducts($vid,$q)
	{
		$sql="select p.*,sum(s.available_qty) as stock from m_product_info p left outer join t_stock_info s on s.product_id=p.product_id where (p.product_name like ? or p.barcode = ? ) and (p.product_id in (select product_id from m_vendor_product_link where vendor_id=?) or p.brand_id in (select brand_id from m_vendor_brand_link where vendor_id=?)) group by p.product_id order by p.product_name asc";
		$ret=$this->db->query($sql,array("%$q%",$q,$vid,$vid))->result_array();
		foreach($ret as $i=>$r)
		{
			$ret[$i]['margin']=$this->db->query("select brand_margin from m_vendor_brand_link where vendor_id=? and brand_id=?",array($vid,$r['brand_id']))->row()->brand_margin;
			$ret[$i]['orders']=$this->get_prodpendingorderqty($ret[$i]['product_id']);
		}
		return $ret;
	}
	
	function getticket($id)
	{
		$sql="select u.name as user,t.*,a.name as assignedto from support_tickets t left outer join king_admin a on a.id=t.assigned_to left outer join king_users u on u.userid=t.user_id";
		return $this->db->query("$sql where t.ticket_id=?",$id)->row_array();
	}
	
	function do_updateadminuser($uid)
	{
		foreach(array("roles","name","email","account_blocked") as $i)
			$$i=$this->input->post($i);
			
		$account_blocked = $account_blocked*1;
			
		$access=$roles[0];
		foreach($roles as $i=>$r)
		{
			$r=(double)$r;
			if($i!=0)
				$access=($access|$r);
		}
		$this->db->query("update king_admin set access=?,name=?,email=?,account_blocked=? where id=? limit 1",array($access,$name,$email,$account_blocked,$uid));
		$this->session->set_flashdata("erp_pop_info","Admin access updated");
		redirect("admin/adminusers");
	}
	
	function do_addadminuser()
	{
		foreach(array("roles","name","username","email") as $i)
			$$i=$this->input->post($i);
		$access=0;
		if(!empty($roles))
			$access=$roles[0];
		else $roles=array();
		foreach($roles as $i=>$r)
		{
			$r=(double)$r;
			if($i!=0)
				$access=((double)$access|$r);
		}
		$password=randomChars(6);
		$this->db->query("insert into king_admin(user_id,username,name,email,access,password) values(?,?,?,?,?,?)",array(md5($username),$username,$name,$email,$access,md5($password)));
		$this->vkm->email($email,"Your Snapittoday ERP account","Hi $name,<br><br>Your account to access snapitoday ERP is created successfully.<br>Username : $username<br>Password : $password<br><br>ERP Team Snapittoday");
		$this->session->set_flashdata("erp_pop_info","New Admin user created");
		redirect("admin/adminusers");
	}
	
	function depr_do_proforma_invoice($list)
	{
		$invoices=array();
		if(empty($list))
			return $invoices;
		$orders=$this->db->query("select * from king_orders where id in ('".implode("','",$list)."') order by sno asc")->result_array();
		$c_invno=$this->db->query("select p_invoice_no as invoice_no from proforma_invoices where is_b2b=0 order by id desc limit 1")->row_array();
		if(empty($c_invno))
			$c_invno=10000;
		else $c_invno=$c_invno['invoice_no'];
		$r_pinvno=$this->db->query("select p_invoice_no as invoice_no from proforma_invoices where is_b2b=1 order by id desc limit 1")->row_array();
		if(empty($r_pinvno))
			$p_invno=100000;
		else $p_invno=$r_pinvno['invoice_no'];
		$done_transids=array();
		foreach($orders as $o)
		{
			
			$transid=$o['transid'];
			
			if(!isset($all_trans[$transid]))
				$all_trans[$transid]=$this->db->query("select * from king_transactions where transid=?",$transid)->row_array();
			$trans=$all_trans[$transid];
 
			$price_amount=$this->db->query("select sum(i.price*o.quantity) as s from king_orders o join king_dealitems i on i.id=o.itemid where o.transid=?",$transid)->row()->s;

			$mrp_amount=$this->db->query("select sum(i.orgprice*o.quantity) as s from king_orders o join king_dealitems i on i.id=o.itemid where o.transid=?",$transid)->row()->s;
			
			$discount=$mrp_amount-$price_amount;
			
			if(!in_array($o['transid'], $done_transids))
			{
				$done_transids[]=$o['transid'];
				if($this->db->query("select is_pnh from king_transactions where transid=?",$transid)->row()->is_pnh=="1")
				{
					$is_b2b=1;
					$invno=$p_invno++;
				}
				else
				{
					$is_b2b=0;
					$invno=$c_invno++;
				}
				$invno++;
				$invoices[]=$invno;
			}
			$oid=$o['id'];

			$orgprice = $o['i_orgprice'];
			$itemdet=$this->db->query("select orgprice,price,nlc,phc,tax,service_tax from king_dealitems where id=?",$o['itemid'])->row_array();

			$o_discount=$orgprice*$discount/$mrp_amount;
			
			$cod=$ship=0;
			
			if($this->db->query("select 1 from king_invoice where transid=?",$o['transid'])->num_rows()==0){
				$cod=$trans['cod'];
				$ship=$trans['ship'];	
			}
			 
			$p_discount = $o['i_discount']+$o['i_coup_discount'];
			
			$offer_price = ($o['i_orgprice'])-$p_discount;
			
			$nlc = round(($offer_price*100/((1+PHC_PERCENT/100)*100)),2);
			$phc = $offer_price-$nlc;
			$tax = $itemdet['tax'];
							
			$this->db->query("insert into proforma_invoices(transid,order_id,p_invoice_no,phc,nlc,tax,service_tax,mrp,discount,cod,ship,invoice_status,createdon,is_b2b) values(?,?,?,?,?,?,?,?,?,?,?,1,?,?)",array($transid,$oid,$invno,$phc,$nlc,$tax,PRODUCT_SERVICE_TAX*100,$orgprice,$p_discount,$cod,$ship,time(),$is_b2b));
			$this->db->query("update king_orders set status=1,actiontime=? where id=? limit 1",array(time(),$o['id']));
		}
		$trans=$this->db->query("select p_invoice_no as invoice_no,transid,count(order_id) as c from proforma_invoices where p_invoice_no in ('".implode("','",$invoices)."') group by transid")->result_array();
		foreach($trans as $tran)
			$this->erpm->do_trans_changelog($tran['transid'],$tran['c']." order(s) processed in Proforma Invoice: {$tran['invoice_no']}");
		return $invoices;
	}
	
	function depr_do_invoice($list)
	{
		$invoices=array();
		if(empty($list))
			return $invoices;
		$orders=$this->db->query("select * from king_orders where id in ('".implode("','",$list)."') order by sno asc")->result_array();
		$c_invno=$this->db->query("select invoice_no from king_invoice where is_b2b=0 order by id desc limit 1")->row()->invoice_no;
		$r_pinvno=$this->db->query("select invoice_no from king_invoice where is_b2b=1 order by id desc limit 1")->row_array();
		if(empty($r_pinvno))
			$p_invno=200000;
		else $p_invno=$r_pinvno['invoice_no'];
		$done_transids=array();
		foreach($orders as $o)
		{
			
			$transid=$o['transid'];
			
			if(!isset($all_trans[$transid]))
				$all_trans[$transid]=$this->db->query("select * from king_transactions where transid=?",$transid)->row_array();
			$trans=$all_trans[$transid];
 
			$price_amount=$this->db->query("select sum(i.price*o.quantity) as s from king_orders o join king_dealitems i on i.id=o.itemid where o.transid=?",$transid)->row()->s;

			$mrp_amount=$this->db->query("select sum(i.orgprice*o.quantity) as s from king_orders o join king_dealitems i on i.id=o.itemid where o.transid=?",$transid)->row()->s;
			
			$discount=$mrp_amount-$price_amount;
			
			if(!in_array($o['transid'], $done_transids))
			{
				$done_transids[]=$o['transid'];
				if($this->db->query("select is_pnh from king_transactions where transid=?",$transid)->row()->is_pnh=="1")
				{
					$is_b2b=1;
					$invno=$p_invno++;
				}
				else
				{
					$is_b2b=0;
					$invno=$c_invno++;
				}
				$invno++;
				$invoices[]=$invno;
			}
			$oid=$o['id'];

			$orgprice = $o['i_orgprice'];
			$itemdet=$this->db->query("select orgprice,price,nlc,phc,tax,service_tax from king_dealitems where id=?",$o['itemid'])->row_array();

			$o_discount=$orgprice*$discount/$mrp_amount;
			
			$cod=$ship=0;
			
			if($this->db->query("select 1 from king_invoice where transid=?",$o['transid'])->num_rows()==0){
				$cod=$trans['cod'];
				$ship=$trans['ship'];	
			}
			 
			$p_discount = $o['i_discount']+$o['i_coup_discount'];
			
			$offer_price = ($o['i_orgprice'])-$p_discount;
			
			$nlc = round(($offer_price*100/((1+PHC_PERCENT/100)*100)),2);
			$phc = $offer_price-$nlc;
			$tax = $itemdet['tax'];
							
			$this->db->query("insert into king_invoice(transid,order_id,invoice_no,phc,nlc,tax,service_tax,mrp,discount,cod,ship,invoice_status,createdon,is_b2b) values(?,?,?,?,?,?,?,?,?,?,?,1,?,?)",array($transid,$oid,$invno,$phc,$nlc,$tax,PRODUCT_SERVICE_TAX*100,$orgprice,$p_discount,$cod,$ship,time(),$is_b2b));
			$this->db->query("update king_orders set status=1,actiontime=? where id=? limit 1",array(time(),$o['id']));
		}
		$trans=$this->db->query("select invoice_no,transid,count(order_id) as c from king_invoice where invoice_no in ('".implode("','",$invoices)."') group by transid")->result_array();
		foreach($trans as $tran)
			$this->erpm->do_trans_changelog($tran['transid'],"Invoice ({$tran['invoice_no']}) created for ".$tran['c']." order(s)");
		return $invoices;
	}
	
	/**
	 * function to generate distinct proforma invoice no 
	 * @param unknown_type $is_pnh
	 * @return unknown
	 */
	function _get_uniq_proformainvoiceno($transid,$is_pnh = 0)
	{
		while(true)
		{
			$new_p_invno = $this->db->query("select max(p_invoice_no)+1 as p_invoice_no from t_trans_proforma_invoice_marker where is_pnh = ? ",$is_pnh)->row()->p_invoice_no;
			@$this->db->query("insert into t_trans_proforma_invoice_marker (transid,p_invoice_no,is_pnh,created_on) values(?,?,?,now())",array($transid,$new_p_invno,$is_pnh));
			if($this->db->affected_rows() == 1)
			{
				break;
			}
		}
		
		return $new_p_invno;
	}
	
	/**
	 * function to generate actual invoice no  
	 * @param $is_pnh
	 */
	function _get_uniq_invoiceno($transid,$is_pnh = 0)
	{
		while(true)
		{
			$new_invno = $this->db->query("select max(invoice_no)+1 as invoice_no from t_trans_invoice_marker where is_pnh = ? ",$is_pnh)->row()->invoice_no;
			@$this->db->query("insert into t_trans_invoice_marker (transid,invoice_no,is_pnh,created_on) values(?,?,?,now())",array($transid,$new_invno,$is_pnh));
			if($this->db->affected_rows() == 1)
			{
				break;
			}
		}
		
		return $new_invno;
	}
	
	
	
	function do_proforma_invoice($list)
	{
		$invoices=array();
		if(empty($list))
			return $invoices;
			
		$orders=$this->db->query("select i_discount,i_coup_discount,i.tax,t.is_pnh,o.transid,o.id,o.itemid,i_orgprice,i_price,t.cod,t.ship,o.quantity,sum(i.orgprice*o.quantity) as mrp_amt,sum(i.price*o.quantity) as price_amt 
										from king_orders o 
										join king_dealitems i on i.id=o.itemid 
										join king_transactions t on t.transid = o.transid
										where o.id in ('".implode("','",$list)."')
										group by o.id order by o.sno asc")->result_array();
		
		
		$trans_ord_invnos = array();
		foreach($orders as $o)
		{
			 $transid=$o['transid'];
			 $oid=$o['id'];
			 $cod=$ship=0;
			 
			 if(!isset($trans_ord_invnos[$transid]))
			 {
			 	$trans_ord_invnos[$transid] = array();
				
			 	$trans_ord_invnos[$transid]['invno'] = $this->_get_uniq_proformainvoiceno($transid,$o['is_pnh']);
			 	
			 	$trans_ord_invnos[$transid]['trans'] = $this->db->query("select t.cod,t.ship,sum(i.orgprice*o.quantity) as ttl_mrp,sum(i.price*o.quantity) as ttl_price from king_orders o join king_dealitems i on i.id=o.itemid join king_transactions t on t.transid=o.transid where o.transid=?",$transid)->row_array();
			 	
				if($this->db->query("select count(*) as tt from proforma_invoices where transid=?",$transid)->row()->tt==0){
					$cod=$trans_ord_invnos[$transid]['trans']['cod'];
					$ship=$trans_ord_invnos[$transid]['trans']['ship'];	
				}
				array_push($invoices,$trans_ord_invnos[$transid]['invno']);
			 }
			 
			 
			 $mrp_amount = $trans_ord_invnos[$transid]['trans']['ttl_mrp'];
			 $price_amount = $trans_ord_invnos[$transid]['trans']['ttl_price'];
			 
			 $invno = $trans_ord_invnos[$transid]['invno'];

			 $discount = $mrp_amount-$price_amount;
			
			 $orgprice = $o['i_orgprice'];
			 $o_discount=$orgprice*$discount/$mrp_amount;
				
			 	
				 
			$p_discount = $o['i_discount']+$o['i_coup_discount'];
			$offer_price = ($o['i_orgprice'])-$p_discount;
			
			$nlc = round(($offer_price*100/((1+PHC_PERCENT/100)*100)),2);
			$phc = $offer_price-$nlc;
			$tax = $o['tax'];

			$is_b2b = $o['is_pnh']?1:0;	
			
			$this->db->query("insert into proforma_invoices(transid,order_id,p_invoice_no,phc,nlc,tax,service_tax,mrp,discount,cod,ship,invoice_status,createdon,is_b2b) values(?,?,?,?,?,?,?,?,?,?,?,1,?,?)",array($transid,$oid,$invno,$phc,$nlc,$tax,PRODUCT_SERVICE_TAX*100,$orgprice,$p_discount,$cod,$ship,time(),$is_b2b));
			$this->db->query("update king_orders set status=1,actiontime=? where id=? and transid = ? limit 1",array(time(),$o['id'],$transid));	
				
		}
		$trans=$this->db->query("select p_invoice_no as invoice_no,transid,count(order_id) as c from proforma_invoices where p_invoice_no in ('".implode("','",$invoices)."') group by transid")->result_array();
		foreach($trans as $tran)
			$this->erpm->do_trans_changelog($tran['transid'],$tran['c']." order(s) processed in Proforma Invoice: {$tran['invoice_no']}");
		return $invoices;
	}
	
	function do_invoice($list)
	{
		$invoices=array();
		if(empty($list))
			return $invoices;
		
		$user = $this->erpm->auth();

		$orders=$this->db->query("select d.catid,d.menuid,d.brandid,t.is_pnh,i_discount,i_coup_discount,i.tax,t.is_pnh,o.transid,o.id,o.itemid,i_orgprice,i_price,t.cod,t.ship,o.quantity,sum(i.orgprice*o.quantity) as mrp_amt,sum(i.price*o.quantity) as price_amt 
										from king_orders o 
										join king_dealitems i on i.id=o.itemid 
										join king_transactions t on t.transid = o.transid
										join king_deals d on d.dealid = i.dealid 
										where o.id in ('".implode("','",$list)."')
										group by o.id order by o.sno asc")->result_array();

		$trans_ord_invnos = array();
		foreach($orders as $o)
		{
			 $transid=$o['transid'];
			 $oid=$o['id'];
			 $cod=$ship=0;
			 
			 if(!isset($trans_ord_invnos[$transid]))
			 {
			 	$trans_ord_invnos[$transid] = array();

				$trans_ord_invnos[$transid]['trans'] = $this->db->query("select t.cod,t.ship,sum(i.orgprice*o.quantity) as ttl_mrp,sum(i.price*o.quantity) as ttl_price from king_orders o join king_dealitems i on i.id=o.itemid join king_transactions t on t.transid=o.transid where o.transid=?",$transid)->row_array();
			 	
			 	
				if($this->db->query("select count(*) as tt from king_invoice where transid=?",$transid)->row()->tt==0){
					$cod=$trans_ord_invnos[$transid]['trans']['cod'];
					$ship=$trans_ord_invnos[$transid]['trans']['ship'];	
				}

				$trans_ord_invnos[$transid]['invno'] = $this->_get_uniq_invoiceno($transid,$o['is_pnh']);;
				array_push($invoices,$trans_ord_invnos[$transid]['invno']);
			 }
			 
			 
			 $mrp_amount = $trans_ord_invnos[$transid]['trans']['ttl_mrp'];
			 $price_amount = $trans_ord_invnos[$transid]['trans']['ttl_price'];
			 
			 $invno = $trans_ord_invnos[$transid]['invno'];
			 
			 $discount = $mrp_amount-$price_amount;
			
			 $orgprice = $o['i_orgprice'];
			 $o_discount=$orgprice*$discount/$mrp_amount;
				
			// check if product menu brand considered for MAP[minimum adverstise price] price config
			
			
			$is_dealconsidered_for_map = $this->erpm->check_dealformap_price($o['menuid'],$o['brandid'],$o['catid']);
			if($is_dealconsidered_for_map)
			{
				$p_discount = $o['i_discount'];
				$credit_note_amt = $o['i_coup_discount'];
			}else
			{
				$p_discount = $o['i_discount']+$o['i_coup_discount'];
				$credit_note_amt = 0;				
			}
			
			$offer_price = ($o['i_orgprice'])-$p_discount;
			$nlc = round(($offer_price*100/((1+PHC_PERCENT/100)*100)),2);
			$phc = $offer_price-$nlc;
			$tax = $o['tax'];

			$is_b2b = $o['is_pnh']?1:0;	
			
			$this->db->query("insert into king_invoice(transid,order_id,invoice_no,phc,nlc,tax,service_tax,mrp,discount,credit_note_amt,cod,ship,invoice_status,createdon,is_b2b) values(?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)",array($transid,$oid,$invno,$phc,$nlc,$tax,PRODUCT_SERVICE_TAX*100,$orgprice,$p_discount,$credit_note_amt,$cod,$ship,time(),$is_b2b));
			$this->db->query("update king_orders set status=1,actiontime=? where id=? limit 1",array(time(),$o['id']));	
				
		}
		
		
		
		$trans_det=$this->db->query("select is_pnh,franchise_id,a.invoice_no,a.transid,c.quantity,sum((mrp-discount)*c.quantity) as inv_amt,count(order_id) as c 
										from king_invoice a 
										join king_transactions b on a.transid = b.transid 
										join king_orders c on c.id = a.order_id 
										where a.invoice_no in ('".implode("','",$invoices)."') group by a.transid")->result_array();
		
		
		
		foreach($trans_det as $tran)
		{
			$this->erpm->do_trans_changelog($tran['transid'],"Invoice ({$tran['invoice_no']}) created for ".$tran['c']." order(s)");
		}
			
		return $invoices;
	}
	
	/*
	 * function to check if product menu brand considered for MAP[minimum adverstise price] price config
	 */
	function check_dealformap_price($menuid,$brandid=0,$catid=0)
	{
		$brandid = 0;
		return $this->db->query("select count(*) as t from m_brand_config_map_price where (menuid = ? and brandid = ? and catid = ? ) and is_active = 1 ",array($menuid,$brandid,$catid))->row()->t; 
	}
	
	 
	
	function getcouriers()
	{
		$cs=$this->db->query("select * from m_courier_info order by courier_name asc")->result_array();
		foreach($cs as $i=>$c)
		{
			$cs[$i]['pincodes']=$this->db->query("select count(1) as l from m_courier_pincodes where courier_id=?",$c['courier_id'])->row()->l;
			$awb=$this->db->query("select * from m_courier_awb_series where courier_id=?",$c['courier_id'])->row_array();
			$cs[$i]['awb']=$awb['awb_no_prefix'].$awb['awb_current_no'].$awb['awb_no_suffix'];
			$cs[$i]['rem_awb']=$awb['awb_end_no']-$awb['awb_current_no'];
		}
		return $cs;
	}
	
	function getpincodesforcourier($cid)
	{
		$raw=$this->db->query("select pincode from m_courier_pincodes where courier_id=? and status=1 order by pincode asc",$cid)->result_array();
		$pincodes=array();
		foreach($raw as $r)
			$pincodes[]=$r['pincode'];
		return $pincodes;
	}
	
	function do_updatepincodesforcourier($courier_id,$pincodes)
	{
		if(!is_array($pincodes))
			$pincodes=explode(",",$pincodes);
		$pincodes=array_unique($pincodes);
		$this->db->query("delete from m_courier_pincodes where courier_id=?",$courier_id);
		foreach($pincodes as $p)
			$this->db->query("insert into m_courier_pincodes(courier_id,pincode,status) values(?,?,1)",array($courier_id,$p));
	}
	
	function do_updateawb($cid)
	{
		foreach(array("courier","name","awb_prefix","awb_suffix","awb_start","awb_end","pincodes") as $inp)
			$$inp=$_POST[$inp];
		$courier=$cid;
		$this->db->query("delete from m_courier_awb_series where courier_id=?",$courier);
		$this->db->query("insert into m_courier_awb_series(courier_id,awb_no_prefix,awb_no_suffix,awb_start_no,awb_end_no,awb_current_no,is_active) values(?,?,?,?,?,?,1)",array($courier,$awb_prefix,$awb_suffix,$awb_start,$awb_end,$awb_start));
	}
	
	function do_addcourier()
	{
		foreach(array("name","awb_prefix","awb_suffix","awb_start","awb_end","pincodes") as $inp)
			$$inp=$_POST[$inp];
		$sql="insert into m_courier_info(courier_name) values(?)";
		$this->db->query($sql,$name);
		$courier_id=$this->db->insert_id();
		$pincodes=explode(",",$pincodes);
		$this->erpm->do_updatepincodesforcourier($courier_id,$pincodes);
		$this->db->query("insert into m_courier_awb_series(courier_id,awb_no_prefix,awb_no_suffix,awb_start_no,awb_end_no,awb_current_no,is_active) values(?,?,?,?,?,?,1)",array($courier_id,$awb_prefix,$awb_suffix,$awb_start,$awb_end,$awb_start));
		$this->session->set_flashdata("erp_pop_info","New courier added");
		redirect("admin/courier");
	}
	
	function do_changepwd()
	{
		$user=$this->erpm->getadminuser();
		foreach(array("exp","new") as $i)
			$$i=$this->input->post($i);
//		$this->db->query("select 1 from king_admin where id=? and password=?",array($user['userid'],md5($exp)))->
	}
	
	function do_shipment_batch_process()
	{
		ini_set('memory_limit','512M');
		$i_transid=false;
		$ttl_num = $num=$this->input->post("num_orders");
		$process_partial=$this->input->post("process_partial");
		
		$user = $this->erpm->auth();
		 
		
		if(empty($num))
			show_error("Enter no of orders to process");
		$i_transid=$this->input->post("transid");

		$ordersby = strtolower($this->input->post("snp_pnh"));
		$en_date = $this->input->post("en_date");
		
		$down_import_summ = 0;
		$cond = '';
		$is_pnh=1;
		 
		if($ordersby == 'others')
		{
			$is_pnh=0;
			$cond .= ' and t.is_pnh = 0 ';
			$snp_pnh_part = $this->input->post("snp_pnh_part");
			if(count($snp_pnh_part))
			{
				$cond .= ' and partner_id in ('.implode(',',$snp_pnh_part).') ';	
			}else
			{
				show_error("Please select atleast one partner");
			}
			
			
			$process_orderby = $this->input->post('process_orderby');
			$by_brandid = $this->input->post('by_brandid');
			
			//$by_p_oids = $this->input->post('by_p_oids');
			if($process_orderby)
			{
				if(count($snp_pnh_part) == 1)
				{
					if($process_orderby == 1)
					{
						$down_import_summ = 1;
						$p_oids = $this->input->post('p_oids');
						
						$p_oids = (explode(',',$p_oids));
						$tmp_poids = array();
						foreach($p_oids as $t_poid)
						{
							if($t_poid)
								array_push($tmp_poids,'"'.$t_poid.'"');
						}
						$p_oids = implode(',',$tmp_poids); 
						
						if(!$p_oids)
						{
							show_error("No Partner Ordernos added.");	
						}else
						{
							$cond .=  ' and partner_reference_no in ('.$p_oids.')';
							$down_import_summ = 1;
							$ttl_num = count(explode(',',$p_oids));
						}
					}else 
					{
						if($by_brandid)
							$cond .=  ' and d.brandid = '.$by_brandid.' ';
						else 
							show_error("No brand selected");	
					}
					
				}else
				{
					show_error("Order ids can be processed only for one partner.");
				}
			}
			
			
		}else
		{
			$cond .= ' and t.is_pnh = 1 ';
			$is_pnh=1;
			
			$by_menu = $this->input->post('by_menu');
			if($by_menu)
			{
				$pmenu_idlist = $this->input->post('pmenu_id');
				if($pmenu_idlist)
				{
					$cond .= ' and menuid in ('.implode(',',$pmenu_idlist).') ';
				}else
				{
					show_error(" No menu selected");
				}
			}
		}
		
		$trans=array();
		$itemids=array();

		if($en_date)
		{
			list($ey,$em,$ed) = explode('-',$en_date);
			$en_date_ts = mktime(23,59,59,$em,$ed,$ey);
			$cond .= ' and t.init < '.$en_date_ts.' ';
		}
		
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
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as stock from t_stock_info where product_id in ('".implode("','",$productids)."') and available_qty >= 0 group by product_id")->result_array();
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
		 * */
		
		$invoices=$this->erpm->do_proforma_invoice($orders);
		
		$batch_id=0;
		$batch_inv_link = array();
		
		$ttl_invoices = count($invoices);
		if($ttl_invoices > $num)
			$ttl_batchs = ceil($ttl_invoices/$num);
		else 
			$ttl_batchs = 1;	
			
		$batch_remarks = $this->input->post('batch_remarks');
		
		if(!empty($invoices))
		{
			for($b=0;$b<$ttl_batchs;$b++)
			{
				$s = $b*$num;
				$ttl_inbatch = ((($s+$num) > $ttl_invoices)?$ttl_invoices-$s:$num);
				
				$this->db->query("insert into shipment_batch_process(num_orders,batch_remarks,created_on) values(?,?,?)",array($ttl_inbatch,$batch_remarks,date('Y-m-d H:i:s')));
				$batch_id=$this->db->insert_id();
				for($k=$s;$k<$s+$ttl_inbatch;$k++)
				{
					$inv = $invoices[$k];
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
				 
				$pen_qty = $total_qty;
				
				// query to fetch stock product ordered by exact mrp and followed by asc mrp.  	
				
				$sql = "select stock_id,product_id,available_qty,location_id,rack_bin_id,mrp,if((mrp-$omrp),1,0) as mrp_diff 
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
						 
							$pen_qty = $pen_qty-$reserv_qty;
							 
						// if all qty updated 
						if(!$pen_qty)	
							break;
						
					}
				}
				
				if(count($alloted_stock))
				{
					foreach($alloted_stock as $allot_stk)
					{
						$this->db->query("update t_stock_info set available_qty = available_qty-? where stock_id = ? ",array($allot_stk['qty'],$allot_stk['stock_info_id']));
						$this->db->insert("t_reserved_batch_stock",$allot_stk);
						$this->erpm->do_stock_log(0,$allot_stk['qty'],$p['product_id'],$invid,false,false,true,-1,0,0,$allot_stk['stock_info_id']);
					}
				}
			}
			
		}
		
		if($down_import_summ)
		{
			 
				$csv_oids = array();
				$csv_oids[] = '"Partner Reference no","Batch ID","Invoice no"';
				$p_oids = $this->input->post('p_oids');
				$p_oid_arr = explode(',',$p_oids);
				foreach($p_oid_arr as $p_oid)
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
				exit;
			 
		}else
		{
			if(!count($batch_inv_link))
				show_error("INSUFFICIENT STOCK TO PROCESS ANY ORDER");
			redirect("admin/batch/$batch_id");		
		}
		
	}
	
	function depr_do_shipment_batch_process()
	{
		$num=$this->input->post("num_orders");
		if(empty($num))
			show_error("Enter no of orders to process");
		$trans=array();
//		while(1)
		{
//			$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and t.batch_enabled=1 and o.status=0 join m_courier_pincodes mp on mp.pincode=o.ship_pincode or mp.pincode='999999' join m_courier_awb_series ma on ma.awb_end_no>ma.awb_current_no and ma.courier_id=mp.courier_id group by t.transid order by t.priority desc, t.init asc limit $num")->result_array();
			$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and t.batch_enabled=1 and o.status=0 group by t.transid order by t.priority desc, t.init asc limit $num")->result_array();
//			if(empty($raw_trans))
//				return;
			foreach($raw_trans as $t)
			{
				
				
/*	courier disabled			
 * 				$flag=false;
				$o=$this->db->query("select m.courier_id,o.ship_pincode,m.awb_end_no,m.awb_current_no from king_orders o join m_courier_pincodes p on p.pincode=o.ship_pincode or p.pincode='999999' join m_courier_awb_series m on m.courier_id=p.courier_id where o.transid=? and o.status=0 order by p.pincode asc limit 1",$t['transid'])->row_array();
				if(!isset($awbs[$o['courier_id']]))
					$awbs[$o['courier_id']]=$o['awb_current_no'];
				if($awbs[$o['courier_id']]<=$o['awb_end_no'])
				{
					$trans[]=$t['transid'];
					$awbs[$o['courier_id']]++;
				}
courier disable ends 
*/			
				
				$trans[]=$t['transid'];
//				if(count($trans)>=$num)
//					break;
			}
//			if(count($trans)>=$num)
//				break;
		}
		if(empty($trans))
			show_error("No orders to process");
		$porders=$this->db->query("select o.* from king_orders o join king_transactions t on t.transid=o.transid where o.status=0 and o.transid in ('".implode("','",$trans)."') order by t.priority desc, o.sno asc")->result_array();
		$items=array();
		foreach($porders as $o)
		{
			if(!in_array($o['itemid'],$items))
				$items[]=$o['itemid'];
		}
		foreach($items as $item)
		{
			$ps=$this->db->query("select itemid,product_id,qty from m_product_deal_link where itemid=?",$item)->result_array();
			$prods[$item]=array();
			foreach($ps as $p)
			{
				$prods[$p['itemid']][]=$p;
				$pids[]=$p['product_id'];
			}
		}
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as s from t_stock_info where product_id in ('".implode("','",$pids)."') group by product_id")->result_array();
		foreach($pids as $i)
			$stock[$i]=0;
		foreach($raw_stock as $s)
			$stock[$s['product_id']]=$s['s'];
		$orders=array();
		foreach($porders as $o)
		{
			$itemid=$o['itemid'];
			$f=true;
			foreach($prods[$itemid] as $p)
				if($stock[$p['product_id']]<$p['qty']*$o['quantity'])
				{
					$f=false;
					break;
				}
			if(!$f)
				continue;
			foreach($prods[$itemid] as $p)
				$stock[$p['product_id']]-=$p['qty']*$o['quantity'];
			$orders[]=$o['id'];
		}
		$batch_id=0;
		$invoices=$this->erpm->do_invoice($orders);
		if(!empty($invoices))
		{
			$this->db->query("insert into shipment_batch_process(num_orders,created_on) values(?,now())",array(count($invoices)));
			$batch_id=$this->db->insert_id();
			foreach($invoices as $inv)
			{
/*	courier disabled			
 * 				$order=$this->db->query("select mp.courier_id,o.ship_pincode from king_invoice i join king_orders o on o.id=i.order_id join m_courier_pincodes mp on mp.pincode=o.ship_pincode or mp.pincode='999999' order by mp.pincode asc limit 1")->row_array();
				$cid=$order['courier_id'];
				if(!isset($couriers[$cid]))
				{
					$courier=$this->db->query("select awb_end_no,awb_current_no from m_courier_awb_series where courier_id=?",$cid)->row_array();
					$couriers[$order['courier_id']]=$courier['awb_current_no'];
					$prefix[$cid]=$courier['awb_no_prefix'];
					$suffix[$cid]=$courier['awb_no_suffix'];
				}
				$awb="{$prefix[$cid]}{$couriers[$cid]}{$suffix[$cid]}";
	courier disable ends
*/	
				$cid=0;
				$awb="";
				
				$this->db->query("insert into shipment_batch_process_invoice_link(batch_id,invoice_no,courier_id,awb) values(?,?,?,?)",array($batch_id,$inv,$cid,$awb));

/*	courier disabled			
				$couriers[$cid]++;
			}
			foreach($couriers as $cid=>$awb)
				$this->db->query("update m_courier_awb_series set awb_current_no=? where courier_id=? limit 1",array($awb,$cid));
*/
			}
		}
		foreach($orders as $o)
		{
			$s_prods=$this->db->query("select p.product_id,p.qty,o.quantity from king_orders o join m_product_deal_link p on p.itemid=o.itemid where o.id=? order by o.sno asc",$o)->result_array();
			foreach($s_prods as $p)
				for($i=1;$i<=$p['quantity']*$p['qty'];$i++)
					$this->db->query("update t_stock_info set available_qty=available_qty-1 where product_id=? and available_qty!=0 order by stock_id asc limit 1",$p['product_id']);
		}
		return $batch_id;
	}
	
	function do_updateclient($cid)
	{
		$user=$this->erpm->getadminuser();
		foreach(array("name","addr1","addr2","locality","city","state","country","remarks","credit_limit","credit_days","cst","pan","vat","tax","c_name","c_design","c_mob1","c_mob2","c_email1","c_email2","c_fax") as $i)
			$$i=$this->input->post($i);
		$sql="update m_client_info set client_name=?,address_line1=?,address_line2=?,locality=?,city_name=?,state_name=?,country=?,credit_limit_amount=?,credit_days=?,cst_no=?,pan_no=?,vat_no=?,service_tax_no=?,remarks=?,modified_by=?,modified_on=now() where client_id=?"; 
		$this->db->query($sql,array($name,$addr1,$addr2,$locality,$city,$state,$country,$credit_limit,$credit_days,$cst,$pan,$vat,$tax,$remarks,$user['userid'],$cid));
			
		$this->db->query("delete from m_client_contacts_info where client_id=?",$cid);
		$sql="insert into m_client_contacts_info(client_id,contact_name,contact_designation,mobile_no_1,mobile_no_2,email_id_1,email_id_2,fax_no,created_by,created_on)
																					values($cid,?,?,?,?,?,?,?,?,now())";
		foreach($c_name as $i=>$c)
		{
			$inp=array($c,$c_design[$i],$c_mob1[$i],$c_mob2[$i],$c_email1[$i],$c_email2[$i],$c_fax[$i],$user['userid']);
			$this->db->query($sql,$inp);
		}
		$this->session->set_flashdata("erp_pop_info","Client details updated");
		redirect("admin/clients");
	}
	
	function do_addclient()
	{
		$user=$this->erpm->getadminuser();
		foreach(array("name","addr1","addr2","locality","city","state","country","remarks","credit_limit","credit_days","cst","pan","vat","tax","c_name","c_design","c_mob1","c_mob2","c_email1","c_email2","c_fax") as $i)
			$$i=$this->input->post($i);
		$sql="insert into m_client_info(client_code,client_name,address_line1,address_line2,locality,city_name,state_name,country,credit_limit_amount,credit_days,cst_no,pan_no,vat_no,service_tax_no,remarks,created_by,created_on) 
														values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,now())";
		$inp=array(randomChars(5),$name,$addr1,$addr2,$locality,$city,$state,$country,$credit_limit,$credit_days,$cst,$pan,$vat,$tax,$remarks,$user['userid']);
		$this->db->query($sql,$inp);
		$cid=$this->db->insert_id();
		$sql="insert into m_client_contacts_info(client_id,contact_name,contact_designation,mobile_no_1,mobile_no_2,email_id_1,email_id_2,fax_no,created_by,created_on)
																					values($cid,?,?,?,?,?,?,?,?,now())";
		foreach($c_name as $i=>$c)
		{
			$inp=array($c,$c_design[$i],$c_mob1[$i],$c_mob2[$i],$c_email1[$i],$c_email2[$i],$c_fax[$i],$user['userid']);
			$this->db->query($sql,$inp);
		}
		$this->session->set_flashdata("erp_pop_info","New client added");
		redirect("admin/clients");
	}
	
	function do_closeclientorder($oid)
	{
		$user=$this->erpm->getadminuser();
		$sql="update t_client_order_info set order_status=3,modified_on=now(),modified_by=? where order_id=?";
		$this->db->query($sql,array($user['userid'],$oid));
		$this->session->set_flashdata("erp_pop_info","Client order closed");
		redirect("admin/client_order/$oid");
	}
	
	function do_payment_client($inv)
	{
		$admin=$this->erpm->getadminuser();
		foreach(array("amount","type","inst_no","inst_date","bank","cleared","remarks","done") as $i)
			$$i=$this->input->post("$i");
		$inp=array($inv,$amount,$type,$inst_no,$inst_date,$bank,$cleared,$remarks,$admin['userid']);
		$this->db->query("insert into t_client_invoice_payment(invoice_id,amount_paid,payment_type,instrument_no,instrument_date,bank_name,is_cleared,remarks,created_by,created_on) values(?,?,?,?,?,?,?,?,?,now())",$inp);
		$s=$done?"1":"2";
		$this->db->query("update t_client_invoice_info set payment_status=$s where invoice_id=? limit 1",$inv);
		$this->session->set_flashdata("erp_pop_info","Client payment updated");
		redirect("admin/client_invoice/$inv");
	}
	
	function getclientinvoice($inv)
	{
		return $this->db->query("select cc.contact_name,i.*,c.* from t_client_invoice_info i join m_client_info c on c.client_id=i.client_id left outer join m_client_contacts_info cc on cc.client_id=i.client_id and cc.active_status=1 where i.invoice_id=? order by cc.id asc limit 1",$inv)->row_array();
	}
	
	function getclientinvoiceforprint($inv)
	{
		return $this->db->query("select pi.*,p.product_name from t_client_invoice_product_info pi join m_product_info p on p.product_id=pi.product_id where pi.invoice_id=?",$inv)->result_array();
	}
	
	function do_stock_log($in,$qty,$pid,$grn_iid,$corp=false,$cancel_invoice=false,$proforma_invoice=false,$mrp_change_updated=-1,$return_product_id=0,$book_slno=0,$stock_info_id=0)
	{
		if($cancel_invoice)
			$in=0;
		$adm=$this->erpm->getadminuser();
		
		if($stock_info_id)
			$stk_inloc=$this->db->query("select available_qty as s from t_stock_info where stock_id=? ",$stock_info_id)->row()->s;
		
			$ttl_prod_stk=$this->db->query("select sum(available_qty) as s from t_stock_info where product_id=? and available_qty >= 0 ",$pid)->row()->s;
		
		$inp=array($pid,$in,0,0,0,0,$qty,$ttl_prod_stk,$adm['userid']);
		if($proforma_invoice)
			$inp[2]=$grn_iid;
		else{
			if($corp && !$in)
				$inp[3]=$grn_iid;
			else if(!$in)
				$inp[4]=$grn_iid;
			else 
				$inp[5]=$grn_iid;
		}
		if($cancel_invoice)
			$inp[1]=1;
			
		$inp[] = $mrp_change_updated;
		$inp[] = $return_product_id;
		$inp[] = $book_slno;
		$inp[] = $stock_info_id;
		$inp[] = $stock_info_id?$stk_inloc:0;
			
		$this->db->query("insert into t_stock_update_log(product_id,update_type,p_invoice_id,corp_invoice_id,invoice_id,grn_id,qty,current_stock,created_on,created_by,mrp_change_updated,return_prod_id,voucher_book_slno,stock_info_id,stock_qty) values(?,?,?,?,?,?,?,?,now(),?,?,?,?,?,?)",$inp);
	}
	
	function do_clientinvoice()
	{
		$admin=$this->erpm->getadminuser();
		foreach(array("invoice_date","client","products","order_id","mrp","offer","tax","iqty") as $i)
			$$i=$this->input->post($i);
		$invoice_no=$this->db->query("select invoice_no as i from t_client_invoice_info order by invoice_id desc limit 1")->row_array();
		if(empty($invoice_no))
			$invoice_no=100028;
		else
			$invoice_no=$invoice_no['i']+1;
		$total=0;
		foreach($offer as $i=>$o)
			$total+=($o*$iqty[$i]);
		$sql="insert into t_client_invoice_info(invoice_no,invoice_date,client_id,total_invoice_value,invoice_status,payment_status,created_date,created_by) values(?,?,?,?,0,0,now(),?)";
		$this->db->query($sql,array($invoice_no,$invoice_date,$client,$total,$admin['userid']));
		$iid=$this->db->insert_id();
		$sql="insert into t_client_invoice_product_info(order_id,invoice_id,product_id,mrp,margin_offered,offer_price,tax_percent,invoice_qty,created_on,created_by) 
																	values(?,?,?,?,?,?,?,?,now(),?)";
		foreach($order_id as $i=>$o)
		{
			if($iqty[$i]==0)
				continue;
			$inp=array($o,$iid,$products[$i],$mrp[$i],$mrp[$i]-$offer[$i],$offer[$i],$tax[$i],$iqty[$i],$admin['userid']);
			$this->db->query($sql,$inp);
			for($i2=1;$i2<=$iqty[$i];$i2++)
				$this->db->query("update t_stock_info set available_qty=available_qty-1 where product_id=? and available_qty!=0 order by stock_id asc limit 1",$products[$i]);
			$this->erpm->do_stock_log(0,$iqty[$i],$products[$i],$iid,1);
			$this->db->query("update t_client_order_product_info set invoiced_qty=invoiced_qty+? where order_id=? and product_id=?",array($iqty[$i],$o,$products[$i]));
			if($this->db->query("select count(1) as l from t_client_order_product_info where order_qty>invoiced_qty and order_id=?",array($o))->num_rows()==0)
				$this->db->query("update t_client_order_info set order_status=2 where order_id=? limit 1",$o);
		}
		$this->session->set_flashdata("erp_pop_info","Client Invoicing done");
		redirect("admin/client_invoice/$iid");
	}
	
	function getinvoicesforclientorder($oid)
	{
		return $this->db->query("select ci.invoice_no,ci.invoice_id,ci.total_invoice_value,ci.payment_status from t_client_invoice_product_info cip join t_client_invoice_info ci on ci.invoice_id=cip.invoice_id where cip.order_id=? group by ci.invoice_id",$oid)->result_array();
	}
	
	function getclientorder($oid)
	{
		$sql="select t.*,c.client_name as client,m.name as closed_by,a.name as created_by from t_client_order_info t join m_client_info c on c.client_id=t.client_id join king_admin a on a.id=t.created_by left outer join king_admin m on m.id=t.modified_by where t.order_id=?";
		$ret['order']=$this->db->query("$sql",$oid)->row_array();
		$sql="select p.product_name as product,t.* from t_client_order_product_info t join m_product_info p on p.product_id=t.product_id where order_id=?";
		$ret['items']=$this->db->query($sql,$oid)->result_array();
		return $ret;
	}
	
	function getclientordersforinvoice($oid)
	{
		$sql="select p.vat as tax,p.product_name as product,t.* from t_client_order_product_info t join m_product_info p on p.product_id=t.product_id where order_id=?";
		$items=$this->db->query($sql,$oid)->result_array();
		foreach($items as $i=>$item)
			$items[$i]['stock']=(int)$this->db->query("select sum(available_qty) as l from t_stock_info where product_id=?",$item['product_id'])->row()->l;
		return $items;
	}
	
	function getclientinvoices($s=false,$e=false)
	{
		if(!$e)
		{
			$s=date("Y-m-d",mktime(0,0,0,date("m"),1));
			$e=date("Y-m-d",mktime(0,0,0,date("m")+1,date("t")));
		}
		return $this->db->query("select i.*,a.name as created_by from t_client_invoice_info i join king_admin a on a.id=i.created_by where i.created_date between ? and ?",array($s,$e))->result_array();
	}
	
	function getclientorders($s=false,$e=false)
	{
		if(!$e)
		{
			$s=date("Y-m-d",mktime(0,0,0,date("m"),1));
			$e=date("Y-m-d",mktime(0,0,0,date("m")+1,date("t")));
		}
		$sql="select t.*,c.client_name as client,a.name as created_by from t_client_order_info t join m_client_info c on c.client_id=t.client_id join king_admin a on a.id=t.created_by where t.created_on between ? and ? order by t.order_id desc";
		return $this->db->query($sql,array($s,$e))->result_array();
	}
	
	function getclientordersbyclient($cid=false)
	{
		$sql="select t.*,c.client_name as client,a.name as created_by from t_client_order_info t join m_client_info c on c.client_id=t.client_id join king_admin a on a.id=t.created_by where t.client_id=? order by t.order_id desc";
		return $this->db->query($sql,array($cid))->result_array();
	}
	
	function getclients()
	{
		return $this->db->query("select * from m_client_info order by client_name asc")->result_array();
	}
	
	function do_cancel_orders()
	{
		
		$user=$this->erpm->getadminuser();
		foreach(array("refund","oids","transid","msg","no_send") as $i)
			$$i=$this->input->post($i);
		$oids=explode(",",$oids);
		
		if(!$this->erpm->auth(true,true))
		{
			$refund = 0;
			foreach($this->db->query("select (i_price-i_coup_discount)*quantity as i_price from king_orders where id in ('".implode("','",$oids)."')")->result_array() as $o)
				$refund+=$o['i_price'];
		}
		
		$this->db->query("insert into t_refund_info(transid,amount,status,created_on,created_by) values(?,?,?,?,?)",array($transid,$refund,0,time(),$user['userid']));
		$rid=$this->db->insert_id();
		foreach($oids as $i=>$o)
		{
			$qty=$this->db->query("select quantity from king_orders where id=?",$o)->row()->quantity;
			$this->db->query("insert into t_refund_order_item_link(refund_id,order_id,qty) values(?,?,?)",array($rid,$o,$qty));
			$this->db->query("update king_orders set status=3,actiontime=".time()." where id=? limit 1",$o);
		}
		$email=$this->db->query("select bill_email as m from king_orders where id=?",$o)->row()->m;
		if(!$no_send)
			$this->vkm->email($email,"Important refund information for $transid",nl2br($msg));
		$this->erpm->do_trans_changelog($transid,count($oids)." order(s) cancelled");
		$this->session->set_flashdata("erp_pop_info","Orders cancelled");
		redirect("admin/trans/$transid");
	}
	
	function do_reship_order()
	{
		foreach(array("transid","oids") as $i)
			$$i=$this->input->post($i);
		if(empty($oids))
			show_error("No orders were selected");
		$orders=$this->db->query("select * from king_orders where id in ('".implode("','",$oids)."')")->result_array();
		$total=0;
		$n_transid=strtoupper("SNR".random_string("alpha",3).random_string("nozero",5));
		$items="";
		foreach($orders as $o)
		{
			unset($o['sno']);
			$o['transid']=$n_transid;
			$o['id']=random_string("numeric",10);
			$o['status']=0;
			$o['actiontime']=0;
			$this->db->insert("king_orders",$o);
			$total+=($o['i_price']-$o['i_coup_discount'])*$o['quantity'];
			$items.=$this->db->query("select name from king_dealitems where id=?",$o['itemid'])->row()->name."<br>";
		}
		$trans=$this->db->query("select * from king_transactions where transid=?",$transid)->row_array();
		unset($trans['id']);
		$trans['amount']=$total;
		$trans['paid']=0;
		$trans['ship']=$trans['cod']=0;
		$trans['transid']=$n_transid;
		$trans['mode']=0;
		$this->db->insert("king_transactions",$trans);
		foreach($this->db->query("select * from king_transaction_notes where transid=?",$transid)->result_array() as $n)
		{
			unset($n['id']);
			$n['transid']=$n_transid;
			$this->db->insert("king_transaction_notes",$n);
		}
		$this->erpm->do_trans_changelog($transid,"$n_transid created for reshipment of following orders<br>".$items);
		$this->erpm->do_trans_changelog($n_transid,"Reshipment of $transid for following orders<br>".$items);
		redirect("admin/trans/$n_transid");
	}
	
	function do_pack()
	{
		//print_r($_POST);
		
		$p_invoice_list=explode(',',$this->input->post("invoice"));
		
		$pid_list=$this->input->post("pids");
		$pbcodes=$this->input->post("pbc");
		$fs_ids=$this->input->post('fs_ids');
		$lgn_user = $this->auth();
		$user = $this->erpm->auth();
		
		if(!$pbcodes)
		{
			show_error("Sorry No Stock Selected");
			die();
		}
		
		$pinv_pids = array();
		$pinv_pids_arr = explode(',',$pid_list);
		foreach($pinv_pids_arr as $pinv_pid)
		{
			list($pinv,$pid) = explode('_',$pinv_pid);
			if(!isset($pinv_pids[$pinv]))
				$pinv_pids[$pinv] = array();
			
			array_push($pinv_pids[$pinv],$pid);
		}
		
		
		
		
		$last_dispatch_id = @$this->db->query("select max(dispatch_id) as id from proforma_invoices ")->row()->id;
		if(!$last_dispatch_id)
			$last_dispatch_id = 500000;

		
		foreach($p_invoice_list as $p_invoice)
		{
			$oids = array();
			$c_oids = array();
			$pids = $pinv_pids[$p_invoice];
			$imeis = array();
			foreach($pids as $pid)
			{
				$imeis[$pid]=array();
				if($this->input->post("imei_{$p_invoice}_$pid"))
					$imeis[$pid]=$this->input->post("imei_{$p_invoice}_$pid");
				$imeis[$pid] = array_unique($imeis[$pid]);
			}
			
			
			//print_r($imeis);
			
			//continue;
		
			$batch_id = $this->db->query("select batch_id from shipment_batch_process_invoice_link where p_invoice_no = ? ",$p_invoice)->row()->batch_id;
			$ord_item_ids = array();
			$r_need_pids=$this->db->query("select l.product_id,l.itemid,o.id,o.is_ordqty_splitd,o.transid from proforma_invoices i join king_orders o on o.id=i.order_id join m_product_deal_link l on l.itemid=o.itemid where i.p_invoice_no=?",$p_invoice)->result_array();
			$ret2=$this->db->query("select p.product_id,o.id,o.itemid,o.is_ordqty_splitd,o.transid from proforma_invoices i join king_orders o on o.id=i.order_id join products_group_orders pgo on pgo.order_id=o.id join m_product_group_deal_link pl on pl.itemid=o.itemid join m_product_info p on p.product_id=pgo.product_id join king_dealitems d on d.id=o.itemid where i.p_invoice_no=?",$p_invoice)->result_array();
			$r_need_pids=array_merge($r_need_pids,$ret2);
			$need_pids = array();
			$is_order_splitted = array();
			foreach($r_need_pids as $r)
			{
				$oids[$r['id']]=$r['id'];
				if(!isset($need_pids[$r['id']]))
					$need_pids[$r['id']]=array();
				$need_pids[$r['id']][]=$r['product_id'];
				
				$ord_item_ids[$r['itemid']] = $r['id'];
				$is_order_splitted[$r['id']]=$r['is_ordqty_splitd'];
			}
			$p_oids=array();
			foreach($need_pids as $oid=>$p)
			{
				$f=true;
				foreach($p as $pp)
					if(!in_array($pp,$pids))
						$f=false;
				if($f)
					$p_oids[]=$oid;
			}
		
			$p_oids_stacked = array();
			$p_oids_stacked[0] = array();//unsplitted orders
			$p_oids_stacked[1] = array();//splitted orders
			
			
			foreach($p_oids as $oid)
			{
				// check if this order is splitted
				array_push($p_oids_stacked[$is_order_splitted[$oid]],$oid);
			}
		
			$processed_ord_ids = array();
			$order_list = array();
			$order_list[0] = $p_oids_stacked[0];
			foreach($p_oids_stacked[1] as $psoid)
			{
				array_push($order_list,array($psoid));
			}
		
		
			// check if the profoma has atleast one splited order for generating invoice group 
			$split_inv_grpno = 0;
			$split_inv_grplist = array();
			$credit_note_grpno = 0;
			if(count($p_oids_stacked[1]))
			{
				$split_inv_grpno = @$this->db->query("select max(split_inv_grpno) as no from king_invoice where split_inv_grpno > 0 ")->row()->no;
				if(!$split_inv_grpno)
					$split_inv_grpno = 900000;
				$credit_note_grpno = @$this->db->query("select max(grp_no) as no from t_invoice_credit_notes where grp_no > 0 ")->row()->no;
				if(!$credit_note_grpno)
					$credit_note_grpno = 0;
				
				$split_inv_grpno++;
				$credit_note_grpno++;
				
			}
		
			foreach($order_list as $p_oids)
			{
				if(count($p_oids) == 0)
					continue;
				$inv=$this->erpm->do_invoice($p_oids);
				$invoice_no=$inv[0];
				
				foreach($p_oids as $p_oid)
					array_push($processed_ord_ids,$p_oid);
			 
				$orders=$this->db->query("select transid,quantity as qty,itemid,id,is_ordqty_splitd from king_orders where id in ('".implode("','",$p_oids)."')")->result_array();
			 	foreach($orders as $o)
				{
					$pls=$this->db->query("select qty,pl.product_id,p.mrp from m_product_deal_link pl join m_product_info p on p.product_id=pl.product_id where itemid=?",$o['itemid'])->result_array();
					
					foreach($pls as $p)
					{
						foreach($imeis[$p['product_id']] as $il=>$imei)
						{
							if($imei===0)
								continue;
							
							$this->db->query("update t_imei_no set order_id=0,status=0 where order_id=? and status = 0 ",array($o['id']));
							
							$imeino_id = $this->db->query("select id from t_imei_no where imei_no = ? and status = 0 ",$imei)->row()->id; 
							
							$ins_data = array();
							$ins_data['imeino_id'] = $imeino_id;
							$ins_data['product_id'] = $p['product_id'];
							$ins_data['imei_no'] = $imei;
							$ins_data['transid'] = $o['transid'];
							$ins_data['order_id'] = $o['id'];
							$ins_data['invoice_no'] = $invoice_no;
							$ins_data['alloted_on'] = cur_datetime();
							$ins_data['alloted_by'] = $user['userid'];
							$this->db->insert('t_imeino_allotment_track',$ins_data);
							
							
							$this->db->query("update t_imei_no set order_id=?,is_returned=0,status=1 where id = ?  limit 1",array($o['id'],$imeino_id));
							
							//t_imeino_allotment_track
							if($this->db->affected_rows() > 0 && $o['is_ordqty_splitd'] == 1)
								break;
							
						}
					}
				}
				
				
				
				if($split_inv_grpno)
				{
					array_push($split_inv_grplist,$invoice_no);
					$this->db->query("update king_invoice set split_inv_grpno = ? where invoice_no = ? ",array($split_inv_grpno,$invoice_no));
				}
					
				
				//----allocateing the book---
				
				$orders_det=$this->db->query("select a.quantity as qty,a.itemid,a.id,b.franchise_id,b.is_pnh,a.i_discount,a.i_coup_discount,a.i_orgprice from king_orders a join king_transactions b on b.transid=a.transid where a.id in ('".implode("','",$p_oids)."')")->result_array();
				
				$allotment_id=0;
				//get the prev allotment id
				$allotment_id=$this->db->query("select ifnull(max(allotment_id),0) as allotment_id from pnh_t_book_allotment")->row()->allotment_id;
				
				if($allotment_id)
					$allotment_id+=1;
				else
					$allotment_id=1;
				
				foreach($orders_det as $o)
				{
					$pls=$this->db->query("select qty,pl.product_id,p.mrp from m_product_deal_link pl join m_product_info p on p.product_id=pl.product_id where itemid=?",$o['itemid'])->result_array();
					foreach($pls as $p)
					{
						foreach($imeis[$p['product_id']] as $il=>$imei)
						{
							if($imei===0)
								continue;
							
							if($o['is_pnh'])
							{
								$is_voucher_book_deal=0;
								
								//cheack if is voucher book deal
								$is_voucher_book_deal=$this->db->query("select count(*) as ttl from pnh_t_book_details where book_slno=?",$imei)->row()->ttl;
								$book_details=$this->db->query("select * from pnh_t_book_details where book_slno=?",$imei)->row_array();
								if($is_voucher_book_deal)
								{
									// query to get book margin from deal mrp and offer price 
									//$book_margin = $this->db->query("select 100-(price/orgprice)*100 as discount from king_dealitems a join m_product_deal_link b on a.id = b.itemid where product_id = ? ",$p['product_id'])->row()->discount;
									$book_mrp=$o['i_orgprice'];
									$book_discount_price=$o['i_orgprice']-($o['i_discount']+$o['i_coup_discount']);
									$book_margin=($book_mrp-$book_discount_price)/$book_mrp*100; //compute the book margin based book sold price
									
									$ins=array();
									$ins['allotment_id']=$allotment_id;
									$ins['book_id']=$book_details['book_id'];
									$ins['franchise_id']=$o['franchise_id'];
									$ins['margin']= $book_margin;
									$ins['created_on']=cur_datetime();
									$ins['status']=1;
									$ins['order_id']=$o['id'];
									$ins['created_by']=$user['userid'];
									$this->db->insert('pnh_t_book_allotment',$ins);
									
									//get book vouchers
									$book_vouchers_res=$this->db->query("select a.*,c.denomination as value from  pnh_t_book_voucher_link a join pnh_t_voucher_details b on b.id= a.voucher_slno_id join pnh_m_voucher c on c.voucher_id= b.voucher_id where book_id=? and b.status=1",$book_details['book_id']);
									if($book_vouchers_res->num_rows())
									{
										foreach($book_vouchers_res->result_array() as $voucher)
										{	
											$franchise_value=((100-$book_margin)/100)*$voucher['value'];
											$this->db->query("update pnh_t_voucher_details set franchise_id=?,is_alloted=1,alloted_on=?,voucher_margin=?,customer_value=?,franchise_value=? where id=? and status=1",array($o['franchise_id'],cur_datetime(),$book_margin,$voucher['value'],$franchise_value,$voucher['voucher_slno_id']));
										}
									}
								}
							}
						}
					}
				}
				//}
				
				//----allocateing the book end---
			
				$proforma_inv_id = $this->db->query("select a.id as id from proforma_invoices a 
																join king_orders b on a.order_id = b.id 
																where a.p_invoice_no = ?  ",array($p_invoice))->row()->id;
				
				$stk_allot_det = array();
				foreach($pbcodes as $stk_prod_sel => $stk_mrp_det)
				{
					list($p_invno,$itemid,$pid,$pbcode) = explode('_',$stk_prod_sel);
					
					if($p_invoice != $p_invno)
						continue;
					
					list($stk_cnt,$pmrp,$stk_id) = explode('_',$stk_mrp_det);
		
					$pbcode = (($pbcode == 'BLANK')?'':$pbcode);
					
					$order_id = $ord_item_ids[$itemid]; 
					
					$check_stock_rerv_res = $this->db->query("select id,qty,released_on,status,ifnull(released_on,0) as is_released,release_qty  from t_reserved_batch_stock where batch_id = ? and p_invoice_no = ? and order_id = ? and stock_info_id = ? ",array($batch_id,$p_invoice,$order_id,$stk_id));
					
					
					if(!$check_stock_rerv_res->num_rows())
					{
						if($this->db->query("select count(*) as t from t_reserved_batch_stock where p_invoice_no = ? and order_id = ? ",array($p_invoice,$order_id))->row()->t)
						{
							$sql="update t_stock_info set available_qty=available_qty-? where product_id=? and stock_id = ? limit 1";
							$this->db->query($sql,array($stk_cnt,$pid,$stk_id));
							
							$allot_stk = array();
							$allot_stk['stock_info_id'] = $stk_id;
							$allot_stk['product_id'] = $pid;
							$allot_stk['p_invoice_no'] = $p_invoice;
							$allot_stk['batch_id'] = $batch_id;
							$allot_stk['order_id'] = $order_id;
							$allot_stk['qty'] = $stk_cnt;
							$allot_stk['status'] = 1;
							$allot_stk['reserved_on'] = time();
							$this->db->insert("t_reserved_batch_stock",$allot_stk);
						}
						
					}else
					{
						
						$stock_rerv_det = $check_stock_rerv_res->row_array();
						
						if($stock_rerv_det['is_released'] == 0 && $stock_rerv_det['status'] != 2)
						{
						 
							if($stock_rerv_det['qty'] > $stk_cnt)
							{
								$release_qty = $stock_rerv_det['qty']-$stk_cnt;
								$sql="update t_stock_info set available_qty=available_qty+? where product_id=? and stock_id = ? limit 1";
								$this->db->query($sql,array($release_qty,$pid,$stk_id));
								
								$this->db->query("update t_reserved_batch_stock set release_qty=?,released_on = ?,status = 1 where id = ? ",array($release_qty,time(),$stock_rerv_det['id']));
								
								//$this->erpm->do_stock_log(0,$release_qty,$pid,$proforma_inv_id,false,true,true);
							}
							else if($stock_rerv_det['qty'] < $stk_cnt)
							{
								$reallot_qty = $stk_cnt-$stock_rerv_det['qty'];
								$sql="update t_stock_info set available_qty=available_qty-? where product_id=? and stock_id = ? limit 1";
								$this->db->query($sql,array($reallot_qty,$pid,$stk_id));
								
								$this->db->query("update t_reserved_batch_stock set release_qty=?,extra_qty=?,released_on = ?,status = 1 where id = ? ",array(0,$reallot_qty,time(),$stock_rerv_det['id']));
								
								//$this->erpm->do_stock_log(1,$reallot_qty,$pid,$proforma_inv_id,false,true,true);
							}
							else
							{
								$this->db->query("update t_reserved_batch_stock set status = 1 where id = ? ",array($stock_rerv_det['id']));	
							}
						}		
					}
					
				}
				
				// check for pending unreleased qty from stock reservation table  
				if($batch_id)
				{
					$reserv_stk_res = $this->db->query('select id,stock_info_id,product_id,qty from t_reserved_batch_stock where batch_id = ? and p_invoice_no = ? and status = 0 and release_qty = 0 and released_on is null ',array($batch_id,$p_invoice));
					if($reserv_stk_res->num_rows())
					{
						foreach($reserv_stk_res->result_array() as $reserv_stk_det)
						{
							$sql="update t_stock_info set available_qty=available_qty+? where product_id=? and stock_id = ? limit 1";
							$this->db->query($sql,array($reserv_stk_det['qty'],$reserv_stk_det['product_id'],$reserv_stk_det['stock_info_id']));
							$this->db->query("update t_reserved_batch_stock set release_qty=? ,released_on = ?,status=2 where id = ? ",array($reserv_stk_det['qty'],time(),$reserv_stk_det['id']));
						}
					}
				}
			 
					
				// process refund for invoiced deals
				$refund_res = $this->db->query('select a.p_invoice_no,c.quantity,di.is_combo,di.is_pnh,c.itemid,d.menuid,c.transid,a.order_id,a.product_id,i_orgprice as ordmrp,i_orgprice-(i_price-i_coup_discount) as disc,b.mrp,(a.qty-ifnull(a.release_qty,0)+ifnull(a.extra_qty,0)) as allot_qty 
															from t_reserved_batch_stock a 
															join t_stock_info b on a.stock_info_id = b.stock_id 
															join king_orders c on c.id = a.order_id 
															join king_dealitems di on c.itemid = di.id 
															join king_deals d on d.dealid = di.dealid
														where batch_id = ? and a.p_invoice_no = ? and a.status = 1  and c.id in ('.implode(',',$p_oids).')
														order by a.order_id 
												',array($batch_id,$p_invoice));
				if($refund_res->num_rows())
				{
					
					$refund_arr = array();
					foreach($refund_res->result_array() as $rd)
					{
						// Check if valid for refund by menu  
						$consider_for_refund = 0;
						if($rd['is_pnh'])
						{
							$consider_for_refund = $this->db->query("select consider_mrp_chng from pnh_menu where id = ? ",$rd['menuid'])->row()->consider_mrp_chng;
						}
						
						if($consider_for_refund==0)
							continue;
						
						$transid = $rd['transid'];
						$oid = $rd['order_id'];
						$ordmrp = $rd['ordmrp'];
						$disc = $rd['disc'];
						$qty = $rd['quantity'];
						
						if(!isset($refund_arr[$transid]))
							$refund_arr[$transid] = array('orders'=>array(),'total_refund'=>0);
		
						if(!isset($refund_arr[$transid]['orders'][$oid]))
						{
							$refund_arr[$transid]['orders'][$oid] = array();
							$refund_arr[$transid]['orders'][$oid]['ordmrp'] = $ordmrp;
							$refund_arr[$transid]['orders'][$oid]['disc'] = $disc;
							$refund_arr[$transid]['orders'][$oid]['qty'] = $qty;
							$refund_arr[$transid]['orders'][$oid]['prod_mrp'] = 0;
							$refund_arr[$transid]['orders'][$oid]['prod_mrp_sum'] = 0;
							$refund_arr[$transid]['orders'][$oid]['new_disc'] = 0;
						}
						$refund_arr[$transid]['orders'][$oid]['prod_mrp_sum'] += $rd['mrp'];
						$refund_arr[$transid]['orders'][$oid]['prod_mrp'] += $rd['mrp']*$rd['allot_qty'];
						
					}
					
					 
					foreach($refund_arr as $tid=>$r_ord_list)
					{
						$ttl_refund = 0;  
						foreach($r_ord_list['orders'] as $oid=>$r_ord_det)
						{
							$t_ord_mrp = $r_ord_det['ordmrp']*$r_ord_det['qty'];
							$t_ord_disc = $r_ord_det['disc']*$r_ord_det['qty'];
							$t_prod_mrp = $r_ord_det['prod_mrp'];
							$t_amt_after_disc = round($t_ord_mrp-$t_ord_disc,2);
							
							$new_disc_amt = round($t_amt_after_disc*$t_prod_mrp/$t_ord_mrp,2);
							$rfd_amt = $t_amt_after_disc - $new_disc_amt;
							$refund_arr[$tid]['orders'][$oid]['new_disc'] = $new_disc_amt; 
							$ttl_refund+=$rfd_amt;
						}
						$refund_arr[$tid]['total_refund'] = $ttl_refund; 
					}
					
					foreach($refund_arr as $tid=>$r_ord_list)
					{
						if($r_ord_list['total_refund']*1 > 0)
						{
							$total_refund_amout = $r_ord_list['total_refund'];
							$this->db->query("insert into t_refund_info(transid,amount,invoice_no,refund_for,status,created_on,created_by) values(?,?,?,?,?,?,?)",array($tid,$total_refund_amout,$invoice_no,'mrpdiff',0,time(),$lgn_user['userid']));
							$rid=$this->db->insert_id();
							
							foreach($r_ord_list['orders'] as $oid=>$r_ord_det)
							{
								$this->db->query("insert into t_refund_order_item_link(refund_id,order_id,invoice_no,qty) values(?,?,?,?)",array($rid,$oid,$invoice_no,$r_ord_det['qty']));
								
								$this->db->query("update king_invoice set mrp = ?,discount=?,nlc=? where invoice_no = ? and order_id = ? ",array($r_ord_det['prod_mrp']/$r_ord_det['qty'],($r_ord_det['prod_mrp']-$r_ord_det['new_disc'])/$r_ord_det['qty'],$r_ord_det['new_disc']/$r_ord_det['qty'],$invoice_no,$oid));
							}
									
							if($tid && $total_refund_amout)
								$this->erpm->do_trans_changelog($tid," Rs ".$total_refund_amout." refund process initiated towards MRP Change in Invoice No:".$invoice_no);
						}
					}
					
				}
					
				$inv_trans_det = $this->db->query("select franchise_id,a.transid,b.invoice_no,is_pnh,sum((mrp-discount)*quantity) as inv_amt
														from king_transactions a 
														join king_invoice b on a.transid = b.transid 
														join king_orders c on c.id = b.order_id 
														where b.invoice_no = ? ",$invoice_no)->row_array();
				$user = $this->erpm->auth();
				if($inv_trans_det['is_pnh'])
				{
					$arr = array($inv_trans_det['franchise_id'],1,$inv_trans_det['invoice_no'],$inv_trans_det['inv_amt'],'',1,date('Y-m-d H:i:s'),$user['userid']);
					$this->db->query("insert into pnh_franchise_account_summary (franchise_id,action_type,invoice_no,debit_amt,remarks,status,created_on,created_by) values(?,?,?,?,?,?,?,?)",$arr);
					
					//check if invoice has credit note amount
					$invoice_credit_note_amt = $this->db->query("select sum(credit_note_amt*quantity) as amt 
																		from king_invoice a 
																		join king_orders b on b.id = a.order_id 
																		where a.invoice_no = ? 
	 															",$invoice_no)->row()->amt;
					if($invoice_credit_note_amt)
					{
						// create creditnote document entry  
						$arr = array($inv_trans_det['franchise_id'],$credit_note_grpno,$inv_trans_det['invoice_no'],$invoice_credit_note_amt,date('Y-m-d H:i:s'),$user['userid']);
						$this->db->query("insert into t_invoice_credit_notes (franchise_id,grp_no,invoice_no,amount,created_on,created_by) values(?,?,?,?,?,?)",$arr);
						$credit_note_id = $this->db->insert_id();
						
						$arr = array($inv_trans_det['franchise_id'],7,$credit_note_id,$inv_trans_det['invoice_no'],$invoice_credit_note_amt,'credit_note',1,date('Y-m-d H:i:s'),$user['userid']);
						$this->db->query("insert into pnh_franchise_account_summary (franchise_id,action_type,credit_note_id,invoice_no,credit_amt,remarks,status,created_on,created_by) values(?,?,?,?,?,?,?,?,?)",$arr);
						
						$this->db->query("update king_invoice set credit_note_id = ? where invoice_no = ? ",array($credit_note_id,$invoice_no));
					}
					
				}
				
				
				if($inv_trans_det['is_pnh'])
				{
					$this->db->query("update shipment_batch_process_invoice_link set invoice_no=$invoice_no,invoiced_on=now(),invoiced_by={$user['userid']} where p_invoice_no=? and invoice_no = 0 limit 1",$p_invoice);
				}else
				{
					$this->db->query("update shipment_batch_process_invoice_link set invoice_no=$invoice_no,packed=1,packed_on=now(),packed_by={$user['userid']} where p_invoice_no=? and invoice_no = 0 limit 1 ",$p_invoice);
				}
			}
		
			$c_oids=array();
			foreach($oids as $oid)
			{
				if(!in_array($oid,$processed_ord_ids))
					$c_oids[]=$oid;
			}		
			if(!empty($c_oids))
				$orders=$this->db->query("select quantity as qty,itemid,id from king_orders where id in ('".implode("','",$c_oids)."')")->result_array();
			else $orders=array();
		
			// revert cancelled orders stock  
			foreach($orders as $o)
			{
				$pls=$this->db->query("select qty,pl.product_id,p.mrp from m_product_deal_link pl join m_product_info p on p.product_id=pl.product_id where itemid=?",$o['itemid'])->result_array();
				foreach($pls as $p)
				{
					
					// get resered qty for product from stock and revert back to stock master
					$p_reserv_qty = 0;
					$reserv_stk_res = $this->db->query('select id,stock_info_id,qty from t_reserved_batch_stock where batch_id = ? and p_invoice_no = ? and order_id = ? and product_id = ? ',array($batch_id,$p_invoice,$o['id'],$p['product_id']));
					if($reserv_stk_res->num_rows())
					{
						foreach($reserv_stk_res->result_array() as $reserv_stk_det)
						{
							$p_reserv_qty += $reserv_stk_det['qty'];
							$sql="update t_stock_info set available_qty=available_qty+? where product_id=? and stock_id = ? limit 1";
							$this->db->query($sql,array($reserv_stk_det['qty'],$p['product_id'],$reserv_stk_det['stock_info_id']));
							
							$this->db->query("update t_reserved_batch_stock set release_qty=? ,released_on = ?,status = 2 where id = ? and p_invoice_no = ?  ",array($reserv_stk_det['qty'],time(),$reserv_stk_det['id'],$p_invoice));
						}
					}
					
					// revert back cancelled orders alloted stock to log 
					$this->erpm->do_stock_log(1,$p_reserv_qty,$p['product_id'],$this->db->query("select id from proforma_invoices where order_id=? order by id desc limit 1",$o['id'])->row()->id,false,true,true);
					
				}
			}
			
			
			if($fs_ids)
			{
				$sql="update king_freesamples_order set invoice_no=? where invoice_no=0 and id in (".$fs_ids.")";
				$this->db->query($sql,array($invoice_no));
			}
			$this->db->query("update king_orders set status=0 where id in ('".implode("','",$c_oids)."')");
			
			
			$user=$this->erpm->getadminuser();
			$bid=$this->db->query("select batch_id from shipment_batch_process_invoice_link where p_invoice_no=?",$p_invoice)->row()->batch_id;
			
			// Status is marked only invoiced in this stage for pnh invoices
			if($inv_trans_det['is_pnh'])
			{
			
				
			
				$this->db->query("update proforma_invoices set dispatch_id = ? where p_invoice_no = ? ",array($last_dispatch_id+1,$p_invoice));
			
				if($this->db->query("select count(1) as l from shipment_batch_process_invoice_link where batch_id=?",$bid)->row()->l<=$this->db->query("select count(1) as l from shipment_batch_process_invoice_link where invoice_no!=0 and batch_id=$bid")->row()->l)
					$this->db->query("update shipment_batch_process set status=2 where batch_id=? limit 1",$bid);
				else
					$this->db->query("update shipment_batch_process set status=1 where batch_id=? limit 1",$bid);
				$this->session->set_flashdata("erp_pop_info","Invoice status Updated");
			}else
			{
			
				if($this->db->query("select count(1) as l from shipment_batch_process_invoice_link where batch_id=?",$bid)->row()->l<=$this->db->query("select count(1) as l from shipment_batch_process_invoice_link where packed=1 and batch_id=$bid")->row()->l+$this->db->query("select count(1) as l from shipment_batch_process_invoice_link bi join proforma_invoices i on i.p_invoice_no=bi.p_invoice_no where bi.batch_id=$bid and bi.packed=0 and i.invoice_status=0")->row()->l)
					$this->db->query("update shipment_batch_process set status=2 where batch_id=? limit 1",$bid);
				else
					$this->db->query("update shipment_batch_process set status=1 where batch_id=? limit 1",$bid);
			
				$this->session->set_flashdata("erp_pop_info","Packed status updated");
			}
			
		}

		exit;
		 
		
		
		if($split_inv_grpno)
			redirect("admin/invoice/$last_dispatch_id");
		else
			redirect("admin/invoice/$invoice_no");
			
	}
	
	function do_addtransmsg($transid)
	{
		$admin=$this->erpm->getadminuser();
		$msg=$this->input->post("msg");
		$usernote=$this->input->post("usernote");
		if($usernote)
		{
			$m=$this->db->query("select * from king_transaction_notes where transid=? and note_priority=1",$transid)->row_array();
			if(!empty($m))
				$this->db->query("update king_transaction_notes set note=? where transid=? and note_priority=1",array($m['note'].", update :".$msg,$transid));
			else 
				$this->db->query("insert into king_transaction_notes(transid,note,note_priority) values(?,?,1)",array($transid,"User Note: ".$msg));
			$msg="User note appended : $msg";
		}
		$this->db->query("insert into transactions_changelog(transid,msg,admin,time) values(?,?,?,?)",array($transid,$msg,$admin['userid'],time()));
		$this->session->set_flashdata("erp_pop_info","Message added");
		redirect("admin/trans/$transid");
	}
	
	function getpartnerorderlogs()
	{
		return $this->db->query("select l.*,p.name,u.name as created_by from partner_orders_log l join partner_info p on p.id=l.partner_id left outer join king_admin u on u.id=l.created_by order by l.id desc")->result_array();
	}
	
	function do_generate_kfile($invs)
	{
		
		$sql="select    ifnull(c.courier_name,'') as courier, ifnull(il.shipped_on,'') as shipdate,ifnull(il.awb,'') as awb_no,
						t.amount as tran_amount,i.invoice_no,t.mode,
						(o.i_price-o.i_coup_discount)*o.quantity as amount,di.name,o.transid,
						o.ship_person,concat(o.ship_address,o.ship_landmark) as ship_address,
						o.ship_city,o.ship_state,o.ship_pincode,o.ship_phone,o.quantity,
						t.partner_id,
						ifnull(p.name,'') as partner_name,
						t.partner_reference_no
					from king_invoice i 
					join king_orders o on o.id=i.order_id 
					join king_dealitems di on di.id=o.itemid 
					join king_transactions t on t.transid=o.transid 
					left join shipment_batch_process_invoice_link il on il.invoice_no = i.invoice_no 
					left join m_courier_info c on c.courier_id = il.courier_id
					left join partner_info p on p.id = t.partner_id   
					where i.invoice_no in ('".implode("','",$invs)."')";
		$ret=array();
		foreach($this->db->query($sql)->result_array() as $r)
		{
			if(!isset($ret[$r['invoice_no']]))
				$ret[$r['invoice_no']]=array();
			$ret[$r['invoice_no']][]=$r;
		}
		$data=$ret;
		ob_start();
		$f=fopen("php://output","w");
		fputcsv($f, array("Invoice No","AWB No","Courier/Medium","Ship Date","Notify Customer","Transaction Reference","Ship Person","Shipping Address","Shipping City","Shipping Pincode","Shipping State","Contact Number","Amount","Mode","Quantity","Product Name","Weight","PartnerName","PartnerRefno","UserNotes"));
		foreach($data as $inv=>$orders)
		{
			$transid=$orders[0]['transid'];
			$csv=array($inv,$orders[0]['awb_no'],($orders[0]['courier']?$orders[0]['courier']:''),(($orders[0]['shipdate']!="")?date('Y-m-d',strtotime($orders[0]['shipdate'])):''),"",$orders[0]['transid'],$orders[0]['ship_person'],$orders[0]['ship_address'],$orders[0]['ship_city'],$orders[0]['ship_pincode'],$orders[0]['ship_state'],$orders[0]['ship_phone']);
			$amount=0;$prods=array();
			foreach($orders as $o)
			{
				$prods[]=$o['name'].":{$o['quantity']}";
				$amount+=$o['amount'];
			}
			$n=count($orders);
			if($n==$this->db->query("select count(1) as l from king_orders where transid=?",$transid)->row()->l)
				$amount=$orders[0]['tran_amount'];
			$csv[]=$amount;
			if($orders[0]['mode']==1)
				$csv[]="COD";
			else
				$csv[]="Prepaid";
			$csv[]=$n;
			$csv[]=implode(", ",$prods);
			$csv[]="";
			$csv[]=$orders[0]['partner_name'];
			$csv[]=$orders[0]['partner_reference_no'];
			if($orders[0]['partner_id'])
			{
				$tnotes_res = $this->db->query("select note from king_transaction_notes where transid = ? ",$transid);
				if($tnotes_res->num_rows())
					$csv[]=$tnotes_res->row()->note;
				else
					$csv[]="";
			}else
			{
				$csv[]="";
			}
			
			fputcsv($f,$csv);
		}
		fclose($f);
		$csv=ob_get_clean();
		@ob_clean();
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.("kfile_".date("d_m_y").".csv"));
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . strlen($csv));
	    @ob_clean();
	    flush();
	    echo $csv;
	    exit;
	}
	
	function do_generate_kfilebyrange($from,$to)
	{
		$invs=explode(",",$this->input->post("ids"));
		if(empty($invs))
			show_error("No Invoices provided");
			
			
		$invs = array();	
		foreach($this->db->query("select * from shipment_batch_process_invoice_link where shipped=1 and  date(shipped_on)>=? and date(shipped_on)<=? ",array($from,$to))->result_array() as $r)
			array_push($invs,$r['invoice_no']);

		$this->do_generate_kfile($invs);		
			 
	}
	
	function getoutscansforkfile()
	{
		$l=5;
		for($i=0;$i<$l;$i++)
		{
			$date=date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$i));
			$ret[$date]=array();
			foreach($this->db->query("select * from shipment_batch_process_invoice_link where shipped=1 and date(shipped_on)=?",$date)->result_array() as $r)
				$ret[$date][]=$r['invoice_no'];
		}
		return $ret;
	}
	
	
	function do_new_order($pl,$mode=1,$prefix="SNC",$partner_id=0,$log_id=0)
	{
		$cons_ship = $this->input->post('cons_ship');
		$iid=explode(":",$pl['itemid']);
		$pl['itemid']=$iid[0];
		if(count($iid)>1)
			$pl['itemid']=$iid[1];
		$cod=0;
		
		$notify=true;
		if(isset($pl['notify']) && $pl['notify']==0)
			$notify=false;
			
		if(!isset($pl['reference']))
			$pl['reference']="";
			
		$p_email=$pl['email'];
		
		$user=false;
		if(!$user)
		{
			$user=$this->vkm->getuserbyemail($p_email);
			if(!$user)
			{
				$password=randomChars(6);
				$this->vkm->newuser($p_email,$pl['bill_person'],$password,$pl['bill_phone'],rand(2030,424321),0,"",0,$notify);
				$user=$this->vkm->getuserbyemail($p_email);
			}
		}
		$uid=0;
		if(!empty($user))
			$uid=$user['userid'];

		$total=$amount=$price=$this->db->query("select price from king_dealitems where id=?",$pl['itemid'])->row()->price*$pl['qty'];
		
		$ship=$cons_ship?$pl['ship_charges']:0;
		
		$gc_total=0;

		$amount+=$ship;
		
		$snp=$prefix;
			
		$transid=$snp.random_string("alpha",3).random_string("nozero",5);
		$transid=strtoupper($transid);
		
		$sql="insert into king_transactions(transid,amount,init,mode,status,cod,ship,partner_reference_no,partner_id) values(?,?,?,?,0,?,?,?,?)";
		$this->db->query($sql,array($transid,$amount,time(),$mode,$cod,$ship,$pl['reference'],$partner_id));
		
		$itemid=$pl['itemid'];
		$item_det=$this->db->query("select d.brandid,d.vendorid,i.orgprice,i.price,i.nlc,i.phc,i.tax,d.is_giftcard from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=?",$pl['itemid'])->row();

		$brandid = $item_det->brandid;
		$vendorid = $item_det->vendorid;
		$is_giftcard = $item_det->is_giftcard;
		
		$buyer_options=array();
		
		$orderid=random_string("numeric",10);

		$sql="insert into king_tmp_orders(id,userid,itemid,brandid,vendorid,bpid,bill_person,bill_address,bill_landmark,bill_city,bill_state,bill_pincode,bill_phone,bill_telephone,bill_email,bill_country,ship_person,ship_address,ship_landmark,ship_city,ship_state,ship_pincode,ship_phone,ship_telephone,ship_email,ship_country,quantity,amount,time,buyer_options,transid,i_orgprice,i_price,i_nlc,i_phc,i_tax,i_discount,i_coup_discount,i_discount_applied_on,giftwrap_order,user_note,is_giftcard,gc_recp_name,gc_recp_email,gc_recp_mobile,gc_recp_msg)" .
																									" values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$inp=array($orderid,$uid,$itemid,$brandid,$vendorid,0);
		$params=array("bill_person","bill_address","bill_landmark","bill_city","bill_state","bill_pincode","bill_phone","bill_telephone","email","bill_country","ship_person","ship_address","ship_landmark","ship_city","ship_state","ship_pincode","ship_phone","ship_telephone","email","ship_country");
		foreach($params as $p)
			$inp[]=$pl[$p];
			
		$log_inp=array("transid"=>'',"log_id"=>$log_id,"i_customer_price"=>$item_det->price,"i_partner_price"=>$item_det->price,'qty'=>$pl['qty']);

		$this->db->query("update king_users set address=?,landmark=?,city=?,state=?,pincode=?,country=? where userid=? limit 1",array($pl['ship_address'],$pl['ship_landmark'],$pl['ship_city'],$pl['ship_state'],$pl['ship_pincode'],$pl['ship_country'],$user['userid']));	
		if($partner_id!=0)
		{
			$p_price=$this->db->query("select customer_price,partner_price from partner_deal_prices where itemid=? and partner_id=?",array($itemid,$partner_id))->row_array();
			if(!empty($p_price))
			{
				$log_inp['i_customer_price']=$item_det->price=$p_price['customer_price'];
				$log_inp['i_partner_price']=$p_price['partner_price'];
			}
		}
			
		$inp[]=$pl['qty'];
		$inp[]=$price;
		$inp[]=time();
		$inp[]=serialize($buyer_options);
		$inp[]=$transid;
		
		$inp[]=$item_det->orgprice;
		$inp[]=$item_det->price;
		$inp[]=$item_det->nlc;
		$inp[]=$item_det->phc;
		$inp[]=$item_det->tax;
		$inp[]=($item_det->orgprice-$item_det->price);
		if(isset($coupon_price_dets[$itemid]['coup_discount']))
			$inp[] = $coupon_price_dets[$itemid]['coup_discount'];
		else
			$inp[] = 0;
		 
		if(isset($coupon_price_dets[$itemid]['discount_applied_amount']))
			$inp[] = $coupon_price_dets[$itemid]['discount_applied_amount'];
		else
			$inp[] = 0;
		
			$inp[] = ($this->input->post('is_giftwrap')?1:0);
			$inp[] = $pl['notes'];
			
		$inp[] = 0;
		$inp[] = '';
		$inp[] = '';
		$inp[] = '';
		$inp[] = '';
			
		$this->db->query($sql,$inp);

		$invno = ''; // not required while new order  	
		
		$user_note = '';
		
		$giftwrap_order = 0;
		
		$sql="select * from king_tmp_orders where transid=?";
		
		$orders=$this->db->query($sql,array($transid))->result_array();
		foreach($orders as $cord=>$t_order)
		{
			$params=array("bpid","id","transid","userid","itemid","brandid","vendorid","bill_person","bill_address","bill_landmark","bill_city","bill_state","bill_phone","bill_telephone","bill_pincode","bill_country","ship_person","ship_address","ship_landmark","ship_city","ship_state","ship_pincode","ship_phone","ship_telephone","ship_country","quantity","bill_email","ship_email","buyer_options");
			$sql="insert into king_orders(".implode(",",$params).",paid,mode,status,invoice_no,time,i_orgprice,i_price,i_tax,i_nlc,i_phc,i_discount,i_coup_discount,i_discount_applied_on,giftwrap_order,is_giftcard,gc_recp_name,gc_recp_email,gc_recp_mobile,gc_recp_msg) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$inp=array();
			foreach($params as $p)
				$inp[]=$t_order[$p];
			$inp[]=$amount;
			$inp[]=$mode;$inp[]=0;$inp[]=$invno++;$inp[]=time();
			
			$inp[]=$t_order['i_orgprice'];
			$inp[]=$t_order['i_price'];
			$inp[]=$t_order['i_tax'];
			$inp[]=$t_order['i_nlc'];
			$inp[]=$t_order['i_phc'];
			$inp[]=$t_order['i_discount'];
			$inp[]=$t_order['i_coup_discount'];
			$inp[]=$t_order['i_discount_applied_on'];
			$inp[]=$t_order['giftwrap_order'];
			$inp[]=$t_order['is_giftcard'];
			
			if($t_order['is_giftcard']){
				$inp[]=$t_order['gc_recp_name'];
				$inp[]=$t_order['gc_recp_email'];
				$inp[]=$t_order['gc_recp_mobile'];
				$inp[]=$t_order['gc_recp_msg'];
			}else{
				$inp[]='';
				$inp[]='';
				$inp[]='';
				$inp[]='';
			}
			
			$giftwrap_order = $t_order['giftwrap_order'];
			
			$user_note = $t_order['user_note'];
			
			$emails[0]=$t_order['bill_email'];
			$emails[1]=$t_order['ship_email'];
			
			$this->db->query($sql,$inp);
			$this->db->query("delete from king_tmp_orders where transid=? limit 1",$transid);
			$sql="update king_dealitems set available=available+{$t_order['quantity']} where id=?";
			$this->db->query($sql,$t_order['itemid']);
			$this->db->query("update king_m_buyprocess set quantity_done=quantity_done+? where id=? limit 1",array($t_order['quantity'],$t_order['bpid']));
			$this->db->query("update king_buyprocess set quantity=?,status=1 where bpid=? and userid=? limit 1",array($t_order['quantity'],$t_order['bpid'],$t_order['userid']));
			
			$qty=$t_order['quantity'];
			$this->db->query("update king_profiles set products=products+?,lastbuy=?, lastbuy_on=? where userid=?",array($t_order['quantity'],$t_order['itemid'],time(),$user['userid']));
			$this->db->query("update king_dealitems set buys=buys+?,snapits=snapits+? where id=?",array($qty,$qty,$t_order['itemid']));
			
		}
		
		if($partner_id)
		{
			$ins_data = array();
			$ins_data['partner_id'] = $partner_id;
			$ins_data['transid'] = $transid;
			$ins_data['order_no'] = $pl['reference'];
			$ins_data['ship_charges'] = $pl['ship_charges'];
			$ins_data['order_date'] = date('y-m-d',strtotime($pl['order_date']));
			$ins_data['awb_no'] = $pl['awb_no'];
			$ins_data['net_amt'] = $pl['net_amt'];
			$ins_data['courier_name'] = $pl['courier_name'];
			$ins_data['created_on'] = date('Y-m-d H:i:s');
			$ins_data['created_by'] = $uid;
			$this->db->insert('partner_transaction_details',$ins_data);
		}
		
		// add  user note to transaction notes 
		$ins_un = array();
		$ins_un['transid'] = $transid;
		$ins_un['note'] = 'User Note:'.$user_note;
		$ins_un['status'] = 1;
		$ins_un['note_priority'] = 1;
		$ins_un['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('king_transaction_notes',$ins_un);
		
		
		$t_tot=0;
		
		$ormsg='<table border=1><tr><th>Product</th><th>Quantity</th><th>Ship to</th></tr>';
		$umail='<table width="100%" border=1 cellspacing=0 cellpadding=7 style="font-size:inherit;margin-top:15px;"><tr><th>Product</th><th>Unit Price</th><th>Quantity</th><th>Price</th></tr>';
		$mrp_total=0;
		$m_items=array();
		$total_discount = 0;
		 
		foreach($orders as $order)
		{
			$m_item=$this->db->query("select orgprice,price,name from king_dealitems where id=?",$order['itemid'])->row_array();
			$itemname=$m_item['name'];
			$ormsg.="<tr><td>{$itemname}</td><td>{$order['quantity']}</td><td>{$order['ship_person']}<br>{$order['ship_address']}<br>{$order['ship_city']}</td></tr>";
			$t=$m_item['price']*$order['quantity'];
			$m=$m_item['orgprice']*$order['quantity'];
			$total_discount+=($order['i_coup_discount']+$order['i_discount'])*$order['quantity'];
			$umail.="<tr><td>{$itemname}</td><td>{$m_item['orgprice']}</td><td>{$order['quantity']}</td><td>{$m}</td></tr>";
			$t_tot+=$t;
			$mrp_total+=$m;
			$m_items[]=$m_item;
		}
		
		
		$log_inp['transid']=$transid;
		if($partner_id)
		{
			$this->db->insert("partner_order_items",$log_inp);
			
			$ttl_payable_amt = $this->db->query("select sum((i_orgprice-(i_coup_discount+i_discount))*quantity ) as ttl from king_orders where transid = ? ",$log_inp['transid'])->row()->ttl;
			$ttl_payable_amt += $this->db->query("select cod+ship+giftwrap_charge as ttl from king_transactions where transid = ? ",$log_inp['transid'])->row()->ttl;
			
			$this->db->query("update king_transactions set amount = ? where partner_id = ? and transid = ? ",array($ttl_payable_amt,$partner_id,$log_inp['transid']));
		}
		
		
		$m_trans=$this->db->query("select * from king_transactions where transid=?",$transid)->row_array();
		$t_amount=$this->db->query("select amount from king_transactions where transid=?",$transid)->row()->amount;
		$ormsg.='</table><div>Refer admin panel for more & correct info</div>';
		$umail.="<tr><td colspan=3 align='right'>Discount</td><td>".floor($total_discount)."</td></tr>";
		if($giftwrap_order)
			$umail.="<tr><td colspan=3 align='right'>GiftWrap Charges</td><td>".GIFTWRAP_CHARGES."</td></tr>";
		$umail.="<tr><td colspan=3 align='right'>Handling/Shipping/COD charges</td><td>".($m_trans['cod']+$m_trans['ship'])."</td></tr>";
		$umail.="<tr><td colspan=3 align='right'>Total</td><td>Rs {$t_amount}</td></tr>";
		$umail.="</table>";
				
		if($notify)
			$this->vkm->sendemail($transid,$emails,$umail);
		
		
		$this->vkm->do_cashbacks($transid);
		
		
		return $transid;
	}
	
	function pnh_getdealpricechanges($v,$iids=array())
	{
		return $this->erpm->pnh_getdealpricechange($v,$iids);
	}
	
	function pnh_getdealpricechange($v,$iids=array())
	{
		$ver=$this->db->query("select * from pnh_app_versions where id=?",$v)->row_array();
		$sql="SELECT * FROM (select i.name,i.pnh_id,pc.* from `deal_price_changelog` pc join king_dealitems i on i.id=pc.itemid and i.is_pnh=1 where pc.created_on>=? ";
		if(!empty($iids))
			$sql.=" and pc.itemid in ('".implode("','",$iids)."')";
		$sql.=" order by pc.id desc) as temp group by itemid order by id desc";
		$rdeals=$this->db->query($sql,$v['version_date'])->result_array();
		$ids=array();
		foreach($rdeals as $d)
			$ids[]=$d['itemid'];
		if(!empty($ids))
		{
			$old_pc_data=$this->db->query("select old_mrp,itemid,old_price from deal_price_changelog where created_on>? and itemid in ('".implode("','",$ids)."') order by id asc",$v['version_date'])->result_array();
			$done=array();
			foreach($old_pc_data as $pc)
			{
				if(in_array($pc['itemid'],$done))
					continue;
				foreach($rdeals as $i=>$deal)
				{
					if($deal['itemid']==$pc['itemid'])
					{
						$rdeals[$i]['old_price']=$pc['old_price'];
						$rdeals[$i]['old_mrp']=$pc['old_mrp'];
					}
				}
				$done[]=$pc['itemid'];
			}
		}
		$deals=array();
		foreach($rdeals as $d)
		{
			$d['changed_on']=date("g:ia d/m/y",$d['created_on']);
			foreach(array("itemid","id","created_by","reference","created_on","itemid") as $r)
				unset($d[$r]);
			$deals[]=$d;
		}
		return $deals;
	}
	
	function do_partner_order_import()
	{
		$user=$this->erpm->getadminuser();
		$pid=$this->input->post("partner");
		$cons_ship = $this->input->post("cons_ship");
		if(empty($pid))
			show_error("Input kissing<br>Invalid partner id");
		$partner=$this->db->query("select * from partner_info where id=?",$pid)->row_array();
		$partner_id=$partner['id'];
		$mode=$partner['trans_mode'];
		$prefix=$partner['trans_prefix'];
		
		$f=fopen($_FILES['pords']['tmp_name'],"r");
		$head=fgetcsv($f);
		$head[]="Transid";$head[]="Msg";
		$out=array($head);
		$c=0;
		$l_inp=array("partner_id"=>$pid,"created_by"=>$user['userid'],"created_on"=>time());
		$this->db->insert("partner_orders_log",$l_inp);
		$log_id=$this->db->insert_id();
		while(($data=fgetcsv($f))!==false)
		{
			$flag=true;
			$payload=array("email"=>$data[0],"notify"=>$data[1],"notes"=>$data[2],"itemid"=>$data[3],"qty"=>$data[5],"ship_charges"=>$data[6],"bill_person"=>$data[7],"bill_address"=>$data[8].$data[9],"bill_landmark"=>"","bill_city"=>$data[10],"bill_state"=>$data[11],"bill_country"=>$data[12],"bill_pincode"=>$data[13],"bill_phone"=>$data[16],"bill_telephone"=>$data[14],"ship_person"=>$data[17],"ship_address"=>$data[18].$data[19],"ship_landmark"=>"","ship_city"=>$data[20],"ship_state"=>$data[21],"ship_country"=>$data[22],"ship_pincode"=>$data[23],"ship_telephone"=>$data[24],"ship_phone"=>$data[26],"reference"=>$data[27],"order_date"=>$data[29],"courier_name"=>$data[30],"awb_no"=>$data[31],"net_amt"=>$data[32]);
			if(count($data)!=33)
			{
				$data['transid']="";
				$data['msg']="Invalid template structure";
				$flag=false;
			}
			$data['transid']="";
			$item=explode(":",$payload["itemid"]);
			if(count($item)==1)
				$itemid=$item[0];
			else $itemid=$item[1];
			if($flag && $this->db->query("select 1 from king_dealitems where id=?",$itemid)->num_rows()==0)
			{
				$flag=false;
				$data['msg']="Itemid : $itemid is not valid";
			}
			$p_mode=0;
			if($flag && strtolower($data[28])!="cod" && strtolower($data[28])!="pg")
			{
				$flag=false;
				$data['msg']="Invalid transaction mode";
			}
			if(strtolower($data[28])=="cod")
				$p_mode=1;
				
			$is_dup = $this->db->query("select  count(*) as t from king_transactions where partner_reference_no = ? and partner_id = ? ",array($payload['reference'],$partner_id))->row()->t;
			if($is_dup)
			{
				$flag=false;
				$data['msg']="Order Already Imported";
			}	
			if($flag)
			{
				$data["transid"]=$this->erpm->do_new_order($payload,$p_mode,$prefix,$partner_id,$log_id);
				$data['msg']="Transaction successful";
			}
			$out[]=$data;
			$c++;
		}
		$amount=$this->db->query("select sum(i_partner_price*qty) as s from partner_order_items where log_id=$log_id")->row()->s;
		$this->db->query("update partner_orders_log set amount=? where id=? limit 1",array($amount,$log_id));
		$this->session->set_flashdata("erp_pop_info","$c Partner orders processed");
		fclose($f);
		@ob_start();
		$f=fopen("php://output","w");
		foreach($out as $o)
			fputcsv($f, $o);
		fclose($f);
		$csv=@ob_get_clean();
		@ob_clean();
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.("partner_orders_".date("d_m_y").".csv"));
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . strlen($csv));
	    @ob_clean();
	    flush();
	    echo $csv;
	    exit;
	}
	
	function do_update_ship_kfile()
	{
		$f=fopen($_FILES['kfile']['tmp_name'],"r");
		$head=fgetcsv($f);
		$count=0;
		while(($data=fgetcsv($f))!==false)
		{
			$invoice_no=$data[0];
			if(empty($invoice_no))
				continue;
			
			// don't process shipment status update via kfile update for pnh invoices .
			if($this->db->query("select count(*) as t from king_invoice a join king_transactions b on a.transid = b.transid where a.invoice_no = ? and is_pnh = 1",$invoice_no)->row()->t)
				continue ;
			
			$awb=$data[1];
			$courierid=$data[2];
			$c=$this->db->query("select courier_name as name from m_courier_info where courier_id=?",$courierid)->row_array();
			$courier="Others";
			if(!empty($c))
				$courier=$c['name'];
			$date=$data[3];
			$notify=$data[4];
			if($this->db->query("select courier_id from shipment_batch_process_invoice_link where invoice_no=?",$invoice_no)->row()->courier_id!=0)
			{
				$b=$this->db->query("select o.medium,o.shipid from king_invoice inv join king_orders o on o.id=inv.order_id where inv.invoice_no=?",$invoice_no)->row_array();
				$this->erpm->do_trans_changelog($this->db->query("select transid from king_invoice where invoice_no=?",$invoice_no)->row()->transid,"Invoice no {$invoice_no} is reshipped.<br>Old courier details are<br>Medium:{$b['medium']}<br>Awb:{$b['shipid']}");
			}
			foreach($this->db->query("select order_id from king_invoice where invoice_no=?",$invoice_no)->result_array() as $o)
				$this->db->query("update king_orders set medium=?,shipid=?,status=2,actiontime=".time()." where id=? limit 1",array($courier,$awb,$o['order_id']));
			
			$this->db->query("update shipment_batch_process_invoice_link set courier_id=?,shipped=?,awb=? where invoice_no=?",array($courierid,1,$awb,$invoice_no));
			if($notify==1)
			{
				$mdata['prods']=$os=$this->db->query("select o.id as order_id,inv.transid,i.name,o.quantity,o.medium,o.shipid from king_invoice inv join king_orders o on o.id=inv.order_id join king_dealitems i on i.id=o.itemid where inv.invoice_no=? order by i.name asc",$invoice_no)->result_array();
				if(empty($os))
					continue;
				$mdata['transid']=$transid=$os[0]['transid'];
				$mdata['medium']=$os[0]['medium'];
				$mdata['trackingid']=$os[0]['shipid'];
				$partial=false;
				if($this->db->query("select count(1) as l from king_orders where status!=2 and transid=?",$transid)->row()->l!=0)
					$partial=true;
				$mdata['partial']=$partial;
				$payload=$this->db->query("select u.name,o.bill_email,o.ship_email from king_orders o join king_users u on u.userid=o.userid where o.id=?",$os[0]['order_id'])->row_array();
				$mdata['name']=$payload['name'];
				$msg=$this->load->view("mails/shipment",$mdata,true);
				$this->vkm->email(array($payload['bill_email'],$payload['ship_email']),"Your order is shipped",$msg,true);
			}
			$count++;
		}
		$this->session->set_flashdata("erp_pop_info","$count invoices updated");
		redirect("admin/update_ship_kfile");
	}
	
	function do_outscan($awb,$partner_id="")
	{
		$user = $this->erpm->auth();
		
		
		$this->db->query("insert into t_outscanentry_log (outscan_no,created_by,created_on) values (?,?,now())",array($awb,$user['userid']));
		$os_log_id = $this->db->insert_id();
		
		$error = 0;
		
		$bpinv_list = array();
		
		$courier_names = '';
		// process logic for hs18 
		if($partner_id == 5 )
		{
			
			$courier_names = explode(',',$this->input->post('inp_courier_name'));
			
			if(!count($courier_names))
			{
				die("Please choose atlease one courier");
				$error = 1;
			}
			
			$bpinv_list_res = $this->db->query("select d.*,c.partner_reference_no,e.is_manifesto_created,e.courier_name 
							from king_invoice a 
							join king_orders b on a.order_id = b.id 
							join king_transactions c on b.transid = c.transid 
							left join shipment_batch_process_invoice_link d on d.invoice_no = a.invoice_no
							join partner_transaction_details e on e.transid = c.transid and e.order_no = c.partner_reference_no
							where c.partner_id = ?  and ( partner_reference_no = ? or (d.invoice_no = ? and d.invoice_no != 0) )         
							limit 1;",array($partner_id,$awb,$awb));
			if($bpinv_list_res->num_rows())
				$bpinv_list = $bpinv_list_res->result_array();
			
		}else
		{
			$bpinv_list=$this->db->query("select * from shipment_batch_process_invoice_link where awb=? or (invoice_no=? and invoice_no!=0)",array($awb,$awb))->result_array();
		}
		 
		if(count($bpinv_list) == 0)
		{
			// check if the document entered is dispatch id
			if($this->db->query("select count(*) as t from proforma_invoices where dispatch_id = ? ",$awb)->row()->t > 0)
			{
				$bpinv_list_res=$this->db->query("select a.*
															from shipment_batch_process_invoice_link a
															join proforma_invoices b on a.p_invoice_no = b.p_invoice_no
															where b.dispatch_id = ?  
														group by a.id",array($awb));
				if($bpinv_list_res->num_rows())
					$bpinv_list = $bpinv_list_res->result_array();
			}else
			{
				echo ("<div class='not_found' style='background:#cd0000;'>AWB/Invoice/Order No:{$awb} not found</div>");
				$error = 1;	
			}
		}
		

		
		
		//echo ("<div class='not_found' style='background:#cd0000;'>AWB/Invoice/Order No:{$awb} not found</div>");
		
		foreach($bpinv_list as $bpinv)
		{


			

		
		$is_pnh = $this->db->query("select is_b2b from proforma_invoices a join shipment_batch_process_invoice_link b on a.p_invoice_no = b.p_invoice_no where b.invoice_no = ? ",$bpinv['invoice_no'])->row()->is_b2b;

		
		
		if($this->db->query("select invoice_status as s from king_invoice where invoice_no=?",$bpinv['invoice_no'])->row()->s=="0")
		{
			echo ("<div class='cancelled' style='background:green;'>Invoice/Order No:{$awb} is cancelled</div>");
			continue;
		}
			
		
		if($bpinv['shipped'])
		{
			echo ("<div class='already_shipped' style='background:orange;'>Invoice/Order No:".$bpinv['invoice_no']." already shipped</div>");
			continue;
		}
			
		
		if($bpinv['packed'] && $is_pnh)
		{
			echo ("<div class='already_packed' style='background:#cd0000;'>Invoice/Order No:".$bpinv['invoice_no']." already Packed</div>");
			continue;
		}
			
		
				
		$user=$this->erpm->getadminuser();
		
		
		if($bpinv && $partner_id)
		{
			if($partner_id==5)
			{
				if(!$bpinv['is_manifesto_created'])
				{
					echo ("<div class='no_manifesto' style='background:#cd0000;'>Order No:{$bpinv['partner_reference_no']} is not in manifesto</div>");
					continue;
				}
					
				if(!in_array($bpinv['courier_name'],$courier_names))
				{
					echo ("<div class='courier_mismatch' style='background:purple;'>Order No:{$bpinv['partner_reference_no']} is not in <b>".implode(',',$courier_names)."</b> </div>");
					continue;
				}
			}
		}
		
		$this->db->query("update t_outscanentry_log set status = 1 where id = ? ",$os_log_id);	

		$tray_terr_id = 0;
		$cur_datetime=cur_datetime();

		if($is_pnh)
		{
			//<!--pnh tray module -->
			//get the territory id
			$tsql="select t.town_name,e.hub_id as territory_id,f.hub_name as territory_name from shipment_batch_process_invoice_link a 
							join king_invoice b on b.invoice_no=a.invoice_no and b.invoice_status=1
							join king_transactions c on c.transid=b.transid 
							join pnh_m_franchise_info d on d.franchise_id=c.franchise_id
							left join pnh_deliveryhub_town_link e on e.town_id=d.town_id and e.is_active = 1 
							left join pnh_deliveryhub f on f.id=e.hub_id
							left join pnh_towns t on t.id=d.town_id
						where c.is_pnh=1 and a.packed=0 and inv_manifesto_id=0 and a.invoice_no=?
						group by b.invoice_no
				";
						
			$terr_det =	$this->db->query($tsql,$bpinv['invoice_no'])->row_array();
			
			if(!$terr_det['territory_id'])
			{
				echo ("<div class='courier_mismatch' style='background:purple;'>Delivery Hub not found - ".$terr_det['town_name']."</div>");
				continue;
			}
				
			
			
			//check if terrritory already in tray link with in use
			
			// get tray terrory linkid
			$tray_terr_det = $this->db->query("
						select tray_id,tray_name,t as tray_terr_id,max_allowed from (
						select a.tray_id,tray_name,ifnull(b.tray_terr_id,0) as t,if(ifnull(b.territory_id={$terr_det['territory_id']},0),1,0) as a,b.status,a.max_allowed   
							from m_tray_info a 
							left join pnh_t_tray_territory_link b on a.tray_id = b.tray_id and is_active = 1 
							group by b.tray_id 
							having  (b.status = 1 or b.status is null) and ((a=1 and t > 0) or (a=0 and t = 0))
							order by t desc limit 1 ) as g ")->row_array();
			
			
			if($tray_terr_det)
			{
				if($tray_terr_det['tray_terr_id'])
				{
					$tray_terr_id = $tray_terr_det['tray_terr_id'];
				}else
				{
					// create tray terr link 
					$tray_terr_param=array();
					$tray_terr_param['tray_id']=$tray_terr_det['tray_id'];
					$tray_terr_param['territory_id']=$terr_det['territory_id'];
					$tray_terr_param['max_shipments']=$tray_terr_det['max_allowed'];
					$tray_terr_param['status']=1;
					$tray_terr_param['is_active']=1;
					$tray_terr_param['created_on']=cur_datetime();
					$tray_terr_param['created_by']=$user['userid'];
					$this->db->insert('pnh_t_tray_territory_link',$tray_terr_param);
					
					$tray_terr_id = $this->db->insert_id();
					
				}
			}
			
			if($tray_terr_id)
			{
				//insert the data of invoice and tray link info
				$tray_inv_param=array();
				$tray_inv_param['tray_terr_id']=$tray_terr_id;
				$tray_inv_param['invoice_no']=$bpinv['invoice_no'];
				$tray_inv_param['status']=1;
				$tray_inv_param['is_active']=1;
				$tray_inv_param['created_on']=cur_datetime();
				$tray_inv_param['created_by']=$user['userid'];
				$this->db->insert('pnh_t_tray_invoice_link',$tray_inv_param);
				
				//update maximum value of the tray
				$check_tray_items=$this->db->query("select count(*) ttl_items_in_tray from pnh_t_tray_invoice_link where tray_terr_id=?",$tray_terr_id)->row()->ttl_items_in_tray;
				
				//compare tray max valume and current valume
				if($tray_terr_det['max_allowed']==$check_tray_items)
				{
					$this->db->query("update pnh_t_tray_territory_link set status=2,modified_on=?,modified_by=? where tray_terr_id=? and status = 1 ",array($cur_datetime,$user['userid'],$tray_terr_id));
				}
			}
		
		//<!--pnh tray module end-->
		
			if($tray_terr_det)
			{
				$this->db->query("update shipment_batch_process_invoice_link set tray_id=?,packed_by=?,packed_on=?,packed=1 where invoice_no=? limit 1",array($tray_terr_det['tray_id'],$user['userid'],date('Y-m-d H:i:s'),$bpinv['invoice_no']));
				
				$trans_logprm=array();
				$trans_logprm['transid']=$this->db->query("select transid from king_invoice where invoice_no=? limit 1",$bpinv['invoice_no'])->row()->transid;
				$trans_logprm['admin']=$user['userid'];
				$trans_logprm['time']=time();
				$trans_logprm['msg']='invoice ('.$bpinv['invoice_no'].') packed';
				$this->db->insert("transactions_changelog",$trans_logprm);
				
				echo ("<div class='outscanned' style='background:#fcfcfc;'>Invoice No ".$bpinv['invoice_no']." ,".$terr_det['territory_name']." </br> Packed - TRAY No:".($tray_terr_det['tray_name'])." <script>$('#working_tray b').html('{$tray_terr_det['tray_name']}');</script></div>");
			}else{
				echo ("<div class='outscanned' style='background:#fcfcfc;'>All the trays are in use please add a new tray</div>");
			}
		}else
		{
			$this->db->query("update shipment_batch_process_invoice_link set shipped_by=?,shipped_on=?,shipped=1 where invoice_no=? limit 1",array($user['userid'],date('Y-m-d H:i:s'),$bpinv['invoice_no']));
			
			// mark shipped status in orders table  
			$inv_order_list = $this->db->query("select order_id from king_invoice where invoice_no = ? ",$bpinv['invoice_no']);
			if($inv_order_list->num_rows())
			{
				foreach($inv_order_list->result_array() as $row)
					$this->db->query("update king_orders set status = 2,actiontime = unix_timestamp() where id = ? ",$row['order_id']);
			}
			
			echo ("<div class='outscanned' style='background:#fcfcfc;'>Invoice/Order No ".$bpinv['invoice_no']." outscanned</div>");
		}
		
		}
		
		
/*		foreach($this->db->query("select order_id from king_invoice where invoice_no=?",$bpinv['invoice_no'])->result_array() as $o)
			$this->db->query("update king_orders set status=2,actiontime=".time()." where id=? limit 1",array($o['order_id']));
		$mdata['prods']=$os=$this->db->query("select inv.transid,inv.order_id,i.name,o.quantity,o.medium,o.shipid from king_invoice inv join king_orders o on o.id=inv.order_id join king_dealitems i on i.id=o.itemid where inv.invoice_no=? order by i.name asc",$bpinv['invoice_no'])->result_array();
		$mdata['transid']=$transid=$os[0]['transid'];
		$mdata['medium']=$os[0]['medium'];
		$mdata['trackingid']=$os[0]['shipid'];
		$partial=false;
		if($this->db->query("select count(1) as l from king_orders where status!=2 and transid=?",$transid)->row()->l!=0)
			$partial=true;
		$mdata['partial']=$partial;
		$payload=$this->db->query("select u.name,o.bill_email,o.ship_email from king_orders o join king_users u on u.userid=o.userid where o.id=?",$os[0]['order_id'])->row_array();
		$mdata['name']=$payload['name'];
		$msg=$this->load->view("mails/shipment",$mdata,true);
//		echo $msg;
		$this->vkm->email(array($payload['bill_email'],$payload['ship_email']),"Your order is shipped",$msg,true);
*/		
		
		exit;
	}
	
	function do_deals_bulk_upload()
	{
		$f=@fopen($_FILES['deals']['tmp_name'],"r");
		$head=fgetcsv($f);
		$template=array("itemcode","name","menu1","menu2","catid","brandid","mrp","price","pic","tax","sdate","edate","shipsin","qty","description1","description2","keywords","products");
		if(empty($head) || count($head)!=count($template))
			show_error("Invalid template structure");
		while(($data=fgetcsv($f))!=false)
		{
			$dealid=$this->erpm->p_genid(10);
			foreach($template as $k=>$v)
				$$v=$data[$k];
			$sdate_a=explode("-",$sdate);
			$edate_a=explode("-",$edate);
			if(count($sdate_a)!=3 || count($edate_a)!=3)
				show_error("Invalid date format for '$name'");
			if($this->db->query("select 1 from king_brands where id=? limit 1",$brandid)->num_rows()==0)
				show_error("No brand with brand id $brandid for deal $name");
			if($this->db->query("select 1 from king_categories where id=? limit 1",$catid)->num_rows()==0)
				show_error("No category with id $catid for deal $name");
			if($this->db->query("select 1 from king_dealitems where name=? and is_pnh=0 limit 1",$name)->num_rows()!=0)
				show_error("Duplicate deal name : $name");
			foreach(explode(",",$products) as $prod)
			{
				list($pid,$qty)=explode(":",$prod);
				if($this->db->query("select 1 from m_product_info where product_id=?",$pid)->num_rows()==0)
					show_error("No product with product id : $pid for deal name $name");
			}
		}
		fclose($f);
		$f=@fopen($_FILES['deals']['tmp_name'],"r");
		$head=fgetcsv($f);
		while(($data=fgetcsv($f))!=false)
		{
			$dealid=$this->erpm->p_genid(10);
			foreach($template as $k=>$v)
				$$v=$data[$k];
			list($d,$m,$y)=explode("-",$sdate);
			$sdate=mktime(0,0,0,$m,$d,$y);
			list($d,$m,$y)=explode("-",$edate);
			$edate=mktime(23,0,0,$m,$d,$y);
			$inp=array("dealid"=>$dealid,"menuid"=>$menu1,"menuid2"=>$menu2,"catid"=>$catid,"brandid"=>$brandid,"startdate"=>$sdate,"enddate"=>$edate,"pic"=>$pic,"tagline"=>$name,"keywords"=>$keywords,"publish"=>0,"is_coupon_applicable"=>1);
			$this->db->insert("king_deals",$inp);
			$itemid=$this->erpm->p_genid(10);
			$url=$name;
			$blacks=array("?","#","@",")","(","[","]","/","\\","!","~","`","%","^","&","*","+","=","'",'"');
			foreach($blacks as $b)
			{
				$url=str_replace(" $b","",$url);
				$url=str_replace($b,"",$url);
			}
			$url=str_replace(" ","-",$url);
			$url.="-p".rand(10,99)."t";
			if(empty($qty) || $qty==0)
				$qty=4294967295;
			$tax=$tax*100;
			$inp=array("id"=>$itemid,"dealid"=>$dealid,"shipsin"=>$shipsin,"itemcode"=>$itemcode,"price"=>$price,"orgprice"=>$mrp,"name"=>$name,"quantity"=>$qty,"pic"=>$pic,"description1"=>$description1,"description2"=>$description2,"url"=>$url,"live"=>1,"tax"=>$tax,"groupbuy"=>0);
			$this->db->insert("king_dealitems",$inp);
			foreach(explode(",",$products) as $prod)
			{
				list($pid,$qty)=explode(":",$prod);
				$inp=array("itemid"=>$itemid,"product_id"=>$pid,"product_mrp"=>$this->db->query("select mrp from m_product_info where product_id=?",$pid)->row()->mrp,"qty"=>$qty);
				$this->db->insert("m_product_deal_link",$inp);
			}
			$report[]=array($itemid,$dealid,$name);
		}
		fclose($f);
		
		$user=$this->erpm->getadminuser();
		$this->db->insert("deals_bulk_upload",array("items"=>count($report),"created_on"=>time(),"created_by"=>$user['userid']));
		$bulk_id=$this->db->insert_id();
		foreach($report as $r)
			$this->db->insert("deals_bulk_upload_items",array("bulk_id"=>$bulk_id,"item_id"=>$r[0]));
		
		if(empty($report))
			show_error("No deals created");
		ob_start();
		$f=fopen("php://output","w");
		fputcsv($f,array("Item ID","Deal ID","Name"));
		foreach($report as $p)
			fputcsv($f,$p);
		fclose($f);
		$csv=ob_get_clean();
		ob_clean();
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.("deals_bulk_upload_report_".date("d_m_y_H\h:i\m").".csv"));
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . strlen($csv));
	    ob_clean();
	    flush();
	    echo $csv;
	    redirect("admin/deals_bulk_upload");
	    exit;
	}
	
	function do_products_group_bulk_upload()
	{
		$user=$this->erpm->getadminuser();
		$f=@fopen($_FILES['pg']['tmp_name'],"r");
		$head=fgetcsv($f);
		$template=array("Group Name","Category ID","Attribute");
		$payload=array();
		$o_data=array();
		$head=array_merge(array("Group ID"),$head);
		$i=0;
		while(($data=fgetcsv($f))!=false)
		{
			$load=array();
			$load['name']=$data[0];
			$load['cat_id']=$data[1];
			if(empty($data[1]))
				show_error("Category ID can't be empty for group '{$data[0]}'");
			$attr=explode(",",$data[2]);
			if(count($data)-3<count($attr))
				show_error("Invalid attribute structure for '{$data[0]}'");
			foreach($attr as $i=>$at)
			{
				$av=explode(",",$data[$i+3]);
				$load['attrs'][$at]=array_unique($av);
			}
			$payload[]=$load;
			$o_data[]=array_combine($head,array_merge(array(0),$data));
			$i++;
		}
		foreach($payload as $i=>$pg)
		{
			$inp=array("group_name"=>$pg['name'],"cat_id"=>$pg['cat_id'],"created_on"=>time(),"created_by"=>$user['userid']);
			$this->db->insert("products_group",$inp);
			$gid=$this->db->insert_id();
			if(isset($o_data[$i]['Group ID']))
				$o_data[$i]['Group ID']=$gid;
			else
				$o_data[$i][0]=$gid;
			foreach($pg['attrs'] as $attr=>$values)
			{
				$inp=array("group_id"=>$gid,"attribute_name"=>$attr);
				$this->db->insert("products_group_attributes",$inp);
				$aid=$this->db->insert_id();
				foreach($values as $v)
					$this->db->insert("products_group_attribute_values",array("group_id"=>$gid,"attribute_name_id"=>$aid,"attribute_value"=>$v));
			}
		}
		$this->erpm->flash_msg(count($payload)." product groups uploaded");
		$this->erpm->export_csv("products_group",$o_data);
	}
	
	function do_pnh_deals_bulk_upload()
	{
		$f=@fopen($_FILES['deals']['tmp_name'],"r");
		$head=fgetcsv($f);
		$template=array("name","tagline","catid","brandid","mrp","price","store_price","nyp_price","gender_attr","pic","tax","description","products","products_group","keywords","menu","shipsin","publish");
		if(empty($head) || count($head)!=count($template))
			show_error("Invalid template structure");
		while(($data=fgetcsv($f))!=false)
		{
			$dealid=$this->erpm->p_genid(10);
			foreach($template as $k=>$v)
				$$v=$data[$k];
			if($this->db->query("select 1 from king_brands where id=? limit 1",$brandid)->num_rows()==0)
				show_error("No brand with brand id $brandid for deal $name");
			if($this->db->query("select 1 from king_categories where id=? limit 1",$catid)->num_rows()==0)
				show_error("No category with id $catid for deal $name");
			if($this->db->query("select 1 from king_dealitems where name=? and is_pnh=1 limit 1",$name)->num_rows()!=0)
				show_error("Duplicate deal name : $name");
			if(!empty($products))
			foreach(explode(",",$products) as $prod)
			{
				list($pid,$qty)=explode(":",$prod);
				if($this->db->query("select 1 from m_product_info where product_id=?",$pid)->num_rows()==0)
					show_error("No product with product id : $pid for deal name $name");
			}
		}
		fclose($f);
		$f=@fopen($_FILES['deals']['tmp_name'],"r");
		$head=fgetcsv($f);
		while(($data=fgetcsv($f))!=false)
		{
			$dealid=$this->erpm->p_genid(10);
			foreach($template as $k=>$v)
				$$v=$data[$k];

			$itemid=$this->erpm->p_genid(10);
			$dealid=$this->erpm->p_genid(10);
			
			//$pnh_id="1".$this->erpm->p_genid(7);
			
			$pnh_id=$this->db->query("select pnh_id+1 as new_pnh_id  
					from 
					(
					select a.pnh_id,(select pnh_id from king_dealitems b where b.pnh_id = a.pnh_id+1 limit 1) as has_next 
					from king_dealitems a 
						where a.pnh_id != 0 and length(a.pnh_id) = 8
						order by a.pnh_id ) as g 
					where has_next is null 
					order by pnh_id limit 1 ")->row()->new_pnh_id;
			
			$inp=array("dealid"=>$dealid,"catid"=>$catid,"brandid"=>$brandid,"pic"=>$pic,"tagline"=>$tagline,"description"=>$description,"publish"=>$publish,"menuid"=>$menu,"keywords"=>$keywords);
			$this->db->insert("king_deals",$inp);

			$inp=array("id"=>$itemid,"shipsin"=>$shipsin,"gender_attr"=>$gender_attr,"dealid"=>$dealid,"name"=>$name,"pic"=>$pic,"orgprice"=>$mrp,"price"=>$price,"store_price"=>$store_price,"nyp_price"=>$nyp_price,"is_pnh"=>1,"pnh_id"=>$pnh_id,"tax"=>$tax*100,"live"=>1);
			$this->db->insert("king_dealitems",$inp);
			
			if(!empty($products))
			foreach(explode(",",$products) as $prod)
			{
				list($pid,$qty)=explode(":",$prod);
				$inp=array("itemid"=>$itemid,"product_id"=>$pid,"product_mrp"=>$this->db->query("select mrp from m_product_info where product_id=?",$pid)->row()->mrp,"qty"=>$qty);
				$this->db->insert("m_product_deal_link",$inp);
			}
			
			if(!empty($products_group))
			foreach(explode(",",$products_group) as $pg)
			{
				list($gid,$qty)=explode(":",$pg);
				$inp=array("itemid"=>$itemid,"group_id"=>$gid,"qty"=>$qty);
				$this->db->insert("m_product_group_deal_link",$inp);
			}
			
			$report[]=array($itemid,$dealid,$name,$pnh_id);
		}
		fclose($f);
		
		$user=$this->erpm->getadminuser();
		$this->db->insert("deals_bulk_upload",array("items"=>count($report),"created_on"=>time(),"created_by"=>$user['userid']));
		$bulk_id=$this->db->insert_id();
		foreach($report as $r)
			$this->db->insert("deals_bulk_upload_items",array("bulk_id"=>$bulk_id,"item_id"=>$r[0]));
		
		if(empty($report))
			show_error("No deals created");
		ob_start();
		$f=fopen("php://output","w");
		fputcsv($f,array("Item ID","Deal ID","Name","PNH ID"));
		foreach($report as $p)
			fputcsv($f,$p);
		fclose($f);
		$csv=ob_get_clean();
		ob_clean();
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.("pnh_deals_bulk_upload_report_".date("d_m_y_H\h:i\m").".csv"));
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . strlen($csv));
	    ob_clean();
	    flush();
	    echo $csv;
	    redirect("admin/deals_bulk_upload");
	    exit;
	}
	
	function do_prods_bulk_upload()
	{
		$user = $this->erpm->auth();
		$f=@fopen($_FILES['prods']['tmp_name'],"r");
		$head=fgetcsv($f);
		while(($data=fgetcsv($f))!==false)
		{
			if($this->db->query("select 1 from m_product_info where product_name=?",$data[0])->num_rows()!=0)
				show_error("Duplicate name for product : ".$data[0]);
			if($this->db->query("select 1 from king_brands where id=?",$data[2])->num_rows()==0)
				show_error("No brand with id {$data[2]} for product {$data[0]}");
			if(!empty($data[12]) || $data[12]!=0)
			{
				$gid=$data[12];
				$group=$this->db->query("select 1 from products_group where group_id=?",$gid)->row_array();
				if(empty($group))
					show_error("Invalid group ID for product '{$data[0]}'");
				if(empty($data[13]))
					show_error("Attribute data missing for product '{$data[0]}'");
				$adata=explode(",",$data[13]);
				$n_attrs=$this->db->query("select count(1) as n from products_group_attributes where group_id=?",$gid)->row()->n;
				if($n_attrs!=count($adata))
					show_error("Insufficient Attribute data for product '{$data[0]}'");
				foreach($adata as $av)
				{
					$aa=explode(":",$av);
					if(count($aa)!=2)
						show_error("Missing attribute name/value for '{$data[0]}'");
					list($a,$v)=$aa;
					if($this->db->query("select 1 from products_group_attributes where group_id=$gid and attribute_name=?",$a)->num_rows()==0)
						show_error("Invalid attribute '$a' for '{$data[0]}'");
					if($this->db->query("select 1 from products_group_attribute_values where group_id=$gid and attribute_value=?",$v)->num_rows()==0)
						show_error("Invalid attribute value '$v' for '{$data[0]}'");
				}
			}
		}
		fclose($f);
		$f=@fopen($_FILES['prods']['tmp_name'],"r");
		$head=fgetcsv($f);
		$template=array("Product Name","Short Description","Brand (ID)","Size","Unit of measurement","MRP","VAT %","Purchase Cost","Barcode","Is offer (0 or 1)","Is Sourceable (0 or 1)","is serial required");
		$db_temp=array("product_name","short_desc","brand_id","size","uom","mrp","vat","purchase_cost","barcode","is_offer","is_sourceable","is_serial_required");
		if(empty($head) || count($head)<count($template))
			show_error("Invalid template structure");
		$pids=array();
		while(($data=fgetcsv($f))!==false)
		{
			$inp=array("product_code"=>"P".rand(10000,99999));
			foreach($db_temp as $i=>$d)
				$inp[$d]=$data[$i];
			
			$inp['created_on'] = date('Y-m-d H:i:s');
			$inp['created_by'] = $user['userid'];
			$this->db->insert("m_product_info",$inp);
			
			$bid=$data[2];
			$pid=$this->db->insert_id();
			$pids[]=array($pid,$data[0],$bid);
			$rackbin=0;$location=0;
			$raw_rackbin=$this->db->query("select l.location_id as default_location_id,l.id as default_rack_bin_id from m_rack_bin_brand_link b join m_rack_bin_info l on l.id=b.rack_bin_id where b.brandid=?",$bid)->row_array();
			if(!empty($raw_rackbin))
			{
				$rackbin=$raw_rackbin['default_rack_bin_id'];
				$location=$raw_rackbin['default_location_id'];
			}
			$this->db->query("insert into t_stock_info(product_id,location_id,rack_bin_id,mrp,available_qty) values(?,?,?,?,0)",array($pid,$location,$rackbin,$data[5]));
			if(!empty($data[12]) || $data[12]!=0)
			{
				$gid=$data[12];
				$adata=explode(",",$data[13]);
				foreach($adata as $av)
				{
					$aa=explode(":",$av);
					list($a,$v)=$aa;
					$aid=$this->db->query("select attribute_name_id as a from products_group_attributes where group_id=$gid and attribute_name=?",$a)->row()->a;
					$vid=$this->db->query("select attribute_value_id as a from products_group_attribute_values where group_id=$gid and attribute_value=?",$v)->row()->a;
					$inp=array("group_id"=>$gid,"product_id"=>$pid,"attribute_name_id"=>$aid,"attribute_value_id"=>$vid);
					$this->db->insert("products_group_pids",$inp);
				}
			}
		}
		fclose($f);
		if(empty($pids))
			show_error("No products created");
		ob_start();
		$f=fopen("php://output","w");
		fputcsv($f,array("product_id","name","brandid"));
		foreach($pids as $p)
			fputcsv($f,$p);
		fclose($f);
		$csv=ob_get_clean();
		ob_clean();
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.("products_bulk_upload_report_".date("d_m_y_H\h:i\m").".csv"));
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . strlen($csv));
	    ob_clean();
	    flush();
	    echo $csv;
	    exit;
	}
	
	function export_csv($fname,$data,$date=true)
	{
		
		$h_filename=$fname.".csv";
	    if($date)
	    	$h_filename="{$fname}_".date("d-m-y_H\h-i\m").".csv";
		
		$head=array();
		if(!empty($data)) 
			foreach($data[0] as $k=>$v)
				$head[]=$k;
		
		$f=fopen($h_filename,"w");
		fputcsv($f,$head);
		foreach($data as $p)
			fputcsv($f,$p);
		fclose($f);
		
	    header('Content-Disposition: attachment; filename='.$h_filename);
	    header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename='.$h_filename);
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($h_filename));
	    echo file_get_contents($h_filename);
		unlink($h_filename);
		
	    exit;
	}
	
	function do_export_data()
	{
		ini_set('memory_limit','1024M');
		$type=$this->input->post("type");
		switch($type)
		{
			case 0:
				$this->erpm->export_csv("products",$this->db->query("select product_id,product_name,mrp,brand_id,is_sourceable  from m_product_info where 1 order by product_name asc")->result_array());break;
			case 1:
				$this->erpm->export_csv("brands",$this->db->query("select id,name from king_brands order by name asc")->result_array());break;
			case 2:
				$this->erpm->export_csv("categories",$this->db->query("select id,name from king_categories order by name asc")->result_array());break;
			case 3:
				$this->erpm->export_csv("menu",$this->db->query("select id,name from king_menu order by name asc")->result_array());
		}
	}
	
	function do_add_storage_loc()
	{
		foreach(array("location_name","damaged") as $inp)
			$$inp=$this->input->post($inp);
		$user=$this->erpm->getadminuser();
		$this->db->query("insert into m_storage_location_info(location_name,is_damaged,created_on,created_by) values(?,?,now(),?)",array($location_name,$damaged,$user['userid']));
		$this->session->set_flashdata("erp_pop_info","New Location added");
		redirect("admin/storage_locs");
	}
	
	function do_updatecat($cat)
	{
		$name=$_POST['cat_name'];
		$type=$_POST['main'];
		$this->db->query("update king_categories set type=?,name=? where id=?",array($type,$name,$cat));
		$this->session->set_flashdata("erp_pop_info","Category updated");
		redirect("admin/viewcat/$cat");
	}
	
	function do_addnewcat()
	{
		$name=$_POST['cat_name'];
		$type=$_POST['main'];
		$url=preg_replace('/[^a-zA-Z0-9_\-]/','',$name);
		$url=str_replace(" ","-",$url);
		$this->db->query("insert into king_categories(name,type,url) values(?,?,?)",array($name,$type,$url));
		$id=$this->db->insert_id();
		$this->session->set_flashdata("erp_pop_info","new category added");
		redirect("admin/viewcat/$id");
	}
	
	function do_pnh_order_import($payload=array(),$output=true)
	{
		if(empty($payload))
		{
			$f=fopen($_FILES['csv']['tmp_name'],"r");
			$head=fgetcsv($f);
			$out=array($head);
			$c=0;
			$payload=array();
			while(($data=fgetcsv($f))!==false)
				$payload[]=$data;
		}
		$out=array();
		$ret_out=array();
		foreach($payload as $pl)
		{
			$total=0;$d_total=0;
			$status=1;$msg="";
			if(count($pl)!=12)
			{
				$status=0;$msg="Invalid template structure";
			}
			$fran=$this->db->query("select * from pnh_m_franchise_info where pnh_franchise_id=?",$pl[0])->row_array();
			$pids=explode(",",$pl[2]);
			$pqtys=explode(",",$pl[3]);
			$transid="";
			if($status && empty($fran))
			{
				$status=0;$msg="Invalid franchise id";
			}
			if($status && $fran['is_suspended']==1)
			{
				$status=0;$msg="Franchise account suspended";
			}
			if($status && count($pqtys)!=count($pids))
			{
				$status=0;$msg="Invalid Order-Qty structure";
			}
			$mid=$pl[1];
			if($status && $this->db->query("select 1 from pnh_member_info where pnh_member_id=?",$mid)->num_rows()==0 && $this->db->query("select 1 from pnh_m_allotted_mid where franchise_id=? and ? between mid_start and mid_end",array($fran['franchise_id'],$mid))->num_rows()==0)
			{
				$status=0;$msg="MID:$mid is not allotted to this franchise";			
			}
			
			
			if($status && !empty($pl[11]))
			{
				$attr=json_decode($pl[11]);
				$attr_data=array();
				foreach($attr as $pid=>$at)
				{
					$attrs=explode(",",$at);
					$gid=$this->db->query("select group_id from m_product_groups_deal_link where itemid=?",$this->db->query("select id from king_dealitems where is_pnh=1 and pnh_id=?",$pid)->row()->id)->row()->group_id;
					foreach($attrs as $att)
					{
						list($an,$vn)=explode(":",$att);
						$a=$this->db->query("select attribute_name_id as a from products_group_attributes where group_id=? and attribute_name=?",array($gid,$an))->row()->a;
						$v=$this->db->query("select attribute_value_id as a from products_group_attribute_values where group_id=? and attribute_name_id=? and attribute_value=?",array($gid,$a,$vn))->row()->a;
						if(!isset($attr_data[$pid]))
							$attr_data[$pid]=array();
						$attr_data[$pid][$a]=$v;
					}
				}
				foreach($pids as $pid)
				{
					if(!isset($attr_data[$pid]))
						continue;
					$prods=array();
					$i=0;
					foreach($attr_data[$pid] as $a=>$v)
					{
						if($i==0)
						{
							$pr=$this->db->query("select product_id from products_group_pids where attribute_name_id=? and attribute_value_id=?",array($a,$v))->result_array();
							foreach($pr as $p)
								$prods[]=$p['product_id'];
						}else{
						$c_prods=$prods;
						$prods=array();
						$pr=$this->db->query("select product_id from products_group_pids where attribute_name_id=? and attribute_value_id=?",array($a,$v))->result_array();
						foreach($pr as $p)
							if(in_array($p['product_id'],$c_prods))
								$prods[]=$p['product_id'];
						}
						$i++;
						if(empty($prods))
						{
							$status=0;
							$msg="{$pid} is not available for selected attribute combination";
							break;
						}else{ $order_attr[$pid]=$prods[0]['product_id'];}
					}
				}
			}
			
			
			if($status)
			{
				$itemids=array();
				$qtys=array();
				foreach(explode(",",$pl[2]) as $i_c=>$pid)
				{
					$pid=trim($pid);
					$r_id=$this->db->query("select id,dealid from king_dealitems where is_pnh=1 and pnh_id=?",$pid)->row_array();
					if(empty($r_id))
					{
						$status=0;$msg="Invalid PNH Product ID $pid";break;
					}
					if($this->db->query("select publish from king_deals where dealid=?",$r_id['dealid'])->row()->publish==0)
					{
						$status=0;$msg="PNH Product ID $pid is disabled";break;
					}
					$itemids[]=$r_id['id'];
					$qtys[]=$pqtys[$i_c];
				}
				if($status)
				{
				$avail=$this->erpm->do_stock_check($itemids,$qtys);
				foreach($itemids as $i=>$id)
					if(!in_array($id,$avail))
					{
						$status=0;$msg="PNH Product ID {$pl[2][$i]} is out of stock";
					}
				}
			}
			if($status)
			{
			/* 	$menuid=$this->db->QUERY("select d.menuid,m.default_margin as margin from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu m ON m.id=d.menuid where i.is_pnh=1 and i.pnh_id=?",$pid)->row_array();
				$fran1=$this->db->query("select * from pnh_franchise_menu_link where fid=? and menuid=?",array($fid,$menuid['menuid']))->row_array();
			 */	$margin=$this->db->query("select margin,combo_margin from pnh_m_class_info where id=?",$fran['class_id'])->row_array();
			/*	if($fran1['sch_discount_start']<time() && $fran1['sch_discount_end']>time() && $fran1['is_sch_enabled'])
					$margin['margin']+=$fran1['sch_discount'];*/
				$items=array();
				foreach($pids as $i=>$p)
					$items[]=array("pid"=>$p,"qty"=>$pqtys[$i]);
				$total=0;$d_total=0;
				$itemnames=$itemids=array();
				foreach($items as $i=>$item)
				{
					$prod=$this->db->query("select i.*,d.publish from king_dealitems i join king_deals d on d.dealid=i.dealid where i.is_pnh=1 and  i.pnh_id=? and i.pnh_id!=0",$item['pid'])->row_array();
					$menuid=$this->db->QUERY("select d.menuid,m.default_margin as margin from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu m ON m.id=d.menuid where i.is_pnh=1 and i.pnh_id=?",$item['pid'])->row_array();
					$fran1=$this->db->query("select * from pnh_franchise_menu_link where fid=? and menuid=?",array($fran['franchise_id'],$menuid['menuid']))->row_array();
					
					if($fran1['sch_discount_start']<time() && $fran1['sch_discount_end']>time() && $fran1['is_sch_enabled'])
						$margin['margin']+=$fran1['sch_discount'];
					
					$items[$i]['tax']=$prod['tax'];
					$items[$i]['mrp']=$prod['orgprice'];
					if($fran['is_lc_store'])
						$items[$i]['price']=$prod['store_price'];
					else
						$items[$i]['price']=$prod['price'];
					//$margin=$this->erpm->get_pnh_margin($fran['franchise_id'],$item['pid']);
					$margin=$this->erpm->get_pnh_margin($fran1['fid'],$item['pid']);
					$items[$i]['itemid']=$prod['id'];
					if($prod['is_combo']=="1")
						$items[$i]['discount']=$items[$i]['price']/100*$margin['combo_margin'];
					else
						$items[$i]['discount']=$items[$i]['price']/100*$margin['margin'];
					$total+=$items[$i]['price']*$items[$i]['qty'];
					$d_total+=($items[$i]['price']-$items[$i]['discount'])*$items[$i]['qty'];
					$itemids[]=$prod['id'];
					$itemnames[]=$prod['name'];
					$items[$i]['margin']=$margin;
				}
				if($fran['credit_limit']+$fran['current_balance']<$d_total)
				{
					$status=0;$msg="Insufficient balance! Balance in your account Rs {$fran['current_balance']} Total order amount : Rs $d_total";
				}
				else{
					if($this->db->query("select 1 from pnh_member_info where pnh_member_id=?",$mid)->num_rows()==0)
					{ 
						$this->db->query("insert into king_users(name,is_pnh,createdon) values(?,1,?)",array("PNH Member: $mid",time()));
						$userid=$this->db->insert_id();
						$this->db->query("insert into pnh_member_info(user_id,pnh_member_id,franchise_id) values(?,?,?)",array($userid,$mid,$fran['franchise_id']));
						$npoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+PNH_MEMBER_FEE;
						$this->db->query("update pnh_member_info set points=? where user_id=? limit 1",array($npoints,$userid));
						$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,"",PNH_MEMBER_FEE,$npoints,time()));
						$this->erpm->pnh_fran_account_stat($fran['franchise_id'],1,PNH_MEMBER_FEE,"member fee for $mid","member",$mid);
						$this->erpm->pnh_fran_account_stat($fran['franchise_id'],0,PNH_MEMBER_BONUS,"New member bonus for $mid","member",$mid);
						$this->db->query("update pnh_member_info set first_name=?,address=?,city=?,pincode=?,email=?,mobile=?,created_on=? where user_id=? limit 1",array($pl[5],$pl[6],$pl[7],$pl[8],$pl[9],$pl[10],time(),$userid));
					}
					else
						$userid=$this->db->query("select user_id from pnh_member_info where pnh_member_id=?",$mid)->row()->user_id;
					$transid=strtoupper("PNH".random_string("alpha",3).$this->p_genid(5));
					$this->db->query("insert into king_transactions(transid,amount,paid,mode,init,actiontime,is_pnh,franchise_id) values(?,?,?,?,?,?,?,?)",array($transid,$d_total,$d_total,3,time(),time(),1,$fran['franchise_id']));
					foreach($items as $item)
					{
						$pid=$item['pnh_id'];
						$inp=array("id"=>$this->p_genid(10),"transid"=>$transid,"userid"=>$userid,"itemid"=>$item['itemid'],"brandid"=>"");
						$inp["brandid"]=$this->db->query("select d.brandid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=?",$item['itemid'])->row()->brandid;
						$inp["bill_person"]=$inp['ship_person']=$fran['franchise_name'];
						$inp["bill_address"]=$inp['ship_address']=$fran['address'];
						$inp["bill_city"]=$inp['ship_city']=$fran['city'];
						$inp['bill_pincode']=$inp['ship_pincode']=$fran['postcode'];
						$inp['bill_phone']=$inp['ship_phone']=$fran['login_mobile1'];
						$inp['bill_email']=$inp['ship_email']=$fran['email_id'];
						$inp['bill_state']=$inp['ship_state']=$fran['state'];
						$inp['quantity']=$item['qty'];
						$inp['time']=time();
						$inp['ship_landmark']=$inp['bill_landmark']=$fran['locality'];
						$inp['bill_country']=$inp['ship_country']="India";
						$inp['i_orgprice']=$item['mrp'];
						$inp['i_price']=$item['price'];
						$inp['i_discount']=$item['mrp']-$item['price'];
						$inp['i_coup_discount']=$item['discount'];
						$inp['i_tax']=$item['tax'];
						$this->db->insert("king_orders",$inp);
						$oid=$this->db->insert_id();
						if(isset($order_attr[$pid]))
							$this->db->insert("products_group_orders",array("transid"=>$transid,"order_id"=>$oid,"product_id"=>$order_attr[$pid]));
						$m_inp=array("transid"=>$transid,"itemid"=>$item['itemid'],"mrp"=>$item['mrp'],"price"=>$item['price'],"base_margin"=>$item['margin']['base_margin'],"sch_margin"=>$item['margin']['sch_margin'],"qty"=>$item['qty'],"final_price"=>$item['price']-$item['discount']);
						$this->db->insert("pnh_order_margin_track",$m_inp);
					}
					$this->erpm->pnh_fran_account_stat($fran['franchise_id'],1, $d_total,"Order $transid - Total Amount: Rs $total","transaction",$transid);
					$balance=$this->db->query("select current_balance from pnh_m_franchise_info where franchise_id=?",$fran['franchise_id'])->row()->current_balance;
					$this->erpm->pnh_sendsms($fran['login_mobile1'],"Your order is placed successfully! Total order amount :Rs $total. Amount deducted is Rs $d_total. Your order ID is $transid Balance in your account Rs $balance",$fran['franchise_id']);
					$points=$this->db->query("select points from pnh_loyalty_points where amount<? order by amount desc limit 1",$total)->row_array();
					if(!empty($points))
						$points=$points['points'];
					else
						$points=0;
					$apoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+$points;
					$this->db->query("update pnh_member_info set points=points+? where user_id=? limit 1",array($points,$userid));
					$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,$transid,$points,$apoints,time()));
					$this->erpm->do_trans_changelog($transid,$output?"PNH order created through import":"PNH Order created through data api",$output?null:1);
					$ins['transid'] = $transid;
					$ins['note'] = 'User Note:'.$pl[4];
					$ins['status'] = 1;
					$ins['note_priority'] = 1;
					$ins['created_on'] = date('Y-m-d H:i:s');
					$this->db->insert('king_transaction_notes',$ins);

					$franid=$fran['franchise_id'];
					$billno=10001;
					$nbill=$this->db->query("select bill_no from pnh_cash_bill where franchise_id=? order by bill_no desc limit 1",$franid)->row_array();
					if(!empty($nbill))
						$billno=$nbill['bill_no']+1;
					$inp=array("bill_no"=>$billno,"franchise_id"=>$franid,"transid"=>$transid,"user_id"=>$userid,"status"=>1);
					$this->db->insert("pnh_cash_bill",$inp);
				
				}
			}
			$ret_plo=array();
			$plo=array();
			$ret_plo['pnh_franchise_id']=$pl[0];$ret_plo['member_id']=$pl[1];$ret_plo['pnh_product_id']=$pl[2];$ret_plo['qtys']=$pl[3];
			$plo['PNH Franchise ID']=$pl[0];$plo['Member ID']=$pl[1];$plo['PNH Product ID']=$pl[2];$plo['Qtys']=$pl[3];
			$plo['Status']=$status;
			$plo['Msg']=$msg;
			$plo['Transid']=$transid;
			$plo['Order value']=$total;
			$plo['Amount detected']=$d_total;
			$ret_plo['status']=$status;$ret_plo['msg']=$msg;$ret_plo['transid']=$transid;$ret_plo['order_value']=$total;$ret_plo['amount_detected']=$d_total;
			$out[]=$plo;
			$ret_out[]=$ret_plo;
		}
		if($output)
			$this->erpm->export_csv("pnh_order_import_results",$out);
		return $ret_out;
	}
	
	function getadminuser()
	{
		return $this->session->userdata("admin_user");
	}
	
	function getinvoiceforpacking($inv_no)
	{
		if($this->db->query("select 1 from shipment_batch_process_invoice_link where p_invoice_no=? and packed=0 and (invoice_no is null or invoice_no = 0) ",$inv_no)->num_rows()==0)
			show_error("Proforma Invoice already packed");
		$this->benchmark->mark('code_start');
		$ret=$this->db->query("select d.pic,d.is_pnh,e.menuid,i.discount,p.product_id,p.mrp,p.barcode,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no from proforma_invoices i join king_orders o on o.id=i.order_id and i.transid = o.transid join m_product_deal_link pl on pl.itemid=o.itemid join m_product_info p on p.product_id=pl.product_id join king_dealitems d on d.id=o.itemid join king_deals e on e.dealid=d.dealid where i.p_invoice_no=? and i.invoice_status=1 order by o.sno ",$inv_no)->result_array();
		$ret2=$this->db->query("select d.pic,d.is_pnh,e.menuid,i.discount,i.discount,p.product_id,p.mrp,p.barcode,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no from proforma_invoices i join king_orders o on o.id=i.order_id and i.transid = o.transid join products_group_orders pgo on pgo.order_id=o.id join m_product_group_deal_link pl on pl.itemid=o.itemid join m_product_info p on p.product_id=pgo.product_id join king_dealitems d on d.id=o.itemid join king_deals e on e.dealid=d.dealid where i.p_invoice_no=? and i.invoice_status=1 order by o.sno ",$inv_no)->result_array();
		$ret=array_merge($ret,$ret2);
		if(empty($ret))
			show_error("Proforma Invoice not found or Invoice is already cancelled");
		$this->benchmark->mark('code_end');
		return $ret;
	}

	function getbatch($bid)
	{
		return $this->db->query("select * from shipment_batch_process where batch_id=?",$bid)->row_array();
	}
	
	function getbatchinvoices($bid,$fid=0)
	{
		//return $this->db->query("select pt.courier_name as p_courier_name,t.init as ordered_on,b.*,pi.transid as pi_transid,pi.invoice_status as p_invoice_status,i.transid,i.invoice_status from shipment_batch_process_invoice_link b left outer join proforma_invoices pi on pi.p_invoice_no=b.p_invoice_no left outer join king_invoice i on i.invoice_no=b.invoice_no left outer join king_transactions t on t.transid=pi.transid left outer join partner_transaction_details pt on pt.transid=t.transid and pt.order_no = t.partner_reference_no where batch_id=? group by b.p_invoice_no",$bid)->result_array();
		$param=array();
		$cond='';
		if($fid)
		{
			$cond.=" and t.franchise_id=? ";
			$param[]=$fid;
		}
		
		$sql="select pt.courier_name as p_courier_name,t.init as ordered_on,b.*,pi.transid as pi_transid,
			         pi.invoice_status as p_invoice_status,i.transid,i.invoice_status 
						from shipment_batch_process_invoice_link b 
						left outer join proforma_invoices pi on pi.p_invoice_no=b.p_invoice_no 
						left outer join king_invoice i on i.invoice_no=b.invoice_no 
						left outer join king_transactions t on t.transid=pi.transid 
						left outer join partner_transaction_details pt on pt.transid=t.transid and pt.order_no = t.partner_reference_no 
					where 1 $cond and batch_id=? group by b.p_invoice_no";
		$param[]=$bid;
		return $this->db->query($sql,$param)->result_array();
	}
	
	function getpnhreport($fid)
	{
		$lmonth=mktime(0,0,1,date("m")-1,1);
		$month=mktime(0,0,1,date("m"),1);
		$ret['orders']=$this->db->query("select count(1) as l from king_transactions where is_pnh=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->l;
		$ret['m_orders']=$this->db->query("select count(1) as l from king_transactions where is_pnh=1 and init>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->l;
		$ret['a_orders']=$this->db->query("select avg(amount) as l from king_transactions where is_pnh=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->l;
		$ret['a_items']=$this->db->query("select avg(n) as l from(select count(o.id) as n from king_orders o join king_transactions t on t.transid=o.transid and t.is_pnh=1".($fid!=0?" and franchise_id=$fid":"")." group by o.transid) as d")->row()->l;
		$ret['sales']=$this->db->query("select sum(amount) as l from king_transactions where is_pnh=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->l;
		$ret['m_sales']=$this->db->query("select sum(amount) as l from king_transactions where is_pnh=1 and init>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->l;
		$ret['users']=$this->db->query("select count(1) as l from pnh_member_info".($fid!=0?" where franchise_id=?":""),$fid)->row()->l;
		$ret['m_users']=$this->db->query("select count(1) as l from king_users u join pnh_member_info m on m.user_id=u.userid where u.createdon>$month".($fid!=0?" and m.franchise_id=?":""),$fid)->row()->l;
		$ret['credits']=$this->db->query("select sum(amount) as s from pnh_franchise_account_stat where type=0".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['debits']=$this->db->query("select sum(amount) as s from pnh_franchise_account_stat where type=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['m_credits']=$this->db->query("select sum(amount) as s from pnh_franchise_account_stat where type=0 and created_on>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['m_debits']=$this->db->query("select sum(amount) as s from pnh_franchise_account_stat where type=1 and created_on>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['comm']=$this->db->query("select sum(o.i_coup_discount*o.quantity) as s from king_orders o join king_transactions t on t.transid=o.transid and t.is_pnh=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['m_comm']=$this->db->query("select sum(o.i_coup_discount*o.quantity) as s from king_orders o join king_transactions t on t.transid=o.transid and t.is_pnh=1".($fid!=0?" and franchise_id=?":"")." where o.time>$month",$fid)->row()->s;
		$ret['topup']=$this->db->query("select sum(receipt_amount) as s from pnh_t_receipt_info where receipt_type=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['m_topup']=$this->db->query("select sum(receipt_amount) as s from pnh_t_receipt_info where receipt_type=1 and created_on>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['r_topup']=$this->db->query("select sum(receipt_amount) as s from pnh_t_receipt_info where receipt_type=1 and status=1".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['m_r_topup']=$this->db->query("select sum(receipt_amount) as s from pnh_t_receipt_info where receipt_type=1 and status=1 and created_on>$month".($fid!=0?" and franchise_id=?":""),$fid)->row()->s;
		$ret['c_a_orders']=$this->db->query("select round(((select sum(amount) from king_transactions where is_pnh=1 and init > $month".($fid!=0?" and franchise_id=$fid":"").")-(select sum(amount) from king_transactions where is_pnh=1 and init between $lmonth and $month".($fid!=0?" and franchise_id=$fid":"")."))/(select sum(amount) from king_transactions where is_pnh=1 and init between $lmonth and $month".($fid!=0?" and franchise_id=$fid":"").")*100,2) as s")->row()->s;
		$ret['c_n_orders']=$this->db->query("select round(((select count(1) from king_transactions where is_pnh=1 and init > $month".($fid!=0?" and franchise_id=$fid":"").")-(select count(1) from king_transactions where is_pnh=1 and init between $lmonth and $month".($fid!=0?" and franchise_id=$fid":"")."))/(select sum(1) from king_transactions where is_pnh=1 and init between $lmonth and $month".($fid!=0?" and franchise_id=$fid":"").")*100,2) as s")->row()->s;
		return $ret;
	}
	
	function getinvestorreport()
	{
		$month=mktime(0,0,1,date("m"),1);
		$ret['orders']=$this->db->query("select count(1) as l from king_orders")->row()->l;
		$ret['m_orders']=$this->db->query("select count(1) as l from king_orders where time>?",$month)->row()->l;
		$ret['a_orders']=$this->db->query("select avg(amount) as l from king_transactions where status=1 or mode=1")->row()->l;
		$ret['a_items']=$this->db->query("select avg(n) as l from(select count(id) as n from king_orders group by transid) as d")->row()->l;
		$ret['sales']=$this->db->query("select sum(amount) as l from king_transactions where status=1 or mode=1")->row()->l+$this->db->query("select sum(total_invoice_value) as l from t_client_invoice_info")->row()->l;
		$ret['m_sales']=$this->db->query("select sum(amount) as l from king_transactions where (status=1 or mode=1) and init>$month")->row()->l+$this->db->query("select sum(total_invoice_value) as l from t_client_invoice_info i where i.created_date>=?",date("Y-m-d",$month))->row()->l;
		$ret['stock']=$this->db->query("select sum(mrp*available_qty) as l from t_stock_info")->row()->l;
		$ret['m_stock']=$this->db->query("select sum(mrp*available_qty) as l from t_stock_info where created_on>=?",date("Y-m-d",$month))->row()->l;
		$ret['users']=$this->db->query("select count(1) as l from king_users")->row()->l;
		return $ret;
	}
	
	function getprodproclistfortransids($tids)
	{
		foreach(explode(",",$tids) as $pinv)
		{
			$sql="select o.transid,p.product_name as product,p.product_id,pl.qty*o.quantity as qty from shipment_batch_process_invoice_link si join proforma_invoices i on i.p_invoice_no=si.p_invoice_no and i.invoice_status=1 join king_orders o on o.id=i.order_id join m_product_deal_link pl on pl.itemid=o.itemid join m_product_info p on p.product_id=pl.product_id where si.p_invoice_no=? and si.packed=0 order by p.product_name asc";
			$raw=$this->db->query($sql,$pinv)->result_array();
			$prods=array();
			$pids=array();
			foreach($raw as $r)
			{
				if(!isset($prods[$r['product_id']]))
					$prods[$r['product_id']]=array("product_id"=>$r['product_id'],"product"=>$r['product'],"qty"=>0,"location"=>"");
				$prods[$r['product_id']]['qty']+=$r['qty'];
				$pids[]=$r['product_id'];
			}
			$pids=array_unique($pids);
			$raw_locs=$this->db->query("select s.mrp,s.product_id,rb.rack_name,rb.bin_name from t_stock_info s join m_rack_bin_info rb on rb.id=s.rack_bin_id where s.product_id in ('".implode("','",$pids)."') order by s.stock_id asc")->result_array();
			$ret=array();
			foreach($prods as $i=>$p)
			{
				$q=$p['qty'];
				$locations=array();
				$mrps=array();
				foreach($raw_locs as $s)
				{
					if($s['product_id']!=$i)
						continue;
					$q-=$s['available_qty'];
					$locations[]=$s['rack_name'].$s['bin_name'];
					$mrps[]="Rs ".$s['mrp'];
					if($q<=0)
						break;
				}
				$prods[$i]['location']=$loc=implode(", ",array_unique($locations));
				$prods[$i]['mrp']=implode(", ",array_unique($mrps));
				$assoc_loc=$loc.substr($p['product'],0,10).rand(110,9999);
				$ret[$assoc_loc]=$prods[$i];
			}
			ksort($ret);
			$transid=$r['transid'];
			$rdata["$transid ($pinv)"]=$ret;
		}
		return $rdata;
	}
	
	function getprodproclist($bid)
	{
		//$sql="select p.product_name as product,p.product_id,pl.qty*o.quantity as qty from shipment_batch_process_invoice_link si join proforma_invoices i on i.p_invoice_no=si.p_invoice_no and i.invoice_status=1 join king_orders o on o.id=i.order_id join m_product_deal_link pl on pl.itemid=o.itemid join m_product_info p on p.product_id=pl.product_id where si.batch_id=? and si.packed=0 order by p.product_name asc";
		$sql = "select p.product_name as product,p.product_id,(pl.qty*o.quantity) as qty 
from proforma_invoices i 
join king_orders o on o.id=i.order_id 
join m_product_deal_link pl on pl.itemid=o.itemid 
join m_product_info p on p.product_id=pl.product_id 
join (select distinct p_invoice_no from shipment_batch_process_invoice_link where batch_id = ? and packed=0) as h on h.p_invoice_no = i.p_invoice_no
where i.invoice_status=1 
order by p.product_name asc 
		";
		$raw=$this->db->query($sql,$bid)->result_array();
		$prods=array();
		$pids=array();
		foreach($raw as $r)
		{
			if(!isset($prods[$r['product_id']]))
				$prods[$r['product_id']]=array("product_id"=>$r['product_id'],"product"=>$r['product'],"qty"=>0,"location"=>"");
			$prods[$r['product_id']]['qty']+=$r['qty'];
			$pids[]=$r['product_id'];
		}
		$pids=array_unique($pids);
		$raw_locs=$this->db->query("select s.mrp,s.product_id,rb.rack_name,rb.bin_name from t_stock_info s join m_rack_bin_info rb on rb.id=s.rack_bin_id where s.product_id in ('".implode("','",$pids)."') order by s.stock_id asc")->result_array();
		$ret=array();
		foreach($prods as $i=>$p)
		{
			$q=$p['qty'];
			$locations=array();
			$mrps=array();
			foreach($raw_locs as $s)
			{
				if($s['product_id']!=$i)
					continue;
				$q-=$s['available_qty'];
				$locations[]=$s['rack_name'].$s['bin_name'];
				$mrps[]="Rs ".$s['mrp'];
				if($q<=0)
					break;
			}
			$prods[$i]['location']=$loc=implode(", ",array_unique($locations));
			$prods[$i]['mrp']=implode(", ",array_unique($mrps));
			$assoc_loc=$loc.substr($p['product'],0,10).rand(110,9999);
			$ret[$assoc_loc]=$prods[$i];
		}
		ksort($ret);
		return $ret;
	}
	
	function getbatchs_date_range($s,$e)
	{
		if(!$s)
		{
			$s=date("Y-m-d",mktime(0,0,0,date("m"),1));
			$e=date("Y-m-d",mktime(0,0,0,date("m")+1,date("t")));
		}
		$sql="select * from shipment_batch_process where created_on between ? and ? order by batch_id desc";
		return $this->db->query($sql,array($s,$e))->result_array();
	}
	
	function getbatchs()
	{
		$sql="select * from shipment_batch_process order by batch_id desc";
		return $this->db->query($sql)->result_array();
	}
	
	function getpendingbatchs()
	{
		$sql="select * from shipment_batch_process where status=0 or status=1 order by batch_id asc";
		return $this->db->query($sql)->result_array();
	}
	
	function getpartialshipments($s,$e)
	{
		$trans=array();
		$itemids=array();
		$raw_trans=$this->db->query("select o.*,t.franchise_id from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=1 and t.init between ? and ? order by t.priority desc, t.init asc",array($s,$e))->result_array();
		foreach($raw_trans as $t)
		{
			$transid=$t['transid'];
			if(!isset($trans[$transid]))
				$trans[$transid]=array();
			$trans[$transid][]=$t;
			$itemids[]=$t['itemid'];
		}
		if(empty($trans))
			return array();
		$itemids=array_unique($itemids);
		$raw_prods=$this->db->query("select itemid,qty,product_id from m_product_deal_link where itemid in ('".implode("','",$itemids)."')")->result_array();
		$products=array();
		$productids=array();
		$partials=array();
		foreach($raw_prods as $p)
		{
			$itemid=$p['itemid'];
			if(!isset($products[$itemid]))
				$products[$itemid]=array();
			$products[$itemid][]=$p;
			$productids[]=$p['product_id'];
		}
		$productids=array_unique($productids);
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as stock from t_stock_info where product_id in ('".implode("','",$productids)."') group by product_id")->result_array();
		$stock=array();
		foreach($productids as $p)
			$stock[$p]=0;
		foreach($raw_stock as $s)
		{
			$pid=$s['product_id'];
			$stock[$pid]=$s['stock'];
		}
		foreach($trans as $transid=>$orders)
		{
			$total_pending[$transid]=count($orders);
			$possible[$transid]=0;
			$not_partial_flag=true;
			foreach($orders as $order)
			{
				$itemid=$order['itemid'];
				$pflag=true;
				
				if(!isset($products[$itemid]))
						continue ;
						
				foreach($products[$itemid] as $p)
					if($stock[$p['product_id']]<$p['qty']*$order['quantity'])
					{
						$pflag=false;
						break;
					}
				if($pflag)
					$possible[$transid]++;
				else
					$not_partial_flag=false;
			}
			if($not_partial_flag)
			{
				foreach($orders as $order)
				{
					if(!isset($products[$order['itemid']]))
						continue ;
					foreach($products[$order['itemid']] as $p)
						$stock[$p['product_id']]-=$p['qty']*$order['quantity'];
				}
			}else
				$partials[]=$transid;
		}
		if(empty($partials))
			return array();
		$ret=$this->db->query("select t.franchise_id,count(o.status) as items,u.name,t.is_pnh,t.batch_enabled,o.userid,t.priority,o.transid,t.init,o.ship_city,o.status,o.ship_phone,o.actiontime from king_orders o join king_users u on u.userid=o.userid join king_transactions t on t.transid=o.transid where o.transid in ('".implode("','",$partials)."') group by o.transid order by t.init asc")->result_array();
		foreach($ret as $i=>$r)
		{
			$ret[$i]['pending']=$total_pending[$r['transid']];
			$ret[$i]['possible']=$possible[$r['transid']];
		}
		return $ret;
	}
	
	function getdisabledbutpossibleshipments()
	{
		$trans=array();
		$itemids=array();
		$raw_trans=$this->db->query("select o.* from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=1 order by t.priority desc, t.init asc")->result_array();
		foreach($raw_trans as $t)
		{
			$transid=$t['transid'];
			if(!isset($trans[$transid]))
				$trans[$transid]=array();
			$trans[$transid][]=$t;
			$itemids[]=$t['itemid'];
		}
		if(!empty($trans))
		{
			$itemids=array_unique($itemids);
			$raw_prods=$this->db->query("select itemid,qty,product_id from m_product_deal_link where itemid in ('".implode("','",$itemids)."')")->result_array();
			$products=array();
			$productids=array();
			foreach($raw_prods as $p)
			{
				$itemid=$p['itemid'];
				if(!isset($products[$itemid]))
					$products[$itemid]=array();
				$products[$itemid][]=$p;
				$productids[]=$p['product_id'];
			}
			$productids=array_unique($productids);
			$raw_stock=$this->db->query("select product_id,sum(available_qty) as stock from t_stock_info where product_id in ('".implode("','",$productids)."') group by product_id")->result_array();
			$stock=array();
			foreach($productids as $p)
				$stock[$p]=0;
			foreach($raw_stock as $s)
			{
				$pid=$s['product_id'];
				$stock[$pid]=$s['stock'];
			}
			foreach($trans as $transid=>$orders)
			{
				$total_pending[$transid]=count($orders);
				$possible[$transid]=0;
				$not_partial_flag=true;
				foreach($orders as $order)
				{
					$itemid=$order['itemid'];
					$pflag=true;
					foreach($products[$itemid] as $p)
						if($stock[$p['product_id']]<$p['qty']*$order['quantity'])
						{
							$pflag=false;
							break;
						}
					if(!$pflag)
						$not_partial_flag=false;
				}
				if($not_partial_flag)
				{
					foreach($orders as $order)
						foreach($products[$order['itemid']] as $p)
							$stock[$p['product_id']]-=$p['qty']*$order['quantity'];
				}
			}
			$o_stock=$stock;
		}
		
		$trans=array();
		$itemids=array();
		$raw_trans=$this->db->query("select o.* from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=0 order by t.priority desc, t.init asc")->result_array();
		foreach($raw_trans as $t)
		{
			$transid=$t['transid'];
			if(!isset($trans[$transid]))
				$trans[$transid]=array();
			$trans[$transid][]=$t;
			$itemids[]=$t['itemid'];
		}
		if(empty($trans))
			return array();
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
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as stock from t_stock_info where product_id in ('".implode("','",$productids)."') group by product_id")->result_array();
		$stock=array();
		foreach($productids as $p)
			$stock[$p]=0;
		foreach($raw_stock as $s)
		{
			$pid=$s['product_id'];
			$stock[$pid]=$s['stock'];
			if(isset($o_stock[$pid]))
				$stock[$pid]=$o_stock[$pid];
		}
		foreach($trans as $transid=>$orders)
		{
			$total_pending[$transid]=count($orders);
			$possible[$transid]=0;
			$not_partial_flag=true;
			foreach($orders as $order)
			{
				$itemid=$order['itemid'];
				$pflag=true;
				foreach($products[$itemid] as $p)
					if($stock[$p['product_id']]<$p['qty']*$order['quantity'])
					{
						$pflag=false;
						break;
					}
				if($pflag)
					$possible[$transid]++;
				else
					$not_partial_flag=false;
			}
			if($not_partial_flag)
			{
				foreach($orders as $order)
					foreach($products[$order['itemid']] as $p)
						$stock[$p['product_id']]-=$p['qty']*$order['quantity'];
				$not_partials[]=$transid;
			}else
				$partials[]=$transid;
		}
		
		
		
		if(empty($not_partials))
			return array();
		$ret=$this->db->query("select count(o.status) as items,u.name,t.is_pnh,t.batch_enabled,o.userid,t.priority,o.transid,t.init,o.ship_city,o.status,o.ship_phone,o.actiontime from king_orders o join king_users u on u.userid=o.userid join king_transactions t on t.transid=o.transid where o.transid in ('".implode("','",$not_partials)."') group by o.transid order by t.init asc")->result_array();
		foreach($ret as $i=>$r)
		{
			$ret[$i]['pending']=$total_pending[$r['transid']];
			$ret[$i]['possible']=$possible[$r['transid']];
		}
		return $ret;
	}
	
	function depr_getpartialshipments()
	{
		$trans=array();
		$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=1 group by t.transid order by t.priority desc, t.init asc")->result_array();
//		$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 join m_courier_pincodes mp on mp.pincode=o.ship_pincode or mp.pincode='999999' join m_courier_awb_series ma on ma.awb_end_no>ma.awb_current_no and ma.courier_id=mp.courier_id group by t.transid order by t.priority desc, t.init asc")->result_array();
		if(empty($raw_trans))
			return array();
		foreach($raw_trans as $t)
		{
//			$flag=false;
//			$o=$this->db->query("select m.courier_id,o.ship_pincode,m.awb_end_no,m.awb_current_no from king_orders o join m_courier_pincodes p on p.pincode=o.ship_pincode or p.pincode='999999' join m_courier_awb_series m on m.courier_id=p.courier_id where o.transid=? and o.status=0 order by p.pincode asc limit 1",$t['transid'])->row_array();
//			if(!isset($awbs[$o['courier_id']]))
//				$awbs[$o['courier_id']]=$o['awb_current_no'];
//			if($awbs[$o['courier_id']]<=$o['awb_end_no'])
//			{
				$trans[]=$t['transid'];
//				$awbs[$o['courier_id']]++;
//			}
		}
		if(empty($trans))
			return array();
		$porders=$this->db->query("select o.* from king_orders o join king_transactions t on t.transid=o.transid where o.status=0 and o.transid in ('".implode("','",$trans)."') order by t.priority desc, o.sno asc")->result_array();
		$items=array();
		foreach($porders as $o)
		{
			if(!in_array($o['itemid'],$items))
				$items[]=$o['itemid'];
		}
		foreach($items as $item)
		{
			$ps=$this->db->query("select itemid,product_id,qty from m_product_deal_link where itemid=?",$item)->result_array();
			$prods[$item]=array();
			foreach($ps as $p)
			{
				$prods[$p['itemid']][]=$p;
				$pids[]=$p['product_id'];
			}
		}
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as s from t_stock_info where product_id in ('".implode("','",$pids)."') group by product_id")->result_array();
		foreach($pids as $i)
		{
			$stock[$i]=0;
			$t_stock[$i]=0;
		}
		foreach($raw_stock as $s)
		{
			$stock[$s['product_id']]=$s['s'];
			$t_stock[$s['product_id']]=$s['s'];
		}
		$orders=array();
		foreach($porders as $o)
		{
			$itemid=$o['itemid'];
			$f=true;
			foreach($prods[$itemid] as $p)
				if($stock[$p['product_id']]<$p['qty']*$o['quantity'])
				{
					$f=false;
					break;
				}
			if(!$f)
				continue;
			foreach($prods[$itemid] as $p)
				$stock[$p['product_id']]-=$p['qty']*$o['quantity'];
			$orders[]=$o['id'];
		}
		$raw_trans=$this->db->query("select o.*,t.batch_enabled,u.name,t.priority,t.init,count(o.id) as possible from king_orders o join king_transactions t on t.transid=o.transid join king_users u on u.userid=o.userid where o.id in ('".implode("','",$orders)."') group by o.transid having count(o.transid)!=(select count(transid) from king_orders where transid=o.transid and status=0)")->result_array();
		$zero_possible=$this->db->query("select o.*,t.batch_enabled,t.priority,u.name,t.init,0 as possible from king_orders o join king_transactions t on t.transid=o.transid join king_users u on u.userid=o.userid where o.id not in ('".implode("','",$orders)."') and t.batch_enabled=1 and o.status=0 group by o.transid having count(o.transid)=(select count(transid) from king_orders where transid=o.transid and status=0)")->result_array();
		foreach($zero_possible as $t)
			$raw_trans[]=$t;
		$rtrans=array();
		foreach($raw_trans as $i=>$r)
		{
			$raw_trans[$i]['items']=$this->db->query("select count(1) as l from king_orders where transid=?",$r['transid'])->row()->l;
			$raw_trans[$i]['pending']=$this->db->query("select count(1) as l from king_orders where transid=? and status=0",$r['transid'])->row()->l;
			$rtrans[]=$r['transid'];
		}
		$rtrans=$this->db->query("select transid from king_transactions where transid in ('".implode("','",$rtrans)."') order by init asc")->result_array();
		$out=array();
		foreach($rtrans as $t)
		{
			$transid=$t['transid'];
			foreach($raw_trans as $r)
				if($r['transid']==$transid)
					break;
			$out[]=$r;
		}
		return $out;
	}
	
	function depr_getdisabledbutpossibleshipments()
	{
		$trans=array();
		$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 where t.batch_enabled=0  group by t.transid order by t.priority desc, t.init asc")->result_array();
//		$raw_trans=$this->db->query("select t.transid from king_transactions t join king_orders o on o.transid=t.transid and o.status=0 join m_courier_pincodes mp on mp.pincode=o.ship_pincode or mp.pincode='999999' join m_courier_awb_series ma on ma.awb_end_no>ma.awb_current_no and ma.courier_id=mp.courier_id group by t.transid order by t.priority desc, t.init asc")->result_array();
		if(empty($raw_trans))
			return array();
		foreach($raw_trans as $t)
		{
//			$flag=false;
//			$o=$this->db->query("select m.courier_id,o.ship_pincode,m.awb_end_no,m.awb_current_no from king_orders o join m_courier_pincodes p on p.pincode=o.ship_pincode or p.pincode='999999' join m_courier_awb_series m on m.courier_id=p.courier_id where o.transid=? and o.status=0 order by p.pincode asc limit 1",$t['transid'])->row_array();
//			if(!isset($awbs[$o['courier_id']]))
//				$awbs[$o['courier_id']]=$o['awb_current_no'];
//			if($awbs[$o['courier_id']]<=$o['awb_end_no'])
//			{
				$trans[]=$t['transid'];
//				$awbs[$o['courier_id']]++;
//			}
		}
		if(empty($trans))
			return array();
		$porders=$this->db->query("select o.* from king_orders o join king_transactions t on t.transid=o.transid where o.status=0 and o.transid in ('".implode("','",$trans)."') order by t.priority desc, t.init asc")->result_array();
		$items=array();
		foreach($porders as $o)
		{
			if(!in_array($o['itemid'],$items))
				$items[]=$o['itemid'];
		}
		foreach($items as $item)
		{
			$ps=$this->db->query("select itemid,product_id,qty from m_product_deal_link where itemid=?",$item)->result_array();
			$prods[$item]=array();
			foreach($ps as $p)
			{
				$prods[$p['itemid']][]=$p;
				$pids[]=$p['product_id'];
			}
		}
		$raw_stock=$this->db->query("select product_id,sum(available_qty) as s from t_stock_info where product_id in ('".implode("','",$pids)."') group by product_id")->result_array();
		foreach($pids as $i)
			$stock[$i]=0;
		foreach($raw_stock as $s)
			$stock[$s['product_id']]=$s['s'];
		$orders=array();
		foreach($porders as $o)
		{
			$itemid=$o['itemid'];
			echo "{$o['transid']}:$itemid:";
			$f=true;
			foreach($prods[$itemid] as $p)
				if($stock[$p['product_id']]<$p['qty']*$o['quantity'])
				{
					$f=false;
					break;
				}
			echo (($f?"available":"false")."\n");
			if(!$f)
				continue;
			foreach($prods[$itemid] as $p)
				$stock[$p['product_id']]-=$p['qty']*$o['quantity'];
			$orders[]=$o['id'];
		}
		$raw_trans=$this->db->query("select o.*,t.batch_enabled,u.name,t.priority,t.init,count(o.id) as possible from king_orders o join king_transactions t on t.transid=o.transid join king_users u on u.userid=o.userid where o.id in ('".implode("','",$orders)."') group by o.transid having count(o.transid)=(select count(transid) from king_orders where transid=o.transid and status=0)")->result_array();
		$rtrans=array();
		foreach($raw_trans as $i=>$r)
		{
			$raw_trans[$i]['items']=$this->db->query("select count(1) as l from king_orders where transid=?",$r['transid'])->row()->l;
			$raw_trans[$i]['pending']=$this->db->query("select count(1) as l from king_orders where transid=? and status=0",$r['transid'])->row()->l;
			$rtrans[]=$r['transid'];
		}
		$rtrans=$this->db->query("select transid from king_transactions where transid in ('".implode("','",$rtrans)."') order by init asc")->result_array();
		$out=array();
		foreach($rtrans as $t)
		{
			$transid=$t['transid'];
			foreach($raw_trans as $r)
				if($r['transid']==$transid)
					break;
			$out[]=$r;
		}
		return $out;
	}
	
	function getordersfortransid($transid)
	{
		//$sql="select o.*,p.name as deal,p.dealid,p.url,u.name as username from king_orders o join king_dealitems p on p.id=o.itemid left outer join king_users u on u.userid=o.userid  where o.transid=?";
		$sql="SELECT DISTINCT o.userid,o.*, p.name AS deal,p.dealid,p.url,u.name AS username,r.offer_text,r.immediate_payment,b.name as brand,c.name as cat_name,r.is_active
				FROM king_orders o 
				JOIN king_dealitems p ON p.id=o.itemid
				LEFT OUTER JOIN king_users u ON u.userid=o.userid
				LEFT OUTER JOIN pnh_member_info i ON i.pnh_member_id=o.member_id
				LEFT OUTER JOIN pnh_m_offers r on r.id=o.offer_refid
				LEFT OUTER JOIN king_brands b on b.id=r.brand_id
				LEFT OUTER JOIN king_categories c on c.id=r.cat_id
				WHERE o.transid=?";
		$ret=$this->db->query($sql,$transid)->result_array();
		return $ret;
	}
	
	function getfreesamplesfortransaction($transid)
	{
		return $this->db->query("select f.name from king_freesamples_order o join king_freesamples f on f.id=o.fsid where o.transid=?",$transid)->result_array();
	}
	
	function getordersbytransaction_date_range($status,$s=false,$e=false,$pg=0,$limit=50,$orders_by='')
	{
		if($s)
		{
			$s=strtotime($s.' 00:00:00');
			$e=strtotime($e.' 23:59:59');
		}
		$cond = '';
		
		$orders_by = strtoupper($orders_by);
		
		if($orders_by)
			if($orders_by == 'PNH' )
			{
				$cond = ' and t.is_pnh = 1 ';
			}else if($orders_by == 'SNP' )
			{
				$cond = ' and t.is_pnh = 0 and t.partner_id = 0 ';
			}else 
			{
				$partner_id = @$this->db->query("select id from partner_info where trans_prefix = ? ",$orders_by)->row()->id;
				if($partner_id)
					$cond = ' and t.is_pnh = 0 and t.partner_id = '.$partner_id;
			}
		
		if($pg == -1)
		{
			$sql="select count(1) as t  
							from king_transactions t join king_orders o on o.transid=t.transid left outer join king_users u on o.userid=u.userid 
							where 1";
			if($s)
				$sql.="	and o.time between ? and ?";
			if($status==1)
				$sql.=" and o.status=0";
				
			$sql .= $cond; 	
			$sql.=" group by t.transid ";
			
			return $this->db->query("$sql",array($s,$e))->num_rows();
		}else
		{
		 
			$sql="select t.franchise_id,t.is_pnh,t.priority,t.batch_enabled,t.transid,o.ship_phone,o.ship_city,o.status,t.amount,o.actiontime,count(o.itemid) as items,u.name,u.userid,t.init,t.amount,t.priority 
							from king_transactions t join king_orders o on o.transid=t.transid left outer join king_users u on o.userid=u.userid 
							where 1";
			if($s)
				$sql.="	and o.time between ? and ?";
			if($status==1)
				$sql.=" and o.status=0";
				
			$sql .= $cond;
				
			$sql.=" group by t.transid order by";
			if($status==0)
				$sql.=" t.id desc,o.status desc";
			else
				$sql.=" t.id asc";
			$sql.=" limit $pg,$limit";
			return $this->db->query("$sql",array($s,$e))->result_array();
		}
			
		
	}

	/**
	 * function to populate pending orders summary    
	 * @param unknown_type $status
	 * @param unknown_type $s
	 * @param unknown_type $e
	 */
	function getordersby($status,$s=false,$e=false)
	{
		if($s)
		{
			$s=strtotime($s);
			$e=strtotime($e);
		}
		
		$cond = '';
		if($s)
			$cond.="	and o.time between ? and ?";
			
		return $this->db->query("select count(*) as total,is_pnh,ifnull((partner_id/partner_id),0) as is_partner 
				from king_transactions a 
				join king_orders b on a.transid = b.transid 
				where b.status = 0  $cond 
				group by is_pnh,is_partner  ",array($s,$e))->result_array();
	}
	
	function getordersbytransaction()
	{
		return $this->db->query("select t.transid,o.ship_city,t.amount,o.actiontime,count(o.itemid) as items,u.name,u.userid,t.init,t.amount,t.priority from king_transactions t join king_orders o on o.transid=t.transid join king_users u on o.userid=u.userid group by t.transid order by o.sno desc limit 20")->result_array();
	}
	
	function gettranschangelog($transid)
	{
		return $this->db->query("select c.*,u.name as admin from transactions_changelog c join king_admin u on u.id=c.admin where c.transid=? order by c.time desc",$transid)->result_array();
	}
	
	function do_changetranshipaddr($transid)
	{
		$o=$this->db->query("select ship_city,ship_address,ship_phone,ship_landmark,ship_state,ship_pincode from king_orders where transid=?",$transid)->row_array();
		$msg="shipping address changed<br><br>Old address:<br>{$o['ship_address']},<br>{$o['ship_landmark']},<br>{$o['ship_city']},<br>{$o['ship_state']},{$o['ship_phone']}<br>{$o['ship_pincode']}<br>";
		foreach(array("address","landmark","city","state","pincode","phone") as $i)
			$$i=$this->input->post("$i");
		$this->db->query("update king_orders set ship_address=?,ship_landmark=?,ship_city=?,ship_state=?,ship_pincode=?,ship_phone=? where transid=?",array($address,$landmark,$city,$state,$pincode,$phone,$transid));
		$this->erpm->do_trans_changelog($transid,$msg);
	}
	
	function do_changetranbilladdr($transid)
	{
		$o=$this->db->query("select bill_city,bill_address,bill_phone,bill_landmark,bill_state,bill_pincode from king_orders where transid=?",$transid)->row_array();
		$msg="billping address changed<br><br>Old address:<br>{$o['bill_address']},<br>{$o['bill_landmark']},<br>{$o['bill_city']},<br>{$o['bill_state']},{$o['bill_phone']}<br>{$o['bill_pincode']}<br>";
		foreach(array("address","landmark","city","state","pincode","phone") as $i)
			$$i=$this->input->post("$i");
		$this->db->query("update king_orders set bill_address=?,bill_landmark=?,bill_city=?,bill_state=?,bill_pincode=?,bill_phone=? where transid=?",array($address,$landmark,$city,$state,$pincode,$phone,$transid));
		$this->erpm->do_trans_changelog($transid,$msg);
	}
	
	function do_addclientorder($cid)
	{
		$admin=$this->erpm->getadminuser();
		foreach(array("product","mrp","qty","ref","remarks") as $i)
			$$i=$this->input->post($i);
		$inp=array($cid,$ref,$remarks,$admin['userid']);
		$this->db->query("insert into t_client_order_info(client_id,order_reference_no,remarks,order_status,created_on,created_by) values(?,?,?,0,now(),?)",$inp);
		$oid=$this->db->insert_id();
		foreach($product as $i=>$p)
			$this->db->query("insert into t_client_order_product_info(order_id,product_id,mrp,order_qty,created_on,created_by) values(?,?,?,?,now(),?)",array($oid,$p,$mrp[$i],$qty[$i],$admin['userid']));
		$this->session->set_flashdata("erp_pop_info","Client order created");
		redirect("admin/client_order/$oid");
	}
	
	function do_trans_changelog($transid,$msg,$uid=null)
	{
		if($uid==null)
			$user=$this->erpm->getadminuser();
		else $user['userid']=$uid;
		$this->db->query("insert into transactions_changelog(transid,msg,admin,time) values(?,?,?,?)",array($transid,$msg,$user['userid'],time()));
	}
	
	function getbatchesstatusfortransid($transid)
	{
		return $this->db->query("SELECT c.courier_name AS courier,bi.shipped,bi.shipped_on,bi.awb,bi.courier_id,bi.batch_id,bi.packed,bi.shipped,i.createdon,i.invoice_status,i.invoice_no,bi.p_invoice_no,bi.is_delivered,DATE_FORMAT(bi.delivered_on,'%d/%m/%Y %h:%i %p') AS delivered_on,n.name AS delivered_by   
								FROM king_invoice i 
								LEFT JOIN sms_invoice_log s ON s.invoice_no=i.invoice_no 
								LEFT JOIN m_employee_info n ON n.employee_id=s.logged_by
								LEFT OUTER JOIN shipment_batch_process_invoice_link bi ON bi.invoice_no=i.invoice_no 
								LEFT OUTER JOIN m_courier_info c ON c.courier_id=bi.courier_id WHERE i.transid=? 
								GROUP BY i.invoice_no",$transid)->result_array();
	}
	
	function gettransaction($transid)
	{
		return $this->db->query("select * from king_transactions where transid=?",$transid)->row_array();
	}
	
	function getticketmsgs($tid)
	{
		return $this->db->query("select a.name as admin_user,m.* from support_tickets_msg m left outer join king_admin a on a.id=m.support_user where m.ticket_id=? order by m.id desc",$tid)->result_array();
	}
	
	function addnotesticket($tid,$type,$medium,$msg,$from_customer=0)
	{
		$user=$this->erpm->getadminuser();
		if(empty($user))
			$user['userid']=0;
		$this->db->query("insert into support_tickets_msg(ticket_id,msg_type,msg,medium,from_customer,support_user,created_on) values(?,?,?,?,?,?,now())",array($tid,$type,$msg,$medium,$from_customer,$user['userid']));
		$this->db->query("update support_tickets set updated_on=now() where ticket_id=? limit 1",$tid);
		return $this->db->insert_id();
	}
	
	function getvariants()
	{
		$sql="select * from variant_info order by variant_id desc";
		return $this->db->query($sql)->result_array();
	}
	
	function getdealitemsforvariant($vid)
	{
		return $this->db->query("select i.dealid,i.url,i.name,v.variant_value from variant_deal_link v join king_dealitems i on i.id=v.item_id where v.variant_id=?",$vid)->result_array();
	}
	
	function do_add_products_group()
	{
		$user=$this->auth();
		foreach(array("group_name","attr_name","attr_values","pids","catid") as $i)
			$$i=$this->input->post($i);
		$this->db->insert("products_group",array("group_name"=>$group_name,"cat_id"=>$catid,"created_by"=>$user['userid'],"created_on"=>time()));
		$gid=$this->db->insert_id();
		$attr_data=array();
		foreach($attr_name as $i=>$aname)
		{
			$inp=array("group_id"=>$gid,"attribute_name"=>$aname);
			$this->db->insert("products_group_attributes",$inp);
			$aid=$this->db->insert_id();
			$attr_data[$aid]=array();
			foreach($attr_values[$i] as $avalue)
			{
				$this->db->insert("products_group_attribute_values",array("group_id"=>$gid,"attribute_name_id"=>$aid,"attribute_value"=>$avalue));
				$attr_data[$aid][]=$this->db->insert_id();
			}
		}
		foreach($pids as $i=>$pid)
		{
			foreach($this->input->post("attr_$pid") as $i2=>$v)
			{
				$i3=0;
				foreach($attr_data as $aid=>$adata)
				{
					if($i3==$i2)
						break;
					$i3++;
				}
				$inp=array("product_id"=>$pid,"group_id"=>$gid,"attribute_name_id"=>$aid,"attribute_value_id"=>$adata[$v]);
				$this->db->insert("products_group_pids",$inp);
			}
		}
		redirect("admin/product_group/$gid");
	}
	
	function do_changestatusticket($tid)
	{
		$statuss=array("Unassigned","Opened","In Progress","Closed");
		$user=$this->erpm->getadminuser();
		$s=$_POST['tck_status'];
		$this->erpm->addnotesticket($tid,0,0,"Status changed to <b>{$statuss[$s]}</b>");
		$this->db->query("update support_tickets set status=? where ticket_id=?",array($s,$tid));
	}
	
	function do_changepriorityticket($tid)
	{
		$prioritys=array("Low","Medium","High","Urgent");
		$user=$this->erpm->getadminuser();
		$s=$_POST['priority'];
		$this->erpm->addnotesticket($tid,0,0,"<b>{$prioritys[$s]}</b> Priority Assigned");
		$this->db->query("update support_tickets set priority=? where ticket_id=?",array($s,$tid));
	}
	
	function do_changetypeticket($tid)
	{
		$statuss=array("Query","Order Issue","Bug","Suggestion","Common","PNH Returns","Courier Followups");
		$user=$this->erpm->getadminuser();
		$s=$_POST['type'];
		$this->erpm->addnotesticket($tid,0,0,"Ticket type changed to <b>{$statuss[$s]}</b>");
		$this->db->query("update support_tickets set type=? where ticket_id=?",array($s,$tid));
	}
	
	
	function do_addnotesticket($tid)
	{
		$user=$this->erpm->getadminuser();
		$type=$this->input->post("type");
		$msg=nl2br($this->input->post("msg"));
		$medium=$this->input->post("medium");
		if($medium==0 && $type==1)
		{
			$prev=$this->db->query("select msg from support_tickets_msg where ticket_id=? and medium=0 and msg_type=1 and from_customer=1 order by id desc",$tid)->row_array();
			if(!empty($prev))
				$msg.='<div style="margin-top:25px;border:1px solid #999;"><div style="background:#eee;padding:3px;font-size:13px;font-weight:bold;">Incident report</div><div style="padding:5px">'.$prev['msg']."</div></div>";
			$ticket=$this->db->query("select * from support_tickets where ticket_id=?",$tid)->row_array();
			$email=$ticket['email'];
			if(!empty($email))
				$this->vkm->email($email,"[TK{$ticket['ticket_no']}] Snapittoday Customer Support",$msg);
		}
		$this->erpm->addnotesticket($tid,$type,$medium,$msg,0);
		$this->session->set_flashdata("erp_pop_info","Notes added");
		redirect("admin/ticket/$tid");
	}
	
	function do_ticket()
	{
		$user=$this->erpm->getadminuser();
		foreach(array("name","mobile","msg","email","transid","type","priority") as $i)
			$$i=$this->input->post("$i");
		$no=rand(1000000000,9999999999);
		$userid=0;
		$u=array();
		if(!empty($email))
			$u=$this->db->query("select userid,mobile from king_users where email=?",$email)->row_array();
		if(!empty($u))
		{
			$userid=$u['userid'];
			if(empty($mobile))
				$mobile=$u['mobile'];
		}
		$msg=nl2br($msg);
		$this->db->query("insert into support_tickets(ticket_no,name,mobile,user_id,email,transid,type,priority,created_on) values(?,?,?,?,?,?,?,?,now())",array($no,$name,$mobile,$userid,$email,$transid,$type,$priority));
		$tid=$this->db->insert_id();
		$this->erpm->addnotesticket($tid,1,1,$msg,1);
		$this->session->set_flashdata("erp_pop_info","New ticket created");
		redirect("admin/ticket/{$tid}");
	}
	
	function gettickets($filter,$s=false,$e=false)
	{
		$filters=array("all"=>-1,"unassigned"=>0,"opened"=>1,"inprogress"=>2,"closed"=>3);
		if(!isset($filters[$filter]))
			return array();
//		if(!$e)
//		{
//			$tom=mktime(0,0,0,date("m"),date("d")+1);
//			$s=date("Y-m-d",time()-(30*24*60*60));
//			$e=date("Y-m-d",time());
//		}
		$filter=$filters[$filter];
		$sql="select u.name as user,t.*,a.name as assignedto from support_tickets t left outer join king_admin a on a.id=t.assigned_to left outer join king_users u on u.userid=t.user_id where";
		if($filter!=-1)
			$sql.=" t.status=$filter and";
		if($e)
		$sql.=" t.created_on between ? and ?";
		else
			$sql.=" 1";
		$sql.=" order by t.updated_on desc, t.created_on desc";
		if(!$e)
			$sql.=" limit 30";
		return $this->db->query($sql,array($s,$e))->result_array();
	}
	
	function getticketsfortrans($transid)
	{
		$sql="select u.name as user,t.*,a.name as assignedto from support_tickets t left outer join king_admin a on a.id=t.assigned_to left outer join king_users u on u.userid=t.user_id";
		$sql.=" where transid=?";
		$sql.=" order by t.created_on desc";
		return $this->db->query($sql,$transid)->result_array();
	}
	
	function getpendinggrns()
	{
		$grns=$this->db->query("select v.vendor_name as vendor,g.* from t_grn_info g join m_vendor_info v on v.vendor_id=g.vendor_id where payment_status=0")->result_array();
		$ids=array();
		foreach($grns as $i=>$g)
			$ids[]=$g['grn_id'];
		if(empty($ids))
			return $grns;
		$pos=$this->db->query("select po.po_status,gp.po_id,gp.grn_id from t_grn_info g join t_grn_product_link gp on gp.grn_id=g.grn_id join t_po_info po on po.po_id=gp.po_id where g.grn_id in (".implode(",",$ids).")")->result_array();
		foreach($pos as $p)
		{
			if(!isset($gpos[$p['grn_id']]))
				$gpos[$p['grn_id']]=$gposdata[$p['grn_id']]=array();
			if(!in_array($p['po_id'],$gpos[$p['grn_id']]))
			{
				$gpos[$p['grn_id']][]=$p['po_id'];
				$gposdata[$p['grn_id']][]=$p;
			}
		}
		$tgrns=$grns;
		$grns=array();
		$gpos=$gposdata;
		foreach($tgrns as $i=>$g)
		{
			if(!isset($gpos[$g['grn_id']]))
				continue;
			
			$g['postatus']=1;
			foreach($gpos[$g['grn_id']] as $po)
				if($po['po_status']==0 || $po['po_status']==1)
					$g['postatus']=0;
			$g['pos']=$gpos[$g['grn_id']];
			$grns[]=$g;
		}
		return $grns;
	}
	
	function getpendingpaygrns()
	{
		$grns=$this->db->query("select * from t_grn_info where payment_status=1")->result_array();
		$ids=array();
		foreach($grns as $i=>$g)
			$ids[]=$g['grn_id'];
		if(empty($ids))
			return $grns;
		$pos=$this->db->query("select po.po_status,gp.po_id,gp.grn_id from t_grn_info g join t_grn_product_link gp on gp.grn_id=g.grn_id join t_po_info po on po.po_id=gp.po_id where g.grn_id in (".implode(",",$ids).")")->result_array();
		foreach($pos as $p)
		{
			if(!isset($gpos[$p['grn_id']]))
				$gpos[$p['grn_id']]=$gposdata[$p['grn_id']]=array();
			if(!in_array($p['po_id'],$gpos[$p['grn_id']]))
			{
				$gpos[$p['grn_id']][]=$p['po_id'];
				$gposdata[$p['grn_id']][]=$p;
			}
		}
		$tgrns=$grns;
		$grns=array();
		$gpos=$gposdata;
		foreach($tgrns as $i=>$g)
		{
			if(!isset($gpos[$g['grn_id']]))
				continue;
			
			$g['postatus']=1;
			foreach($gpos[$g['grn_id']] as $po)
				if($po['po_status']==0 || $po['po_status']==1)
					$g['postatus']=0;
			$g['pos']=$gpos[$g['grn_id']];
			$grns[]=$g;
		}
		return $grns;
	}
	
	function getrackkbinsforbrands()
	{
		return $this->db->query("select r.rack_name,r.bin_name,rb.brandid from m_rack_bin_brand_link rb join m_rack_bin_info r on r.id=rb.rack_bin_id order by r.rack_name")->result_array();
	}
	
	function do_addbrand()
	{
		foreach(array("name","rb") as $b)
			$$b=$this->input->post("$b");
		$bid=$this->adminmodel->genbrandid();
		$rb=array_unique($rb);
		$url=preg_replace('/[^a-zA-Z0-9_\-]/','',$name);
		$url=str_replace(" ","-",$url);
		$this->db->query("insert into king_brands(id,name,url) values(?,?,?)",array($bid,$name,$url));
		foreach($rb as $r)
			$this->db->query("insert into m_rack_bin_brand_link(rack_bin_id,brandid) values(?,?)",array($r,$bid));
		$this->session->set_flashdata("erp_pop_info","Brand added");
		redirect("admin/brands");
	}
	
	function do_editbrand($bid)
	{
		foreach(array("name","rb") as $b)
			$$b=$this->input->post("$b");
		$rb=array_unique($rb);
		$this->db->query("update king_brands set name=? where id=?",array($name,$bid));
		$this->db->query("delete from m_rack_bin_brand_link where brandid=?",$bid);
		foreach($rb as $r)
			$this->db->query("insert into m_rack_bin_brand_link(rack_bin_id,brandid) values(?,?)",array($r,$bid));
		$this->session->set_flashdata("erp_pop_info","Brand details updated");
		redirect("admin/viewbrand/$bid");
	}
	
	function getrackbinsforbrand($bid)
	{
		return $this->db->query("select rb.rack_bin_id,r.rack_name,r.bin_name,rb.brandid from m_rack_bin_brand_link rb join m_rack_bin_info r on r.id=rb.rack_bin_id where rb.brandid=? order by r.rack_name",$bid)->result_array();
	}
	
	function loadroles()
	{
		foreach($this->db->query("select const_name,value from user_access_roles order by id asc")->result_array() as $r)
			define(strtoupper($r['const_name']),(double)$r['value']);
	}
	
	function is_user_role($uid,$role)
	{
		$user=$this->db->query("select access from king_admin where id=?",$uid)->row_array();
		if(empty($user))
			return false;
		if(((double)$user['access']&(double)$role)>0)
			return true;
		return false;
	}
	
	function auth($super=false,$ret=false)
	{
		if(!defined("CALLCENTER_ROLE"))
			$this->erpm->loadroles();
		$user=$this->session->userdata("admin_user");
		
		if(!$user)
			redirect("admin");
		if($super===false)
			return $user;
		if($super===true)
			$super=ADMINISTRATOR_ROLE;
		if(((double)$user['access']&(double)$super)>0)
			return $user;
		if(((double)$user['access']&(double)ADMINISTRATOR_ROLE)>0)
			return $user;
		if($ret)
			return false;
		show_error("Access Denied<br><div style='font-size:14px;'>I reckon you don't have permission to do this</div>");
	}
	
	function do_voucher_exp()
	{
		foreach(array("bill","type","vvalue","mode","inst_no","inst_date","bank","narration") as $i)
			$$i=$this->input->post($i);
		$adminuser=$this->erpm->getadminuser();
		$inp=array(1,$vvalue,$mode,$inst_no,$inst_date,$bank,$narration,$adminuser['userid']);
		$this->db->query("insert into t_voucher_info(voucher_type_id,voucher_date,voucher_value,payment_mode,instrument_no,instrument_date,instrument_issued_bank,narration,created_on,created_by)
																	values(?,now(),?,?,?,?,?,?,now(),?)",$inp);
		$vid=$this->db->insert_id();
		$this->db->query("insert into t_voucher_expense_link(voucher_id,expense_type,bill_no) values(?,?,?)",array($vid,$type,$bill));	
		$this->session->set_flashdata("erp_pop_info","New expense voucher created");
		redirect("admin/voucher/$vid");
	}
	
	function do_voucher()
	{
		foreach(array("grns","adjusted_grn","pos","adjusted_po","vvalue","mode","inst_no","inst_date","bank","narration") as $i)
			$$i=$this->input->post($i);
		if(empty($grns) && empty($pos))
			show_error("No GRNs or POs specified");
		$adminuser=$this->erpm->getadminuser();
		$inp=array(1,$vvalue,$mode,$inst_no,$inst_date,$bank,$narration,$adminuser['userid']);
		$this->db->query("insert into t_voucher_info(voucher_type_id,voucher_date,voucher_value,payment_mode,instrument_no,instrument_date,instrument_issued_bank,narration,created_on,created_by)
																	values(?,now(),?,?,?,?,?,?,now(),?)",$inp);
		$vid=$this->db->insert_id();
		foreach($grns as $i=>$grn)
		{
			$this->db->query("insert into t_voucher_document_link(voucher_id,adjusted_amount,ref_doc_id,ref_doc_type,created_on)
																		values(?,?,?,?,now())",array($vid,$adjusted_grn[$i],$grn,1,now()));
			$this->db->query("update t_grn_info set payment_status=2 where grn_id=? limit 1",$grn);
		}
		foreach($pos as $i=>$po)
			$this->db->query("insert into t_voucher_document_link(voucher_id,adjusted_amount,ref_doc_id,ref_doc_type,created_on)
																		values(?,?,?,?,now())",array($vid,$adjusted_po[$i],$po,2,now()));
		$this->session->set_flashdata("erp_pop_info","New voucher created");
		redirect("admin/voucher/$vid");
	}
	
	function do_updatevendor($vid)
	{
		$admin = $this->auth(false);
		
		foreach(array("name","addr1","addr2","locality","landmark","city","state","country","postcode","ledger","credit_limit","credit_days","advance","cst","pan","vat","stax","tat","rpolicy","payterms","remarks","cnt_name","cnt_desgn","cnt_mob1","cnt_mob2","cnt_telephone","cnt_fax","cnt_email1","cnt_email2","l_brand","l_margin","l_from","l_until") as $i)
			$$i=$this->input->post("$i");
			
			
		$inp=array($name,$addr1,$addr2,$locality,$landmark,$postcode,$city,$state,$country,$ledger,$credit_limit,$credit_days,$advance,$cst,$pan,$vat,$stax,$tat,$rpolicy,$payterms,$remarks,$admin['userid'],$vid);
		$this->db->query("update m_vendor_info set vendor_name=?,address_line1=?,address_line2=?,locality=?,landmark=?,postcode=?,city_name=?,state_name=?,country=?,ledger_id=?,credit_limit_amount=?,credit_days=?,require_payment_advance=?,cst_no=?,pan_no=?,vat_no=?,service_tax_no=?,avg_tat=?,return_policy_msg=?,payment_terms_msg=?,remarks=?,modified_by=?,modified_on=now() where vendor_id=? limit 1",$inp);
		$this->db->query("delete from m_vendor_contacts_info where vendor_id=?",$vid);
		//$this->db->query("delete from m_vendor_brand_link where vendor_id=?",$vid);
		
		
		foreach($cnt_name as $i=>$cn)
		{
			$sql="insert into m_vendor_contacts_info(vendor_id,contact_name,contact_designation,mobile_no_1,mobile_no_2,telephone_no,email_id_1,email_id_2,fax_no,created_on)
														values(?,?,?,?,?,?,?,?,?,now())";
			$inp=array($vid,$cnt_name[$i],$cnt_desgn[$i],$cnt_mob1[$i],$cnt_mob2[$i],$cnt_telephone[$i],$cnt_email1[$i],$cnt_email2[$i],$cnt_fax[$i]);
			$this->db->query($sql,$inp);
		}
		if($l_brand)
		foreach($l_brand as $i=>$b)
		{
			if($this->db->query("select id from m_vendor_brand_link where vendor_id = ? and brand_id = ? ",array($b,$vid))->num_rows())
			{
				$this->db->query("update m_vendor_brand_link set brand_margin = ? ,applicable_from = ?,applicable_till = ?,created_on = now() where vendor_id = ? and brand_id = ? ",array($l_margin[$i],strtotime($l_from[$i]),strtotime($l_until[$i]),$vid,$b));
			}
			else
			{
				$this->db->query("insert into m_vendor_brand_link(brand_id,vendor_id,brand_margin,applicable_from,applicable_till,created_on) values(?,?,?,?,?,now())",array($b,$vid,$l_margin[$i],strtotime($l_from[$i]),strtotime($l_until[$i])));	
			}
			
		}
			
			
		redirect("admin/vendor/{$vid}");
	}
	
	function do_addvendor()
	{
		foreach(array("l_brand","l_margin","l_from","l_until","name","addr1","addr2","locality","landmark","city","state","country","postcode","ledger","credit_limit","credit_days","advance","cst","pan","vat","stax","tat","rpolicy","payterms","remarks","cnt_name","cnt_desgn","cnt_mob1","cnt_mob2","cnt_telephone","cnt_email1","cnt_email2","cnt_fax") as $i)
			$$i=$this->input->post($i);
		$code="V".rand(1000,9999);
		$inp=array($code,$name,$addr1,$addr2,$locality,$landmark,$postcode,$city,$state,$country,$ledger,$credit_limit,$credit_days,$advance,$cst,$pan,$vat,$stax,$tat,$rpolicy,$payterms,$remarks);
		$this->db->query("insert into m_vendor_info(vendor_code,vendor_name,address_line1,address_line2,locality,landmark,postcode,city_name,state_name,country,ledger_id,credit_limit_amount,credit_days,require_payment_advance,cst_no,pan_no,vat_no,service_tax_no,avg_tat,return_policy_msg,payment_terms_msg,remarks,created_on) 
														values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())",$inp);
		$vid=$this->db->insert_id();
		foreach($cnt_name as $i=>$cn)
		{
			$sql="insert into m_vendor_contacts_info(vendor_id,contact_name,contact_designation,mobile_no_1,mobile_no_2,telephone_no,email_id_1,email_id_2,fax_no,created_on)
														values(?,?,?,?,?,?,?,?,?,now())";
			$inp=array($vid,$cnt_name[$i],$cnt_desgn[$i],$cnt_mob1[$i],$cnt_mob2[$i],$cnt_telephone[$i],$cnt_email1[$i],$cnt_email2[$i],$cnt_fax[$i]);
			$this->db->query($sql,$inp);
		}
		foreach($l_brand as $i=>$b)
			$this->db->query("insert into m_vendor_brand_link(brand_id,vendor_id,brand_margin,applicable_from,applicable_till,created_on) values(?,?,?,?,?,now())",array($b,$vid,$l_margin[$i],strtotime($l_from[$i]),strtotime($l_until[$i])));
	}
	
	function do_grn()
	{
		$pos = array();
		$user=$this->erpm->getadminuser();
		$vid=$this->db->query("select vendor_id from t_po_info where po_id=?",$_POST['poids'][0])->row()->vendor_id;
		
                
		$proceed_grn = 0;
		foreach($_POST['invno'] as $i=>$no)
		{
			if($this->db->query("select count(*) as t from t_grn_invoice_link a join t_grn_info b on a.grn_id = b.grn_id where purchase_inv_no = ? and purchase_inv_date = ? and vendor_id  = ? ",array($no,$_POST['invdate'][$i],$vid))->row()->t)
				continue;
				
			$proceed_grn++;
		}
		
		if(!$proceed_grn)
			die("Vendor Invoices are already added,Please check if its an duplicate stock intake");
		
                //
		
		foreach($_POST['poids'] as $p)
		{
			if($this->db->query('select count(*) as t from t_po_info where po_id=? and po_status > 1 ',$p)->row()->t)
				continue;
			
			if(!isset($pos[$p]))
				$pos[$p]=array();
                        
			if(isset($_POST["pid$p"]))	
			foreach($_POST["pid$p"] as $i=>$null)
			{
				$po=array();
				$po['product']=$_POST["pid$p"][$i];
				$poi=$this->db->query("select * from t_po_product_link where po_id=? and product_id=?",array($p,$po['product']))->row_array();
				$po['oqty']=$_POST["oqty$p"][$i];
				$po['rqty']=$_POST["rqty$p"][$i];
				$po['mrp']=$_POST["mrp$p"][$i];
				$po['price']=$_POST["price$p"][$i];
				$po['tax']=$this->db->query("select vat from m_product_info where product_id=?",$po['product'])->row()->vat;
				$po['rackbin']=$_POST['storage'.$p][$i];
				$po['location']=$this->db->query("select location_id from m_rack_bin_info where id=?",$po['rackbin'])->row()->location_id;
				$po['margin']=$poi['margin'];
				$po['foc']=$poi['is_foc'];
				$po['offer']=$poi['has_offer'];
				$po['barcode']=isset($_POST["pbarcode".$p][$i])?$_POST["pbarcode".$p][$i]:'';
				$po['upd_pmrp']=isset($_POST["upd_pmrp".$p][$po['product']])?$_POST["upd_pmrp".$p][$po['product']]:0;
				$pos[$p][]=$po;
			}
                        
                        
		}
		
		if(!count($pos))
		{
			show_error("Stock intake already processed ");
			exit;
		}
		
		$this->db->query("insert into t_grn_info(vendor_id,remarks,created_on) values(?,?,now())",array($vid,$_POST['remarks']));
			$grn=$this->db->insert_id();
		
                

                        
		foreach($pos as $poid=>$po)
		{
                        
			foreach($po as $p)
			{
//                            print_r($p);
//
//                            die("GRN ID = 3811");
                
                            $prod_id=$p['product']; //2360;
                            $mrp=$p['mrp']; //8000;
                            $bc=$p['barcode']; //'8806085354760';
                            $loc_id=$p['location'];//1;
                            $rb_id=$p['rackbin'];//1;
                            $p_stk_id=0;
                            $qty=$p['oqty'];//4;
                            $update_by=4; //grn
                            $stk_movtype=1; //in=1 out=0
                            $update_by_refid=$grn;//0; //gtn number
                            $mrp_change_updated=-1;
                            $msg='';
                            $this->_upd_product_stock($prod_id, $mrp, $bc, $loc_id, $rb_id, $p_stk_id, $qty,$update_by,$stk_movtype,$update_by_refid,$mrp_change_updated,$msg);
                            
			}
			
			$po_status=2;
			foreach($this->db->query("select * from t_po_product_link where po_id=?",$poid)->result_array() as $poi)
			{
				if($poi['order_qty']>$poi['received_qty'])
					$po_status=1;
				$this->db->query("update t_po_info set po_status=? where po_id=? limit 1",array($po_status,$poid));
			}
		}
		$invs=array();
		foreach($_POST['invno'] as $i=>$no)
		{
			$this->db->query("insert into t_grn_invoice_link(grn_id,purchase_inv_no,purchase_inv_date,purchase_inv_value,created_on) values(?,?,?,?,now())",array($grn,$no,$_POST['invdate'][$i],$_POST['invamount'][$i]));
			$invs[]=$this->db->insert_id();
		}
		if(!empty($_FILES))
		{
			foreach($_FILES as $scanid=>$f)
			{
				list($null,$id)=explode("_",$scanid);
				$inv_id=$invs[$id];
				if(file_exists(ERP_PHYSICAL_IMAGES."invoices/$inv_id.jpg"))
					unlink(ERP_PHYSICAL_IMAGES."invoices/$inv_id.jpg");
				move_uploaded_file($f['tmp_name'], ERP_PHYSICAL_IMAGES."invoices/{$inv_id}.jpg");
			}
		}
		
		return $grn;
	}
	
	function do_addvariant()
	{
		foreach(array("name","type","itemid","value") as $inp)
			$$inp=$this->input->post($inp);
		$user=$this->erpm->getadminuser();
		$this->db->query("insert into variant_info(variant_name,variant_type,created_by,created_on) values(?,?,?,now())",array($name,$type,$user['userid']));
		$vid=$this->db->insert_id();
		foreach($itemid as $i=>$item)
			$this->db->query("insert into variant_deal_link(variant_id,item_id,variant_value) values(?,?,?)",array($vid,$item,$value[$i]));
		$this->session->set_flashdata("erp_pop_info","Variant added");
		redirect("admin/variants");
	}
	
	function getvouchers_date_range($s,$e)
	{
		if(!$s)
		{
			$s=date("Y-m-d",mktime(0,0,0,date("m"),1));
			$e=date("Y-m-d",mktime(0,0,0,date("m")+1,date("t")));
		}
		return $this->db->query("select * from t_voucher_info where created_on between ? and ? order by voucher_id desc",array($s,$e))->result_array();
	}
	
	function getvouchers()
	{
		return $this->db->query("select * from t_voucher_info order by voucher_id desc")->result_array();
	}
	
	function do_account_grn()
	{
		if(!empty($_FILES))
		{
			foreach($_FILES as $scanid=>$f)
			{
				list($null,$inv_id)=explode("_",$scanid);
				if(file_exists(ERP_PHYSICAL_IMAGES."invoices/$inv_id.jpg"))
					unlink(ERP_PHYSICAL_IMAGES."invoices/$inv_id.jpg");
				move_uploaded_file($f['tmp_name'], ERP_PHYSICAL_IMAGES."invoices/{$inv_id}.jpg");
			}
		}
		foreach(array("inv_amounts","inv_ids","adjusted","grn","mrp","items","discount","type","vvalue","mode","inst_no","inst_date","bank","narration") as $i)
			$$i=$this->input->post($i);
		foreach($inv_ids as $i=>$id)
			$this->db->query("update t_grn_invoice_link set purchase_inv_value=? where id=? limit 1",array($inv_amounts[$i],$id));
		foreach($items as $i=>$item)
		{
			$disc=$discount[$i];
			if($type[$i]==1)
				$disc=$mrp[$i]*$disc/100;
			$this->db->query("update t_grn_product_link set grn_invoice_link_id=?,scheme_discount_value=?,scheme_discunt_type=? where id=? limit 1",array($invoice[$i],$disc,$type[$i],$item));
		}
		$this->db->query("update t_grn_info set payment_status=1 where grn_id=? limit 1",$grn);
		/*
		$inp=array(1,$vvalue,$mode,$inst_no,$inst_date,$bank,$narration);
		$this->db->query("insert into t_pending_voucher_info(voucher_type_id,voucher_value,
,instrument_no,instrument_date,instrument_issued_bank,narration,created_on,voucher_date)
															values(?,?,?,?,?,?,?,now(),now())",$inp);
		$vid=$this->db->insert_id();
		$this->db->query("insert into t_pending_voucher_document_link(voucher_id,adjusted_amount,ref_doc_id,ref_doc_type,created_on) values(?,?,?,1,now())",array($vid,$adjusted,$grn));
		*/
	}
	
	function do_po_prodwise()
	{
		foreach($_POST['vendor'] as $i=>$v)
		{
			$po=array();
			if(!isset($pos[$v]))
				$pos[$v]=array();
			$po['product']=$_POST['product'][$i];
			$po['qty']=$_POST['qty'][$i];
			if(empty($po['qty']) || $po['qty']==0)
				continue;
			$po['margin']=$_POST['margin'][$i];
			$po['mrp']=$_POST['mrp'][$i];
			$price=$po['mrp']-($po['mrp']*$po['margin']/100);
			$po['discount']=$_POST['sch_type'][$i]?$price*$_POST['sch_discount'][$i]/100:$price-$_POST['sch_discount'][$i];
			$po['type']=$_POST['sch_type'][$i];
			$po['price']=$price-$po['discount'];
			$po['foc']=$this->input->post("foc$i");
			$po['offer']=$this->input->post("offer$i");
			$po['note']=$_POST['note'][$i];
			$pos[$v][]=$po;
		}
		foreach($pos as $v=>$po)
		{
			$total=0;
			$this->db->query("insert into t_po_info(vendor_id,remarks,po_status,created_on) values(?,?,0,now())",array($v,"productwise-".date("d/m/y")));
			$poid=$this->db->insert_id();
			foreach($po as $p)
			{
				$inp=array($poid,$p['product'],$p['qty'],$p['mrp'],$p['margin'],$p['discount'],$p['type'],$p['price'],$p['foc'],$p['offer'],$p['note']);
				$this->db->query("insert into t_po_product_link(po_id,product_id,order_qty,mrp,margin,scheme_discount_value,scheme_discount_type,purchase_price,is_foc,has_offer,special_note,created_on)
																								values(?,?,?,?,?,?,?,?,?,?,?,now())",$inp);
				$total+=$p['price']*$p['qty'];
			}
			$this->db->query("update t_po_info set total_value=? where po_id=? limit 1",array($total,$poid));
		}
		$this->session->set_flashdata("erp_pop_info","POs created");
		redirect("admin/viewpo/$poid");
	}
	
	function getlinkeddealsforproduct($pid)
	{
		return $this->db->query("select l.qty,i.* from m_product_deal_link l join king_dealitems i on i.id=l.itemid where l.product_id=? order by i.name asc",$pid)->result_array();
	}
	
	function getproductdetails_old($id)
	{
		$sql="select a.*,b.name as brand_name 
				from m_product_info a 
				join king_brands b on a.brand_id = b.id 
				where product_id=?
			";
		$r=$this->db->query($sql,$id)->row_array();
		$r['orders']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.time>".(time()-(24*60*60*90)),$id)->row()->s;
		$r['pen_ord_qty']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.status = 0 ",$id)->row()->s;
		$r['cur_stk']=$this->db->query("select ifnull(sum(available_qty),0) as cur_stk from t_stock_info where product_id = ?",$id)->row()->cur_stk;
		$r['vendors']=$vs=$this->db->query("select v.vendor_id,v.vendor_name,CONCAT(v.vendor_name,' (',b.brand_margin,'%)') as vendor,b.brand_margin as ven_margin from m_product_info p join m_vendor_brand_link b on p.brand_id=b.brand_id join m_vendor_info v on v.vendor_id=b.vendor_id where p.product_id=? order by b.brand_margin desc",array($id,$id))->result_array();
		$r['margin']="0";
		
		if(!empty($vs))
		{
			$v=$vs[0]['vendor_id'];
			$r['margin']=$this->db->query("select brand_margin from m_vendor_brand_link where vendor_id=? and brand_id=?",array($v,$r['brand_id']))->row()->brand_margin;
		}
		return $r;
	}

	function getproductdetails($id)
	{
		
		//error_reporting(E_ALL);
		
	$sql="SELECT a.*,b.id as brand_id,b.name AS brand_name,d.menuid,ifnull(m.name,m1.name) AS menu,v.vendor_id,r.vendor_name,date_format(from_unixtime(o.time),'%d/%m/%Y') as last_orderdon,o.transid,t.partner_id,p.name AS partner_name,t.is_pnh
				FROM m_product_info a
				left join m_product_deal_link l on l.product_id=a.product_id 
				left JOIN king_dealitems di ON di.id= l.itemid 
				left JOIN king_deals d ON d.dealid=di.dealid
				left JOIN king_brands b ON a.brand_id = b.id
				left JOIN pnh_menu m ON m.id=d.menuid and di.is_pnh = 1
				left JOIN king_menu m1 ON m1.id=d.menuid and di.is_pnh = 0   
				left join m_vendor_brand_link v on a.brand_id=v.brand_id 
				left join m_vendor_info r on r.vendor_id=v.vendor_id
				left join king_orders o on o.itemid=l.itemid  
				left JOIN king_transactions t ON t.transid=o.transid and o.status = 0  
				LEFT JOIN partner_info p ON p.id = t.partner_id 
				WHERE a.product_id=? 
				group by a.product_id
				";
		
		$r=$this->db->query($sql,$id)->row_array();
		$r['orders']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.time>".(time()-(24*60*60*90)),$id)->row()->s;
		$r['last_pen_order'] = @$this->db->query("SELECT a.transid,DATE_FORMAT(FROM_UNIXTIME(time),'%d/%m/%Y') AS orderd_on
									FROM king_transactions a 
									JOIN king_orders b ON a.transid = b.transid 
									LEFT JOIN partner_info c ON c.id = a.partner_id 
									JOIN m_product_deal_link l ON l.itemid=b.itemid 
									WHERE l.product_id=?  AND b.status = 0 
									GROUP BY is_pnh,partner_id  
									order by a.init asc  
										",$id)->row_array();
										
		if(isset($r['last_pen_order']['transid']))
		{
			$r['transid'] = $r['last_pen_order']['transid'];
			$r['last_orderdon'] = $r['last_pen_order']['orderd_on'];
		}								
		
		$r['pen_ord_qty']=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid where l.product_id=? and o.status = 0 ",$id)->row()->s;
		$r['cur_stk']=$this->db->query("select ifnull(sum(available_qty),0) as cur_stk from t_stock_info where product_id = ?",$id)->row()->cur_stk;
		$r['vendors']=$vs=$this->db->query("select v.vendor_id,v.vendor_name,CONCAT(v.vendor_name,' (',b.brand_margin,'%)') as vendor,b.brand_margin as ven_margin from m_product_info p join m_vendor_brand_link b on p.brand_id=b.brand_id join m_vendor_info v on v.vendor_id=b.vendor_id where p.product_id=? order by b.brand_margin desc",$id)->result_array();
		$r['partners']=$this->db->query("SELECT is_pnh,partner_id,c.name AS partner_name,IFNULL(SUM(b.quantity*l.qty),0)  AS total,a.is_pnh
														FROM king_transactions a 
														JOIN king_orders b ON a.transid = b.transid 
														LEFT JOIN partner_info c ON c.id = a.partner_id 
														JOIN m_product_deal_link l ON l.itemid=b.itemid 
														WHERE l.product_id=?  AND b.status = 0 
														GROUP BY is_pnh,partner_id  
														ORDER BY total,is_pnh,partner_id,partner_name",$id)->result_array();
		$r['margin']="0";
										
		if(!empty($vs))
		{
			$v=$vs[0]['vendor_id'];
			$r['margin']=$this->db->query("select brand_margin from m_vendor_brand_link where vendor_id=? and brand_id=?",array($v,$r['brand_id']))->row()->brand_margin;
		}
	
			return $r;
	}
	
	function searchproducts($q,$limit=100)
	{
		$sql="select p.*,sum(s.available_qty) as stock from m_product_info p left outer join t_stock_info s on s.product_id=p.product_id where p.product_name like ? or (p.barcode=? and p.barcode!='') group by p.product_id order by p.product_name asc limit 0,?";
		return $this->db->query($sql,array("%$q%",$q,$limit))->result_array();
	}
	
	function searchproductsfordeal($q)
	{
		if($this->input->post("type")=="prod")
			$sql="select p.*,sum(s.available_qty) as stock from m_product_info p left outer join t_stock_info s on s.product_id=p.product_id where p.product_name like ? or (p.barcode=? and p.barcode!='') group by p.product_id order by p.product_name asc";
		else
			$sql="select concat(g.group_name,' (GROUP)') as product_name,'na' as mrp,g.group_id as product_id,'na' as stock from products_group g where g.group_name like ?";
		return $this->db->query($sql,array("%$q%",$q))->result_array();
	}
	
	function getposforvendor($vid)
	{
		return $this->db->query("select * from t_po_info where vendor_id=?",$vid)->result_array();
	}
	
	function getpos_date_range($s=false,$e=false)
	{
//		if(!$s)
//		{
//			$s=date("Y-m-d",mktime(0,0,0,date("m"),1));
//			$e=date("Y-m-d",mktime(0,0,0,date("m")+1,date("t")));
//		}
		$sql="select v.vendor_name,v.city_name as city,p.* from t_po_info p join m_vendor_info v on v.vendor_id=p.vendor_id where 1";
		if($s)
			$sql.=" and p.created_on between ? and ?";
		$sql.=" order by p.po_id desc";
		if(!$s)
			$sql.=" limit 20";
		
		return $this->db->query($sql,array($s,$e))->result_array();
	}
	
	function getpos()
	{
		return $this->db->query("select v.vendor_name,v.city_name as city,p.* from t_po_info p join m_vendor_info v on v.vendor_id=p.vendor_id order by p.po_id desc")->result_array();
	}
	
	function getpo($p)
	{
		return $this->db->query("select v.vendor_name,p.po_id,p.remarks,p.approval_status,p.approval_date,po_status from t_po_info p join m_vendor_info v on v.vendor_id=p.vendor_id where p.po_id=?",$p)->row_array();
	}
	
	function getpoitems($p)
	{
		return $this->db->query("select p.default_rackbin_id,p.product_name,i.* from t_po_product_link i join m_product_info p on p.product_id=i.product_id where i.po_id=?",$p)->result_array();
	}
	
	function getstoragelocs()
	{
		return $this->db->query("select * from m_storage_location_info")->result_array();
	}
	
	function getpoitemsforgrn($p)
	{
		$ret=$this->db->query("select p.is_serial_required,ifnull(p.barcode,'') as barcode,p.default_rackbin_id,p.product_name,i.*,p.brand_id,p.mrp as prod_mrp from t_po_product_link i join m_product_info p on p.product_id=i.product_id where i.po_id=? and i.order_qty>i.received_qty",$p)->result_array();
		foreach($ret as $i=>$r)
		{
			$ret[$i]['rbs']="";
			foreach($this->db->query("select r.id,r.rack_name,r.bin_name from m_rack_bin_brand_link rb join m_rack_bin_info r on r.id=rb.rack_bin_id where rb.brandid=?",$r['brand_id'])->result_array() as $rb)
				$ret[$i]['rbs'].="<option value='{$rb['id']}'>{$rb['rack_name']}-{$rb['bin_name']}</option>";

			$ret[$i]['bcodes']=array();
			foreach($this->db->query("select product_id,product_barcode from t_stock_info where product_id = ? and product_barcode <> '' ",$r['product_id'])->result_array() as $pbcode)
				array_push($ret[$i]['bcodes'],$pbcode['product_barcode']);	
				
		}
		return $ret;
	}
	
	function createpo()
	{
		$edod = $_POST['e_dod'].' '.$_POST['e_dod_h'].':00:00';
		$this->db->query("insert into t_po_info(vendor_id,remarks,date_of_delivery,created_on,po_status) values(?,?,?,now(),0)",array($_POST['vendor'],$_POST['remarks'],$edod));
		$po=$this->db->insert_id();
		$pt=$_POST;
		$total=0;
		foreach($_POST['product'] as $i=>$p)
		{
			if(!isset($pt["foc$i"]))
				$pt["foc$i"]=0;
			if(!isset($pt["offer$i"]))
				$pt["offer$i"]=0;
			$sv=$pt['sch_type'][$i]?$pt['mrp'][$i]*$pt['sch_discount'][$i]/100:$pt['sch_discount'][$i];
			$inp=array($po,$p,$pt['qty'][$i],$pt['mrp'][$i],$pt['margin'][$i],$sv,$pt['sch_type'][$i],$pt['price'][$i],$pt["foc$i"],$pt["offer$i"],$pt['note'][$i]);
			$this->db->query("insert into t_po_product_link(po_id,product_id,order_qty,mrp,margin,scheme_discount_value,scheme_discount_type,purchase_price,is_foc,has_offer,special_note,created_on)
																					values(?,?,?,?,?,?,?,?,?,?,?,now())",$inp);
			$total+=$pt['price'][$i]*$pt['qty'][$i];
		}
		$this->db->query("update t_po_info set total_value=? where po_id=? limit 1",array($total,$po));
		$this->session->set_flashdata("erp_pop_info","PO created");
		redirect("admin/viewpo/$po");
	}
	
	function getrackbins()
	{
		return $this->db->query("select * from m_rack_bin_info order by rack_name asc")->result_array();
	}
	
	function searchvendors($q)
	{
		return $this->db->query("select * from m_vendor_info where vendor_name like ? order by vendor_name limit 20","%$q%")->result_array();
	}

	function getproductsbytax($bid)
	{
		return $this->db->query("select sum(s.available_qty) as stock,p.*,b.name as brand from m_product_info p join king_brands b on b.id=p.brand_id left outer join t_stock_info s on s.product_id=p.product_id where p.vat=? group by p.product_id order by p.product_name asc",$bid)->result_array();
	}
	
	function getproductsbybrand($bid)
	{
		return $this->db->query("select sum(s.available_qty) as stock,p.*,b.name as brand from m_product_info p join king_brands b on b.id=p.brand_id left outer join t_stock_info s on s.product_id=p.product_id where p.brand_id=? group by p.product_id order by p.product_name asc",$bid)->result_array();
	}
	
	function getdealsforbrand($bid)
	{
		return $this->db->query("select i.*,d.* from king_deals d join king_dealitems i on i.dealid=d.dealid where d.brandid=? order by i.name asc",$bid)->result_array();
	}
	
	function getdealsforbrand_pnh($bid)
	{
		return $this->db->query("select i.*,d.* from king_deals d join king_dealitems i on i.dealid=d.dealid where d.brandid=? and is_pnh=1 order by i.name asc",$bid)->result_array();
	}
	
	function getdealsforbrand_sit($bid)
	{
		return $this->db->query("select i.*,d.* from king_deals d join king_dealitems i on i.dealid=d.dealid where d.brandid=? and is_pnh!=1 order by i.name asc",$bid)->result_array();
	}
	
	function getproducts()
	{
		return $this->db->query("select sum(s.available_qty) as stock,p.*,b.name as brand from m_product_info p join king_brands b on b.id=p.brand_id left outer join t_stock_info s on s.product_id=p.product_id group by p.product_id order by p.product_id desc limit 30")->result_array();
	}
	
	function getproductsforbrand($id)
	{
		return $this->db->query("select * from m_product_info where brand_id=?",$id)->result_array();
	}
	
	function getbrandsforvendor($id)
	{
		return $this->db->query("select b.*,vb.brand_id,vb.brand_margin,vb.applicable_from,vb.applicable_till from king_brands b join m_vendor_brand_link vb on vb.brand_id=b.id and vb.vendor_id=? order by b.name asc",$id)->result_array();
	}
	
	function getvendorsforbrand($id)
	{
		return $this->db->query("select c.*,v.*,sum(p.total_value) as total_value,count(p.total_value) as pos from m_vendor_info v left outer join t_po_info p on p.vendor_id=v.vendor_id join m_vendor_brand_link vb on vb.vendor_id=v.vendor_id join m_vendor_contacts_info c on c.vendor_id=v.vendor_id where vb.brand_id=? group by v.vendor_id order by v.vendor_name asc",$id)->result_array();
	}
	
	function getvendors()
	{
		return $this->db->query("select c.*,v.*,sum(p.total_value) as total_value,count(p.total_value) as pos from m_vendor_info v left outer join t_po_info p on p.vendor_id=v.vendor_id join m_vendor_contacts_info c on c.vendor_id=v.vendor_id group by v.vendor_id order by v.vendor_name asc")->result_array();
	}
	
	function getvendor($id)
	{
		return $this->db->query("select * from m_vendor_info where vendor_id=?",$id)->row_array();
	}
	
	function getvendorcontacts($id)
	{
		return $this->db->query("select * from m_vendor_contacts_info where vendor_id=?",$id)->result_array();
	}
	
	function pnh_getfranchisesbytown($tid)
	{
		$user=$this->erpm->getadminuser();
		$sql="select f.created_on,f.is_suspended,f.franchise_id,f.is_lc_store,c.class_name,c.margin,c.combo_margin,f.pnh_franchise_id,f.franchise_name,f.locality,f.city,f.current_balance,f.login_mobile1,f.login_mobile2,f.email_id,u.name as assigned_to,t.territory_name from pnh_m_franchise_info f left outer join king_admin u on u.id=f.assigned_to join pnh_m_territory_info t on t.id=f.territory_id join pnh_m_class_info c on c.id=f.class_id";
	//	if(!$this->erpm->auth(CALLCENTER_ROLE,true))
	//		$sql.=" join pnh_franchise_owners o on o.franchise_id=f.franchise_id ";
		$sql.=" left outer join pnh_franchise_owners ow on ow.franchise_id=f.franchise_id left outer join king_admin a on a.id=ow.admin where town_id=? group by f.franchise_id order by f.franchise_name asc";
		return $this->db->query($sql,$tid)->result_array();
	}
	
	function pnh_getfranchisesbyterry($tid)
	{
		$user=$this->erpm->getadminuser();
		$sql="select f.created_on,f.is_suspended,tw.town_name as town_name,f.franchise_id,f.is_lc_store,c.class_name,c.margin,c.combo_margin,f.pnh_franchise_id,f.franchise_name,f.locality,f.city,f.current_balance,f.login_mobile1,f.login_mobile2,f.email_id,u.name as assigned_to,t.territory_name from pnh_m_franchise_info f left outer join king_admin u on u.id=f.assigned_to join pnh_m_territory_info t on t.id=f.territory_id join pnh_towns tw on tw.id=f.town_id join pnh_m_class_info c on c.id=f.class_id";	
//		if(!$this->erpm->auth(CALLCENTER_ROLE,true))
//			$sql.=" join pnh_franchise_owners o on o.franchise_id=f.franchise_id ";
		$sql.=" left outer join pnh_franchise_owners ow on ow.franchise_id=f.franchise_id left outer join king_admin a on a.id=ow.admin where f.territory_id=? group by f.franchise_id order by f.franchise_name asc";
		return $this->db->query($sql,$tid)->result_array();
	}
	
	function pnh_getfranchises()
	{
		$user=$this->erpm->getadminuser();
		$sql="select f.created_on,f.is_suspended,group_concat(a.name) as owners,tw.town_name as town,f.is_lc_store,f.franchise_id,c.class_name,c.margin,c.combo_margin,f.pnh_franchise_id,f.franchise_name,f.locality,f.city,f.current_balance,f.login_mobile1,f.login_mobile2,f.email_id,u.name as assigned_to,t.territory_name from pnh_m_franchise_info f left outer join king_admin u on u.id=f.assigned_to join pnh_m_territory_info t on t.id=f.territory_id join pnh_towns tw on tw.id=f.town_id join pnh_m_class_info c on c.id=f.class_id";	
		//if(!$this->erpm->auth(CALLCENTER_ROLE,true))
			//$sql.=" join pnh_franchise_owners o on o.franchise_id=f.franchise_id ";

		$sql.=" left outer join pnh_franchise_owners ow on ow.franchise_id=f.franchise_id left outer join king_admin a on a.id=ow.admin group by f.franchise_id order by f.franchise_name asc";
		return $this->db->query($sql)->result_array();
	}

	function do_pnh_addmember()
	{
		$user=$this->erpm->getadminuser();
//		$this->debug_post();
		foreach(array("mid","gender","salute","fname","lname","dob_d","dob_m","dob_y","address","city","pincode","mobile","email","marital","spouse","cname1","cname2","dow_d","dow_m","dow_y","dobc1_d","dobc1_m","dobc1_y","dobc2_d","dobc2_m","dobc2_y","profession","expense") as $i)
			$$i=$this->input->post($i);
		if(strlen($mid)!=8 || $mid{0}!=2)
			show_error("Invalid MID : $mid");
		$fid=$this->db->query("select franchise_id as fid from pnh_m_allotted_mid where ? between mid_start and mid_end",$mid)->row_array();
		if(empty($fid))
			show_error("$mid is not allotted to any Franchise");
		$fid=$fid['fid'];
		if($this->db->query("select 1 from pnh_member_info where pnh_member_id=?",$mid)->num_rows()==0)
		{
			$this->db->query("insert into king_users(name,is_pnh,createdon) values(?,1,?)",array("PNH Member: $mid",time()));
			$userid=$this->db->insert_id();
			$this->db->query("insert into pnh_member_info(user_id,pnh_member_id,franchise_id) values(?,?,?)",array($userid,$mid,$fid));
			$npoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+PNH_MEMBER_FEE;
			$this->db->query("update pnh_member_info set points=? where user_id=? limit 1",array($npoints,$userid));
			$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,"",PNH_MEMBER_FEE,$npoints,time()));
			$this->erpm->pnh_fran_account_stat($fid,1,PNH_MEMBER_FEE-PNH_MEMBER_BONUS,"50 Credit Points purchase for Member $mid","member",$mid);
			// pnh account summary - membership  
			$arr = array($fid,4,$mid,PNH_MEMBER_FEE-PNH_MEMBER_BONUS,'',1,date('Y-m-d H:i:s'),$user['userid']);
			$this->db->query("insert into pnh_franchise_account_summary (franchise_id,action_type,member_id,debit_amt,remarks,status,created_on,created_by) values(?,?,?,?,?,?,?,?)",$arr);
			
		}
		else $userid=$this->db->query("select user_id as u from pnh_member_info where pnh_member_id=?",$mid)->row()->u;
		$payload=array("pnh_member_id"=>$mid,"gender"=>$gender,"salute"=>$salute,"first_name"=>$fname,"last_name"=>$lname);
		$payload['dob']=$dob_y.'-'.$dob_m.'-'.$dob_d;
		$payload['address']=$address;
		$payload['city']=$city;
		$payload['pincode']=$pincode;
		$payload['mobile']=$mobile;
		$payload['email']=$email;
		$payload['marital_status']=$marital;
		$payload['spouse_name']=$spouse;
		$payload['child1_name']=$cname1;
		$payload['child2_name']=$cname2;
		$payload['anniversary']=$dow_y.'-'.$dow_m.'-'.$dow_d;
		$payload['child1_dob']=$dobc1_y.'-'.$dobc1_m.'-'.$dobc1_d;
		$payload['child2_dob']=$dobc2_y.'-'.$dobc2_m.'-'.$dobc2_d;
		$payload['expense']=$expense;
		$payload['profession']=$profession;
		$payload['created_on']=time();
		$payload['created_by']=$user['userid'];
		$this->db->update("pnh_member_info",$payload,array("user_id"=>$userid));
		$this->erpm->flash_msg("Member details updated");
		
		redirect("admin/pnh_addmember");
	}
	
	
	function do_pnh_editmember()
	{
		$user=$this->erpm->getadminuser();
//		$this->debug_post();
		foreach(array("userid","mid","gender","salute","fname","lname","dob_d","dob_m","dob_y","address","city","pincode","mobile","email","marital","spouse","cname1","cname2","dow_d","dow_m","dow_y","dobc1_d","dobc1_m","dobc1_y","dobc2_d","dobc2_m","dobc2_y","profession","expense") as $i)
			$$i=$this->input->post($i);
		if(strlen($mid)!=8 || $mid{0}!=2)
			show_error("Invalid MID : $mid");
		$fid=$this->db->query("select franchise_id as fid from pnh_m_allotted_mid where ? between mid_start and mid_end",$mid)->row_array();
		if(empty($fid))
			show_error("$mid is not allotted to any Franchise");
		$fid=$fid['fid'];
		
		$payload=array("pnh_member_id"=>$mid,"gender"=>$gender,"salute"=>$salute,"first_name"=>$fname,"last_name"=>$lname);
		$payload['dob']=$dob_y.'-'.$dob_m.'-'.$dob_d;
		$payload['address']=$address;
		$payload['city']=$city;
		$payload['pincode']=$pincode;
		$payload['mobile']=$mobile;
		$payload['email']=$email;
		$payload['marital_status']=$marital;
		$payload['spouse_name']=$spouse;
		$payload['child1_name']=$cname1;
		$payload['child2_name']=$cname2;
		$payload['anniversary']=$dow_y.'-'.$dow_m.'-'.$dow_d;
		$payload['child1_dob']=$dobc1_y.'-'.$dobc1_m.'-'.$dobc1_d;
		$payload['child2_dob']=$dobc2_y.'-'.$dobc2_m.'-'.$dobc2_d;
		$payload['expense']=$expense;
		$payload['profession']=$profession;
		$payload['modified_on']=time();
		$payload['modified_by']=$user['userid'];
		$this->db->update("pnh_member_info",$payload,array("user_id"=>$userid,"pnh_member_id"=>$mid));
		
		$this->erpm->flash_msg("Member details updated");
		
		redirect("admin/pnh_editmember/".$mid);
	}
	
	function do_pnh_bulkaddmembers()
	{
		$user=$this->erpm->getadminuser();
		
		$error = '';
		//check if file is uploaded.
		if(isset($_FILES['imp_file']))
		{
			if(!$_FILES['imp_file']['name'])
				$error = "Please choose import file";
			else
			{
				//validate import file
				$f = fopen($_FILES['imp_file']['tmp_name'],'r');
				$head=fgetcsv($f);
				fclose($f);
				if(count($head) != 28)
					$error = "Invalid import file format";
			}
		}else
			$error = "Invalid access";
		
		
		if($error)
		{
			$this->erpm->flash_msg($error);
			redirect("admin/pnh_bulkaddmembers");
		}
		 
 
		 
		$f = fopen($_FILES['imp_file']['tmp_name'],'r');
			 
		$head = array("mid","gender","salute","fname","lname","dob_d","dob_m","dob_y","address","city","pincode","mobile","email","marital","spouse","cname1","cname2","dow_d","dow_m","dow_y","dobc1_d","dobc1_m","dobc1_y","dobc2_d","dobc2_m","dobc2_y","profession","expense");
		$newdump = array();
		 
		$m=0;
		while(($data=fgetcsv($f))!=false)
		{
			if($m < 1)
			{
				$m = 1;
				continue;
			}
			foreach($head as $i=>$d)
				$$d=$data[$i];
				
			$gender = trim(strtolower($gender));
				
			if($gender == 'male')
				$gender_indx = 0;
			else if($gender == 'female')
				$gender_indx = 1;
			else if($gender == '')
				$gender_indx = 2;
				
			$salute_indx = 0;
			$salute = trim(strtolower($salute));
			if($salute == 'mr')
				$salute_indx = 0;
				else if($salute == 'mrs')
					$salute_indx = 1;
					else if($salute == 'ms')
						$salute_indx = 2;
			
			$expense_index = 0;
			if($expense <= 2000)
				$expense_index = 0;
			else if($expense > 2000 && $expense <=5000)
					$expense_index = 1;
				else if($expense > 5000 && $expense <=10000)
					$expense_index = 2;
					else if($expense > 10000)
						$expense_index = 3;
				
			$marital_indx = 0;			
			$marital = trim(strtolower($marital));				
			if($marital == 'married')
				$marital_indx = 0;
			else if($marital == 'single')
			 	 $marital_indx = 1;
				else if($marital == 'other') 	
				 	$marital_indx = 2; 
						
			if(strlen($mid)!=8 || $mid{0}!=2)
			{
				$data[28] = 0;
			}
			else
			{
				$fid=$this->db->query("select franchise_id as fid from pnh_m_allotted_mid where ? between mid_start and mid_end",$mid)->row_array();
				if(empty($fid))
					show_error("$mid is not allotted to any Franchise");
				$fid=$fid['fid'];
				if($this->db->query("select 1 from pnh_member_info where pnh_member_id=?",$mid)->num_rows()==0)
				{
					$this->db->query("insert into king_users(name,is_pnh,createdon) values(?,1,?)",array("PNH Member: $mid",time()));
					$userid=$this->db->insert_id();
					$this->db->query("insert into pnh_member_info(user_id,pnh_member_id,franchise_id) values(?,?,?)",array($userid,$mid,$fid));
					$npoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+PNH_MEMBER_FEE;
					$this->db->query("update pnh_member_info set points=? where user_id=? limit 1",array($npoints,$userid));
					$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,"",PNH_MEMBER_FEE,$npoints,time()));
					$this->erpm->pnh_fran_account_stat($fid,1,PNH_MEMBER_FEE-PNH_MEMBER_BONUS,"50 Credit Points purchase for Member $mid","member",$mid);
				}
				else $userid=$this->db->query("select user_id as u from pnh_member_info where pnh_member_id=?",$mid)->row()->u;
				$payload=array("pnh_member_id"=>$mid,"gender"=>$gender_indx,"salute"=>$salute_indx,"first_name"=>$fname,"last_name"=>$lname);
				$payload['dob']=$dob_y.'-'.$dob_m.'-'.$dob_d;
				$payload['address']=$address;
				$payload['city']=$city;
				$payload['pincode']=$pincode;
				$payload['mobile']=$mobile;
				$payload['email']=$email;
				$payload['marital_status']=$marital;
				$payload['spouse_name']=$spouse;
				$payload['child1_name']=$cname1;
				$payload['child2_name']=$cname2;
				$payload['anniversary']=$dow_y.'-'.$dow_m.'-'.$dow_d;
				$payload['child1_dob']=$dobc1_y.'-'.$dobc1_m.'-'.$dobc1_d;
				$payload['child2_dob']=$dobc2_y.'-'.$dobc2_m.'-'.$dobc2_d;
				$payload['expense']=$expense_index;
				$payload['profession']=$profession;
				$payload['created_on']=time();
				$payload['created_by']=$user['userid'];
				$this->db->update("pnh_member_info",$payload,array("user_id"=>$userid));
				$m++;
				$data[28] = 1;
			}
			$newdump[] = $data;
		}
		fclose($f);
		if($m)
		{
			$head[] = 'Status';
			@ob_start();
			$f=fopen("php://output","w");
			fputcsv($f,$head);
			foreach($newdump as $p)
				fputcsv($f,$p);
			fclose($f);
			$csv=ob_get_clean();
			@ob_clean();
		    header('Content-Description: File Transfer');
		    header('Content-Type: text/csv');
		    header('Content-Disposition: attachment; filename='.("members_bulk_upload_".date("d_m_y_H\h:i\m").".csv"));
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . strlen($csv));
		    @ob_clean();
		    flush();
		    echo $csv;
			
		}else
		{
			$this->erpm->flash_msg("No Members updated");
			redirect("admin/pnh_bulkaddmembers");
		}
		
	}
	
	
	function pnh_getfranchise($fid)
	{
		$sql="select f.*,f.franchise_id,f.sch_discount,f.sch_discount_start,f.sch_discount_end,f.credit_limit,f.security_deposit,c.class_name,c.margin,c.combo_margin,f.pnh_franchise_id,f.franchise_name,f.locality,f.city,f.current_balance,f.login_mobile1,f.login_mobile2,f.email_id,u.name as assigned_to,t.territory_name,f.is_prepaid from pnh_m_franchise_info f left outer join king_admin u on u.id=f.assigned_to join pnh_m_territory_info t on t.id=f.territory_id join pnh_m_class_info c on c.id=f.class_id where f.franchise_id=? order by f.franchise_name asc";	
		return $this->db->query($sql,$fid)->row_array();
	}
	
	function do_pnh_manage_devices($fid)
	{
		$user=$this->erpm->getadminuser();
		$sno=$this->input->post("dsno");
		$types=$this->input->post("dtype");
		foreach($sno as $i=>$s)
		{
			$type=$types[$i];
			if(empty($s))
				break;
			if($this->db->query("select 1 from pnh_m_device_info where device_sl_no=? and device_type_id=? and issued_to!=0",array($s,$types[$i]))->num_rows()!=0)
				show_error("Device with serial no $s is already allotted to someone");
			if($this->db->query("select 1 from pnh_m_device_info where device_sl_no=? and device_type_id=?",array($s,$types[$i]))->num_rows()==0)
			{
				$this->db->query("insert into pnh_m_device_info(device_sl_no,device_type_id) values(?,?)",array($s,$types[$i]));
				$did=$this->db->insert_id();
			}else
				$did=$this->db->query("select id from pnh_m_device_info where device_sl_no=? and device_type_id=?",array($s,$type))->row()->id;
			$this->db->query("update pnh_m_device_info set issued_to=$fid where id=?",$did);
			$this->db->query("insert into pnh_t_device_movement_info(device_id,issued_to,created_by,created_on) values(?,?,?,?)",array($did,$fid,$user['userid'],time()));
		}
		redirect("admin/pnh_manage_devices/$fid");
	}
	
	/**
	 * Check for stock by item ids
	 * 
	 * @param $items
	 * @param $i_qty
         * @return array Payload / Available stock
	 */ 
	function do_stock_check($items,$i_qty=array(),$return_stock=false)
	{
		$payload=array();
		$pids=array();
		/** fixing for test 1 qty **/ 
		if(empty($i_qty))
			foreach($items as $i)
				$i_qty[]=1;

		/** assiging the req qty for item stock check **/		
		$i=0;
		foreach($items as $item)
		{
			$qty[$item]=$i_qty[$i];
			$i++;
		}
				
		/** Getting Products for Items  **/ 
		$i=0;
		foreach($items as $item)
		{
			$d=array();
			foreach($this->db->query("select l.product_id,p.product_name,l.qty,p.is_sourceable as src from m_product_deal_link l join m_product_info p on p.product_id=l.product_id where l.itemid=?",$item)->result_array() as $p)
			{
				$d[]=array("pid"=>$p['product_id'],"product_name"=>$p['product_name'],"qty"=>$p['qty'],"status"=>$p['src'],"stk"=>0);
				$pids[]=$p['product_id'];
			}
			$payload[$item]=$d;
			$i++;
		}
		
		// Check for Stock Available and alloted stock pid    
		$pids=array_unique($pids);
		foreach($pids as $pid)
		{
			$available[$pid]=$this->db->query("select ifnull(sum(available_qty),0) as s from t_stock_info where product_id=?",$pid)->row()->s;
			$required[$pid]=$this->db->query("select ifnull(sum(o.quantity*l.qty),0) as s from m_product_deal_link l join king_orders o on o.itemid=l.itemid and o.status=0 where l.product_id=?",$pid)->row()->s;
			
		}
		
		
		
		
		$i=0;
		foreach($payload as $iid=>$pay)
		{
			// Check if stock available for required and requested qty of product 
			foreach($pay as $ip=>$item)
				if($available[$item['pid']]>=$required[$item['pid']]+($item['qty']*$qty[$iid]))
				{
					$payload[$iid][$ip]['status']=1; // yes stock available
					$payload[$iid][$ip]['stk']=$available[$item['pid']];
				}
					
			 
					
			// check if deal items having stock  		
			$all_avail=true;
			foreach($pay as $item)
				if($item['status']==0)
					$all_avail=false;
					
			if($all_avail)
				foreach($pay as $i2=>$item)
				{
					// remove required stock from  available stock with required for further iteration checks 
					$available[$item['pid']]-=($item['qty']*$qty[$iid]);
					$payload[$iid][$i2]['status']=1;
					//$payload[$iid][$ip]['stk']=$available[$item['pid']];
				}
			$i++;
		}
		
		$p_stock_det = array();
		// get all item ids for which stock available 
		$stock_avail=array();
		foreach($payload as $iid=>$pay)
		{
			$all_avail=true;
			foreach($pay as $item)
				if($item['status']==0)
					$all_avail=false;
			if($all_avail)
			{
				$stock_avail[]=$iid;
				$p_stock_det[$iid]=array();
			}
		}
	 
		if($return_stock)
			return $payload;
		else
			return $stock_avail;
	}
		
	function do_pnh_add_fran_bank_details($fid)
	{
		$user=$this->erpm->getadminuser();
		foreach(array("bank_name","account_no","branch_name","ifsc_code") as $i)
			$inp[$i]=$this->input->post($i);
		$inp['created_by']=$user['userid'];
		$inp['created_on']=time();
		$inp['franchise_id']=$fid;
		$this->db->insert("pnh_franchise_bank_details",$inp);
		redirect("admin/pnh_franchise/$fid#bank");			
	}
	
	function do_pnh_cancel_receipt($rid)
	{
		$user=$this->erpm->getadminuser();
		$msg=$this->input->post("msg");
		$r=$this->db->query("select * from pnh_t_receipt_info where receipt_id=?",$rid)->row_array();
		$this->db->query("update pnh_t_receipt_info set reason=?,status=3,activated_by=?,activated_on=? where receipt_id=? limit 1",array($msg,$user['userid'],time(),$rid));
		$this->erpm->flash_msg("Receipt $rid cancelled");
		redirect("admin/pnh_pending_receipts");
	}
	
	function do_pnh_activate_receipt($rid)
	{
		$user=$this->erpm->getadminuser();
		$msg=$this->input->post("msg");
		$r=$this->db->query("select * from pnh_t_receipt_info where receipt_id=?",$rid)->row_array();
		$this->db->query("update pnh_t_receipt_info set reason=?,status=1,activated_by=?,activated_on=? where receipt_id=? and (is_submitted=1 or payment_mode=0) limit 1",array($msg,$user['userid'],time(),$rid));
		
		if($r['receipt_type']==1)
			$this->erpm->pnh_fran_account_stat($r['franchise_id'],0, $r['receipt_amount'],"Topup for Receipt:{$r['receipt_id']} ".date("d/m/y",$r['created_on']),"topup",$r['receipt_id']);
		else
			$this->db->query("update pnh_m_franchise_info set security_deposit=security_deposit+? where franchise_id=? limit 1",array($r['receipt_amount'],$r['franchise_id']));
		
		$receipt_det = $this->db->query('select * from pnh_t_receipt_info where receipt_id = ?',$rid)->row_array();
		
		$arr = array($receipt_det['franchise_id'],(($r['receipt_type']==1)?3:2),$rid,$receipt_det['receipt_type'],$receipt_det['receipt_amount'],$msg,1,date('Y-m-d H:i:s'),$user['userid']);
				$this->db->query("insert into pnh_franchise_account_summary (franchise_id,action_type,receipt_id,receipt_type,credit_amt,remarks,status,created_on,created_by) values(?,?,?,?,?,?,?,?,?)",$arr);
				
		$this->erpm->flash_msg("Receipt $rid activated");
		redirect("admin/pnh_pending_receipts");
	}
	
	function do_pnh_topup($fid)
	{
		$user=$this->erpm->getadminuser();
		foreach(array("r_type","amount","bank","type","no","date","msg","transit_type") as $i)
			$$i=$this->input->post($i);
		$inp=array("receipt_type"=>$r_type,"franchise_id"=>$fid,"bank_name"=>$bank,"receipt_amount"=>$amount,"payment_mode"=>$type,"instrument_no"=>$no,"instrument_date"=>strtotime($date),"created_by"=>$user['userid'],"created_on"=>time(),"remarks"=>$msg,"in_transit"=>$transit_type);
		
		// if cash receipt is added.
		if($type == 0)
			$inp['instrument_date'] = strtotime(date('Y-m-d'));
		
		$this->db->insert("pnh_t_receipt_info",$inp);
		$recpt_id = $this->db->insert_id();
//		$this->erpm->pnh_fran_account_stat($fid,0, $amount,"Topup $no $date");
		
		/*
		$arr = array($fid,((!$r_type)?2:3),$recpt_id,$r_type,$amount,$msg,1,date('Y-m-d H:i:s'),$user['userid']);
				$this->db->query("insert into pnh_franchise_account_summary (franchise_id,action_type,receipt_id,receipt_type,credit_amt,remarks,status,created_on,created_by) values(?,?,?,?,?,?,?,?,?)",$arr);
		*/
		$franchise_det=$this->erpm->pnh_getfranchise($fid);
		if($franchise_det['is_suspended']==2 && $franchise_det['security_deposit']==0)
		{
			$this->db->query("update pnh_m_franchise_info set is_suspended=0 where franchise_id=?",$fid);
		}
		redirect("admin/pnh_franchise/$fid");
	}
	
	function do_pnh_updatedeal($itemid)
	{
		$user_det=$this->session->userdata("admin_user");
		
		//check if any prices updated
		$is_price_update=0;
		$prices_det=$this->db->query("select orgprice,price from king_dealitems where id=? and is_pnh=1",$itemid)->row_array();
		
		$msg_flag=0;
		foreach(array("gender_attr","menu","keywords","tagline","name","mrp","offer_price","store_offer_price","nyp_offer_price","brand","category","description","pid","qty","tax","shipsin","p_price") as $q)
			$$q=$this->input->post($q);
		$imgname = randomChars ( 15 );
		if (isset ( $_FILES ['pic'] ) && $_FILES ['pic'] ['error'] == 0)
		{
			$this->load->library("thumbnail");
			$img=$_FILES['pic']['tmp_name'];
			if($this->thumbnail->check($img))
			{
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/300/$imgname.jpg","width"=>300));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/small/$imgname.jpg","width"=>200));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/thumbs/$imgname.jpg","width"=>50,"max_height"=>50));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/$imgname.jpg","width"=>400));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/big/$imgname.jpg","width"=>1000));
			}
		}
		else
			$imgname=NULL;
		$dealid=$this->db->query("select dealid from king_dealitems where id=?",$itemid)->row()->dealid;
		if($imgname)
		{
			$this->db->query("update king_deals set pic=? where dealid=? limit 1",array($imgname,$dealid));
			$this->db->query("update king_dealitems set pic=? where dealid=? limit 1",array($imgname,$dealid));
		}
		
		//check if price changes if changes push to log
		if($mrp!=$prices_det['orgprice'])
			$is_price_update=1;
		else if($offer_price!=$prices_det['price'])
			$is_price_update=1;
			
		
		$this->db->query("update king_dealitems set name=?,orgprice=?,price=?,store_price=?,nyp_price=?,gender_attr=?,tax=?,shipsin=?,modified=?,modified_on=?,modified_by=? where id=?",array($name,$mrp,$offer_price,$store_offer_price,$nyp_offer_price,$gender_attr,$tax*100,$shipsin,time(),date('Y-m-d H:i:s'),$user_det['userid'],$itemid));
		$this->db->query("update king_deals set description=?,keywords=?,menuid=?,keywords=?,catid=?,brandid=?,tagline=? where dealid=?",array($description,$keywords,$menu,$keywords,$category,$brand,$tagline,$dealid));
		
		
		if($is_price_update)
		{
			$price_upt_prm=array();
			$price_upt_prm['itemid']=$itemid;
			$price_upt_prm['old_mrp']=$prices_det['orgprice'];
			$price_upt_prm['new_mrp']=$mrp;
			$price_upt_prm['new_price']=$offer_price;
			$price_upt_prm['old_price']=$prices_det['price'];
			$price_upt_prm['created_by']=$user_det['userid'];
			$price_upt_prm['created_on']=time();
			$price_upt_prm['reference_grn']=0;
			
			$this->db->insert('deal_price_changelog',$price_upt_prm);
		}
			
		
		
		
		if(!empty($pid))
		{
			foreach($pid as $i=>$p)
			{
				$sql1="select * from m_product_deal_link where itemid=? and product_id=?;";
				$pnh_deal_prd_link_det=$this->db->query($sql1,array($itemid,$p))->row_array();
				if(!$pnh_deal_prd_link_det)
				{	
					$msg_flag=1;
					$mrp=$this->db->query("select mrp from m_product_info where product_id=?",$p)->row()->mrp;
					$this->db->query("insert into m_product_deal_link(product_id,itemid,qty,created_on,created_by,product_mrp) values(?,?,?,?,?,?)",array($p,$itemid,$qty[$i],date('Y-m-d H:i:s'),$user_det['userid'],$mrp));
					$this->db->query("insert into t_upd_product_deal_link_log(itemid,product_id,qty,perform_on,perform_by,is_updated,product_mrp)values(?,?,?,?,?,?,?)",array($itemid,$p,$qty[$i],date('Y-m-d H:i:s'),$user_det['userid'],2,$mrp));
				}
			}
		}
		
		
		$this->session->set_flashdata("erp_pop_info","Deal updated");
		redirect("admin/pnh_deal/$itemid");
	}
	
	function flash_msg($msg)
	{
		$this->session->set_flashdata("erp_pop_info",$msg);
	}
	
	function do_pnh_deal_extra_images($id)
	{
		$s=0;
		$dealid=$this->db->query("select dealid from king_dealitems where id=?",$id)->row()->dealid;
		foreach($_FILES['pic']['error'] as $i=>$e)
		{
			if($e==0)
			{
				$imgname = randomChars ( 15 );
				$this->load->library("thumbnail");
				$img=$_FILES['pic']['tmp_name'][$i];
				if($this->thumbnail->check($img))
				{
					$s++;
					$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/300/$imgname.jpg","width"=>300));
					$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/small/$imgname.jpg","width"=>200));
					$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/thumbs/$imgname.jpg","width"=>50,"max_height"=>50));
					$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/$imgname.jpg","width"=>400));
					$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/big/$imgname.jpg","width"=>1000));
					$this->db->query("insert into king_resources(dealid,itemid,type,id) values($dealid,$id,0,'$imgname')");
					$this->erpm->flash_msg("$s images uploaded");
					redirect("admin/pnh_deal/$id");
				}
			}
		}
	}
	
	function do_pnh_adddeal()
	{
		$user=$this->erpm->getadminuser();
		
		foreach(array("gender_attr","menu","keywords","tagline","name","mrp","offer_price","store_offer_price","nyp_offer_price","brand","category","description","pid","qty","tax","shipsin","pid_g","qty_g") as $q)
			$$q=$this->input->post($q);
		$imgname = randomChars ( 15 );
		if (isset ( $_FILES ['pic'] ) && $_FILES ['pic'] ['error'] == 0)
		{
			$this->load->library("thumbnail");
			$img=$_FILES['pic']['tmp_name'];
			if($this->thumbnail->check($img))
			{
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/300/$imgname.jpg","width"=>300));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/small/$imgname.jpg","width"=>200));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/thumbs/$imgname.jpg","width"=>50,"max_height"=>50));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/$imgname.jpg","width"=>400));
				$this->thumbnail->create(array("source"=>$img,"dest"=>"images/items/big/$imgname.jpg","width"=>1000));
			}
		}
		else
			$imgname=null;
		if(!$imgname)
			show_error("No valid image uploaded");
		$itemid=$this->erpm->p_genid(10);
		$dealid=$this->erpm->p_genid(10);
		$pnh_id="1".$this->erpm->p_genid(7);
		$inp=array($dealid,$menu,$keywords,$category,$brand,$imgname,$tagline,$description,1);
		$this->db->query("insert into king_deals(dealid,menuid,keywords,catid,brandid,pic,tagline,description,publish) values(?,?,?,?,?,?,?,?,?)",$inp);
		$inp=array($itemid,$dealid,$name,$imgname,$mrp,$offer_price,$store_offer_price,$nyp_offer_price,$gender_attr,1,$pnh_id,$tax*100,$shipsin,1,time(),date("Y-m-d H:i:s"),$user['userid']);
		$this->db->query("insert into king_dealitems(id,dealid,name,pic,orgprice,price,store_price,nyp_price,gender_attr,is_pnh,pnh_id,tax,shipsin,live,created,created_on,created_by) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",$inp);
		if(!empty($pid))
		foreach($pid as $i=>$p)
			$this->db->query("insert into m_product_deal_link(product_id,itemid,qty,created_on,created_by) values(?,?,?,?,?)",array($p,$itemid,$qty[$i],date('Y-m-d H:i:s'),$user['userid']));
		if(!empty($pid_g))
		foreach($pid_g as $i=>$p)
			$this->db->query("insert into m_product_group_deal_link(group_id,itemid,qty,created_on,created_by) values(?,?,?,?,?)",array($p,$itemid,$qty_g[$i],date('Y-m-d H:i:s'),$user['userid']));
		redirect("admin/pnh_deals");
	}
	
	function pnh_getdeals()
	{
		//return $this->db->query("select d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid where i.is_pnh=1 order by i.created_on desc,i.name asc limit 30")->result_array();
		$sql = "select ifnull(group_concat(smd.special_margin),0) as sm,d.publish,d.brandid,d.catid,i.orgprice,
						i.price,i.name,i.pic,i.pnh_id,i.id as itemid,
						b.name as brand,c.name as category 
					from king_deals d 
					join king_dealitems i on i.dealid=d.dealid 
					join king_brands b on b.id=d.brandid 
					join king_categories c on c.id=d.catid 
					left join pnh_special_margin_deals smd on i.id = smd.itemid  and smd.from <= unix_timestamp() and smd.to >=unix_timestamp()  
					where i.is_pnh=1 
					group by i.id 
					order by i.sno desc
					limit 30 
				";
		return $this->db->query($sql)->result_array();
	}
	
	function pnh_getdealsbycat($catid,$brandid,$type=0)
	{
		if($type==0)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left join pnh_special_margin_deals smd on i.id = smd.itemid  and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.catid=? and d.brandid=? group by i.id order by i.name asc",array($catid,$brandid))->result_array();
		else if($type==1)
			$ret=$this->db->query("select * from (select ifnull(group_concat(smd.special_margin),0) as sm,i.id,o.transid,o.time as order_time,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.catid=? and d.brandid= ?group by i.id order by o.time desc) as dd group by dd.id order by dd.order_time desc",array($catid,$brandid))->result_array();
		else if($type==2)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,o.quantity as qty,ifnull(sum(o.quantity),0) as sold,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left outer join king_transactions t on t.transid=o.transid and t.is_pnh=1 left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.catid=? and d.brandid=? group by i.id order by count(o.id) desc",array($catid,$brandid))->result_array();
		else if($type==3)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,o.quantity as qty,ifnull(sum(o.quantity),0) as sold,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left outer join king_transactions t on t.transid=o.transid and t.is_pnh=1 left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.catid=? and (o.time>".mktime(0,0,0,0,-90)." or o.time is null) and d.brandid=? group by i.id order by count(o.id) desc,i.name asc",array($catid,$brandid))->result_array();
		return $ret;	

	}
	
	function pnh_getreceiptbytype($type,$st_date=false,$en_date=false,$tid=false,$pg=0)
	{
		
		if($st_date)
		{
			$st_date=strtotime($st_date.' 00:00:00');
			$en_date=strtotime($en_date.' 23:59:59');
		}
		
		
		if($type==0)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby  FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1  and is_submitted=0  ORDER BY instrument_date DESC";
		}
		
		if($st_date && $type==0 )
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby  FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.instrument_date between ? and ? ORDER BY instrument_date DESC";
		}
		if($type==1 )
		{
  			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.status=0  ORDER BY instrument_date asc";
		}
		if($type==1  && $st_date)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate() AND f.is_suspended=0 and is_submitted=0 and r.status=0  and r.instrument_date between ? and ?  ORDER BY instrument_date asc";
		}
  		if($type==2 )
  		{
  			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate() AND f.is_suspended=0 and is_submitted=0  ORDER BY instrument_date asc";
  		}
  		if($type==2 && $st_date)
  		{
  			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate() AND f.is_suspended=0 and is_submitted=0 and r.instrument_date between ? and ?  ORDER BY instrument_date asc";
  		}
  		
  		if($type==3)
  		{
  			$cond = '';
  			$cond_params = array();
  			if($st_date)
  				$cond = " and r.activated_on between ? and ?  ";
  			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 AND f.is_suspended=0 and (is_submitted=1 or r.activated_on!=0) and r.is_active=1 $cond ORDER BY activated_on desc";
  			
  		}
  		
  		
  		if($type==4)
  		{
  			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM pnh_t_receipt_info r LEFT JOIN `pnh_m_deposited_receipts`d ON d.receipt_id=r.receipt_id LEFT JOIN `pnh_m_bank_info` b ON b.id=d.bank_id LEFT JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE  f.is_suspended=0 AND r.is_submitted=1 AND r.status=0  and r.is_active=1  order by d.submitted_on desc";
  		}
  		if($type==4 && $st_date)
  		{
  			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM pnh_t_receipt_info r LEFT JOIN `pnh_m_deposited_receipts`d ON d.receipt_id=r.receipt_id LEFT JOIN `pnh_m_bank_info` b ON b.id=d.bank_id LEFT JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE  f.is_suspended=0 AND r.is_submitted=1 AND r.status=0  and r.is_active=1 and unix_timestamp(d.submitted_on) between ? and ? order by unix_timestamp(d.submitted_on) desc";
		}
		
		if($type==5)
		{
			$cond = '';
			$cond_params = array();
			if($st_date)
				$cond = " and unix_timestamp(c.cancelled_on) between ? and ?  ";
			
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND r.is_active=1  $cond
						GROUP BY r.receipt_id
						ORDER BY activated_on DESC
						";
			
		}
		
		$total_rows = $this->db->query($sql,array($st_date,$en_date))->num_rows();
		
		$sql = $sql.' limit '.$pg.',50';
		$receipts=$this->db->query($sql,array($st_date,$en_date))->result_array();
		
		return array($total_rows,$receipts);
		
	}
	
	function pnh_getactivationlistbytype($type=0,$pg=0)
	{
	
		if($type==0)
			$sql = "SELECT m.*,f.franchise_name FROM pnh_member_info m JOIN pnh_m_franchise_info f ON f.franchise_id=m.franchise_id WHERE DATE(FROM_UNIXTIME(m.created_on))=DATE(NOW()) order by m.created_on desc";
		if($type==1)
			$sql="SELECT m.*,f.franchise_name FROM pnh_member_info m JOIN pnh_m_franchise_info f ON f.franchise_id=m.franchise_id WHERE MONTH(FROM_UNIXTIME(m.created_on)) = MONTH(CURDATE()) ORDER BY m.created_on DESC";
		if($type==2)
			$sql="SELECT t.*,m.pnh_member_id,m.first_name,m.last_name,f.franchise_name FROM pnh_t_voucher_details t JOIN pnh_member_info m ON m.pnh_member_id=t.member_id JOIN pnh_m_franchise_info f ON f.franchise_id=t.franchise_id  WHERE DATE(activated_on)=CURDATE() and t.status >=3 order by activated_on desc";
	
		if($type==3)
			$sql="SELECT t.*,m.pnh_member_id,m.first_name,m.last_name,f.franchise_name FROM pnh_t_voucher_details t JOIN pnh_member_info m ON m.pnh_member_id=t.member_id JOIN pnh_m_franchise_info f ON f.franchise_id=t.franchise_id WHERE MONTH(activated_on) = MONTH(CURDATE()) and t.status >=3 order by activated_on desc";
	
		$sql = $sql.' limit '.$pg.',20';
		$activation_list=$this->db->query($sql)->result_array();
	
		return $activation_list;
	
	
	}
	
	function pnh_getreceiptbytypeterry($type=0,$tid=false,$pg=0)
	{
		if($type==0)
			$sql = "SELECT r.*,f.franchise_name,a.name,m.name AS modifiedby AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and f.territory_id=?   ORDER BY instrument_date DESC";
		if($type==1 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and f.territory_id=? and r.is_active=1 ORDER BY instrument_date asc";
		if($type==2 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and f.territory_id=?  ORDER BY instrument_date asc";
		if($type==3)
			$sql = "SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and f.territory_id=?  ORDER BY date(from_unixtime(activated_on)) DESC";
		if($type==4)
			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1  AND r.status=0 and  r.is_active=1 and f.territory_id=? order by d.submitted_on desc";
		if($type==5)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=2 AND r.is_active=1   AND r.is_active=1 AND  f.territory_id=? ORDER BY cancelled_on DESC";

		
			$total_rows = $this->db->query($sql,$tid)->num_rows();
			$sql = $sql.' limit '.$pg.',50';
			$receipts=$this->db->query($sql,$tid)->result_array();
			return array($total_rows,$receipts);
	}
	
	
	function to_get_pending_receipts()
	{
		$pending_receipts=$this->db->query("SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.status=0  ORDER BY instrument_date asc")->result_array();
		return $pending_receipts;
	}
	
	
	/**
	 * function to get total value 
	 * @param unknown_type $type
	 * @param unknown_type $tid
	 * @return unknown
	 */
	function pnh_getreceiptttl_valuebytypeterry($type=0,$tid=false)
	{
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and f.territory_id=?   ORDER BY instrument_date DESC",$tid)->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and f.territory_id=? and r.is_active=1 ORDER BY instrument_date asc",$tid)->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate() and is_submitted=0 and f.territory_id=?  ORDER BY instrument_date asc",$tid)->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and f.territory_id=?  ORDER BY date(from_unixtime(activated_on)) DESC",$tid)->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND f.is_suspended=0 AND r.status=0 and  r.is_active=1 and f.territory_id=? order by d.submitted_on desc",$tid)->row_array();
		if($type==5)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on,sum(receipt_amount) as total 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND  r.is_active=1 AND r.is_active=1 and f.territory_id=?  
						ORDER BY cancelled_on DESC";
			$total_value=$this->db->query($sql,$tid)->row_array();
		}	
		return $total_value;
	}
	
	function pnh_getreceiptbytypecash($type=0,$cash_type=false,$pg=0)
	{
		if($type==0)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 and is_submitted=0 and r.payment_mode=? ORDER BY instrument_date DESC";
		if($type==1 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.payment_mode=? and r.is_active=1 ORDER BY instrument_date asc";
		if($type==2 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.payment_mode=?  ORDER BY instrument_date asc";
		if($type==3)
			$sql = "SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and r.payment_mode=?  ORDER BY date(from_unixtime(activated_on)) DESC";
		if($type==4)
			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND  r.status=0 and  r.is_active=1 and r.payment_mode=? order by d.submitted_on desc";
		
		
			$total_rows = $this->db->query($sql,$cash_type)->num_rows();
			$sql = $sql.' limit '.$pg.',50';
			$receipts=$this->db->query($sql,$cash_type)->result_array();
			return array($total_rows,$receipts);
	}
	
	
	function pnh_getreceiptbyr_type($type=0,$r_type=false,$pg=0)
	{
		if($type==0)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.receipt_type=? ORDER BY instrument_date DESC";
		if($type==1 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.receipt_type=? and r.is_active=1 ORDER BY instrument_date asc";
		if($type==2 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.receipt_type=?  ORDER BY instrument_date asc";
		if($type==3)
			$sql = "SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and r.receipt_type=?  ORDER BY date(from_unixtime(activated_on)) DESC";
		if($type==4)
			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND  r.status=0 and  r.is_active=1 and r.receipt_type=? order by d.submitted_on desc";
		if($type==5)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND  r.is_active=1 AND r.is_active=1 and r.receipt_type=?  
						ORDER BY cancelled_on DESC";
		
		
		$total_rows = $this->db->query($sql,$r_type)->num_rows();
		$sql = $sql.' limit '.$pg.',50';
		$receipts=$this->db->query($sql,$r_type)->result_array();
		return array($total_rows,$receipts);
	}
	
	function pnh_getreceiptbytrans_type($type=0,$t_type=false,$pg=0)
	{
		if($type==0)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.in_transit=? ORDER BY instrument_date DESC";
		if($type==1 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.in_transit=? and r.is_active=1 ORDER BY instrument_date asc";
		if($type==2 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.in_transit=?  ORDER BY instrument_date asc";
		$total_rows = $this->db->query($sql,$t_type)->num_rows();
		$sql = $sql.' limit '.$pg.',50';
		$receipts=$this->db->query($sql,$t_type)->result_array();
		return array($total_rows,$receipts);
	}
	
	function pnh_getreceiptbytypefran($type=0,$fid=false,$pg=0)
	{
		
		if($type==0)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and f.franchise_id=?  ORDER BY instrument_date DESC";
		if($type==1 )
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and f.franchise_id=? ORDER BY instrument_date asc";
		if($type==2 )
			$sql = "SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and f.franchise_id=?  ORDER BY instrument_date asc";
		if($type==3)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1  and (is_submitted=1 or r.activated_on!=0) and r.is_active=1 and f.franchise_id=? ORDER BY activated_on desc";
		if($type==4)
			$sql = "SELECT r.*,d.bank_id,d.is_submitted,d.status,d.remarks,d.receipt_id,f.franchise_id,f.franchise_name,DATE(d.submitted_on) AS submitted_on,r.created_on FROM `pnh_t_receipt_info`r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT JOIN pnh_m_deposited_receipts d  ON d.receipt_id=r.receipt_id  WHERE r.is_submitted=1 AND r.status=0  AND r.franchise_id=? AND r.is_active=1 ";
		if($type==5)
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND  r.is_active=1 AND r.is_active=1 and r.franchise_id=?
						ORDER BY cancelled_on DESC";
		
		$total_rows = $this->db->query($sql,$fid)->num_rows();
		$sql = $sql.' limit '.$pg.',50';
		$receipts=$this->db->query($sql,$fid)->result_array();
		return array($total_rows,$receipts);
	}
	
	function pnh_getreceiptbytypebank($type=4,$bid=false,$pg=0)
	{
		if($type==4)
		{
			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,date_format(d.submitted_on,'%d/%m/%Y') AS submitted_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND 1  AND r.status=0 and b.id=? order by d.submitted_on desc";
			$total_rows = $this->db->query($sql,$bid)->num_rows();
			$sql = $sql.' limit '.$pg.',50';
			$receipts=$this->db->query($sql,$bid)->result_array();
			return array($total_rows,$receipts);
		}
		
		return $receipts;
	}
	
	// function to get total amount value by type
	 
	function pnh_getreceiptttl_valuebytype($type=0)
	{
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,f.franchise_name,a.name AS admin,sum(receipt_amount) as total FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 ")->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,f.franchise_name,a.name AS admin,sum(receipt_amount) as total FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()and is_submitted=0  ORDER BY instrument_date asc")->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,f.franchise_name,a.name AS admin,sum(receipt_amount) as total FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()and is_submitted=0  ORDER BY instrument_date asc")->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by,sum(receipt_amount) as total FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1  and (is_submitted=1 or r.activated_on!=0) and r.is_active=1  ORDER BY activated_on desc")->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,d.submitted_on,sum(receipt_amount) as total,r.created_on  FROM pnh_t_receipt_info r LEFT JOIN `pnh_m_deposited_receipts`d ON d.receipt_id=r.receipt_id LEFT JOIN `pnh_m_bank_info` b ON b.id=d.bank_id LEFT JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND r.status=0  and r.is_active=1  order by d.submitted_on desc")->row_array();
		if($type==5)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on,sum(receipt_amount) as total 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND  r.is_active=1 AND r.is_active=1   
						ORDER BY cancelled_on DESC";
			$total_value=$this->db->query($sql,$tid)->row_array();
		}	
		return $total_value;
	}
	
	//function to get total value by bank
	function pnh_getreceiptttl_valuebytypebank ($type=4,$bid=false)
	{
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,d.submitted_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND 1  AND r.status=0 and b.id=? order by d.submitted_on desc",$bid)->row_array();
		return $total_value;
	}
	
	//function to get total value by franchisee
	function pnh_getreceipttttl_valuebytypefran($type=0,$fid=false)
	{
	
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and f.franchise_id=?  ORDER BY instrument_date DESC",$fid)->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and f.franchise_id=? ORDER BY instrument_date asc",$fid)->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and f.franchise_id=?  ORDER BY instrument_date asc",$fid)->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by,sum(receipt_amount) as total FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1  and (is_submitted=1 or r.activated_on!=0) and r.is_active=1 and r.franchise_id=?  ORDER BY activated_on desc",$fid)->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,d.submitted_on,sum(receipt_amount) as total,r.created_on  FROM pnh_t_receipt_info r LEFT JOIN `pnh_m_deposited_receipts`d ON d.receipt_id=r.receipt_id LEFT JOIN `pnh_m_bank_info` b ON b.id=d.bank_id LEFT JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE f.franchise_id=? and r.is_submitted=1 AND r.status=0  and r.is_active=1  order by d.submitted_on desc",$fid)->row_array();
		if($type==5)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on,sum(receipt_amount) as total 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status =2 AND  r.is_active=1 AND r.is_active=1   
						and r.franchise_id = ? 
						ORDER BY activated_on DESC";
			$total_value=$this->db->query($sql,$fid)->row_array();
		}	
		return $total_value;
	}
	 // function to get total value by cashtype
	function pnh_getreceiptttl_valuebytypecash($type=0,$cash_type=false)
	{
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.payment_mode=? ORDER BY instrument_date DESC",$cash_type)->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.payment_mode=? and r.is_active=1 ORDER BY instrument_date asc",$cash_type)->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.payment_mode=?  ORDER BY instrument_date asc",$cash_type)->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and r.payment_mode=?  ORDER BY date(from_unixtime(activated_on)) DESC",$cash_type)->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,date(d.submitted_on) AS submitted_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1  AND r.status=0 and  r.is_active=1 and r.payment_mode=? order by d.submitted_on desc",$cash_type)->row_array();
		if($type==5)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on,sum(receipt_amount) as total 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND  r.is_active=1 AND r.is_active=1   
						and r.payment_mode=? 
						ORDER BY cancelled_on DESC";
			$total_value=$this->db->query($sql,$cash_type)->row_array();
		}	
		return $total_value;
	}
	
	 //function to get total value by receipt type
	function pnh_getreceiptttl_valuebyrtype($type=0,$r_type=false)
	{
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.receipt_type=? ORDER BY instrument_date DESC",$r_type)->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.receipt_type=? and r.is_active=1 ORDER BY instrument_date asc",$r_type)->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.receipt_type=?  ORDER BY instrument_date asc",$r_type)->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 and is_submitted=1 and r.receipt_type=?  ORDER BY date(from_unixtime(activated_on)) DESC",$r_type)->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,date(d.submitted_on) AS submitted_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1  AND r.status=0 and  r.is_active=1 and r.receipt_type=? order by d.submitted_on desc",$r_type)->row_array();
		if($type==5)
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on,sum(receipt_amount) as total 
						FROM pnh_t_receipt_info r 
						JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id 
						left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
						LEFT OUTER JOIN king_admin a ON a.id=r.created_by 
						LEFT OUTER JOIN king_admin d ON d.id=r.activated_by 
						WHERE r.status in (2,3) AND r.is_active=1 AND r.is_active=1   
						and r.receipt_type=? 
						ORDER BY cancelled_on DESC";
			$total_value=$this->db->query($sql,$r_type)->row_array();
		}		
		return $total_value;
	}
	
	function pnh_getreceiptttl_valuebytranstype($type=0,$t_type=false)
	{
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1  and is_submitted=0 and r.in_transit=? ORDER BY instrument_date DESC",$t_type)->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.in_transit=? and r.is_active=1 ORDER BY instrument_date asc",$t_type)->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate()  and is_submitted=0 and r.in_transit=?  ORDER BY instrument_date asc",$t_type)->row_array();
	
		return $total_value;
	}
	
	//function to get total value by receipt date
/*	function pnh_getreceiptttl_valuebydate($type=0,$st_date=false,$en_date=false)
	{
		if($st_date)
		{
			$st_date=strtotime($st_date.' 00:00:00');
			$en_date=strtotime($en_date.' 23:59:59');
		}
		if($type==0)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND f.is_suspended=0 and is_submitted=0 and r.instrument_date between ? and ? ORDER BY instrument_date DESC",array($st_date,$en_date))->row_array();
		if($type==1 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) < curdate() AND f.is_suspended=0 and is_submitted=0 and r.status=0  and r.instrument_date between ? and ?  ORDER BY instrument_date asc",array($st_date,$en_date))->row_array();
		if($type==2 )
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,f.franchise_name,a.name AS admin FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) >= curdate() AND f.is_suspended=0 and is_submitted=0 and r.instrument_date between ? and ?  ORDER BY instrument_date asc",array($st_date,$en_date))->row_array();
		if($type==3)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,DATE_FORMAT(r.activated_on,'%d/%m/%Y')as activated_on,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 AND f.is_suspended=0 and (is_submitted=1 or r.activated_on!=0) and r.is_active=1  and unix_timestamp(r.activated_on) between ? and ? and ORDER BY activated_on desc",array($st_date,$en_date))->row_array();
		if($type==4)
			$total_value=$this->db->query("SELECT r.*,sum(receipt_amount) as total,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,date(d.submitted_on) AS submitted_on  FROM `pnh_m_deposited_receipts`d JOIN `pnh_t_receipt_info` r ON r.receipt_id=d.receipt_id JOIN `pnh_m_bank_info` b ON b.id=d.bank_id JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE r.is_submitted=1 AND f.is_suspended=0 AND r.status=0 and  r.is_active=1 and r.receipt_type=? order by d.submitted_on desc",$r_type)->row_array();
		return $total_value;
	}*/
	

	
	function pnh_getdealsbybrand($brandid,$type=0)
	{
		if($type==0)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.brandid=? group by i.id order by i.name asc",$brandid)->result_array();
		else if($type==1)
			$ret=$this->db->query("select * from (select ifnull(group_concat(smd.special_margin),0) as sm,i.id,o.transid,o.time as order_time,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.brandid=? group by i.id order by o.time desc) as dd group by dd.id order by dd.order_time desc",$brandid)->result_array();
		else if($type==2)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,o.quantity as qty,ifnull(sum(o.quantity),0) as sold,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left outer join king_transactions t on t.transid=o.transid and t.is_pnh=1 left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.brandid=? group by i.id order by count(o.id) desc",$brandid)->result_array();
		else if($type==3)
			$ret=$this->db->query("select ifnull(group_concat(smd.special_margin),0) as sm,o.quantity as qty,ifnull(sum(o.quantity),0) as sold,d.publish,d.brandid,d.catid,i.orgprice,i.price,i.name,i.pic,i.pnh_id,i.id as itemid,b.name as brand,c.name as category from king_deals d join king_dealitems i on i.dealid=d.dealid join king_brands b on b.id=d.brandid join king_categories c on c.id=d.catid left outer join king_orders o on o.itemid=i.id left outer join king_transactions t on t.transid=o.transid and t.is_pnh=1 left join pnh_special_margin_deals smd on i.id = smd.itemid and smd.from <= unix_timestamp() and smd.to >=unix_timestamp() where i.is_pnh=1 and d.brandid=? and (o.time>".mktime(0,0,0,0,-90)." or  o.time is null) group by i.id order by count(o.id) desc,i.name asc",$brandid)->result_array();
		return $ret;	
	}
	
	function pnh_fran_account_stat($fid,$type,$amount,$desc,$action_for='',$ref_id=0)
	{
		$bal=$this->db->query("select current_balance from pnh_m_franchise_info where franchise_id=?",$fid)->row()->current_balance;
		if($type==0)
			$nbal=$bal+$amount;
		else
			$nbal=$bal-$amount;
		$inp=array("franchise_id"=>$fid,"type"=>$type,"amount"=>$amount,"balance_after"=>$nbal,"desc"=>$desc,"action_for"=>$action_for,"ref_id"=>$ref_id,"created_on"=>time());
		$this->db->insert("pnh_franchise_account_stat",$inp);
		$acc_stat_id = $this->db->insert_id();
		$this->db->query("update pnh_m_franchise_info set current_balance=? where franchise_id=? limit 1",array($nbal,$fid));
		return $acc_stat_id;
	}
	
	
	function pnh_sendsms($to,$msg,$fid=0,$empid=0,$type=0)
	{
		$sms_log_id = 0;
		if($fid!=0)
		{
			$inp=array("to"=>$to,"msg"=>$msg,"franchise_id"=>$fid,"pnh_empid"=>$empid,"type"=>$type,"sent_on"=>time());
			$this->db->insert("pnh_sms_log_sent",$inp);
			$sms_log_id = $this->db->insert_id();
		}
		if($_SERVER['HTTP_HOST']=="localhost")
			return;
		$exotel_sid="snapittoday";
		$exotel_token="491140e9fbe5c507177228cf26cf2f09356e042c";
		$post = array(
		    'From'   => '9243404342',
		    'To'    => $to,
		    'Body'  => $msg
		);
		$url = "https://".$exotel_sid.":".$exotel_token."@twilix.exotel.in/v1/Accounts/".$exotel_sid."/Sms/send";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$http_result = curl_exec($ch);
		$error = curl_error($ch);
		$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($sms_log_id)
			return $sms_log_id; 
	}
	
	function do_pnh_updatefranchise($fid)
	{
		$user=$this->erpm->getadminuser();
		
		$menu_selected=array();
		$menu_selected = $this->input->post('fran_menu');
	
		
		$this->db->query("update pnh_franchise_menu_link set status = 2,is_sch_enabled=0,modified_on=now() where status = 1 and fid = ? ",$fid);
		//get the latest modified record
		
		
	//	$this->db->query("update pnh_m_franchise_info")
		if($menu_selected)
		foreach($menu_selected as $m)
		{
			// check for no change
			$fm_stat_res = $this->db->query("select id,status from pnh_franchise_menu_link where fid = ? and menuid = ? order by id desc limit 1 ",array($fid,$m));
			if($fm_stat_res->num_rows())
			{
				$fm_stat = $fm_stat_res->row_array();
				// check for status
				if($fm_stat['status'] == 2)
				{
					$this->db->query("update pnh_franchise_menu_link set status = 1,modified_on=now(),is_sch_enabled=1 where status = 2 and id = ? ",$fm_stat['id']);
					continue ;
				}
			}   
			
			$ins_data=array();
			$ins_data['fid']=$fid;
			$ins_data['menuid']=$m;
			$ins_data['created_by']=$user['userid'];
			$ins_data['created_on']=date("Y-m-d H:i:s");
			$ins_data['status']=1;
			$this->db->insert("pnh_franchise_menu_link",$ins_data);
		}
		
		$this->db->query("update pnh_franchise_menu_link set status = 0,modified_on=?,modified_by=? where status = 2 and fid = ? ",array(time(),$user['userid'],$fid));
		
		if($this->db->affected_rows()>0)
		{
			//get the latest modified records
			$menu_det=$this->db->query("select * from pnh_franchise_menu_link  where fid=? and status=0 order by modified_on desc",$fid)->result_array();
			
			foreach($menu_det as $menu)
			{
			//disable scheme discount
			$this->db->query("update pnh_sch_discount_brands set valid_to=?,is_sch_enabled=0,modified_on=? where menuid=?",array(time()-20,time(),$menu['menuid']));
			//disable super scheme
			$this->db->query("update pnh_super_scheme set valid_to=?,is_active=0 where menu_id=?",array(time()-20,$menu['menuid']));
			}
		}
		
		//check if it is have prepaid menu
		$is_prepaid_franchise=$this->check_franchise_have_prepaid_menu($fid);
		if(!$is_prepaid_franchise)
		{
			$this->db->query("update pnh_m_franchise_info set is_prepaid=0 where franchise_id=?",$fid);
		}
		
		
		$cols=array("franchise_name", "address", "locality", "city","state", "postcode","login_mobile1","login_mobile2","email_id", "store_name","business_type","no_of_employees", "store_area","lat","long","store_open_time", "store_close_time","website_name","internet_available","class_id","territory_id","town_id", "security_question", "security_answer","security_question2","security_answer2","security_custom_question","security_custom_question2","store_pan_no","store_tin_no","store_service_tax_no","store_reg_no","is_lc_store","modified_by","modified_on");
		foreach(array("name","address","locality","city","state","postcode","login_mobile1","login_mobile2","login_email","shop_name","business_type","shop_emps","shop_area","lat","long","shop_from","shop_to","website","internet","class","territory","town","sec_q","sec_a","sec_q2","sec_a2","sec_cq","sec_cq2","shop_pan","shop_tin","shop_stax","shop_reg","is_lc_store") as $i=>$a)
			$data[$cols[$i]]=$this->input->post($a);
		if($this->input->post("sec_q")!="-1")
			$data['security_custom_question']="";
		if($this->input->post("sec_q2")!="-1")
			$data['security_custom_question2']="";
		foreach(array("cnt_name","cnt_desgn","cnt_mob1","cnt_mob2","cnt_telephone","cnt_fax","cnt_email1","cnt_email2") as $i)
			$$i=$this->input->post($i);
		$this->db->query("delete from pnh_m_franchise_contacts_info where franchise_id=?",$fid);
		foreach($cnt_name as $i=>$cname)
		{
			$inp=array("franchise_id"=>$fid,"contact_name"=>$cname,"contact_designation"=>$cnt_desgn[$i],"contact_mobile1"=>$cnt_mob1[$i],"contact_mobile2"=>$cnt_mob2[$i],"contact_telephone"=>$cnt_telephone[$i],"contact_fax"=>$cnt_fax[$i],"contact_email1"=>$cnt_email1[$i],"contact_email2"=>$cnt_email2[$i]);
			$this->db->insert("pnh_m_franchise_contacts_info",$inp);
		}
		$data['modified_by']=$user['userid'];
		$data['modified_on']=time();
		$this->db->update("pnh_m_franchise_info",$data,array("franchise_id"=>$fid));
		redirect("admin/pnh_franchise/$fid");
	}
	
	function do_pnh_addfranchise()
	{
//		$this->debug_post();
		$user=$this->erpm->getadminuser();
		foreach(array("name","address","own","locality","city","state","postcode","class","territory","town","login_mobile1","login_mobile2","login_email","internet","shop_name","shop_emps","shop_area","store_menu","lat","long","shop_from","shop_to","website","assign_to","dev_sno","dev_type","cnt_name","cnt_desgn","cnt_mob1","cnt_mob2","cnt_telephone","cnt_fax","cnt_email1","cnt_email2","sec_amount","sec_type","sec_bank","sec_no","sec_date","business_type","sec_q","sec_a","sec_q2","sec_a2","sec_msg","shop_reg","shop_tin","shop_pan","shop_stax","is_lc_store") as $i)
			$$i=$this->input->post($i);
		if($this->db->query("select 1 from pnh_m_franchise_info where (login_mobile1=? and login_mobile1!='') || (login_mobile1=? and login_mobile1!='') || (login_mobile2=? and login_mobile2!='') || (login_mobile2=? and login_mobile2!='')",array($login_mobile1,$login_mobile2,$login_mobile1,$login_mobile2))->num_rows()!=0)
			show_error("Already a franchise exists with given login mobile");
		$fid="3".$this->erpm->p_genid(7);
		$inp=array("town_id"=>$town,"business_type"=>$business_type,"pnh_franchise_id"=>$fid,"franchise_name"=>$name,"address"=>$address,"locality"=>$locality,"city"=>$city,"postcode"=>$postcode,"state"=>$state,"territory_id"=>$territory,"class_id"=>$class,"security_deposit"=>0,"login_mobile1"=>$login_mobile1,"login_mobile2"=>$login_mobile2,"email_id"=>$login_email,"assigned_to"=>$assign_to,"no_of_employees"=>$shop_emps,"store_name"=>$shop_name,"store_area"=>$shop_area,"lat"=>$lat,"long"=>$long,"store_open_time"=>mktime($shop_from),"store_close_time"=>mktime($shop_to),"own_rented"=>$own,"internet_available"=>$internet,"website_name"=>$website,"created_by"=>$user['userid'],"security_question"=>$sec_q,"security_answer"=>$sec_a,"security_question2"=>$sec_q2,"security_answer2"=>$sec_a2,"created_on"=>time(),"store_tin_no"=>$shop_tin,"store_pan_no"=>$shop_pan,"store_service_tax_no"=>$shop_stax,"store_reg_no"=>$shop_reg,"is_lc_store"=>$is_lc_store);
//		$sql="insert into pnh_m_franchise_info(pnh_franchise_id,franchise_name,address,locality,city,postcode,state,territory_id,class_id,security_deposit,login_mobile1,login_mobile2,email_id,assigned_to,no_of_employees,store_name,store_area,store_open_time,store_close_time,own_rented,internet_available,website_name,created_by,created_on)
//																																			values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
//		$inp=array($fid,$name,$address,$locality,$city,$postcode,$state,$territory,$class,$sec_amount,$login_mobile1,$login_mobile2,$login_email,$assign_to,$shop_emps,$shop_name,$shop_area,mktime($shop_from),mktime($shop_to),$own,$internet,$website,$user['userid'],time());
//		$this->db->query($sql,$inp);
		$this->db->insert("pnh_m_franchise_info",$inp);
		$fid = $id=$this->db->insert_id();
		
		
		$menu_selected = $this->input->post('fran_menu');
		
		if($menu_selected)
		foreach($menu_selected as $m)
		{
			$ins_data=array();
			$ins_data['fid']=$fid;
			$ins_data['menuid']=$m;
			$ins_data['created_by']=$user['userid'];
			$ins_data['created_on']=date("Y-m-d H:i:s");
			$ins_data['status']=1;
			$this->db->insert("pnh_franchise_menu_link",$ins_data);
		}
		
		//suspend the franchise;
		$this->db->query("update pnh_m_franchise_info set is_suspended=2 where franchise_id=?",$id);
		if($this->db->affected_rows())
		{
			$this->db->query("insert into franchise_suspension_log(franchise_id,suspension_type,reason,suspended_on,suspended_by)values(?,?,?,?,?)",array($id,2,'Deposit pending',time(),$user['userid']));
		}
		
		//deposit receipt creation
		//$sql="insert into pnh_t_receipt_info(franchise_id,bank_name,receipt_amount,receipt_type,payment_mode,instrument_no,instrument_date,remarks,created_by,created_on,status) values(?,?,?,?,?,?,?,?,?,?,?)";
		//$this->db->query($sql,array($id,$sec_bank,$sec_amount,0,$sec_type,$sec_no,strtotime($sec_date),$sec_msg,$user['userid'],time(),0));
		
		$this->erpm->pnh_sendsms($login_mobile1,"Hi $name, Welcome to PayNearHome. Your Franchise account is created successfully. Happy Franchising!",$id);
		foreach($cnt_name as $i=>$cname)
		{
			$inp=array("franchise_id"=>$id,"contact_name"=>$cname,"contact_designation"=>$cnt_desgn[$i],"contact_mobile1"=>$cnt_mob1[$i],"contact_mobile2"=>$cnt_mob2[$i],"contact_telephone"=>$cnt_telephone[$i],"contact_fax"=>$cnt_fax[$i],"contact_email1"=>$cnt_email1[$i],"contact_email2"=>$cnt_email2[$i]);
			$this->db->insert("pnh_m_franchise_contacts_info",$inp);
		}
		$this->db->query("insert into pnh_franchise_owners(admin,franchise_id,created_by,created_on) values(?,?,?,?)",array($user['userid'],$id,$user['userid'],time()));
//		$inp=array();
//		foreach($dev_sno as $i=>$sno)
//			$inp[]=array("device_sl_no"=>$sno,"device_type_id"=>$dev_type[$i],"issued_to"=>$fid);
//		$this->db->insert_batch("pnh_m_device_info",$inp);
		redirect("admin/pnh_franchises");
	}
	
	function do_pnh_upload_images($fid)
	{
		$user=$this->erpm->getadminuser();
		foreach($_FILES['pic']['tmp_name'] as $i=>$t)
		{
			if(strpos($_FILES['pic']['type'][$i],"image")===false)
				continue;
			$p="p".rand(433235,32535235).".".pathinfo($_FILES['pic']['name'][$i],PATHINFO_EXTENSION);
			$data=array("franchise_id"=>$fid,"pic"=>$p,"caption"=>$_FILES['pic']['name'][$i],"created_by"=>$user['userid'],"created_on"=>time());
			$this->db->insert("pnh_franchise_photos",$data);
			move_uploaded_file($t,ERP_PHYSICAL_IMAGES."franchises/".$p);
		}
		$this->erpm->flash_msg("Images Uploaded");
		redirect("admin/pnh_franchise/$fid");
	}
	
	function get_pnh_margin($fid,$pid)
	{
		$itemid=$this->db->query("select id from king_dealitems where pnh_id=?",$pid)->row()->id;
		
		//$menuid=$this->db->query("select d.menuid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.is_pnh=1 and i.pnh_id=?",$pid)->row()->menuid;
		
		$menuid=$this->db->query("select d.menuid,m.default_margin as margin from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu m ON m.id=d.menuid where i.is_pnh=1 and i.pnh_id=?",$pid)->row_array();
		
		//print_r($menuid);
		$brandid=$this->db->query("select d.brandid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.is_pnh=1 and i.pnh_id=?",$pid)->row()->brandid;
		
		$catid=$this->db->query("select d.catid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.is_pnh=1 and i.pnh_id=?",$pid)->row()->catid;
		
		$fran=$this->db->query("select * from pnh_m_franchise_info where franchise_id=?",$fid)->row_array();
		
		//$fran1=$this->db->query("select * from pnh_franchise_menu_link where fid=? and menuid=?",array($fid,$menuid['menuid']))->row_array();
		
		$fran1=$this->db->query("select * from pnh_sch_discount_brands where franchise_id=? and menuid=? and brandid=? and is_sch_enabled = 1 ",array($fid,$menuid['menuid'],$brandid))->row_array();
		 
		$margin=$this->db->query("select margin,combo_margin,less_margin_brands from pnh_m_class_info where id=?",$fran['class_id'])->row_array();

		$margin['base_margin']=$menuid['margin'];
		$margin['margin']=$menuid['margin'];
		$has_special_margin = 0;
		$has_super_scheme=0;
		 
		//check for super scheme
		$super_scheme=$this->db->query("select * from pnh_super_scheme where menu_id=? and is_active=1",$menuid['menuid'])->result_array();
		
		if($super_scheme)
		{
			if($super_scheme['valid_from']<time() && $super_scheme['valid_to']>time() && $super_scheme['is_sch_enabled']==1)
				$has_super_scheme=1;
		} 
		
		$prod_margin=$this->db->query("select * from pnh_special_margin_deals where is_active=1 and itemid=? and ? between `from` and `to` order by id desc limit 1",array($itemid,time()))->row_array();
		$chk_brand_marg = 1;
		if(!empty($prod_margin))
		{
			if($prod_margin['special_margin'] >= 0)
			{
				$margin['margin']=$prod_margin['special_margin'];
				$chk_brand_marg = 0;
				$has_special_margin = 1;
			}
		}
		if($chk_brand_marg)	
			if($this->db->query("select 1 from pnh_less_margin_brands where brandid=?",$brandid)->num_rows()!=0)
				$margin['margin']=$margin['less_margin_brands'];
		
		$margin['base_margin']=$margin['margin'];
		$margin['sch_margin']=0;
		$margin['bal_discount']=0;
		$bmargin=$this->db->query("select discount from pnh_sch_discount_brands where franchise_id=? and ? between valid_from and valid_to and brandid=? and catid=? and menuid=? and is_sch_enabled = 1  order by id desc limit 1",array($fid,time(),$brandid,$catid,$menuid['menuid']))->row()->discount;
		
		if(empty($bmargin))
			$bmargin=$this->db->query("select discount from pnh_sch_discount_brands where franchise_id=?  and ? between valid_from and valid_to and brandid=? and catid=0 and menuid=? and is_sch_enabled = 1 order by id desc limit 1",array($fid,time(),$brandid,$menuid['menuid']))->row()->discount;
		if(empty($bmargin))
			$bmargin=$this->db->query("select discount from pnh_sch_discount_brands where franchise_id=?  and ? between valid_from and valid_to and catid=? and brandid=0 and menuid=? and is_sch_enabled = 1 order by id desc limit 1",array($fid,time(),$catid,$menuid['menuid']))->row()->discount;
		if(empty($bmargin))
			$bmargin=$this->db->query("select discount from pnh_sch_discount_brands where franchise_id=?  and ? between valid_from and valid_to and catid=0 and brandid=0 and menuid=? and is_sch_enabled = 1 order by id desc limit 1",array($fid,time(),$menuid['menuid']))->row()->discount;
		
		//echo $this->db->last_query();
		if(!$has_special_margin)
		{
			if(empty($bmargin))
			{
				if($fran1['sch_discount_start']<time() && $fran1['sch_discount_end']>time() && $fran1['is_sch_enabled'])
				{
						$margin['margin']+=$fran1['sch_discount'];
						$margin['sch_margin']=$fran1['sch_discount'];
				}
			}else{
				$margin['margin']+=$bmargin;
				$margin['sch_margin']=$bmargin;
			}
		}
		
		$c_balance_det = $this->db->query("select min_balance_value,bal_discount from king_dealitems a join king_deals b on a.dealid = b.dealid join pnh_menu c on c.id = b.menuid where a.pnh_id = ? ",$pid)->row_array();
		if($c_balance_det['min_balance_value'])
		{
			$balance=$this->db->query("select current_balance from pnh_m_franchise_info where franchise_id=?",$fran['franchise_id'])->row()->current_balance;
			if($balance >= $c_balance_det['min_balance_value'])
			{
				$margin['margin']+=$c_balance_det['bal_discount']*1;
				$margin['bal_discount']+=$c_balance_det['bal_discount']*1;
			}
		}
		
		unset($margin['less_margin_brands']);
		return $margin;
		
	}
	/**
	 * Un orderd franchise details
	 * @return boolean
	 */

 	function get_pnh_unorderdfran()
	{
		$unorderd_fran_res=$this->db->query("SELECT DISTINCT b.pnh_franchise_id,a.franchise_id,b.franchise_name,login_mobile1,login_mobile2,actiontime,FROM_UNIXTIME(actiontime,'%d/%m/%Y %h:%i %p') AS last_orderd
				FROM king_transactions a
				RIGHT JOIN `pnh_m_franchise_info`b ON b.franchise_id=a.franchise_id
				WHERE a.is_pnh=1  AND FROM_UNIXTIME(init)<=(CURDATE()-INTERVAL 7 DAY)
				GROUP BY pnh_franchise_id
				");
		if($unorderd_fran_res->num_rows())
		{
			return $unorderd_fran=$unorderd_fran_res->result_array();
				
		}
		else
			return false;
	} 
	
	/**
	 * function to generate new member id for dynamic allocation 
	 */
	function _gen_uniquememberid()
	{
		$lastmem_id = $this->_get_config_param('LAST_MEMBERID_ALLOTED');
		$member_id = $lastmem_id+1;
		$this->_set_config_param('LAST_MEMBERID_ALLOTED',$member_id);
		// check if member id is already alloted 
		if($this->db->query("select count(*) as t from pnh_member_info where pnh_member_id = ? ",$member_id)->row()->t)
			return $this->_gen_uniquememberid();
		else
			return $member_id;
	}
	
	/**
	 * function to get config parameters from Database
	 */
	function _get_config_param($name)
	{
		return $this->db->query("select value from m_config_params where name = ?  ",$name)->row()->value;
	}
	
	/**
	 * function to set config paramaters based on name and value pair  
	 */
	function _set_config_param($name,$value)
	{
		$this->db->query("update m_config_params set value = ? where name = ? limit 1",array($value,$name));
	}
	
	function do_pnh_offline_order()
	{
		$fran_status_arr=array();
		$fran_status_arr[0]="Live";
		$fran_status_arr[1]="Permanent Suspension";
		$fran_status_arr[2]="Payment Suspension";
		$fran_status_arr[3]="Temporary Suspension";
		$admin = $this->auth(false);
		foreach(array("fid","pid","qty","mid","redeem","redeem_points") as $i)
			$$i=$this->input->post($i);
		
		if($redeem)
			$redeem_points = 150;
		
		$fran=$this->db->query("select * from pnh_m_franchise_info where franchise_id=?",$fid)->row_array();
		
		$menuid=$this->db->query("select d.menuid,m.default_margin as margin from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu m ON m.id=d.menuid where i.is_pnh=1 and i.pnh_id=?",$pid)->row_array();
		$fran=$this->db->query("select * from pnh_m_franchise_info where franchise_id=?",$fid)->row_array();
		$fran1=$this->db->query("select * from pnh_franchise_menu_link where fid=? and menuid=?",array($fid,$menuid['menuid']))->row_array();
		$margin=$this->db->query("select margin,combo_margin from pnh_m_class_info where id=?",$fran['class_id'])->row_array();
		$fran_status=$fran['is_suspended'];//franchise suspension status
		
		$has_super_scheme=0;
		$has_scheme_discount=0;
		$has_member_scheme=0;
		$has_offer=0;
		
		
		if($fran1['sch_discount_start']<time() && $fran1['sch_discount_end']>time() && $fran1['is_sch_enabled'])
		{
			$fran1['sch_type']=1;
			$has_scheme_discount=1;
			$menuid['margin']+=$fran1['sch_discount'];
		}
		
		$super_scheme=$this->db->query("select * from pnh_super_scheme where menu_id=? and is_active=1 and franchise_id = ? limit 1",array($menuid['menuid'],$fid))->row_array();
		//super scheme enabled for scheme discount
		if(!empty($super_scheme))
		{
			if($super_scheme['valid_from']<time() && $super_scheme['valid_to']>time() && $super_scheme['is_active'] == 1)
				$has_super_scheme=1;
		}
		
		$member_scheme=$this->db->query("select * from imei_m_scheme where is_active=1 and franchise_id=? and ? between sch_apply_from and scheme_to order by created_on desc limit 1",array($fid,time()))->row_array();
		//member scheme enabled for scheme discount
		if(!empty($member_scheme))
		{
			$has_member_scheme=1;
		}
		
		
		$offer_scheme=$this->db->query("select * from pnh_m_offers where menu_id=? and franchise_id=? and ? between offer_start and offer_end order by id desc limit 1",array($menuid['menuid'],$fid,time()))->row_array();
		/* echo $this->db->last_query();
		echo $has_offer;
		die; */
		if(!empty($offer_scheme))
		{
			$has_offer=1;
		}
		
		$items=array();
		foreach($pid as $i=>$p)
			$items[]=array("pid"=>$p,"qty"=>$qty[$i]);
		$total=0;$d_total=0;
		$itemnames=$itemids=array();
		
		//compute redeem val per item : total_items
		$ordered_menus_list=array();
		$item_pnt =  $redeem_points/count($items);
		$redeem_value = 0;
		foreach($items as $i=>$item)
		{
			$split_order=$this->db->query("SELECT i.*,d.publish,c.loyality_pntvalue FROM king_dealitems i JOIN king_deals d ON d.dealid=i.dealid JOIN pnh_menu c ON c.id = d.menuid WHERE i.is_pnh=1 AND  i.pnh_id=? AND i.pnh_id!=0 AND c.id IN(112,118,122)",$item['pid'])->row_array();
			
			$prod=$this->db->query("select i.*,d.publish,c.loyality_pntvalue,d.menuid from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu c ON c.id = d.menuid where i.is_pnh=1 and  i.pnh_id=? and i.pnh_id!=0",$item['pid'])->row_array();
			$ordered_menus_list[]=$prod['menuid'];
			if(empty($prod))
				die("There is no product with ID : ".$item['pid']);
			if($prod['publish']!=1)
				die("Product {$prod['name']} is not available");
			$items[$i]['tax']=$prod['tax'];
			$items[$i]['mrp']=$prod['orgprice'];
			if($fran['is_lc_store'])
				$items[$i]['price']=$prod['store_price'];
			else
				$items[$i]['price']=$prod['price'];
			$items[$i]['itemid']=$prod['id'];
			//$margin=$this->erpm->get_pnh_margin($fran['franchise_id'],$item['pid']);
			$margin=$this->erpm->get_pnh_margin($fran1['fid'],$item['pid']);
			if($prod['is_combo']=="1")
				$items[$i]['discount']=$items[$i]['price']/100*$margin['combo_margin'];
			else
				$items[$i]['discount']=$items[$i]['price']/100*$margin['margin'];
			
			$items[$i]['margin']=$margin;
			$total+=$items[$i]['price']*$items[$i]['qty'];
			$d_total+=($items[$i]['price']-$items[$i]['discount'])*$items[$i]['qty'];
			$itemids[]=$prod['id'];
			$itemnames[]=$prod['name'];
			$loyalty_pntvalue=$prod['loyality_pntvalue'];
			
			if($redeem)
				$redeem_value += $item_pnt_value = $item_pnt*$prod['loyality_pntvalue']; 
		}
		$avail=$this->erpm->do_stock_check($itemids);
		foreach($itemids as $i=>$itemid)
		 if(!in_array($itemid,$avail))
		 	die("{$itemnames[$i]} is out of stock");
		 
		$fran_crdet = $this->erpm->get_fran_availcreditlimit($fran['franchise_id']);
		$fran['current_balance'] = $fran_crdet[3];
		 
		//check if it is prepaid franchise block
		$is_prepaid_franchise=$this->is_prepaid_franchise($fid);
		if($is_prepaid_franchise)
		{
			if(count(array_unique($ordered_menus_list))==1)
			{
				if($ordered_menus_list[0]!=VOUCHERMENU)
					$is_prepaid_franchise=false;
			}else{
				$is_prepaid_franchise=false;
			}
		}
		
		//check if it is prepaid franchise block end
		
		if($fran['current_balance']<$d_total && !$is_prepaid_franchise)
			show_error("Insufficient balance! Balance in your account Rs {$fran['current_balance']} Total order amount : Rs $d_total");
		$rand_mid = 0;
		if(!$mid)
		{
			$mid=$this->erpm->_gen_uniquememberid();
			$rand_mid = 1;
		}
		
		if($mid)
		{
		
			if($this->db->query("select 1 from pnh_member_info where pnh_member_id=?",$mid)->num_rows()==0)
			{ 
				if($rand_mid==0)
				if($this->db->query("select 1 from pnh_m_allotted_mid where ? between mid_start and mid_end and franchise_id=?",array($mid,$fran['franchise_id']))->num_rows()==0)
					show_error("Member ID $mid is not allotted to you");
				$this->db->query("insert into king_users(name,is_pnh,createdon) values(?,1,?)",array("PNH Member: $mid",time()));
				$userid=$this->db->insert_id();
				$this->db->insert("pnh_member_info",array("user_id"=>$userid,"pnh_member_id"=>$mid,"franchise_id"=>$fran['franchise_id'],"first_name"=>$this->input->post("m_name"),"mobile"=>$this->input->post("m_mobile")));
				$npoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+PNH_MEMBER_FEE;
				$this->db->query("update pnh_member_info set points=? where user_id=? limit 1",array($npoints,$userid));
				$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,"",PNH_MEMBER_FEE,$npoints,time()));
				$this->erpm->pnh_fran_account_stat($fran['franchise_id'],1,PNH_MEMBER_FEE-PNH_MEMBER_BONUS,"50 Credit Points purchase for Member $mid","member",$mid);
			}
			else
				$userid=$this->db->query("select user_id from pnh_member_info where pnh_member_id=?",$mid)->row()->user_id;
		}else
		{
			$userid = 0;
		}
		$transid=strtoupper("PNH".random_string("alpha",3).$this->p_genid(5));
        if($redeem)
        {
                $total-=$redeem_value;
                $d_total-=$redeem_value;
                $apoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points-$redeem_points;
                $this->db->query("update pnh_member_info set points=points-? where user_id=? limit 1",array($redeem_points,$userid));
                $this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,$transid,-$redeem_points,$apoints,time()));
                $this->erpm->do_trans_changelog($transid,"$redeem_points Loyalty points redeemed");
        }
		
		$bal_discount_amt = 0;
		
		//check if franchise is in suspension 
		if($fran_status==0)
			$batch_enabled = 1;
		else	
			$batch_enabled = 0;
              
        $trans_grp_refno = $this->db->query("select max(trans_grp_refno) as grp_refno from king_transactions order by grp_refno desc limit 1")->row()->grp_refno + 1;
               
                
		if(!$split_order)
		{
			$transid=strtoupper("PNH".random_string("alpha",3).$this->p_genid(5));
			$this->db->query("insert into king_transactions(transid,amount,paid,mode,init,actiontime,is_pnh,franchise_id,trans_created_by,batch_enabled,trans_grp_refno) values(?,?,?,?,?,?,?,?,?,?,?)",array($transid,$d_total,$d_total,3,time(),time(),1,$fran['franchise_id'],$admin['userid'],$batch_enabled,$trans_grp_refno));
		}else 
                	$transid=strtoupper("PNH".random_string("alpha",3).$this->p_genid(5));
                
                
		foreach($items as $item)
		{
                        
                        
			// check if belongs to split invoice condiciton config
			$split_order=$this->db->query("SELECT i.*,d.publish,c.loyality_pntvalue FROM king_dealitems i JOIN king_deals d ON d.dealid=i.dealid JOIN pnh_menu c ON c.id = d.menuid WHERE i.is_pnh=1 AND  i.pnh_id=? AND i.pnh_id!=0 AND c.id IN(112,118,122)",$item['pid'])->row_array();
			//$split_order = 0;
			if($split_order)
			{
				$ttl_qty = $item['qty'];
				$p_qty = 1;
			}else
			{
				$ttl_qty = 1;
				$p_qty = $item['qty'];
			}
				
			
			for($qi=0;$qi<$ttl_qty;)
			{
				
				if($split_order)
				{
					$transid=strtoupper("PNH".random_string("alpha",3).$this->p_genid(5));
				}
				$qi = $qi+$p_qty;
				
				$inp=array("id"=>$this->p_genid(10),"transid"=>$transid,"userid"=>$userid,"itemid"=>$item['itemid'],"brandid"=>"");
				
				$item['qty'] = $p_qty;
				
				$inp["brandid"]=$this->db->query("select d.brandid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=?",$item['itemid'])->row()->brandid;
				$brandid=$inp["brandid"];
				$catid=$this->db->query("select d.catid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=?",$item['itemid'])->row()->catid;
				$menuid=$this->db->query("select d.menuid from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=? and menuid2=0",$item['itemid'])->row()->menuid;
				$inp["bill_person"]=$inp['ship_person']=$fran['franchise_name'];
				$inp["bill_address"]=$inp['ship_address']=$fran['address'];
				$inp["bill_city"]=$inp['ship_city']=$fran['city'];
				$inp['bill_pincode']=$inp['ship_pincode']=$fran['postcode'];
				$inp['bill_phone']=$inp['ship_phone']=$fran['login_mobile1'];
				$inp['bill_email']=$inp['ship_email']=$fran['email_id'];
				$inp['bill_state']=$inp['ship_state']=$fran['state'];
				$inp['quantity']=$p_qty;//$item['qty'];
				$inp['time']=time();
				$inp['ship_landmark']=$inp['bill_landmark']=$fran['locality'];
				$inp['bill_country']=$inp['ship_country']="India";
				$inp['i_orgprice']=$item['mrp'];
				$inp['i_price']=$item['price'];
				$inp['i_discount']=$item['mrp']-$item['price'];
				$inp['redeem_value']=($item['price']/($total+$redeem_value))*$redeem_value;
                                
                                $membr_id=$this->erpm->_gen_uniquememberid();
                                
				if($split_order)
				{
					
					
					if($this->db->query("select * from pnh_member_info where pnh_member_id=?",$membr_id)->num_rows()==0);
					$inp['member_id']=$membr_id;
					$inp['is_ordqty_splitd']=1;
					
					$this->db->query("insert into king_users(name,is_pnh,createdon) values(?,1,?)",array("PNH Member: $membr_id",time()));
						$userid=$this->db->insert_id();
						$inp['userid']=$userid;
						$this->db->query("insert into pnh_member_info(pnh_member_id,user_id,franchise_id,created_by,created_on)values(?,?,?,?,?)",array($membr_id,$userid,$fid,$admin['userid'],time()));
							
				}	
				//if super scheme is enabled
				
				if($has_super_scheme!=0)
				{
					
					//check item enabled for super scheme
					$check_superschdisableditem=$this->db->query("select * from pnh_superscheme_deals where is_active=0 and ? between valid_from and valid_to and itemid=? order by created_on desc limit 1",array(time(),$item['itemid']))->row_array();
					
					//$super_scheme_brand=$this->db->query("select * from pnh_super_scheme where brand_id=? and franchise_id = ? ",array($brandid,$fid))->result_array();
					$super_scheme_brand=$this->db->query("select * from pnh_super_scheme where menu_id=? and cat_id=? and brand_id=? and franchise_id = ? and is_active=1",array($menuid,$catid,$brandid,$fid))->result_array();
					if(empty($super_scheme_brand))
						$super_scheme_brand=$this->db->query("select * from pnh_super_scheme where menu_id=? and cat_id=0 and brand_id=? and franchise_id = ? and is_active=1 order by id desc limit 1",array($menuid,$brandid,$fid))->result_array();
					if(empty($super_scheme_brand))
						$super_scheme_brand=$this->db->query("select * from pnh_super_scheme where menu_id=? and cat_id=? and brand_id=0 and franchise_id = ? and is_active=1 order by id desc limit 1",array($menuid,$catid,$fid))->result_array();
					if(empty($super_scheme_brand))
						$super_scheme_brand=$this->db->query("select * from pnh_super_scheme where menu_id=? and cat_id=0 and brand_id=0 and franchise_id = ? and is_active=1 order by id desc limit 1",array($menuid,$fid))->result_array();
					//print_r($super_scheme_brand);
					if(!empty($super_scheme_brand) && empty($check_superschdisableditem))
					{ 
						
						foreach($super_scheme_brand as $super_scheme)
						{ 
							if($super_scheme['valid_from']<time() && $super_scheme['valid_to']>time() && $super_scheme['is_active'] == 1)
							{
								
								$inp['super_scheme_logid']=$super_scheme['id'];
								$inp['has_super_scheme']=1;
								$inp['super_scheme_target']=$super_scheme['target_value'];
								$inp['super_scheme_cashback']=$super_scheme['credit_prc'];
							}
						}
					}
				}
					
				if($has_member_scheme==1)
				{
					//check item enabled for member scheme
					$check_mbrschdisableditem=$this->db->query("select * from pnh_membersch_deals where is_active=0 and ? between valid_from and valid_to and itemid=? order by created_on desc limit 1",array(time(),$item['itemid']))->row_array();
					
					$member_scheme_brand=$this->db->query("select * from imei_m_scheme where menuid=? and categoryid=? and brandid=? and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$catid,$brandid,$fid))->result_array();
					if(empty($member_scheme_brand))
						$member_scheme_brand=$this->db->query("select * from imei_m_scheme where menuid=? and categoryid=? and brandid=0 and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$catid,$fid))->result_array();
					if(empty($member_scheme_brand))
						$member_scheme_brand=$this->db->query("select * from imei_m_scheme where menuid=? and categoryid=0 and brandid=? and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$brandid,$fid))->result_array();
					if(empty($member_scheme_brand))
						$member_scheme_brand=$this->db->query("select * from imei_m_scheme where menuid=? and categoryid=0 and brandid=0 and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$fid))->result_array();
					
					if(!empty($member_scheme_brand)  && empty($check_mbrschdisableditem))
						foreach($member_scheme_brand as $member_scheme)
						{
							$inp['imei_scheme_id']=$member_scheme['id'];
							if($member_scheme['scheme_type']==0)
								$inp['imei_reimbursement_value_perunit']=$member_scheme['credit_value'];
							else 
								$inp['imei_reimbursement_value_perunit']=$item['price']-($item['price']-$item['price']*$member_scheme['credit_value']/100);
						}
				}

				if($has_offer==1)
				{
					
					
					$offer_det=$this->db->query("select * from pnh_m_offers where menu_id=? and cat_id=? and brand_id=? and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$catid,$brandid,$fid))->result_array();
					
					if(empty($offer_det))
						$offer_det=$this->db->query("select * from pnh_m_offers where menu_id=? and cat_id=? and brand_id=0 and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$catid,$fid))->result_array();
					if(empty($offer_det))
						$offer_det=$this->db->query("select * from pnh_m_offers where menu_id=? and cat_id=0 and brand_id=? and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$brandid,$fid))->result_array();
					
					if(empty($offer_det))
						$offer_det=$this->db->query("select * from pnh_m_offers where menu_id=? and cat_id=0 and brand_id=0 and franchise_id=? and is_active=1 order by created_on desc limit 1",array($menuid,$fid))->result_array();
					
					if(!empty($offer_det))
					{
						foreach($offer_det as $offer)
						{
							$inp['has_offer']=1;
							$inp['offer_refid']=$offer['id'];
						}
					}
					
				}
		
				if($redeem)
					$inp['i_coup_discount']=$item['discount']+$inp['redeem_value'];
				else
					$inp['i_coup_discount']=$item['discount'];
				$inp['i_tax']=$item['tax'];
				
				$this->db->insert("king_orders",$inp);
				$this->db->query("insert into king_transactions(transid,amount,paid,mode,init,actiontime,is_pnh,franchise_id,trans_created_by,batch_enabled,trans_grp_refno) values(?,?,?,?,?,?,?,?,?,?,?)",array($transid,$d_total,$d_total,3,time(),time(),1,$fran['franchise_id'],$admin['userid'],$batch_enabled,$trans_grp_refno));
				
				foreach($this->db->query("select group_id from m_product_group_deal_link where itemid=?",$inp['itemid'])->result_array() as $g)
				{
					$attr_n=array();
					$attr_v=array();
					foreach($this->db->query("select attribute_name_id from products_group_attributes where group_id=?",$g['group_id'])->result_array() as $p)
					{
						$attr_n[]=$p['attribute_name_id'];
						$attr_v[]=$this->input->post($item['pid']."_".$p['attribute_name_id']);
					}
					$sql="select product_id from products_group_pids where attribute_name_id=? and attribute_value_id=?";
					foreach($this->db->query($sql,array($attr_n[0],$attr_v[0]))->result_array() as $p)
					{
						$f=true;
						foreach($attr_n as $i=>$an)
							if($this->db->query("select 1 from products_group_pids where product_id=? and attribute_name_id=? and attribute_value_id=?",array($p['product_id'],$an,$attr_v[$i]))->num_rows()==0)
								$f=false;
						if($f)
							break;
					}
					$this->db->insert("products_group_orders",array("transid"=>$transid,"order_id"=>$inp['id'],"product_id"=>$p['product_id']));

                                        
				}
				
				$bal_discount_amt = ($item['price']*$item['margin']['bal_discount']/100)*$item['qty'];
				
				$m_inp=array("transid"=>$transid,"itemid"=>$item['itemid'],"mrp"=>$item['mrp'],"price"=>$item['price'],"base_margin"=>$item['margin']['base_margin'],"sch_margin"=>$item['margin']['sch_margin'],"bal_discount"=>$item['margin']['bal_discount'],"qty"=>$item['qty'],"final_price"=>$item['price']-$item['discount']);
				$this->db->insert("pnh_order_margin_track",$m_inp);
			}
		}
		$bal_discount_amt_msg = '';
		if($bal_discount_amt)
			$bal_discount_amt_msg = ', Topup Damaka Applied : Rs'.$bal_discount_amt;
		
		$this->erpm->pnh_fran_account_stat($fran['franchise_id'],1, $d_total,"Order $transid - Total Amount: Rs $total".$bal_discount_amt_msg,"transaction",$transid);
		
		$balance=$this->db->query("select current_balance from pnh_m_franchise_info where franchise_id=?",$fran['franchise_id'])->row()->current_balance;
		
		//$this->erpm->pnh_sendsms($fran['login_mobile1'],"Your order is placed successfully! Total order amount :Rs $total. Amount deducted is Rs $d_total. Your order ID is $transid Balance in your account Rs $balance",$fran['franchise_id']);
		
		$this->sendsms_franchise_order($transid,$d_total);
		
		$points=0;
		if(!$redeem)
		{
			$rpoints=$this->db->query("select points from pnh_loyalty_points where amount<? order by amount desc limit 1",$total)->row_array();
			if(!empty($rpoints))
				$points=$rpoints['points'];
		}
		$apoints=$this->db->query("select points from pnh_member_info where user_id=?",$userid)->row()->points+$points;
		$this->db->query("update pnh_member_info set points=points+? where user_id=? limit 1",array($points,$userid));
		$this->db->query("insert into pnh_member_points_track(user_id,transid,points,points_after,created_on) values(?,?,?,?,?)",array($userid,$transid,$points,$apoints,time()));
		
		// check if franchise is suspended 
		if($fran_status==0)
			$this->erpm->do_trans_changelog($transid,"PNH Offline order created");
		else
			$this->erpm->do_trans_changelog($transid,"Batch Disabled as Franchise is on ".$fran_status_arr[$fran_status ]);
			
		$franid=$fran['franchise_id'];
		$billno=10001;
		$nbill=$this->db->query("select bill_no from pnh_cash_bill where franchise_id=? order by bill_no desc limit 1",$franid)->row_array();
		if(!empty($nbill))
			$billno=$nbill['bill_no']+1;
		$inp=array("bill_no"=>$billno,"franchise_id"=>$franid,"transid"=>$transid,"user_id"=>$userid,"status"=>1);
		$this->db->insert("pnh_cash_bill",$inp);
		
		redirect("admin/trans/$transid");
	}
	
	
	function sendsms_franchise_order($transid = '',$d_total=0)
	{
		$d_total = $d_total*-1;
		$pnh_beauty_menu = array(100,102,104);
		 
		$sql_trans = "select a.id,a.itemid,b.name as itemname,concat(b.print_name,'-',b.pnh_id) as print_name,i_orgprice,login_mobile1,i_price,i_coup_discount,i_discount,a.quantity,c.menuid,a.transid,f.franchise_id,f.franchise_name   
							from king_orders a 
							join king_dealitems b on a.itemid = b.id 
							join king_deals c on b.dealid = c.dealid 
							join pnh_menu d on d.id = c.menuid 
							join king_transactions e on e.transid = a.transid
							join pnh_m_franchise_info f on f.franchise_id = e.franchise_id 
							where a.transid = ?  
					 ";
		$res_trans = $this->db->query($sql_trans,array($transid));
		
		$total_product_qty = 0;
		$total_products = 0;
		
		$sms_msg = '';
		
		$order_product_list = array();
		$fran_det = array('id'=>'','name'=>'','mob'=>'');
		 
		
		
		if($res_trans->num_rows())
		{
			foreach($res_trans->result_array() as $row_trans)
			{
				$total_products++;
				$total_product_qty+=$row_trans['quantity'];
				$fran_det['name'] = $row_trans['franchise_name'];
				$fran_det['id']= $row_trans['franchise_id'];
				$fran_det['mob']= $row_trans['login_mobile1'];
				
				if(!in_array($row_trans['menuid'],$pnh_beauty_menu))
				{
					$item_land_price = 'Rs '.round($row_trans['i_orgprice']-($row_trans['i_discount']+$row_trans['i_coup_discount'])); 
					$order_product_list[] = $row_trans['print_name'].'('.$row_trans['quantity'].' Qty,'.$item_land_price.')';
				}
			}
		}
		
		if($total_products)
		{
			// has only beauty ,health and baby products  
			if(count($order_product_list) == 0)
			{
				$sms_msg = 'Dear '.$fran_det['name'].', your '.$transid.' with '.$total_products.' products and '.$total_product_qty.' qty and order value of Rs '.$d_total.' is placed successfully';
			}else if(count($order_product_list) == $total_products)
			{
				$sms_msg = 'Dear '.$fran_det['name'].', your '.$transid.' with products : '.implode(',',$order_product_list).' is placed successfully';
			}else if(count($order_product_list) < $total_products)
			{
				$sms_msg = 'Dear '.$fran_det['name'].', your '.$transid.' with '.$total_products.' products and '.$total_product_qty.' qty and order value of Rs '.$d_total.' ,contains products : '.implode(',',$order_product_list).' ,Happy Franchising';
			}
			
			$this->erpm->pnh_sendsms($fran_det['mob'],$sms_msg,$fran_det['id']);
		}
			
			
			
	}

	/**
	 * function to get franchise IMEI Scheme discount by franchise id and PNHID
	 */
	function get_franimeischdisc_pid($fid,$pid)
	{
		$ddet_res = $this->db->query("select menuid,catid,brandid from king_deals a join king_dealitems b on a.dealid = b.dealid where pnh_id = ? ",$pid);
		if($ddet_res->num_rows())
		{
			$ddet = $ddet_res->row_array();
			return @$this->db->query("select scheme_type,credit_value,if(scheme_type,concat(credit_value,'%'),concat('Rs ',credit_value)) as disc,
											menuid,categoryid,brandid 
											from imei_m_scheme 
											where franchise_id = ? and brandid = ? and categoryid = ? and menuid = ?
											and unix_timestamp() between scheme_from and scheme_to 
											and is_active = 1 order by id desc limit 1",array($fid,$ddet['brandid'],$ddet['catid'],$ddet['menuid']))->row()->disc;
		}
		return 0;	
	}

	/**
	 * function to get maxallowedorder qty for pid for current day 
	 */
	function get_maxordqty_pid($fid,$pid)
	{
		return $this->db->query("select (sum(max_allowed_qty)-sum(qty)) as allowed_qty  
								from 
								(
									(select  0 as max_allowed_qty,ifnull(sum(a.quantity),0) as qty  
										from king_orders a 
										join king_dealitems b on a.itemid = b.id 
										join king_transactions c on c.transid = a.transid 
										where b.pnh_id = ? and a.status !=3 and franchise_id = ? and date(from_unixtime(c.init)) = curdate() 
									) 
									union 
									(select max_allowed_qty,0 from king_dealitems where pnh_id = ? and max_allowed_qty > 0 ) 
								) as g",array($pid,$fid,$pid))->row()->allowed_qty*1;

	}
	
	function is_franchise_auth($fid)
	{
		$user=$this->erpm->getadminuser();
		if($this->erpm->auth(true,true))
			return true;
		if($this->db->query("select 1 from pnh_franchise_owners where franchise_id=? and admin=?",array($fid,$user['userid']))->num_rows()==0)
				show_error("Access Denied<br><div style='font-size:14px;'>I reckon you don't have permission to do this</div>");
		return true;
	}
	
	function do_pnh_download_stat($fid_list,$from,$to,$bypasszero=1)
	{
		$fid_str = implode(',',$fid_list);
		@list($s_y,$s_m,$s_d)=@explode("-",$from);
		$s=mktime(0,0,0,$s_m,$s_d,$s_y);
				 
		@list($s_y,$s_m,$s_d)=@explode("-",$to);
		$e=mktime(23,59,59,$s_m,$s_d,$s_y);
				
		if($fid_str)
		{
			$has_entry = 0;
			if(!$bypasszero)
				$has_entry=$this->db->query("select count(*) as total from pnh_franchise_account_stat where franchise_id=? and created_on between ? and ? order by created_on desc",array($fid_str,$s,$e))->row()->total;
			
			$this->load->library("pdf");
			$this->pdf->doc_type("Account Statement");
			$this->pdf->first=1;
			$this->pdf->AliasNbPages();
				
			foreach($fid_list as $fid)
			{
				$fran=$this->db->query("select * from pnh_m_franchise_info where franchise_id=?",$fid)->row_array();
				 
				
				$cdata=$this->db->query("select IF(type=0,'In','Out') as Type,`desc` as Description,amount as Amount,balance_after as `Balance After`,created_on from pnh_franchise_account_stat where franchise_id=? and created_on between ? and ? order by created_on desc",array($fid,$s,$e))->result_array();
				$data=array();
				foreach($cdata as $i=>$d)
				{
					$r=array($d['Type'],$d['Description'],$d['Amount'],$d['Balance After'],date("g:ia d/m/y",$d['created_on']));
					$data[]=$r;
				}
				
				if(!count($data) && !$bypasszero)
					continue ;
					
				$this->pdf->AddPage();
				$this->pdf->SetFont('Arial','',6);
				$this->pdf->Cell(180,-3,"Statement Date :",0,0,"R");
				$this->pdf->SetFont('Arial','B',6);
				$this->pdf->Cell(30,-3,date("d/m/y"),0,1);
				$this->pdf->SetFont('Arial','B',11);
				$this->pdf->Cell(165,5,"Franchise Account Statement",0,1);
				$this->pdf->SetFont('Arial','',8);
				$this->pdf->Ln(1);
				$this->pdf->Cell(300,3,"Statement Period : ".date("d M y",$s)."  to  ".date("d M y",$e),0,1);
				$this->pdf->Cell(23,4,"Current Balance : ",0);
				$this->pdf->SetFont('Arial','B',8);
				$this->pdf->Cell(50,4,"Rs {$fran['current_balance']}",0,1);
				$this->pdf->SetFont('Arial','B',9);
				$this->pdf->Ln(3);
				$this->pdf->Cell(300,5,"Franchise Details",0,1);
				$this->pdf->SetFont('Arial','B',8);
				$this->pdf->Cell(200,4.5,"{$fran['franchise_name']}   (FID: {$fran['pnh_franchise_id']})",0,1);
				$this->pdf->SetFont('Arial','',8);
				$this->pdf->MultiCell(150,3.5,"{$fran['address']}, {$fran['locality']}, \n{$fran['city']}, {$fran['state']} - {$fran['postcode']}",0,"L");
				$this->pdf->Image("images/paynearhome.jpg",165,22,30);
				$this->pdf->Ln(5);
				$this->pdf->account_stat(array("Type","Description","Amount","Balance After","Date"),$data);
				
		//		$this->pdf->Output();
		//		$this->erpm->export_csv("Account_statement_{$from}_to_{$to}",$data,false);
			}
			
			$this->pdf->Output("Account Statement - $from to $to.pdf","D");
		}
		
	}
	
	
	function do_pnh_download_stat_new($fid_list,$from,$to,$bypasszero=1)
	{
		$fid_str = implode(',',$fid_list);
		@list($s_y,$s_m,$s_d)=@explode("-",$from);
		$s=mktime(0,0,0,$s_m,$s_d,$s_y);
				 
		@list($s_y,$s_m,$s_d)=@explode("-",$to);
		$e=mktime(23,59,59,$s_m,$s_d,$s_y);
				
		$from = $from.' 00:00:00';
		$to = $to.' 23:59:59';		
		if($fid_str)
		{
			$has_entry = 0;
			if(!$bypasszero)
				$has_entry=$this->db->query("select count(*) as total from pnh_franchise_account_summary where franchise_id=? and from_unixtime(created_on) between ? and ? order by created_on desc",array($fid_str,$s,$e))->row()->total;
			
			$this->load->library("pdf");
			$this->pdf->doc_type("Account Statement");
			$this->pdf->first=1;
			$this->pdf->AliasNbPages();
				
			foreach($fid_list as $fid)
			{
				$fran=$this->db->query("select * from pnh_m_franchise_info where franchise_id=?",$fid)->row_array();
				 
				
				$cdata=$this->db->query("select IF(type=0,'In','Out') as Type,`desc` as Description,amount as Amount,balance_after as `Balance After`,created_on from pnh_franchise_account_stat where franchise_id=? and created_on between ? and ? order by created_on desc",array($fid,$s,$e))->result_array();
				$data=array();
				foreach($cdata as $i=>$d)
				{
					$r=array($d['Type'],$d['Description'],$d['Amount'],$d['Balance After'],date("g:ia d/m/y",$d['created_on']));
					$data[]=$r;
				}
				
				if(!count($data) && !$bypasszero)
					continue ;
					
				$this->pdf->AddPage();
				$this->pdf->SetFont('Arial','',6);
				$this->pdf->Cell(180,-3,"Statement Date :",0,0,"R");
				$this->pdf->SetFont('Arial','B',6);
				$this->pdf->Cell(30,-3,date("d/m/y"),0,1);
				$this->pdf->SetFont('Arial','B',11);
				$this->pdf->Cell(165,5,"Franchise Account Statement",0,1);
				$this->pdf->SetFont('Arial','',8);
				$this->pdf->Ln(1);
				$this->pdf->Cell(300,3,"Statement Period : ".date("d M y",$s)."  to  ".date("d M y",$e),0,1);
				
				/* 
				$this->pdf->Cell(23,4,"Current Balance : ",0);
				$this->pdf->SetFont('Arial','B',8);
				$this->pdf->Cell(50,4,"Rs {$fran['current_balance']}",0,1);
				*/ 
				 
				$this->pdf->SetFont('Arial','B',9);
				$this->pdf->Ln(3);
				$this->pdf->Cell(300,5,"Franchise Details",0,1);
				$this->pdf->SetFont('Arial','B',8);
				$this->pdf->Cell(200,4.5,"{$fran['franchise_name']}   (FID: {$fran['pnh_franchise_id']})",0,1);
				$this->pdf->SetFont('Arial','',8);
				$this->pdf->MultiCell(150,3.5,"{$fran['address']}, {$fran['locality']}, \n{$fran['city']}, {$fran['state']} - {$fran['postcode']}",0,"L");
				$this->pdf->Image("images/paynearhome.jpg",150,22,50);
				
				
				 
				// Payment Details - Activated/Confirmed   
				
				$ttl_summary = array();
				$ttl_summary['inv_active'] = 0;
				$ttl_summary['inv_cancelled'] = 0;
				$ttl_summary['rcpt_active'] = 0;
				$ttl_summary['rcpt_cancelled'] = 0;
				$ttl_summary['rcpt_pending'] = 0;
				
				$ttl_summary['pending_amt'] = 0;
				
				$sql = "select payment_mode,statement_id,a.receipt_id,acc_correc_id,instrument_no as cheque_no,debit_amt,credit_amt,date(a.created_on) as credit_date  
							from pnh_franchise_account_summary a
							join pnh_t_receipt_info b on a.receipt_id = b.receipt_id 
							where action_type = 3 and a.franchise_id = ? and a.receipt_type = 1 and a.status = 1 and b.status = 1  
							and unix_timestamp(a.created_on) between unix_timestamp(?) 
							and unix_timestamp(?)

						";
				$rcpt_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($rcpt_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Cleared Payments		",0,1);
				
					$amt_total = array(0,0);
					$data = array();
					foreach($rcpt_det_res->result_array() as $k=>$rcpt)
					{
						$rcpt_status = $rcpt['acc_correc_id']?'Reversed':'Active';
						
						$ttl_summary['rcpt_active'] += $rcpt['credit_amt'];
						$ttl_summary['rcpt_cancelled'] += $rcpt['debit_amt'];
						
						$amt_total[0] +=  $rcpt['credit_amt'];
						$amt_total[1] +=  $rcpt['debit_amt'];
						
						$data[] = array($rcpt['credit_date'],$rcpt['receipt_id'],$rcpt['acc_correc_id']?'':((($rcpt['payment_mode']==1)?('CHQ-'.$rcpt['cheque_no']):'Cash')),$rcpt['credit_amt'],$rcpt['debit_amt'],$rcpt_status);
					}
					$data[] = array("","","Sub Total",$amt_total[0],$amt_total[1],"");
					$this->pdf->build_table(array("Date","Receipt #","Payment Mode","Credit (Rs)","Debit (Rs)","Status / Remarks"),array(20,20,40,20,20,70),$data);	
				}
				
				
				
				
				$sql = "select a.remarks,a.is_returned,statement_id,a.invoice_no,credit_amt,debit_amt,date(created_on) as inv_date,b.shipped 
							from pnh_franchise_account_summary a 
							join shipment_batch_process_invoice_link b on a.invoice_no = b.invoice_no   
							join king_invoice c on c.invoice_no = b.invoice_no 
							where action_type = 1 
							and franchise_id = ?  
							and unix_timestamp(created_on) between unix_timestamp(?) 
							and unix_timestamp(?)
							group by a.statement_id 
						";
				$inv_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($inv_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Shipped/Invoiced Products",0,1);
					$amt_total = array(0,0);
					$data = array();
					foreach($inv_det_res->result_array() as $k=>$inv)
					{
						$ttl_summary['inv_active'] += $inv['debit_amt'];
						$ttl_summary['inv_cancelled'] += $inv['credit_amt'];
						
						$amt_total[0] += $inv['credit_amt'];
						$amt_total[1] += $inv['debit_amt'];
						
						$invoice_stat_msg = $inv['shipped']?'Shipped':'Invoiced';
						
						if($inv['is_returned'])
						{
							$invoice_stat_msg = 'Returned';
						}else
						{
							$invoice_stat_msg = $inv['credit_amt']?'Cancelled':$invoice_stat_msg;	
						}
						
						$invoice_stat_msg = $invoice_stat_msg.($inv['remarks']?'-'.$inv['remarks']:'');
						
						$data[] = array(format_date($inv['inv_date']),$inv['invoice_no'],round($inv['credit_amt'],2),round($inv['debit_amt'],2),$invoice_stat_msg);
						
					}
					$data[] = array("","Sub Total",$amt_total[0],$amt_total[1],"");
					$this->pdf->build_table(array("Invoice Date","Invoice no","Credit (Rs)","Debit (Rs)","Status/Remarks"),array(20,60,20,20,70),$data);
				}
				
				
				
				/*
				
				$sql = "select statement_id,member_id,credit_amt,debit_amt,date(created_on) as action_date 
							from pnh_franchise_account_summary a 
							 
							where action_type = 4 
							and franchise_id = ?  
							and unix_timestamp(created_on) between unix_timestamp(?) 
							and unix_timestamp(?)
						";
				$item_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($item_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"MemberShip Details",0,1);
					$amt_total = array(0,0);
					$data = array();
					foreach($item_det_res->result_array() as $k=>$itm)
					{
						$amt_total[0] += $itm['credit_amt'];
						$amt_total[1] += $itm['debit_amt'];
						$data[] = array(format_date($inv['action_date']),$itm['member_id'],round($itm['credit_amt'],2),round($itm['debit_amt'],2),$itm['credit_amt']?'Cancelled':'Active');
					}
					$data[] = array("","Sub Total",$amt_total[0],$amt_total[1],"");
					$this->pdf->build_table(array("Date","Member ID","Credit (Rs)","Debit (Rs)","Status/Remarks"),array(20,60,20,20,70),$data);
				}
				*/
				
				$sql = "select statement_id,acc_correc_id,credit_amt,debit_amt,date(created_on) as action_date,remarks 
							from pnh_franchise_account_summary 
							where action_type = 5  
							and franchise_id = ?  
							and unix_timestamp(created_on) between unix_timestamp(?) 
							and unix_timestamp(?)
						";
				$item_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($item_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Account Adjustments",0,1);
					$amt_total = array(0,0);
					$data = array();
					foreach($item_det_res->result_array() as $k=>$itm)
					{
						//$ttl_summary['rcpt_active'] += $itm['credit_amt'];
						//$ttl_summary['rcpt_cancelled'] += $itm['debit_amt'];
						
						$amt_total[0] += $itm['credit_amt'];
						$amt_total[1] += $itm['debit_amt'];
						
						$data[] = array(format_date($itm['action_date']),$itm['acc_correc_id'],round($itm['credit_amt'],2),round($itm['debit_amt'],2),$itm['remarks']);
					}
					$data[] = array("","Sub Total",$amt_total[0],$amt_total[1],"");
					$this->pdf->build_table(array("Date","Correction ID","Credit (Rs)","Debit (Rs)","Status/Remarks"),array(20,60,20,20,70),$data);
				}
				
				
				
				$net_payable_amt = round($ttl_summary['inv_active']-$ttl_summary['rcpt_active'],2);
				
				
				
				$this->pdf->Ln(3);
				$this->pdf->build_table(array(),array(80,20,20,70),array(array("",($ttl_summary['rcpt_active']+$ttl_summary['inv_active']),($ttl_summary['rcpt_cancelled']+$ttl_summary['inv_cancelled']),"")),1);
				
				 
				 
				$sql = "select * from ((
select statement_id,type,count(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,concat('Total ',count(a.invoice_no),' IMEI Activations') as remarks 
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.credit_note_id = b.id 
		where action_type = 7 and type = 2 
		and a.franchise_id = ?  
		and unix_timestamp(a.created_on) between unix_timestamp(?) 
		and unix_timestamp(?)  
	group by action_date 	
)
union
(
select statement_id,1 as type,(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,remarks
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.invoice_no = b.invoice_no 
		where action_type = 7 and type = 1   
		and a.franchise_id = ?  
		and unix_timestamp(a.created_on) between unix_timestamp(?) 
		and unix_timestamp(?)  
	 group by statement_id 
)
) as g 
order by action_date 
						";
				$item_det_res = $this->db->query($sql,array($fid,$from,$to,$fid,$from,$to));
				if($item_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Credit Notes",0,1);
					$amt_total = array(0,0);
					$data = array();
					foreach($item_det_res->result_array() as $k=>$itm)
					{
						//$ttl_summary['rcpt_active'] += $itm['credit_amt'];
						//$ttl_summary['rcpt_cancelled'] += $itm['debit_amt'];
						
						$amt_total[0] += $itm['credit_amt'];
						$amt_total[1] += $itm['debit_amt'];
						
						$data[] = array(format_date($itm['action_date']),(($itm['type']==1)?'Invoice':'IMEI Activation'),$itm['invoice_no'],round($itm['credit_amt'],2),round($itm['debit_amt'],2),(($itm['type']==1)?'':$itm['remarks']));
					}
					$data[] = array("","","Sub Total",$amt_total[0],$amt_total[1],"");
					$this->pdf->build_table(array("Date","Credit For",'Invoice no',"Credit (Rs)","Debit (Rs)","Status/Remarks"),array(20,30,30,20,20,70),$data);
				} 
				
				
				$sql = "select franchise_id,receipt_id,receipt_amount,payment_mode,instrument_no,bank_name,instrument_date,date(from_unixtime(created_on)) as rcpt_date
								from pnh_t_receipt_info 
								where franchise_id = ? 
								and (created_on) between unix_timestamp(?) and unix_timestamp(?)
								and status = 0 and receipt_type = 1  
								order by rcpt_date
						";
						 
				$inv_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($inv_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Uncleared Payments ",0,1);
				
					$pen_receipt = 0;
					$data = array();
					foreach($inv_det_res->result_array() as $k=>$inv)
					{
						$ttl_summary['rcpt_pending'] += $inv['receipt_amount'];
						$pen_receipt += $inv['receipt_amount'];
						$data[] = array(format_date($inv['rcpt_date']),$inv['receipt_id'],($inv['payment_mode']?'CHQ':'Cash'),$inv['receipt_amount'],$inv['instrument_no'],$inv['bank_name'],format_date(date('Y-m-d',$inv['instrument_date'])));
					}
					$data[] = array("","","Sub Total",$pen_receipt,"","","");
					$this->pdf->build_table(array("Date","Receipt #","Mode","Amount (Rs)","CHQ/DD no","Bank Name","CHQ/DD date"),array(20,20,15,30,40,40,20),$data,0);	
				}
				 
				
				
				
				$sql = "select franchise_id,receipt_id,receipt_amount,payment_mode,instrument_no,bank_name,instrument_date,date(from_unixtime(created_on)) as rcpt_date
								from pnh_t_receipt_info 
								where franchise_id = ? 
								and (created_on) between unix_timestamp(?) and unix_timestamp(?) 
								and status in (2,3) and is_active = 1   
								order by rcpt_date
						";
						 
				$inv_det_res = $this->db->query($sql,array($fid,$from,$to));
				if($inv_det_res->num_rows())
				{
					$this->pdf->Ln(5);
					$this->pdf->SetFont('Arial','',12);
					$this->pdf->Cell(10,5,"Returned/Bounced Payments",0,1);
				
					$cancelled_receipt = 0;
					$data = array();
					foreach($inv_det_res->result_array() as $k=>$inv)
					{
						$ttl_summary['rcpt_cancelled'] += $inv['receipt_amount'];
						$cancelled_receipt += $inv['receipt_amount'];
						$data[] = array(format_date($inv['rcpt_date']),$inv['receipt_id'],($inv['payment_mode']?'CHQ':'Cash'),$inv['receipt_amount'],$inv['instrument_no'],$inv['bank_name'],format_date(date('Y-m-d',$inv['instrument_date'])));
					}
					$data[] = array("","","Sub Total",$cancelled_receipt,"","");
					$this->pdf->build_table(array("Date","Receipt #","Mode","Amount (Rs)","CHQ/DD no","Bank Name","CHQ/DD date"),array(20,20,15,30,40,40,20),$data,0);	
				}
				 
				$not_shipped_amount = $this->db->query(" select sum(t) as amt from (
															select a.invoice_no,debit_amt as t 
																from pnh_franchise_account_summary a 
																join king_invoice c on c.invoice_no = a.invoice_no and invoice_status =  1 
																where action_type = 1 
																and franchise_id = ?  
															group by a.invoice_no ) as a 
															join shipment_batch_process_invoice_link b on a.invoice_no = b.invoice_no and shipped = 0  ",$fid)->row()->amt;
				
				
				
				$total_invoice_val = $this->db->query("select sum(debit_amt) as amt from pnh_franchise_account_summary where action_type = 1 and franchise_id = ? ",$fid)->row()->amt;
				$total_invoice_cancelled_val = $this->db->query("select sum(credit_amt) as amt from pnh_franchise_account_summary where action_type = 1 and franchise_id = ? ",$fid)->row()->amt;
				
				$sql = "select sum(credit_amt) as amt   
							from pnh_franchise_account_summary a
							join pnh_t_receipt_info b on a.receipt_id = b.receipt_id 
							where action_type = 3 and a.franchise_id = ? and a.receipt_type = 1 and a.status = 1 and b.status = 1 
						";
				$total_active_receipts_val = $this->db->query($sql,array($fid))->row()->amt ;
				
				$sql = "select sum(receipt_amount) as amt  
								from pnh_t_receipt_info 
								where franchise_id = ? 
								and status in (2,3) and is_active = 1   
						";
						 
				$total_cancelled_receipts_val = $this->db->query($sql,array($fid))->row()->amt;
				
				
				$sql = "select sum(receipt_amount) as amt 
								from pnh_t_receipt_info 
								where franchise_id = ? and status = 0 and receipt_type = 1  and is_active = 1  
						";
						 
				$total_pending_receipts_val = $this->db->query($sql,array($fid))->row()->amt;
				
				$sql = "select sum(credit_amt-debit_amt) as amt  
							from pnh_franchise_account_summary where action_type = 5 and franchise_id = ? ";
				$acc_adjustments_val = $this->db->query($sql,array($fid))->row()->amt;
				
				/*
				$sql = "select sum(credit_amt-debit_amt) as amt  
							from pnh_franchise_account_summary where action_type = 7 and type in (1,2) and franchise_id = ? ";
				 */
				 $sql = "select (sum(credit_amt)-sum(debit_amt)) as amt from ((
select statement_id,type,count(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,concat('Total ',count(a.invoice_no),' IMEI Activations') as remarks 
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.credit_note_id = b.id 
		where action_type = 7 and type = 2 
		and a.franchise_id = ?   
		 
	group by action_date 	
)
union
(
select statement_id,1 as type,(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,remarks
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.invoice_no = b.invoice_no 
		where action_type = 7 and type = 1   
		and a.franchise_id = ?   
		 
	 group by statement_id 
)
) as g 
order by action_date";  
				$ttl_credit_note_val = $this->db->query($sql,array($fid,$fid))->row()->amt;
				
				$total_active_invoice=$total_invoice_val-$total_invoice_cancelled_val;
				
				$net_payable_amt = ($total_active_invoice-($total_active_receipts_val+$acc_adjustments_val+$ttl_credit_note_val));
				
				$this->pdf->Ln(6);
				$this->pdf->SetFont('Arial','',10);
				$this->pdf->Cell(200,4.5,"Total Value of products Shipped/Invoiced									Rs ".formatInIndianStyle($total_active_invoice),0,1);
				$this->pdf->Ln(2);
				$this->pdf->Cell(200,4.5,"Total Value of products Cancelled/Returned					Rs ".formatInIndianStyle($total_invoice_cancelled_val),0,1);
				$this->pdf->Ln(2);
				$this->pdf->Cell(200,4.5,"Total Value of Credit Notes Raised																			Rs ".formatInIndianStyle($ttl_credit_note_val),0,1);
				$this->pdf->Ln(2);
				$this->pdf->Cell(200,4.5,"Total Cleared Payments		     																													Rs ".formatInIndianStyle($total_active_receipts_val),0,1);
				$this->pdf->Ln(2);
				$this->pdf->Cell(200,4.5,"Total Returned/Bounced Payments 																		Rs ".formatInIndianStyle($total_cancelled_receipts_val),0,1);
				
				if($acc_adjustments_val)
				{
					$this->pdf->Ln(2);
					$this->pdf->Cell(200,4.5,"Total Account Adjustments 																															Rs ".formatInIndianStyle($acc_adjustments_val),0,1);	
				}
				
				
				$this->pdf->Ln(2);
				$this->pdf->Ln(2);
				$this->pdf->SetFont('Arial','B',12);
				
				$this->pdf->Cell(200,4.5,"Total Amount Pending for Payment		Rs ".formatInIndianStyle($net_payable_amt).($total_pending_receipts_val?' (uncleared  Rs '.formatInIndianStyle($total_pending_receipts_val).')':''));
				if($not_shipped_amount)
				{
					$this->pdf->Ln(6);
					$this->pdf->SetFont('Arial','B',12);
					$this->pdf->Cell(200,4.5,"Invoiced But not shipped yet		          	Rs ".formatInIndianStyle($not_shipped_amount));	
				}
				
				 
			}
			
			$this->pdf->Output("New Account Statement - $from to $to.pdf","D");
		}
			
	}
	
	function do_pnh_add_class()
	{
		foreach(array("class","margin","combo_margin","less_margin_brands") as $i)
			$$i=$this->input->post($i);
		if($this->db->query("select 1 from pnh_m_class_info where class_name=?",$class)->num_rows()!=0)
			show_error("Class $class already exists");
		$this->db->query("insert into pnh_m_class_info(class_name,margin,combo_margin,less_margin_brands) values(?,?,?,?)",array($class,$margin,$combo_margin,$less_margin_brands));
		redirect("admin/pnh_class");
	}
	
	function do_pnh_update_class()
	{
		foreach(array("id","margin","combo_margin","less_margin") as $i)
			$$i=$this->input->post($i);
		$this->db->query("update pnh_m_class_info set margin=?,combo_margin=?,less_margin_brands=? where id=?",array($margin,$combo_margin,$less_margin,$id));
		$this->erpm->flash_msg("Class Updated");
		redirect("admin/pnh_class");
	}
	
	function do_pnh_add_territory()
	{
		$user=$this->erpm->getadminuser();
		$name=$this->input->post("terry");
		if($this->db->query("select 1 from pnh_m_territory_info where territory_name=?",$name)->num_rows()!=0)
			show_error("Territory $name already exists");
		$this->db->query("insert into pnh_m_territory_info(territory_name,created_on,created_by) values(?,?,?)",array($name,time(),$user['userid']));
		redirect("admin/pnh_territories");
	}
	
	function do_pnh_update_territory()
	{
		$user=$this->erpm->getadminuser();
		$id=$this->input->post("id");
		$name=$this->input->post("terry");
		$this->db->query("update pnh_m_territory_info set territory_name=?,modified_by={$user['userid']},modified_on=".time()." where id=?",array($name,$id));
		redirect("admin/pnh_territories");
	}
	
	function do_pnh_add_device_type()
	{
		$user=$this->erpm->getadminuser();
		$name=$this->input->post("terry");
		$description=$this->input->post("desc");
		if($this->db->query("select 1 from pnh_m_device_type where device_name=?",$name)->num_rows()!=0)
			show_error("Territory $name already exists");
		$this->db->query("insert into pnh_m_device_type(device_name,description,created_on,created_by) values(?,?,?,?)",array($name,$description,time(),$user['userid']));
		redirect("admin/pnh_device_type");
	}
	
	function do_pnh_update_device_type()
	{
		$user=$this->erpm->getadminuser();
		$id=$this->input->post("id");
		$name=$this->input->post("terry");
		$desc=$this->input->post("desc");
		$this->db->query("update pnh_m_device_type set device_name=?,description=?,modified_by={$user['userid']},modified_on=".time()." where id=?",array($name,$desc,$id));
		redirect("admin/pnh_device_type");
	}
	
	function do_pnh_addtown()
	{
		$terry=$this->input->post("territory");
		$towns=$this->input->post("town");
		foreach(explode(",",$towns) as $town)
		{
			if($this->db->query("select 1 from pnh_towns where town_name=?",array($town,$terry))->num_rows()!=0)
				show_error("Town : $town already exists or alloted to another territory");
			$this->db->query("insert into pnh_towns(town_name,territory_id) values(?,?)",array($town,$terry));
		}
		redirect("admin/pnh_towns");
	}
	
	function pnh_gettowns($tid)
	{
		$sql="select t.id as town_id,territory_id,t.town_name,tr.territory_name 
					from pnh_towns t 
					join pnh_m_territory_info tr on tr.id=t.territory_id
				";
		if($tid!=0)
			$sql.=" where t.territory_id=?";
		$sql.=" order by t.town_name asc";
		return $this->db->query($sql,$tid)->result_array();
	}
	
	function p_genid($len)
	{
		$st="";
		for($i=0;$i<$len;$i++)
			$st.=rand(1,9);
		return $st;
	}
	
	function send_admin_note($msg,$isub="")
	{
		$sub="ERP Notification";
		if(!empty($isub))
			$sub.=" : $isub";
		$this->vkm->email(array("vimal@localcircle.in","sri@localcircle.in","gova@localcircle.in","sushma@localcircle.in","sushma@thecouch.in"),$sub,$msg);
	}
	
	function debug_post($die=true)
	{
		echo "<pre>";
		print_r($_POST);
		echo "\n\n\n\n";
		$keys=array();
		foreach($_POST as $key=>$k)
			$keys[]='"'.$key.'"';
		echo implode(",",$keys);
		if($die)
			die;
	}
	
	function getpafdata($pafid)
	{
		return $this->db->query('select a.id as paf_id,handled_by,b.mrp,b.qty,b.vendor_id,a.remarks,ifnull(d.barcode,0) as barcode,
													b.id as prod_paf_id,group_concat(distinct concat(c.vendor_id,"::",c.vendor_name,"::",e.brand_margin)) as vendors,
													b.product_id,d.product_name,notify_handler,
													po_id,paf_status,a.created_on   
												from t_paf_list a
												join t_paf_productlist b on a.id = b.paf_id
												join m_product_info d on b.product_id = d.product_id 
												left join m_vendor_brand_link e on e.brand_id = d.brand_id  
												left join m_vendor_info c on c.vendor_id = e.vendor_id 
												where  a.id = ? 
											group by b.product_id
											order by d.brand_id ',$pafid)->result_array();
	}
	
	function getpafsmslog($pafid)
	{
		return $this->db->query('select * from t_paf_smslog where paf_id = ? order by logged_on desc',$pafid);
	} 
	
	function getpafprodstk($pafid)
	{
		return $this->db->query('select b.product_id,sum(available_qty) as qty  
														from t_stock_info a 
														join t_paf_productlist b on a.product_id = b.product_id 
														where b.paf_id = ? 
														group by b.product_id ',$pafid);
	}
	
	function getpafpendingstk($pafid)
	{
		return $this->db->query('select b.product_id,ifnull(sum(o.quantity*l.qty),0) as qty  
												from m_product_deal_link l 
												left join king_orders o on o.itemid=l.itemid 
												join t_paf_productlist b on b.product_id = l.product_id 
												where b.paf_id = ? and o.status = 0   
											group by b.product_id',$pafid);
	}
	
	function getpafprod_pastorders($pafid)
	{
		return $this->db->query("select b.product_id,ifnull(sum(o.quantity*l.qty),0) as s 
												from m_product_deal_link l 
												left join king_orders o on o.itemid=l.itemid 
												join t_paf_productlist b on b.product_id = l.product_id 
												where b.paf_id = ? and o.time>".(time()-(24*60*60*90))."  
											group by b.product_id",$pafid);
	}
	
	
	/**
	 * function to get emp info
	 * @param unknown_type $emp_id
	 * @return boolean
	 */

	function get_empinfo($emp_id)
	{
		
		$sql = "SELECT *,b.role_name,c.role_name as role FROM `m_employee_info` a
				left JOIN `m_employee_roles`b ON b.role_id=a.job_title
				LEFT JOIN m_employee_roles c ON c.role_id=a.job_title2				
				WHERE a.`employee_id`=?
				";
		$res = $this->db->query($sql,$emp_id);
		
		if($res->num_rows()){
			return $res->row_array();
		}
		return false;
	}
	
    

	/**
	 * function to get roles
	 * @return boolean
	 */
	function getRolesList(){
		$sql="select * from m_employee_roles";
		$res=$this->db->query($sql);
		if($res->num_rows()){
			return $res->result_array($sql);
		}
		return false;
	}
	/**
	 * 
	 * function to get is active territory list
	 */
	function isactive_territorylist(){
		return $this->db->query('SELECT b.territory_name FROM m_town_territory_link a
								JOIN `pnh_m_territory_info`b ON b.id=a.territory_id
								WHERE is_active=1
								GROUP BY a.territory_id')->result_array();
	}
	/**
	 * function to get is unactive territory list
	 */
	function unactive_territorylist(){
		return $this->db->query('SELECT b.territory_name FROM m_town_territory_link a
									JOIN `pnh_m_territory_info`b ON b.id=a.territory_id
									WHERE is_active=0
									GROUP BY b.territory_name')->result_array();
	}
	/**
	 * function to get is active town list
	 */
	function isactive_townlist(){
		return $this->db->query('SELECT b.town_name FROM m_town_territory_link a
									JOIN `pnh_towns`b ON b.id=a.town_id
									WHERE a.is_active=1
									GROUP BY b.town_name;')->result_array();
	}
	/**
	 * function to get not active town list
	 */
	function unactive_townlist(){
		return $this->db->query('SELECT a.town_id,b.town_name FROM m_town_territory_link a
									JOIN `pnh_towns`b ON b.id=a.town_id
									WHERE a.is_active=0
									GROUP BY a.town_id;')->result_array();
	}
	/**
	 * 
	 * @param unknown_type $emp_id
	 */

	function get_working_under_det($emp_id){
		$role_id=$this->db->query("select job_title2 from m_employee_info where employee_id=? and is_suspended=0",$emp_id);
		/*if($role_id->row() && $role_id<6)
		{
			
		}*/
		return $this->db->query('SELECT a.parent_emp_id,c.name,b.role_name
				FROM m_employee_rolelink a
				JOIN `m_employee_info`c ON c.employee_id=a.parent_emp_id
				JOIN `m_employee_roles`b ON b.role_id=c.job_title
				WHERE a.employee_id =? AND is_active = 1 and c.is_suspended=0',$emp_id)->row_array();
	}
	/**
	 * to get assigned territory details
	 * @param unknown_type $emp_id
	 */
	
	function get_assigned_territory_det($emp_id)
	{
		return $this->db->query('SELECT a.employee_id,d.name AS assigned_under,a.territory_id,a.town_id,b.territory_name,a.created_on,a.modified_on
								FROM `m_town_territory_link` a
								JOIN `pnh_m_territory_info` b ON b.id=a.territory_id
								JOIN `m_employee_rolelink` c ON c.employee_id=a.employee_id
								JOIN `m_employee_info`d ON d.employee_id=c.parent_emp_id
								WHERE a.employee_id=? AND a.is_active=1 AND c.is_active=1 and d.is_suspended=0
				                GROUP BY a.territory_id',$emp_id)->result_array();
				
		
	}
	/**
	 * to get assigned town details
	 * @param unknown_type $emp_id
	 */
	
	function get_assigned_town_det($emp_id){
		return $this->db->query('SELECT a.employee_id,d.name AS assigned_under,a.territory_id,a.town_id,b.town_name,a.created_on,a.modified_on
								FROM `m_town_territory_link` a
								JOIN `pnh_towns` b ON b.id=a.town_id
								JOIN `m_employee_rolelink` c ON c.employee_id=a.employee_id
								JOIN `m_employee_info`d ON d.employee_id=c.parent_emp_id
								WHERE a.employee_id=? AND a.is_active=1 AND c.is_active=1 and d.is_suspended=0 
		                        GROUP BY a.town_id',$emp_id)->result_array();
	}
	
	

	/**
	 * function to fetch user access roles
	 *
	 * @return boolean
	 */
	function get_emp_access_roles(){
		$user_authdet = $this->session->userdata('user_authdet');

		$access_list_res = $this->db->query("SELECT  role_id,role_name
												FROM m_employee_roles
												WHERE role_id > 1 ");
				
			
		if($access_list_res->num_rows()){
			return $access_list_res->result_array();
		}else{
			return false;
		}
	}
	
	/**
	 * function to get employee list by role id
	 *
	 * @param unknown_type $role_id
	 * @return boolean
	 */
	function get_empbyroleid($role_id){
		$emp_list_res = $this->db->query("SELECT employee_id,name,job_title FROM m_employee_info WHERE job_title = ? and is_suspended=0",$role_id);

		if($emp_list_res->num_rows()){
			return $emp_list_res->result_array();
		}
		return false;
	}
	
	function to_get_all_territories(){
		return $this->db->query("select * from pnh_m_territory_info")->result_array();
	}
	function get_all_towns($territory_id){
		$town_list=$this->db->query("select * from pnh_towns where territory_id=?",$territory_id);
		
		if($town_list->num_rows()){
			return $town_list->result_array();
		}
		return false;
	}
	/**
	 * function to get assignment histroy
	 */
	function to_get_assignmnt_histroy(){
		return $this->db->query('SELECT a.employee_id,c.role_name AS assigned_under,c.role_id AS assig_id,b.name,date_format(a.assigned_on,"%d/%m/%y") as assigned_on,date_format(a.modified_on,"%d/%m/%y") as modified_on,d.job_title,e.role_name,d.name AS emp_name
								FROM `m_employee_rolelink` a
								JOIN `m_employee_info`b ON b.employee_id=parent_emp_id
								JOIN `m_employee_roles`c ON c.role_id=b.job_title 
								JOIN `m_employee_info`d ON d.employee_id=a.employee_id
								JOIN  `m_employee_roles`e ON e.role_id = d.job_title
				 				where c.is_suspended=0
				                ORDER BY c.role_id DESC')->result_array();
	}
	
	function to_get_assignmnt_details($emp_id){
		return $this->db->query('SELECT a.employee_id,c.role_name AS assigned_under,c.role_id AS assig_id,b.name,a.assigned_on,a.modified_on,d.job_title,e.role_name,d.name AS emp_name
									FROM `m_employee_rolelink` a
									JOIN `m_employee_info`b ON b.employee_id=parent_emp_id
									JOIN `m_employee_roles`c ON c.role_id=b.job_title
									JOIN `m_employee_info`d ON d.employee_id=a.employee_id
									JOIN  `m_employee_roles`e ON e.role_id = d.job_title
									WHERE a.employee_id=?  and b.is_suspended=0
									GROUP BY employee_id
									ORDER BY assigned_on,modified_on DESC',$emp_id)->result_array();
	}
	
	function getempbyroles($role_id,$pg=0){
		return $this->db->query("SELECT *,b.role_name FROM  m_employee_info a
									JOIN m_employee_roles b ON b.role_id=a.job_title
									WHERE job_title=? and a.is_suspended=0
									limit $pg,".MIN_ROWS_DISP,$role_id)->result_array();
	}
	
   function getempbyterritory($terr_id,$pg=0){
   	return $this->db->query("SELECT *,b.role_name FROM  m_employee_info a
								JOIN m_employee_roles b ON b.role_id=a.job_title
								JOIN m_town_territory_link c ON a.employee_id=c.employee_id
								WHERE c.territory_id = ? AND c.is_active=1 and a.is_suspended=0
                                GROUP BY a.employee_id
   			                    limit $pg,".MIN_ROWS_DISP,$terr_id)->result_array();
   } 
   
   function get_emplistbyids($sub_emp_ids){
	return $this->db->query("select employee_id,job_title,name from m_employee_info where employee_id=? and is_suspended=0",$sub_emp_ids)->result_array(); 
  }
   /**
    * 
    * @param unknown_type $route_id
    */
   function route_linkd_twns($route_id)
   {
   	return $this->db->query("SELECT town_name,b.route_name FROM pnh_towns a
								JOIN pnh_routes b ON b.id=a.route_id WHERE b.id=?",$route_id)->result_array();
   }
   
  /**
    * function to fetch manifesto log details  
    *
    * @param unknown_type $partner_id
    * @param unknown_type $pg 
    * @return unknown
    */
   function get_partner_manifesto_log($pg=-1,$limit=0)
   {
   		// fetch total if pg is -1   
   		if($pg == -1)
   			return $this->db->query("select count(distinct serial_no) as ttl from t_partner_manifesto_log where 1 ")->row()->ttl;
   		else
   		   	return $this->db->query("select serial_no,count(*) as ttl,DATE_FORMAT(created_on,'%d/%m/%Y %r') as created_on from t_partner_manifesto_log where 1 group by serial_no  order by serial_no desc limit $pg,$limit");
   }
   
   function do_pnh_update_execu_account_log()
   {
	   	$user=$this->auth();
	   	
	   	$log_id=$this->input->post('log_id');
	   	$remarks=$this->input->post('remarks');
	   	$this->db->query("update pnh_executive_accounts_log set reciept_status=1,updated_by=?,remarks=?,updated_on=now() where id=?",array($user['userid'],$remarks,$log_id));
	   	 
		if($this->db->affected_rows()==1)
		{
			return true;
		}else 
			return false;
	
   }
	function get_menubyparentid($parent_id)
	{
		$sql="select * from m_erp_menu where parent=?;";
		$result=$this->db->query($sql,$parent_id);
		if($result->num_rows())
				return $result->result_array(); 
	}
			
	function get_menu_parent_id($url,$parent_id='')
	{
		$cond = array(); 
		$sql="select id,parent from m_erp_menu ";
		if($parent_id)
		{
			$sql.=" where id = ? ";
			$cond[] = $parent_id;
		}	
		else
		{
			$sql.=" where link = ? ";
			$cond[] = $url;
		}
			 
		$result=$this->db->query($sql,$cond);
		if($result->num_rows())
			return $result->result_array();
	
	}


	//function to get call details made by callcenter team
	function get_call_log()
	{
		$log_details=$this->db->query("SELECT a.msg,date_format(FROM_UNIXTIME(a.created_on),'%d/%m/%y %h:%i %p') as created_on,a.sender,c.franchise_name
									FROM pnh_sms_log a
									JOIN pnh_m_franchise_info c ON c.franchise_id=a.franchise_id
									WHERE c.is_suspended=0 AND `type`='CALL'");
		if($log_details->num_rows())
		{
			return $log_details ->result_array();
			
		}else
			
			return false;
	}
	
	function pnh_executive_account_log($fil_date)
	{
		$user=$this->auth();
		if($_POST)
		{
			$this->erpm->do_pnh_update_execu_account_log();
			redirect('admin/pnh_executive_account_log');
			exit;
		}
	
		if($fil_date == '')
			$fil_date = date('Y-m-d');
	
		$pnh_exec_account_details=$this->db->query("SELECT a.id as log_id,b.contact_no,a.msg,a.remarks,a.reciept_status,b.name,b.name AS employee_name,c.name AS updatedby_name,DATE_FORMAT(a.updated_on,'%d/%m/%y %h:%i %p') AS updated_on,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on
														FROM `pnh_executive_accounts_log` a
														JOIN m_employee_info b ON b.employee_id=a.emp_id
														LEFT JOIN king_admin c ON c.id=a.updated_by
														where date(a.logged_on) = date(?) and a.type='paid'
														",$fil_date);
		
		if($pnh_exec_account_details->num_rows())
			return $pnh_exec_account_details->result_array();
		else 
			return false;
	
	} 
	
	function _set_notifications( $notifications )
	{
		$this->_current_notifications = $notifications;
		$this->CI->session->set_flashdata('notifications', $this->_current_notifications);
	}
	
	function get_pnh_invreturns($pg,$cond='',$param)
	{
		$sql = "select c.transid,a.return_id,a.invoice_no,franchise_name,return_by,returned_on,b.name as handled_by_name,a.status  
					from pnh_invoice_returns a
					join king_admin b on a.handled_by = b.id
					join king_invoice c on c.invoice_no = a.invoice_no 
					join king_transactions d on c.transid = d.transid 
					left join pnh_m_franchise_info e on e.franchise_id = d.franchise_id  
					join pnh_invoice_returns_product_link f on f.return_id = a.return_id 
					where 1 $cond
					group by a.return_id  
					order by returned_on desc
				limit $pg,10 ";
		$res = $this->db->query($sql,$param);
		if($res->num_rows())
			return $res->result_array();
		return false;	
	}

	function get_pnh_invreturns_ttl($cond='',$param)
	{
		//$sql = "select count(*) as ttl from pnh_invoice_returns as a where 1 $cond";
		$sql = "select c.transid,a.return_id,a.invoice_no,franchise_name,return_by,returned_on,b.name as handled_by_name,a.status
					from pnh_invoice_returns a
					join king_admin b on a.handled_by = b.id
					join king_invoice c on c.invoice_no = a.invoice_no
					join king_transactions d on c.transid = d.transid
					left join pnh_m_franchise_info e on e.franchise_id = d.franchise_id
					join pnh_invoice_returns_product_link f on f.return_id = a.return_id 
					where 1 $cond
					group by a.return_id
					order by returned_on desc
				 ";
		$res = $this->db->query($sql,$param);
		if($res->num_rows())
			return $res->num_rows();
		else
			return 0;
	}
	
	
	function get_pnh_product_service($pg)
	{
		$sql = "select a.return_id,a.invoice_no,product_name,franchise_name,return_by,returned_on,b.name as handled_by_name,a.status  
					from pnh_invoice_returns a
					join king_admin b on a.handled_by = b.id
					join king_invoice c on c.invoice_no = a.invoice_no 
					join king_transactions d on c.transid = d.transid 
					left join pnh_m_franchise_info e on e.franchise_id = d.franchise_id
					join pnh_invoice_returns_product_link f on f.return_id = a.return_id
					join m_product_info g on g.product_id = f.product_id     
					where  f.condition_type = 6  
					group by f.id   
					order by returned_on desc
				limit $pg,10 ";
		$res = $this->db->query($sql);
		if($res->num_rows())
			return $res->result_array();
		return false;	
	}

	function get_pnh_product_service_ttl()
	{
		$sql = "select count(*) as ttl from pnh_invoice_returns a join pnh_invoice_returns_product_link b on a.return_id = b.return_id where b.condition_type = 6  ";
		return $this->db->query($sql)->row()->ttl;
	}
	
	/**
	 * function to get invoice details with order list by invoice no 
	 *
	 * @param unknown_type $invno
	 * @return unknown
	 */
	function get_invoicedet_forreturn($invno)
	{
		$invdet = array();
		
		$sql = "select  a.invoice_no,b.id as order_id,b.itemid,c.name,b.quantity,d.packed,d.shipped,d.shipped_on,
						a.invoice_status  
					from king_invoice a
					join king_orders b on a.order_id = b.id 
					join king_dealitems c on c.id = b.itemid 
					join shipment_batch_process_invoice_link d on d.invoice_no = a.invoice_no
					join king_transactions t on t.transid = b.transid
					left join pnh_invoice_returns_product_link e on e.order_id = b.id  
					where a.invoice_no = ? and b.status in (1,2) 
					group by a.order_id  
				";
		$res = $this->db->query($sql,$invno);
		//echo $this->db->last_query();
		if($res->num_rows())
		{
			$invord_list = $res->result_array();
			if(!$invord_list[0]['shipped'])
			{
				$invdet['error'] = 'Invoice is not shipped';
			}else 
			{
				
				
				
				$invdet['itemlist'] = array();
		
				foreach ($invord_list as  $ord)
				{
					
					
					
					$sql = "(select e.is_shipped,e.shipped_on,e.is_refunded,is_stocked,is_serial_required,a.id as order_id,b.product_id,product_name,b.qty from king_orders a join m_product_deal_link b on a.itemid = b.itemid join m_product_info c on c.product_id = b.product_id left join pnh_invoice_returns_product_link e on e.product_id = b.product_id and e.order_id = a.id where a.id = ? group by e.id )
								union
							(select e.is_shipped,e.shipped_on,e.is_refunded,is_stocked,is_serial_required,a.order_id,b.product_id,product_name,d.qty from products_group_orders a join m_product_info b on b.product_id = a.product_id join king_orders c on c.id = a.order_id join m_product_group_deal_link d on d.itemid = c.itemid left join pnh_invoice_returns_product_link e on e.product_id = a.product_id and e.order_id = a.order_id where a.order_id = ? group by e.id )
							";
					$prod_list_res = $this->db->query($sql,array($ord['order_id'],$ord['order_id']));
					
					if(!isset($invdet['itemlist'][$ord['order_id']]))
						$invdet['itemlist'][$ord['order_id']] = array();
					
					$invdet['itemlist'][$ord['order_id']] = $ord;
					
					if(!isset($invdet['itemlist'][$ord['order_id']]['product_list']))
						$invdet['itemlist'][$ord['order_id']]['product_list'] = array();
						
					$prod_list = array();	
					foreach($prod_list_res->result_array() as $prod_det)
					{
						$ttl_pending_inreturn_qty = $this->db->query("select ifnull(sum(qty),0) as t from pnh_invoice_returns_product_link where order_id = ? and product_id = ? and status != 3 ",array($ord['order_id'],$prod_det['product_id']))->row()->t;
						
						$prod_det['pen_return_qty'] = $ttl_pending_inreturn_qty; 
						$prod_det['has_barcode'] = $this->db->query("select count(*) as t from t_stock_info where product_id = ? and product_barcode != '' ", $prod_det['product_id'] )->row()->t;
						$prod_list[] = $prod_det;
					}	
					$invdet['itemlist'][$ord['order_id']]['product_list'][] = $prod_list;
					
					$ord_imei_list_res = $this->db->query("select * from t_imei_no where order_id = ? and status = 1 and is_returned = 0 and return_prod_id = 0 ",$ord['order_id']);
					if($ord_imei_list_res->num_rows())
					{
						foreach($ord_imei_list_res->result_array() as $p_imei_det)
						{
							if(!isset($invdet['itemlist'][$ord['order_id']]['imei_list'][$p_imei_det['product_id']]))
								$invdet['itemlist'][$ord['order_id']]['imei_list'][$p_imei_det['product_id']] = array();
								
							$invdet['itemlist'][$ord['order_id']]['imei_list'][$p_imei_det['product_id']][] = $p_imei_det['imei_no'];  
						}						
					}
					
					// check if the order qty already processedIn 
					
				}
			}
		}else
		{
			$invdet['error'] = 'Invoice not found';
		}
		return $invdet;
	}

	function get_pnh_invreturn($return_id)
	{
		$return_det = array();
		$return_det_res = $this->db->query("select c.transid,e.franchise_id,e.franchise_name,a.return_id,a.invoice_no,return_by,returned_on,
												b.name as handled_by_name,a.status  
												from pnh_invoice_returns a 
												join king_admin b on a.handled_by = b.id 
												join king_invoice c on c.invoice_no = a.invoice_no
												join king_transactions d on d.transid = c.transid
												join pnh_m_franchise_info e on e.franchise_id = d.franchise_id 
												where return_id = ? 
											",$return_id);
		if($return_det_res->num_rows())
		{
			$return_det['det'] = $return_det_res->row_array();
			// return product list details 
			$ret_prod_list_res = $this->db->query("select a.is_packed,a.readytoship,d.franchise_id,e.franchise_name,a.is_refunded,a.is_shipped,a.is_stocked,
															a.id as return_product_id,a.return_id,a.order_id,a.product_id,
															b.product_name,qty,a.barcode,a.imei_no,condition_type,a.status 
														from pnh_invoice_returns_product_link a
														join pnh_invoice_returns f on f.return_id = a.return_id  
														join m_product_info b on a.product_id = b.product_id 
														join king_invoice c on c.invoice_no = f.invoice_no
														join king_transactions d on d.transid = c.transid
														join pnh_m_franchise_info e on e.franchise_id = d.franchise_id  
														where a.return_id = ?
														group by a.id   
													",$return_id);
			$return_det['product_list'] = $ret_prod_list_res->result_array();	
		}
		
		return $return_det;
	}
	
	function get_pnh_invreturnprod_remarks($return_prod_id)
	{
		$rp_remarks_res = $this->db->query("select a.*,b.name as created_by_name from pnh_invoice_returns_remarks a join king_admin b on a.created_by = b.id where return_prod_id = ? ",$return_prod_id);
		if($rp_remarks_res->num_rows())
			return $rp_remarks_res->result_array();
		return false;	 
	}
	
	function get_frandetbyinvno($inv_no)
	{
		return $this->db->query("select a.* from pnh_m_franchise_info a join king_transactions b on a.franchise_id = b.franchise_id join king_invoice c on c.transid = b.transid where c.invoice_no = ? ",$inv_no)->row_array();
	}
	
	function get_returnproddetbyid($return_prod_id)
	{
		return $this->db->query("select a.imei_no,a.barcode,a.is_shipped,readytoship,is_stocked,is_refunded,b.invoice_no,a.return_id,a.id as return_prod_id,qty,a.product_id,c.product_name,d.mrp-d.discount as price  
													from pnh_invoice_returns_product_link a
													join pnh_invoice_returns b on a.return_id = b.return_id 
													join m_product_info c on c.product_id = a.product_id 
													join king_invoice d on a.order_id = d.order_id
													where a.id = ? order by a.id desc limit 1 ",$return_prod_id)->row_array();
	}
	
	/**
	 * Process to create bulk PO request by File 
	 *
	 */
	function do_po_byfile()
	{
		
		$user = $this->erpm->auth(PURCHASE_ORDER_ROLE);
		if(!$_FILES['po_file']['name'])
			show_error('Please choose file');
			
		// check if the file is valid csv 
		if($_FILES['po_file']['type'] != 'application/vnd.ms-excel') 
			show_error('Invalid File format');

		// process the file for creating PO.
		
		$po_list = array();	
		$tmpfname = $_FILES['po_file']['tmp_name'];
		$fp = fopen($tmpfname,'r');
		$i = 0;
		while(!feof($fp))
		{
			$csv_data = fgetcsv($fp);
			if(!$i++)
				continue;
			if(!$csv_data[0])
				continue ;
				
			if(!isset($po_list[$csv_data[0]]))
				$po_list[$csv_data[0]] = array();
				
			if(!isset($po_list[$csv_data[0]][$csv_data[1]]))
				$po_list[$csv_data[0]][$csv_data[1]] = 0;	

			$po_list[$csv_data[0]][$csv_data[1]] += $csv_data[3];
			
		}
		fclose($fp);
		
		
		if($po_list)
		{
			$total_po = 0;
			foreach ($po_list as $vid=>$po_prod)
			{
				// create po entry
				$inp = array($vid,$user['userid']);
				$this->db->query("insert into t_po_info (vendor_id,remarks,po_status,created_by,created_on) values (?,'Created from file',1,?,now()) ",$inp);
				$po_id = $this->db->insert_id();
				$total=0; 
				foreach ($po_prod as $pid=>$qty)
				{
					$prod_det = $this->db->query("select a.product_id,a.mrp,b.brand_margin as margin,round(a.mrp-(a.mrp*b.brand_margin/100),2) as price from m_product_info a join m_vendor_brand_link b on a.brand_id = b.brand_id where vendor_id = ? and product_id = ? ",array($vid,$pid))->row_array();
					if($prod_det)
					{
						// create po prod request entry
						$inp=array($po_id,$pid,$qty,$prod_det['mrp'],$prod_det['margin'],0,1,$prod_det['price'],0,0,'');
						$this->db->query("insert into t_po_product_link(po_id,product_id,order_qty,mrp,margin,scheme_discount_value,scheme_discount_type,purchase_price,is_foc,has_offer,special_note,created_on) values(?,?,?,?,?,?,?,?,?,?,?,now())",$inp);
						$total+=$prod_det['price']*$qty;	
					}
				}
				
				$this->db->query("update t_po_info set total_value=? where po_id=? limit 1",array($total,$po_id));
				
				$total_po++; 
			}
			
			$this->session->set_flashdata("erp_pop_info",$total_po." PO's created");
			redirect("admin/bulk_createpo_byfile",'refresh');
			
		}else
		{
			show_error("Uploaded file is Blank or not in format");
		}
			
		exit;
	}
	
	/**
	 * function to get frachise pending balance
	 *
	 * @param unknown_type $fr_id
	 */
	function get_franchise_payment_details($fr_id)
	{
		$data = array();
		$data['ordered_tilldate'] = @$this->db->query("select round(sum((i_orgprice-(i_coup_discount+i_discount))*b.quantity),2) as amt  
		from king_transactions a 
		join king_orders b on a.transid = b.transid 
	        join pnh_m_franchise_info c on c.franchise_id = a.franchise_id 
		where a.franchise_id = ? ",$fr_id)->row()->amt;
		
		$data['shipped_tilldate'] = @$this->db->query("SELECT round(sum((i_orgprice-(i_coup_discount+i_discount))*b.quantity),2) as amt 
														FROM king_transactions a
														JOIN king_orders b ON a.transid = b.transid
														JOIN pnh_m_franchise_info c ON c.franchise_id = a.franchise_id
														JOIN king_invoice d ON d.order_id = b.id
														JOIN shipment_batch_process_invoice_link e ON e.invoice_no = d.invoice_no
														AND e.shipped =1 AND e.packed =1
														WHERE a.franchise_id = ? AND d.invoice_status = 1 and b.status != 0 
								 ",$fr_id)->row()->amt;
		$data['cancelled_tilldate'] = $data['ordered_tilldate']-$data['shipped_tilldate'];
		
		$data['paid_tilldate'] = @$this->db->query("select sum(receipt_amount) as amt from pnh_t_receipt_info where receipt_type = 1 and status = 1 and franchise_id = ? ",$fr_id)->row()->amt;
		$data['uncleared_payment'] = @$this->db->query("select sum(receipt_amount) as amt from pnh_t_receipt_info where status = 0 and franchise_id = ? ",$fr_id)->row()->amt;
		
		$data['pending_payment'] = $data['shipped_tilldate']-$data['paid_tilldate'];
		return $data;
	}

	/**
	 * function for bulk pnh deals update
	 */
	function do_pnh_deals_bulk_update()
	{
		$report=array();
		$updated_items=array();
		$global_update_flag=0;
	
		$f=@fopen($_FILES['deals']['tmp_name'],"r");
		$head=fgetcsv($f);
		$template=array("Item id","Deal Name","Category (ID)","Brand (ID)","Menu (ID)","Description");
		$arr_diff=array_diff($head,$template);
	
		if(empty($head) || count($head)!=count($template) ||count($arr_diff)!=0)
			show_error("Invalid template structure");
	
		$this->load->plugin('csv_logger');
		$csv_obj=new csv_logger_pi();
		$csv_obj->head(array("Item ID","Deal ID","Name","Brand Id","Category Id","Menu Id","Description","Status"));
	
		while(($data=fgetcsv($f))!=false)
		{
			$item_id=$data[0];
			$name=trim($data[1]);
			$brandid=trim($data[3]);
			$catid=trim($data[2]);
			$menutid=trim($data[4]);
			$desc=trim(trim($data[5]));
			$status='Not Updated';
	
			if($this->db->query("select 1 from king_brands where id=? limit 1",$brandid)->num_rows()==0)
				show_error("No brand with brand id $brandid for deal $name");
			if($this->db->query("select 1 from king_categories where id=? limit 1",$catid)->num_rows()==0)
				show_error("No category with id $catid for deal $name");
			if($this->db->query("select 1 from pnh_menu where id=? limit 1",$menutid)->num_rows()==0)
				show_error("No Menu with id $menutid for deal $name");
	
			$sql="select d.name,d.dealid,c.catid,c.brandid,c.menuid,c.description
			from king_dealitems as d
			join king_deals as c on d.dealid=c.dealid
			where d.id=? and is_pnh=1;";
			$deal_det=$this->db->query($sql,$item_id)->result_array();
	
			$deal_id=$deal_det[0]['dealid'];
			$p_deal_name=$deal_det[0]['name'];
			$p_cat_id=$deal_det[0]['catid'];
			$p_brandid=$deal_det[0]['brandid'];
			$p_menuid=$deal_det[0]['menuid'];
			$p_desc=$deal_det[0]['description'];
	
			$update_quer_parma=array();
			$update_query_fields=array();
			$update_flag=0;
	
	
			$update_query="update king_dealitems as d
			join king_deals as c on d.dealid=c.dealid set";
	
			if($name!='' && $name!=$p_deal_name)
			{
				$update_query_fields[]=" d.name=? ";
				$update_quer_parma[]=$name;
				$update_flag=1;
				$status='Updated';
			}
	
			if($brandid!='' && $brandid!=$p_brandid)
			{
				$update_query_fields[]=" c.brandid=? ";
				$update_quer_parma[]=$brandid;
				$update_flag=1;
				$status='Updated';
			}
	
			if($catid!='' && $catid!=$p_cat_id)
			{
				$update_query_fields[]=" c.catid=? ";
				$update_quer_parma[]=$catid;
				$update_flag=1;
				$status='Updated';
			}
	
			if($menutid!='' && $menutid!=$p_menuid)
			{
				$update_query_fields[]=" c.menuid=? ";
				$update_quer_parma[]=$menutid;
				$update_flag=1;
				$status='Updated';
			}
			
			if($desc!='' && $desc!= $p_desc)
			{
				$update_query_fields[]=" c.description=? ";
				$update_quer_parma[]=$desc;
				$update_flag=1;
				$status='Updated';
			}
	
	
			$update_query.=implode(",",$update_query_fields)." where d.id=? and is_pnh=1;";
			$update_quer_parma[]=$item_id;
	
			if($update_flag)
			{
				$updated_items[]=array("item_id"=>$item_id,"old_name"=>$p_deal_name,"name"=>$name,"old_brandid"=>$p_brandid,"brandid"=>$brandid,"old_category_id"=>$p_cat_id,"catid"=>$catid,"old_menu_id"=>$p_menuid,"menuid"=>$menutid,"old_description"=>$p_desc,"Description"=>$desc);
				$this->db->query($update_query,$update_quer_parma);
				$global_update_flag=1;
			}
	
			$csv_obj->push(array($item_id,$deal_id,$name,$brandid,$catid,$menutid,$desc,$status));
			//$report[]=array($item_id,$deal_id,$name,$brandid,$catid,$menutid,$status);
	
		}
		fclose($f);
	
		if($global_update_flag)
		{
			$user=$this->erpm->getadminuser();
			$this->db->insert("m_deals_bulk_update",array("items"=>count($updated_items),"created_on"=>time(),"created_by"=>$user['userid'],"updated_data"=>json_encode($updated_items)));
		}
	
		$csv_obj->download('pnh_deals_bulk_update_report');
		redirect("admin/do_pnh_deals_bulk_update");
		exit;
	}
	
	function get_manifesto_sent_log($manifesto_id=0,$pg=0,$from_date=0,$to_date=0,$srch_invoice='')
	{
		$param=array();
		
		$sql="select ifnull(e.role_name,'Other') as role_type,b.name,a.id,a.is_printed,a.sent_invoices,a.manifesto_id,a.remark,
					c.name as driver_name,a.hndleby_name,a.sent_on,d.name as sent_by,c.contact_no,a.hndlby_roleid,a.hndleby_contactno,
					a.hndleby_empid,f.name as pick_up_by,f.contact_no as pick_up_by_contact
							from pnh_m_manifesto_sent_log a
							join pnh_manifesto_log b on a.manifesto_id=b.id
							left join m_employee_info c on a.hndleby_empid=c.employee_id
							left join king_admin d on a.created_by=d.id
							left join m_employee_roles e on a.hndlby_roleid = e.role_id
							left join m_employee_info f on f.employee_id=a.pickup_empid
							";
		
							
		if($manifesto_id)
		{
			$sql.=" where a.manifesto_id=?";
			$param[]=$manifesto_id;
		}
		
		if($from_date && $to_date)
		{
			$sql.=" where date(sent_on) >=? &&  date(sent_on) <= ?";
			$param[]=$from_date;
			$param[]=$to_date;
		}
		
		if($srch_invoice)
		{
			$sql.=" where sent_invoices like ? ";
			$param[]='%'.$srch_invoice.'%';
		}
		$sql.=' order by a.sent_on desc';
		
		if(!$manifesto_id)
			$sql.=" limit $pg , 10";
			
		$mainfesto_sent_det=$this->db->query($sql,$param)->result_array();
		return $mainfesto_sent_det;
		
	}
	
	
	function get_manifesto_sent_log_det($manifesto_id=0,$pg=0,$from_date=0,$to_date=0,$srch_invoice='',$status='',$mid=0,$m_ids_by_hub=array())
	{
		$param=array();
	
		$sql="select g.short_name as dest_shortname,ifnull(e.role_name,'Other') as role_type,b.name,a.id,a.is_printed,a.sent_invoices,a.manifesto_id,a.remark,
						c.name as driver_name,a.hndleby_name,a.sent_on,d.name as sent_by,c.contact_no,a.hndlby_roleid,a.hndleby_contactno,
						a.hndleby_empid,f.name as pick_up_by,f.contact_no as pick_up_by_contact,a.status,a.bus_id,a.bus_destination,
						a.hndleby_vehicle_num,a.start_meter_rate,a.amount,a.office_pickup_empid,a.pickup_empid,c.job_title2,a.lrno,a.hndlby_type,h.courier_name,a.hndleby_courier_id,a.modified_on,a.modified_by
				from pnh_m_manifesto_sent_log a
				join pnh_manifesto_log b on a.manifesto_id=b.id
				left join m_employee_info c on a.hndleby_empid=c.employee_id
				left join king_admin d on a.created_by=d.id
				left join m_employee_roles e on a.hndlby_roleid = e.role_id
				left join m_employee_info f on f.employee_id=a.pickup_empid
				left join pnh_transporter_dest_address g on g.id = a.bus_destination
				left join m_courier_info h on h.courier_id = a.hndleby_courier_id
				where 1 ";
	
	
		if($manifesto_id)
		{
			$sql.=" and a.manifesto_id=? ";
			$param[]=$manifesto_id;
		}
	
		if(($from_date && $to_date) && ($from_date!='0000-00-00' || $to_date!='0000-00-00'))
		{
			$sql.=" and (date(sent_on) >=? &&  date(sent_on) <= ?) ";
			$param[]=$from_date;
			$param[]=$to_date;
		}
	
		if($srch_invoice)
		{
			$sql.=" and sent_invoices like ? ";
			$param[]='%'.$srch_invoice.'%';
		}
		
		if($status)
		{
			$sql.=" and status = ? ";
			$param[]=$status;
		}
		
		if($mid)
		{
			$sql.=" and a.id = ? ";
			$param[]=$mid;
		}
		
		if($m_ids_by_hub)
		{
			$sql.=" and a.manifesto_id in (".implode(',',$m_ids_by_hub).")";
		}
		
		$sql.=' order by a.sent_on desc';
	
		if(!$manifesto_id)
			$sql.=" limit $pg , 10";
	
		$mainfesto_sent_det=$this->db->query($sql,$param)->result_array();
		
		return $mainfesto_sent_det;
	
	}
	
	
	/**
	 * get the territory name by invoices
	 * @param unknown_type $manifesto_log_id
	 */
	function get_terriory_name_by_invoice($manifesto_log_id)
	{
		$sql="select a.invoice_no,territory_id,territory_name,d.franchise_name,c.transid 
						from king_invoice a
						join king_orders b on a.order_id = b.id 
						join king_transactions c on c.transid = b.transid 
						join pnh_m_franchise_info d on d.franchise_id = c.franchise_id 
						join pnh_m_territory_info e on e.id = d.territory_id 
						where find_in_set(a.invoice_no,(select invoice_nos from pnh_manifesto_log where id = ?)) and is_pnh = 1 
						group by a.invoice_no 
						order by territory_name";
		
		$territories_det=$this->db->query($sql,$manifesto_log_id)->result_array();
		
		$territory_invoice_link=array();
		$franchise_invoice_link=array();
		$trans_id_invoice_link=array();
		foreach($territories_det as $territory)
		{
			$territory_invoice_link[$territory['invoice_no']]=$territory['territory_id'];
			$franchise_invoice_link[$territory['invoice_no']]=$territory['franchise_name'];
			$trans_id_invoice_link[$territory['invoice_no']]=$territory['transid'];
		}
		
	return array('territories_det'=>$territories_det,'territory_invoice_link'=>$territory_invoice_link,"franchise_invoice_link"=>$franchise_invoice_link,"trans_id_invoice_link"=>$trans_id_invoice_link);	
	}
	
	/**
	 * get the driver list
	 */
	function get_drivres_list()
	{
		//get the driver details
		$sql="select b.name,b.employee_id,b.contact_no from m_employee_roles as a
					join m_employee_info as b on a.role_id=b.job_title2
					where a.role_id=? and b.is_suspended=0";
		
		return $driver_details=$this->db->query($sql,array('7'))->result_array();
		
	}
	
	/**
	 * get the field cordinators list
	 */
	function get_field_cordinators_list()
	{
		//get the driver details
		$sql="select b.name,b.employee_id,b.contact_no  from m_employee_roles as a
					join m_employee_info as b on a.role_id=b.job_title2
					where a.role_id=? and b.is_suspended=0";
		
		return $this->db->query($sql,array('6'))->result_array();
	} 
	/**
	 * get manifesto sent invoices det
	 */
	function get_manifesto_send_invoices_det($manifesto_log_id)
	{
		//$invoices_nos_det=$this->db->query("select invoice_nos from pnh_manifesto_log where id=?",$manifesto_log_id)->result_array();
		$invoices_nos_query="select a.invoice_nos,GROUP_CONCAT(b.sent_invoices) as sent_invoices from pnh_manifesto_log  as a
									left join pnh_m_manifesto_sent_log as b on a.id=b.manifesto_id
									where a.id=?
									group by a.id;";
		
		return $invoices_nos_det=$this->db->query($invoices_nos_query,$manifesto_log_id)->result_array();
	}
	
	function get_manifesto_sen_invoices_det_by_emp($emp_id,$pg,$from_date='',$to_date='')
	{
		$cond='';
		$param=array();
		if($from_date && $to_date)
		{
			$cond=' date(b.sent_on) >=? && date(b.sent_on) <=? and ';
			$param[]=$from_date;
			$param[]=$to_date;
		}
		$param[]=$emp_id;
		
		/*$sql1="select count(*) as total from m_employee_info a
					join pnh_m_manifesto_sent_log b on a.employee_id=b.hndleby_empid
					join pnh_manifesto_log c on c.id=b.manifesto_id
					where $cond b.hndleby_empid=?";
		
		$total_sent_invoices=$this->db->query($sql1,$param)->result_array();
		
		
		$sql="select c.name,b.* from m_employee_info a
					join pnh_m_manifesto_sent_log b on a.employee_id=b.hndleby_empid
					join pnh_manifesto_log c on c.id=b.manifesto_id
					where $cond b.hndleby_empid=?
					limit $pg , 5";*/
		
		$sql="select c.name,b.*,group_concat(distinct d.invoice_no) as invoices,i.territory_name,h.town_name from m_employee_info a
							join pnh_m_manifesto_sent_log b on a.employee_id=b.hndleby_empid
							join pnh_manifesto_log c on c.id=b.manifesto_id
							join king_invoice d on find_in_set(d.invoice_no,b.sent_invoices) and invoice_status=1
							join king_transactions e on e.transid=d.transid
							join pnh_m_franchise_info f on f.franchise_id=e.franchise_id
							join pnh_m_territory_info i on i.id=f.territory_id
							join pnh_towns h on h.id=f.town_id
							where $cond b.hndleby_empid = ?
					group by i.id,h.id";
		
		$total_sent_invoices=$this->db->query($sql,$param)->result_array();
		$sql1=$sql." limit $pg , 5";
		$sent_invoices=$this->db->query($sql1,$param)->result_array();
		
		return array('total_sent_invoices'=>count($total_sent_invoices),'sent_invoices'=>$sent_invoices);
	}
	
	//executive to system
	function get_paid_sms_details($emp_id)
	{
		$sql="SELECT a.id AS log_id,b.contact_no,a.msg,a.remarks,a.reciept_status,b.name,b.name AS employee_name,c.name AS updatedby_name,DATE_FORMAT(a.updated_on,'%d/%m/%y %h:%i %p') AS updated_on,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on
				FROM pnh_executive_accounts_log a
				JOIN m_employee_info b ON b.employee_id=a.emp_id
				LEFT JOIN king_admin c ON c.id=a.updated_by
				WHERE `type`='paid' AND a.emp_id=?
				GROUP BY a.id
				ORDER BY a.logged_on DESC";
		$paid_details=$this->db->query($sql,$emp_id);
		if($paid_details->num_rows())
		{
			return $paid_details->result_array();
		}else 
			
			return false;
	}
	/**
	 * 
	 * @param unknown_type $emp_id
	 * @return boolean
	 */
	function get_new_sms_details($emp_id)
	{
		$sql="SELECT a.id AS log_id,b.contact_no,a.msg,a.remarks,a.reciept_status,b.name,b.name AS employee_name,c.name AS updatedby_name,DATE_FORMAT(a.updated_on,'%d/%m/%y %h:%i %p') AS updated_on,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on
				FROM pnh_executive_accounts_log a
				JOIN m_employee_info b ON b.employee_id=a.emp_id
				LEFT JOIN king_admin c ON c.id=a.updated_by
				WHERE `type`='new' AND a.emp_id=?
				GROUP BY a.id
				ORDER BY a.logged_on DESC";
		$paid_details=$this->db->query($sql,$emp_id);
		if($paid_details->num_rows())
		{
			return $paid_details->result_array();
		}else
				
			return false;
	}
	/**
	 * 
	 * @param unknown_type $emp_id
	 * @return boolean
	 */
	function get_existingfransms_details($emp_id)
	{
		$sql="SELECT a.id AS log_id,b.contact_no,a.msg,a.remarks,a.reciept_status,b.name,b.name AS employee_name,c.name AS updatedby_name,DATE_FORMAT(a.updated_on,'%d/%m/%y %h:%i %p') AS updated_on,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on
				FROM pnh_executive_accounts_log a
				JOIN m_employee_info b ON b.employee_id=a.emp_id
				LEFT JOIN king_admin c ON c.id=a.updated_by
				WHERE `type`='existing' AND a.emp_id=?
				GROUP BY a.id
				ORDER BY a.logged_on DESC";
		$existingfran_details=$this->db->query($sql,$emp_id);
		if($existingfran_details->num_rows())
		{
			return $existingfran_details->result_array();
		}else
	
			return false;
	}
	
	/* function get_delivered_invsms_details($emp_id)
	{
		$sql="SELECT invoice_no,b.franchise_name,c.name,d.town_name,t.territory_name,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on,c.contact_no 
					FROM sms_invoice_log a
					JOIN pnh_m_franchise_info b ON b.franchise_id=a.fid
					JOIN m_employee_info c ON c.employee_id=a.emp_id1
					left JOIN pnh_towns d ON d.id=b.town_id
					JOIN pnh_m_territory_info t ON t.id=b.territory_id
					WHERE `type`=1 AND a.emp_id1=?
					GROUP BY a.id
					ORDER BY a.logged_on DESC";
		$delivered_invoice_details=$this->db->query($sql,$emp_id);
		if($delivered_invoice_details->num_rows())
		{
			return $delivered_invoice_details->result_array();
		}else
			return false;
	} */
	
	function get_returned_invsms_details($emp_id)
	{
		$sql="SELECT invoice_no,b.franchise_name,c.name,d.town_name,t.territory_name,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on,c.contact_no
				FROM sms_invoice_log a
				JOIN pnh_m_franchise_info b ON b.franchise_id=a.fid
				JOIN m_employee_info c ON c.employee_id=a.emp_id1
				left JOIN pnh_towns d ON d.id=b.town_id
				JOIN pnh_m_territory_info t ON t.id=b.territory_id
				WHERE `type`=2 AND a.emp_id1=?
				GROUP BY a.id
				ORDER BY a.logged_on DESC";
			$returned_invoice_details=$this->db->query($sql,$emp_id);
			if($returned_invoice_details)
			{
				return $returned_invoice_details -> result_array();
			}else 
				return false;
		
	}
	
	 function get_task_remarks_sms($emp_id)
	{
		$sql="SELECT b.task_type,a.task_id AS ref_id,a.remarks,d.name AS logged_by,DATE_FORMAT(a.logged_on,'%d/%m/%y %h:%i %p') AS logged_on,d.contact_no,f.town_name,t.territory_name,b.id AS task_id,b.comments
				FROM pnh_task_remarks a
				JOIN pnh_m_task_info b ON b.ref_no=a.task_id
				JOIN m_employee_info d ON d.employee_id=a.emp_id
				JOIN m_town_territory_link e ON e.employee_id=a.emp_id
				left JOIN pnh_towns f ON f.id=b.asgnd_town_id
				JOIN pnh_m_territory_info t ON t.id=e.territory_id
				WHERE 1 AND a.logged_by=?
				GROUP BY a.id
				ORDER BY a.logged_on DESC";
		$task_sms=$this->db->query($sql,$emp_id);
		if($task_sms->num_rows())
		{
			return $task_sms->result_array();
		}else 
			return false;
	} 
	
	 function erpSms_2emp($emp_id)
	{
		$sql="SELECT l.*,CONCAT(f.name,', ',f.city) AS employee 
				FROM pnh_sms_log_sent l
				JOIN m_employee_info f ON f.contact_no=l.to
				WHERE f.employee_id=?";
		$start_day_sms=$this->db->query($sql,$emp_id);
		if($start_day_sms->num_rows())
		{
			return $start_day_sms->result_array();
		}else 
			return false;
	
	}
	
	function sms_fran()
	{
		$SQL="SELECT o.reply_for,CONCAT(f.franchise_name,', ',f.city) AS franchise,l.franchise_id,l.sender AS `from`,l.msg AS input,o.msg AS reply,l.created_on,o.created_on AS reply_on
				FROM pnh_sms_log l
				JOIN pnh_m_franchise_info f ON f.franchise_id=l.franchise_id
				LEFT OUTER JOIN pnh_sms_log o ON o.reply_for=l.id
				WHERE  1 ";
		$erpsms_fran1=$this->db->QUERY($SQL);
		if($erpsms_fran1->num_rows())
		{
			return $erpsms_fran1->result_array();
		}else
		return FALSE;
	
	}
	
	
	//  SMS Sent from erp to PNH Employee
	
	function paymentcollection_exec($emp_id)
	{
		$sql="SELECT a.contact_no,a.emp_id,b.name,grp_msg,DATE_FORMAT(a.created_on,'%d/%m/%Y %h:%i %p') AS sent_on
				FROM pnh_employee_grpsms_log a
				JOIN  m_employee_info b ON b.employee_id=a.emp_id
				WHERE `type`=1 and emp_id=?";
		$payment_details=$this->db->query($sql,$emp_id);
		if($payment_details->num_rows())
			return $payment_details->result_array();
		else 
			return false;
	}
	
	function task_remaindersms($emp_id)
	{
		$sql="SELECT a.contact_no,a.emp_id,b.name,grp_msg,DATE_FORMAT(a.created_on,'%d/%m/%Y %h:%i %p') AS sent_on
				FROM pnh_employee_grpsms_log a
				JOIN  m_employee_info b ON b.employee_id=a.emp_id
				WHERE `type`=2  and emp_id=?
				order by a.id desc";
		$task_remainder=$this->db->query($sql,$emp_id);
		if($task_remainder->num_rows())
		{
			return $task_remainder->result_array();
		}else 
			return false;
	}
	// SMS Sent from erp to PNH Employee *END*
	
	
	//SMS Sent from erp to franchisee
	function fran_currbalsms()
	{
		$sql="SELECT to,msg,a.franchise_id,b.franchise_name,DATE_FORMAT((FROM_UNIXTIME(a.sent_on)),'%d/%m/%Y %h:%i %p') AS sent_on
				FROM `pnh_sms_log_sent`a
				JOIN `pnh_m_franchise_info`b ON b.franchise_id=a.franchise_id
				ORDER BY sent_on DESC";
		$curr_balsms=$this->db->query($sql);
		if($curr_balsms->num_rows())
		{
			return $curr_balsms->result_array();
		}else 
			return false;
	}
	//SMS Sent from erp to franchisee *END*
	
	//End day SMS from ERP to Executive
	function end_dysms()
	{
		$sql="SELECT a.contact_no,a.emp_id,b.name,grp_msg,DATE_FORMAT(a.created_on,'%d/%m/%Y %h:%i %p') AS sent_on
						FROM pnh_employee_grpsms_log a
						JOIN m_employee_info b ON b.employee_id=a.emp_id
						WHERE `type`=3
						order by a.id desc";
		$end_dysms=$this->db->query($sql);
		if($end_dysms->num_rows())
		{
			return $end_dysms->result_array();
		}else 
			return false;
	}
	
	
	/**
	 * function to generate sales report by given start and end dates 
	 */
	function do_gen_salesreportfortally($st,$en,$sales_by)
	{
		ini_set('memory_limit','512M');
		
		$cond = '';
		if($sales_by == 'sit')
			$cond .= ' and is_pnh = 0 and partner_id = 0 ';
		else if($sales_by == 'sit_part')
			$cond .= ' and is_pnh = 0  ';
		else if($sales_by == 'pnh')
			$cond .= ' and is_pnh = 1 ';
		
		$sql = "select c.createdon,a.transid,b.ship_person,ifnull(d.pnh_franchise_id,0) as pnh_franchise_id,c.invoice_no,
						sum(b.quantity*c.mrp) as mrp,
						sum(b.quantity*c.discount) as discount,c.tax,c.service_tax,sum(c.ship+c.giftwrap_charge) as handling_charge
					from king_transactions a   
					join king_orders b on a.transid = b.transid    
					join king_invoice c on c.order_id = b.id    
					left join pnh_m_franchise_info d on d.franchise_id = a.franchise_id 
					where date(from_unixtime(c.createdon)) >= ? and date(from_unixtime(c.createdon)) <= ? and c.invoice_status = 1 $cond  
					group by c.invoice_no,c.tax     
					order by c.createdon asc 
					";
		$res = $this->db->query($sql,array($st,$en));	
		$inv_gross_total = array();		
		if($res->num_rows())
		{
			$tax_list = array('1450'=>1,'550'=>1,'0'=>1);
			$tax_list_tax = array('1450'=>1,'550'=>1,'0'=>1);
			$tax_list_sales = array('1450'=>1,'550'=>1,'0'=>1);
			$tax_list_disc = array('550'=>1,'1450'=>1,'0'=>1);
			$tax_list_op_vat = array('1450'=>1,'550'=>1);
			
			$sales_det = array();
			foreach($res->result_array() as $row)
			{
				if(!isset($sales_det[$row['invoice_no']]))
					$sales_det[$row['invoice_no']] = array();
				if(!isset($sales_det[$row['invoice_no']][$row['tax']]))
					$sales_det[$row['invoice_no']][$row['tax']] = array();
				
				array_push($sales_det[$row['invoice_no']][$row['tax']],$row);
				if($row['tax'])
					$tax_list[$row['tax']] = 1;
				
				if(!isset($sales_inv_det[$row['invoice_no']]))	
				{
					$sales_inv_det[$row['invoice_no']] = array();
					$sales_inv_det[$row['invoice_no']]['date'] = date('d m Y',$row['createdon']);
					$sales_inv_det[$row['invoice_no']]['invno'] = $row['invoice_no'];
					$sales_inv_det[$row['invoice_no']]['partyname'] = $row['ship_person'].($row['pnh_franchise_id']?'-'.$row['pnh_franchise_id']:'');
					
					$sales_inv_det[$row['invoice_no']]['billing_price'] = ($row['mrp']-$row['discount']);
					
					$sales_inv_det[$row['invoice_no']]['hand_charge'] = $row['handling_charge'];
					$sales_inv_det[$row['invoice_no']]['serv_tax'] = $row['handling_charge']*($row['service_tax']/10000);
					
					$sales_inv_det[$row['invoice_no']]['vchtype'] = 'Sales PNH';
					
					$sales_inv_det[$row['invoice_no']]['round_off_part'] = 0;
					//$sales_inv_det[$row['invoice_no']]['sales_exempt'] = 0;
					
					$sales_inv_det[$row['invoice_no']]['disc_sum'] = 0;
					
					
					$sales_inv_det[$row['invoice_no']]['gross_total'] = ($sales_inv_det[$row['invoice_no']]['hand_charge']+$sales_inv_det[$row['invoice_no']]['serv_tax']);
					
				}
				
				$tax = $row['tax'];
				
				$disc_amt =  round(($row['discount']*($row['tax']/10000)),2);
				
				$disc = round($row['discount'],2);
				$tax_amt =  round(($row['mrp']-$row['discount'])/(1/1+($row['tax']/10000)),2);
				
				$op_vat_amt =  round(($tax_amt*($row['tax']/10000)),2); 
				$sales_amt =  round($tax_amt+$disc,2);
				
				//$sales_inv_det[$row['invoice_no']]['sales_exempt'] += $tax?0:($sales_amt+$disc);
				
				$sales_inv_det[$row['invoice_no']]['discount_'.$tax] = $disc;
				$sales_inv_det[$row['invoice_no']]['tax_'.$tax] = $tax_amt;	
				$sales_inv_det[$row['invoice_no']]['sales_'.$tax] = $sales_amt;
				$sales_inv_det[$row['invoice_no']]['op_vat_'.$tax] = $op_vat_amt;

				$sales_inv_det[$row['invoice_no']]['gross_total'] += ($row['mrp']-$row['discount']);
				
				
				$sales_inv_det[$row['invoice_no']]['disc_sum'] +=  $disc;
					
				
				$sales_inv_det[$row['invoice_no']]['round_off_part'] += ($sales_amt)+$op_vat_amt;
				
					
			}
			
			$op_head = array();
			$op_head['date'] = "Date";
			$op_head['invno'] = "Inv No";
			$op_head['partyname'] = "Party Name";
			$op_head['gross_total'] = "Gross Total";
			
			foreach ($tax_list_tax as $tax_v=>$stat) 
				$op_head['tax_'.$tax_v] = $tax_v?"Taxable Amount ".round($tax_v/100,1)."%":'AssesVal Sales-Exempt';
						
			foreach ($tax_list_sales as $tax_v=>$stat) 
				$op_head['sales_'.$tax_v] = $tax_v?"Sales ".round($tax_v/100,1)."%":'Sales Exempt';
			
			//$op_head['sales_exempt'] = "Sales Exempt";
			
			foreach ($tax_list_disc as $tax_v=>$stat) 
				$op_head['discount_'.$tax_v] = "Discount @ ".round($tax_v/100,1)."%";
				
			
			$op_head['hand_charge'] = "Handling Charges";
			$op_head['serv_tax'] = "Service tax";
			
			foreach ($tax_list_op_vat as $tax_v=>$stat) 
				if($tax_v)
					$op_head['op_vat_'.$tax_v] = "Output vat ".round($tax_v/100,1)."%";
			
			$op_head['round_off'] = "Round off";
			$op_head['vchtype'] = "VchType";
			
			
			$csv_data = array();
			$csv_data [] = '"'.implode('","',array_values($op_head)).'"';
			
			foreach ($sales_inv_det as $sale_inv) 
			{
				$sale_inv['gross_total'] = round($sale_inv['gross_total']);
				$sale_inv['round_off'] = (($sale_inv['round_off_part']+$sale_inv['hand_charge']+$sale_inv['serv_tax'])-($sale_inv['gross_total']+$sale_inv['disc_sum']));
							
				$t = array();
				foreach (array_keys($op_head) as $k) 
				{
					array_push($t,isset($sale_inv[$k])?$sale_inv[$k]:0);
				}	
				$csv_data[] = '"'.implode('","',array_values($t)).'"';
			}
			
			$csv = implode("\r\n",$csv_data);
			
			header('Content-Description: File Transfer');
		    header('Content-Type: text/csv');
		    header('Content-Disposition: attachment; filename='.("SalesReport-".$sales_by.'-'.date("d_m_y_H\h:i\m").".csv"));
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . strlen($csv));
		    
			echo $csv;
		}else
		{
			echo '<script>alert("No Invoices found")</script>'; 		
		}
		
		exit;
	}
	
	/**
	 * get the details of pnh deal update log
	 * @param unknown_type $itemid
	 */
	function get_pnh_deal_update_log($itemid)
	{
		$sql="select a.*,b.product_name,c.username from t_upd_product_deal_link_log as a 
					join m_product_info as b on b.product_id=a.product_id
					join king_admin c on c.id=a.perform_by
					where a.itemid=?
					order by a.perform_on desc;";
		
		return $this->db->query($sql,$itemid)->result_Array();
	}
	
	function get_executives_by_territroy($territory_id,$terr_ids=0)
	{
		/*$sql="select a.name,job_title,job_title2
					from m_employee_info a 
					join m_employee_roles b on b.role_id=a.job_title2
					join m_town_territory_link c on c.employee_id=a.employee_id and c.territory_id = ?
					where (a.job_title = 5 and a.job_title2 = 0) or (a.job_title = 5 and a.job_title2 = 6) ";*/
		
		
		$sql="(select a.employee_id,a.name,'BE' as role_name 
									from m_employee_info a 
									join m_town_territory_link c on c.employee_id=a.employee_id and is_suspended = 0   
									where 1 ";
		if($territory_id)
			$sql.=" and c.territory_id = ? ";
		if($terr_ids)
			$sql.=" and c.territory_id in ($terr_ids) ";
		
		$sql.=" and c.is_active = 1 and a.job_title = 5 and a.job_title2 = 5 ) 
			union	
					(select a.employee_id,a.name,'FC' as role_name 
									from m_employee_info a 
									join m_town_territory_link b on b.employee_id=a.employee_id and is_suspended = 0  
									where a.job_title = 5 and a.job_title2 = 6 ) 
									order by role_name 
				";
		if($territory_id)
			return $this->db->query($sql,array($territory_id))->result_array();
		else
			return $this->db->query($sql)->result_array();
	}
	
	/**
	 * get the territory manager
	 * @param unknown_type $terr_ids
	 * @return unknown
	 */
	function get_territory_manager($terr_ids)
	{
		$territory_manager=$this->db->query("select a.employee_id,a.name,'Tm' as role_name 
								from m_employee_info a 
								join m_town_territory_link c on c.employee_id=a.employee_id and is_active = 1   
								where 1  and c.territory_id in ($terr_ids) and c.is_active = 1 and a.job_title2 = 4 and a.is_suspended = 0 " )->result_array();
		
		
		return $territory_manager;
		
	}
	
	/**
	 * get the pnh unshiped shipments by tray;
	 * @return unknown
	 */
	function get_pnh_unshiped_inv_by_tray()
	{
		$outcan_summdet=array();
		$outcan_summdet['outscan_summ']='';
		$outcan_summdet['ttl_penpak']='';
		/*$sql="select a.invoice_no,a.tray_id,b.tray_name,count(*) as ttl from shipment_batch_process_invoice_link a
					join m_tray_info b on b.tray_id=a.tray_id
     			where a.packed=1 and shipped=0
     			group by a.tray_id";*/
		
		//get pached scan summary with tray
		$sql="select a.tray_id,a.tray_name,a.max_allowed,c.hub_name as territory_name,count(*) as total_shipments from m_tray_info a 
								join pnh_t_tray_territory_link b on b.tray_id=a.tray_id
								join pnh_deliveryhub c on c.id=b.territory_id
								join pnh_t_tray_invoice_link d on d.tray_terr_id=b.tray_terr_id
					where b.is_active=1 and d.status=1
					group by b.tray_terr_id
					order by a.tray_id";
		
		
		$outcan_summdet['outscan_summ']=$this->db->query($sql)->result_array();
		
		//get total of pending for packing
		$sql2="select count(distinct a.invoice_no) as total_penpacking 
							from shipment_batch_process_invoice_link a 
							join king_invoice b on b.invoice_no=a.invoice_no and b.invoice_status=1 
							join king_transactions c on c.transid=b.transid 
			where c.is_pnh=1 and a.packed=0 and inv_manifesto_id=0";
		
		$outcan_summdet['ttl_penpak']=$this->db->query($sql2)->result_array();
		
		//get invoices of pending for packing
		$sql3="select group_concat(distinct a.invoice_no) as inv,e.territory_name,f.town_name
						from shipment_batch_process_invoice_link a 
						join king_invoice b on b.invoice_no=a.invoice_no and b.invoice_status=1 
						join king_transactions c on c.transid=b.transid
						join pnh_m_franchise_info d on d.franchise_id=c.franchise_id
						join pnh_m_territory_info e on e.id=d.territory_id
						join pnh_towns f on f.id=d.town_id
					where c.is_pnh=1 and a.packed=0 and inv_manifesto_id=0
					group by d.territory_id,d.town_id
					order by e.territory_name,f.town_name";
		
		$outcan_summdet['invoices']=$this->db->query($sql3)->result_array();
		
		
		//to check trays are full or not
		$sql4="select a.tray_name,if(b.is_active,1,0) as status from m_tray_info a
					left join pnh_t_tray_territory_link b on b.tray_id=a.tray_id and b.is_active=1
					group by status";
		
		$outcan_summdet['to_check_trays']=$this->db->query($sql4)->result_array();
		
		return  $outcan_summdet;
	}
	
	/**
	 * function for insert the sms log data to group sms log table
	 */
	function insert_pnh_employee_grpsms_log($param)
	{
		$this->db->insert("pnh_employee_grpsms_log",$param);
	}
	
	function get_shipements_update_sms($param)
	{
		$sms=@$this->db->query("select grp_msg from pnh_employee_grpsms_log where emp_id=? and type=? order by created_on desc limit 1",$param)->row()->grp_msg;
		return $sms;
	}
	
	function get_shipements_update_sms_from($param)
	{
		$sms=@$this->db->query("select * from pnh_employee_grpsms_log where emp_id=? and type=? order by created_on desc limit 1",$param)->row_array();
		return $sms;
	}
	
	/**
	 * function to get franchise account statement summary details 
	 */
	function get_franchise_account_stat_byid($fid)
	{
		$det = array();
		
		$ordered_tilldate = @$this->db->query("select round(sum((i_orgprice-(i_coup_discount+i_discount))*b.quantity),2) as amt  
			from king_transactions a 
			join king_orders b on a.transid = b.transid 
		        join pnh_m_franchise_info c on c.franchise_id = a.franchise_id 
			where a.franchise_id = ? ",$fid)->row()->amt;
			
			
			
		$not_shipped_amount = $this->db->query(" select sum(t) as amt from (
											select a.invoice_no,debit_amt as t 
												from pnh_franchise_account_summary a 
												join king_invoice c on c.invoice_no = a.invoice_no and invoice_status =  1 
												where action_type = 1 
												and franchise_id = ?  
											group by a.invoice_no ) as a 
											join shipment_batch_process_invoice_link b on a.invoice_no = b.invoice_no and shipped = 0  ",$fid)->row()->amt;
				
						
		$total_invoice_val = $this->db->query("select sum(debit_amt) as amt from pnh_franchise_account_summary where action_type = 1 and franchise_id = ? ",$fid)->row()->amt;
		$total_invoice_cancelled_val = $this->db->query("select sum(credit_amt) as amt from pnh_franchise_account_summary where action_type = 1 and franchise_id = ? ",$fid)->row()->amt;
			
			$sql = "select sum(credit_amt) as amt   
						from pnh_franchise_account_summary a
						join pnh_t_receipt_info b on a.receipt_id = b.receipt_id 
						where action_type = 3 and a.franchise_id = ? and a.receipt_type = 1 and a.status = 1 and b.status = 1 
					";
		$total_active_receipts_val = $this->db->query($sql,array($fid))->row()->amt ;
			
			$sql = "select sum(receipt_amount) as amt  
							from pnh_t_receipt_info 
							where franchise_id = ? 
							and status in (2,3) and is_active = 1   
					";
					 
		$total_cancelled_receipts_val = $this->db->query($sql,array($fid))->row()->amt;
			
		$sql = "select sum(receipt_amount) as amt 
							from pnh_t_receipt_info 
							where franchise_id = ? and status = 0 and receipt_type = 1  
					";
					 
		$total_pending_receipts_val = $this->db->query($sql,array($fid))->row()->amt;
			
		$sql = "select sum(credit_amt-debit_amt) as amt  
						from pnh_franchise_account_summary where action_type = 5 and franchise_id = ? ";
		$acc_adjustments_val = $this->db->query($sql,array($fid))->row()->amt;
		
		/*
		$sql = "select sum(credit_amt-debit_amt) as amt  
							from pnh_franchise_account_summary where action_type = 7 and franchise_id = ? ";
		$ttl_credit_note_val = $this->db->query($sql,array($fid))->row()->amt;
		 */ 
		
		$sql = "select (sum(credit_amt)-sum(debit_amt)) as amt from ((
select statement_id,type,count(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,concat('Total ',count(a.invoice_no),' IMEI Activations') as remarks 
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.credit_note_id = b.id 
		where action_type = 7 and type = 2 
		and a.franchise_id = ?  
		 
	group by action_date 	
)
union
(
select statement_id,1 as type,(a.invoice_no) as invoice_no,sum(credit_amt) as credit_amt,sum(debit_amt) as debit_amt,date(a.created_on) as action_date,remarks
		from pnh_franchise_account_summary a 
		join t_invoice_credit_notes b on a.invoice_no = b.invoice_no 
		where action_type = 7 and type = 1   
		and a.franchise_id = ?   
		 
	 group by statement_id 
)
) as g 
order by action_date";
		$ttl_credit_note_val = $this->db->query($sql,array($fid,$fid))->row()->amt; 
		
		$total_active_invoiced = ($total_invoice_val-$total_invoice_cancelled_val);
		
		$data['net_payable_amt'] = ($total_active_invoiced-($total_active_receipts_val+$acc_adjustments_val+$ttl_credit_note_val));
		
		$data['shipped_tilldate'] = $total_active_invoiced-$not_shipped_amount; 
		$data['credit_note_amt']  = $ttl_credit_note_val;
		$data['paid_tilldate']  = $total_active_receipts_val;
		$data['uncleared_payment']  = $total_pending_receipts_val;
		$data['cancelled_tilldate'] = $total_cancelled_receipts_val;
		$data['ordered_tilldate'] = $ordered_tilldate;
		$data['not_shipped_amount'] = $not_shipped_amount;
		$data['acc_adjustments_val'] = $acc_adjustments_val;
		$data['current_balance'] = ($data['shipped_tilldate']-$data['paid_tilldate']+$data['acc_adjustments_val']-$data['credit_note_amt'])*-1;
		
		$payment_pen = ($data['shipped_tilldate']-$data['paid_tilldate']+$data['acc_adjustments_val']);
		$data['pending_payment'] = ($payment_pen<0)?0:$payment_pen;
			
		return $data;	
	}
	
	function to_get_all_franchise(){
		return $this->db->query("select * from pnh_m_franchise_info where is_suspended=0")->result_array();
	}

	function get_fran_availcreditlimit($fr_id=0)
	{
		$fr_accdet = $this->erpm->get_franchise_account_stat_byid($fr_id);
		
		$max_credit_limit = $this->db->query('select credit_limit from pnh_m_franchise_info where franchise_id = ? ',$fr_id)->row()->credit_limit;	
		
		$pending_payment = ($fr_accdet['shipped_tilldate']-($fr_accdet['paid_tilldate']+$fr_accdet['acc_adjustments_val']+$fr_accdet['credit_note_amt']));
		$fr_open_order_amt = $this->db->query('select ifnull(sum(amt),0) as amt from (
												select ifnull(sum(a.i_orgprice-(a.i_coup_discount+a.i_discount))*a.quantity,0) as amt,sum(b.invoice_status) as is_invoiced 
													from king_orders a
													left join king_invoice b on a.id = b.order_id  
													join king_transactions c on c.transid = a.transid
													where c.franchise_id = ? and a.status in (0,1) 
													group by a.id 
													having is_invoiced = 0 ) as g ',$fr_id)->row()->amt;
													
													
		 
		return array($max_credit_limit,$pending_payment,$fr_open_order_amt,$max_credit_limit - $pending_payment - $fr_open_order_amt);
	}	
 	
	/**
	 * function for get territory manager and business excutives for specified town for  send sms for shipment notification
	 */
	function get_emp_by_territory_and_town($invoices)
	{
		$sql="(select distinct a.invoice_no,c.town_id,d.town_name,c.territory_id,f.territory_name,c.franchise_name,emp.name,emp.job_title2,emp.contact_no,emp.employee_id,emp.send_sms
					from king_invoice a
					join king_transactions b on b.transid = a.transid
					join pnh_m_franchise_info c on c.franchise_id = b.franchise_id
					join pnh_towns d on d.id=c.town_id 
					join pnh_m_territory_info f on f.id = c.territory_id
					join m_town_territory_link e on e.town_id=c.town_id and e.is_active=1
					join m_employee_info emp on emp.employee_id = e.employee_id and emp.job_title2 in (4,5)
				where a.invoice_no in ($invoices) and is_pnh = 1 and emp.is_suspended=0
				group by emp.employee_id
			)union(
				select distinct a.invoice_no,c.town_id,d.town_name,c.territory_id,f.territory_name,c.franchise_name,emp.name,emp.job_title2,emp.contact_no,emp.employee_id,emp.send_sms
					from king_invoice a
					join king_transactions b on b.transid = a.transid
					join pnh_m_franchise_info c on c.franchise_id = b.franchise_id
					join pnh_towns d on d.id=c.town_id 
					join pnh_m_territory_info f on f.id = c.territory_id
					join m_town_territory_link e on e.territory_id=c.territory_id and e.is_active=1
					join m_employee_info emp on emp.employee_id = e.employee_id and emp.job_title2 in (4)
				where a.invoice_no in ($invoices) and is_pnh = 1 and emp.is_suspended=0
				group by emp.employee_id
		)";
		
		return $this->db->query($sql)->result_array();
	}
	
	/**
	 * function for check manifesto deliverd
	 * @param unknown_type $manifesto_id
	 */
	function check_manifesto_delivered($manifesto_id=0)
	{
		if($manifesto_id)
		{
			$manifesto_det=$this->db->query("select * from pnh_m_manifesto_sent_log where id=?",$manifesto_id);
			if($manifesto_det->num_rows())
			{
				$manifesto_det=$manifesto_det->row_array();
				
				$transit_inv=$this->db->query("select invoice_no from pnh_invoice_transit_log where sent_log_id=? and status=3",$manifesto_id)->result_array();
				
				if($transit_inv)
				{
					$t_inv_list=array();
					foreach($transit_inv as $tinv)
					{
						$t_inv_list[]=$tinv['invoice_no'];
					}
					
					$m_inv=explode(',',$manifesto_det['sent_invoices']);
					
					$not_found=0;
					foreach($m_inv as $minv)
					{
						if(in_array($minv, $t_inv_list))
						{
							
						}else{
							$not_found=1;
						}
					}
					
					if(!$not_found)
						return true;
					else 
						return false;
				}
			}
		}
		
		return false;
	}
	
	function get_empcount_by_fids($fids)
	{
		$sql="SELECT a.employee_id,b.name,t.town_name,r.territory_name ,f.franchise_name,job_title2,b.contact_no
								FROM m_town_territory_link a
								JOIN m_employee_info b ON b.employee_id=a.employee_id
								JOIN `pnh_m_franchise_info` f ON f.territory_id=a.territory_id
								JOIN `pnh_m_territory_info`r ON r.id=f.territory_id
								LEFT JOIN pnh_towns t ON t.id=a.town_id
								WHERE f.is_suspended=0 AND a.is_active=1 AND b.is_suspended=0 AND b.job_title2 in (4,5) AND f.franchise_id in (".$fids.") 
								GROUP BY a.employee_id";
		$emp_count=$this->db->query($sql)->result_array();
		return $emp_count;
	}
	
	/**
	 * function for sent to franchise sms shipments notification
	 * @param unknown_type $transid
	 * @param unknown_type $d_total
	 */
	function sendsms_franchise_shipments($invoices='',$d_total=0)
	{
		
		$sql_trans = "select inv.invoice_no,a.id,e.franchise_id,a.itemid,group_concat(b.name) as itemname,concat(b.print_name,'-',b.pnh_id) as print_name,i_orgprice,login_mobile1,i_price,i_coup_discount,i_discount,group_concat(a.quantity) as qty,c.menuid,a.transid,f.franchise_id,f.franchise_name
							from king_invoice inv
							join king_orders a on a.id=inv.order_id
							join king_dealitems b on a.itemid = b.id
							join king_deals c on b.dealid = c.dealid 
							join pnh_menu d on d.id = c.menuid 
							join king_transactions e on e.transid = a.transid
							join pnh_m_franchise_info f on f.franchise_id = e.franchise_id 
							where inv.invoice_no in ($invoices)
							group by inv.invoice_no";
		
		$res_trans = $this->db->query($sql_trans);
	
		$sms_msg = '';
		$datetime = new DateTime('tomorrow');
		
		if($res_trans->num_rows())
		{
			
			$fran_shipment_sms = array();
			$fran_det = array();
			
			foreach($res_trans->result_array() as $row_trans)
			{
				
				if(!isset($fran_shipment_sms[$row_trans['franchise_id']]))
				{
					$fran_shipment_sms[$row_trans['franchise_id']] = array();
					$fran_det[$row_trans['franchise_id']] = array($row_trans['franchise_name'],$row_trans['login_mobile1']);
				}
					
					
				$fran_shipment_sms[$row_trans['franchise_id']][] = " invoice no(".$row_trans['invoice_no'].") with ".$row_trans['print_name']." products and ".$row_trans['qty']." qty ";				
			}
			
			foreach($fran_shipment_sms as $fr_id=>$fr_sms_list)
			{
				$sms_msg="Dear ".$row_trans['franchise_name'].", your ".implode(',',$fr_sms_list)." is shipped successfully, please expect delivery on ".$datetime->format('d/m/Y');
				$this->erpm->pnh_sendsms($fran_det[$fr_id][1],$sms_msg,$fr_id,0,12);	
			}
			
		}
		
		
		// Send Notification to TM about Shipment.
		
		$sql = "select d.employee_id,e.name,d.territory_id,b.franchise_id,franchise_name,contact_no,group_concat(distinct a.invoice_no order by a.invoice_no) as invoices 
							from king_invoice a
							join king_transactions b on a.transid = b.transid 
							join pnh_m_franchise_info c on c.franchise_id = b.franchise_id 
							join m_town_territory_link d on d.territory_id = c.territory_id and d.town_id = 0
							join m_employee_info e on e.employee_id = d.employee_id and job_title = 4 
							where invoice_no in ($invoices) 
						group by employee_id,franchise_id ";
		 
		
		$res = $this->db->query($sql);
		if($res->num_rows())
		{
			
			$emp_sms_det = array();
			
			foreach($res->result_array() as $row)
			{
				//tm employee id 
				
				list($emp_mobno,$emp_mobno2) = explode(',',$row['contact_no']); 
				$invlist = explode(',',$row['invoices']);
				
				$invlist = array_filter($invlist);
				$franid = $row['franchise_id'];
				
				if(!count($invlist))
					continue;
				
				
				if(!isset($emp_sms_det[$row['employee_id']]))
				{
					$emp_sms_det[$row['employee_id']] = array();
					$emp_sms_det[$row['employee_id']]['employee_id'] = $row['employee_id'];
					$emp_sms_det[$row['employee_id']]['name'] = $row['name'];
					$emp_sms_det[$row['employee_id']]['contact_no'] = $emp_mobno;
					
					$emp_sms_det[$row['employee_id']]['fran'] = array();
				}
				
				$emp_sms_det[$row['employee_id']]['fran'][$franid] = array();
				$emp_sms_det[$row['employee_id']]['fran'][$franid]['fname'] = $row['franchise_name'];
				$emp_sms_det[$row['employee_id']]['fran'][$franid]['trans'] = array();
				
				foreach($invlist as $inv)
				{
					$transid = $this->db->query("select transid from king_invoice where invoice_no = ? ",$inv)->row()->transid;
					
					$unshipped_items = @$this->db->query("select group_concat(trim(item),' x ',qty) as items from (
															select a.transid,f.franchise_id,franchise_name,concat(c.print_name,'-',pnh_id) as item,sum(a.quantity) as qty 
																from king_orders a
																join (
																	select distinct transid 
																		from shipment_batch_process_invoice_link a 
																		join proforma_invoices b on a.p_invoice_no = b.p_invoice_no 
																		where invoice_no in (".$inv.") 
																	) as b on a.transid = b.transid 
																join king_dealitems c on c.id = a.itemid 
																join king_deals d on d.dealid = c.dealid 
																join king_transactions e on e.transid = a.transid 
																join pnh_m_franchise_info f on f.franchise_id = e.franchise_id 
																where a.status = 0  
															group by a.transid,a.itemid ) as g
															group by franchise_id")->row()->items;
					if(!isset($emp_sms_det[$row['employee_id']]['fran'][$franid]['trans'][$transid]))
					{
						$emp_sms_det[$row['employee_id']]['fran'][$franid]['trans'][$transid] = array('inv_list'=>array(),'unshipped'=>'');
					}
					
					$emp_sms_det[$row['employee_id']]['fran'][$franid]['trans'][$transid]['inv_list'][] = $inv;
					
					if($unshipped_items)
					{
						$emp_sms_det[$row['employee_id']]['fran'][$franid]['trans'][$transid]['unshipped'] = $unshipped_items;
					}
															
				}
				
			}
			
			foreach($emp_sms_det as $emp_id=>$emp_ship_det)
			{
				$emp_id = $emp_ship_det['employee_id'];
				$emp_name = $emp_ship_det['name'];
				$emp_mob_no = $emp_ship_det['contact_no'];
				$emp_territory_id = $emp_ship_det['territory_id'];
				
				$sms_msg = array();
				
				foreach($emp_ship_det['fran'] as $fid=>$fran_trans_det)
				{
					$total_invoices = 0;
					$fran_inv_list = array();
					foreach($fran_trans_det['trans'] as $transid => $trans_ship_det)
					{
						$t_inv_list = array_values($trans_ship_det['inv_list']);
						$total_invoices += count($t_inv_list);
						
						$inv_sms = implode(',',$t_inv_list);
						if($trans_ship_det['unshipped'])
							$inv_sms .= ':'.$trans_ship_det['unshipped'];
						
						$fran_inv_list[] = $inv_sms;
						
					}
					
					$sms_msg[] = "Shipped ".$total_invoices." Invoices for ".($fran_trans_det['fname'])." (".implode(',',$fran_inv_list).")";
					
				}
				
				
				if(count($sms_msg))
				{
					$sms_text .= implode(',',$sms_msg).",Storeking";
					
					//echo $sms_text;
					
					$this->erpm->pnh_sendsms($emp_mob_no,$sms_text,0,$emp_id);
						
					//	echo $emp_mob_no,$sms_msg;
					$log_prm=array();
					$log_prm['emp_id']=$emp_id;
					$log_prm['contact_no']=$emp_mob_no;
					$log_prm['type']=4;
					$log_prm['territory_id']=$emp_territory_id;
					$log_prm['town_id']=0;
					$log_prm['grp_msg']=$sms_text;
					$log_prm['created_on']=cur_datetime();
					$this->erpm->insert_pnh_employee_grpsms_log($log_prm);
				}
			}
		}
	}
	
	
	/**
	 * function for sent to franchise sms shipments notification
	 * @param unknown_type $transid
	 * @param unknown_type $d_total
	 */
	function sendsms_tm_unshipped_products($invoices='')
	{
		
		$sql_trans = "select inv.invoice_no,a.id,e.franchise_id,a.itemid,group_concat(concat(b.print_name,'-',pnh_id)) as itemname,i_orgprice,login_mobile1,i_price,i_coup_discount,i_discount,group_concat(a.quantity) as qty,c.menuid,a.transid,f.franchise_id,f.franchise_name
							from king_invoice inv
							join king_orders a on a.id=inv.order_id
							join king_dealitems b on a.itemid = b.id
							join king_deals c on b.dealid = c.dealid 
							join pnh_menu d on d.id = c.menuid 
							join king_transactions e on e.transid = a.transid
							join pnh_m_franchise_info f on f.franchise_id = e.franchise_id 
							where inv.invoice_no in ($invoices)
							group by inv.invoice_no";
		
		$res_trans = $this->db->query($sql_trans);
	
		$sms_msg = '';
		$datetime = new DateTime('tomorrow');
		
		if($res_trans->num_rows())
		{
			foreach($res_trans->result_array() as $row_trans)
			{
				$sms_msg="Dear ".$row_trans['franchise_name'].", your invoice no(".$row_trans['invoice_no'].") with ".$row_trans['itemname']." products and ".$row_trans['qty']." qty is shipped successfully, please expect delivery on ".$datetime->format('d/m/Y');
				$this->erpm->pnh_sendsms($row_trans['login_mobile1'],$sms_msg,$row_trans['franchise_id'],0,12);
			}
		}
	
	}
	
	
	//-------------------coupon module--------------
	/**
	 * function for check given franchise is prepaid or not
	 */
	function is_prepaid_franchise($fid=0)
	{
		$franchise_menu_det=$this->db->query("select a.menuid from pnh_franchise_menu_link a where fid=? and status=1",$fid)->result_array();
		if($franchise_menu_det)
		{
			$prepaid=$this->db->query("select is_prepaid from pnh_m_franchise_info  where franchise_id=? and is_suspended=0",$fid)->row()->is_prepaid;
			
			if($prepaid)
				return true;
			else
				return false;
		}
	}
	
	/**
	 * function for check franchise have prepaid menus
	 * @param unknown_type $fid
	 * @param unknown_type $type
	 * @return number
	 */
	function check_franchise_have_prepaid_menu($fid=0,$type=0)
	{
		$franchise_menu_det=$this->db->query("select a.menuid from pnh_franchise_menu_link a where fid=? and status=1",$fid)->result_array();
		$have_prepaid_menu=0;
		$type_of_prepaid=0;
		
		if($franchise_menu_det)
		{
			$menu_ids=array();
			foreach($franchise_menu_det as $menu)
			{
				$menu_ids[]=$menu['menuid'];
				
				$prepaid_menu_res=$this->db->query("select * from pnh_prepaid_menu_config where menu_id =? and is_active=1",$menu['menuid']);
				
				if($prepaid_menu_res->num_rows())
					$have_prepaid_menu=1;
			}
			
			//check what type of prepaid franchise 1:fully prepaid 2:post & prepaid
			if($type)
			{
				$prepaid_menus=$this->db->query("select * from pnh_prepaid_menu_config where menu_id in (".implode(',',$menu_ids).") and is_active=1")->result_array();
				
				if(count($menu_ids)==count($prepaid_menus))
				{
					$type_of_prepaid=1;
					return $type_of_prepaid;
				}else{
					$type_of_prepaid=2;
					return $type_of_prepaid;
				}
			}
			
			return $have_prepaid_menu;
		}
	}
	//-------------------coupon module end--------------
	
	function update_inv_status_in_order_tbl($manifesto_id)
	{
		$inv_order_list = $this->db->query(" select order_id
				from shipment_batch_process_invoice_link a
				join king_invoice b on a.invoice_no = b.invoice_no
				where a.inv_manifesto_id = ?
				group by order_id
				",$manifesto_id);
		
		if($inv_order_list->num_rows())
		{
			foreach($inv_order_list->result_array() as $row)
				$this->db->query("update king_orders set status = 2,actiontime = unix_timestamp() where id = ? ",$row['order_id']);
		}
	}
	
	function update_return_inv_shipped_status($manifesto_id)
	{
		$return_inv_list_res = $this->db->query("select invoice_no from shipment_batch_process_invoice_link where inv_manifesto_id = ? and is_returned = 1 ",$manifesto_id);
		if($return_inv_list_res->num_rows())
		{
			foreach($return_inv_list_res->result_array() as  $return_inv)
			{
				$return_id = $this->db->query("select return_id from pnh_invoice_returns where invoice_no = ? ",$return_inv['invoice_no'])->row()->return_id;
				$this->db->query('update pnh_invoice_returns_product_link set is_shipped = 1 where return_id = ? ',$return_id);
			}
		}
	}
	function do_gen_receiptsreport($export_rtype)
	{
		
		$modes=array("Cash","Cheque","DD","Transfer");
		$transit_types=array("In Hand","Via Courier","With Executive");


		if($export_rtype == '5')
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by ,c.cancel_reason,c.cancelled_on
					FROM pnh_t_receipt_info r
					JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id
					left JOIN `pnh_m_deposited_receipts`c ON c.receipt_id=r.receipt_id
					LEFT OUTER JOIN king_admin a ON a.id=r.created_by
					LEFT OUTER JOIN king_admin d ON d.id=r.activated_by
					WHERE r.status in (2,3) AND r.is_active=1
					GROUP BY r.receipt_id
					ORDER BY activated_on DESC
					";
		}
		elseif($export_rtype == '1')
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) <= curdate()  and is_submitted=0 and r.status=0  ORDER BY instrument_date asc";
		}
		elseif($export_rtype == '2')
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,m.name AS modifiedby FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin m ON m.id=r.modified_by  WHERE r.status=0 AND r.is_active=1 AND date(from_unixtime(instrument_date)) > curdate() AND f.is_suspended=0 and is_submitted=0  ORDER BY instrument_date asc";
		}
		elseif($export_rtype == '3')
		{
			$sql = "SELECT r.*,f.franchise_name,a.name AS admin,d.username AS activated_by FROM pnh_t_receipt_info r JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id LEFT OUTER JOIN king_admin a ON a.id=r.created_by LEFT OUTER JOIN king_admin d ON d.id=r.activated_by WHERE r.status=1 AND r.is_active=1 AND f.is_suspended=0 and (is_submitted=1 or r.activated_on!=0) and r.is_active=1  ORDER BY activated_on desc";
		}
		elseif($export_rtype == '4')
		{
			$sql = "SELECT r.*,b.bank_name AS submit_bankname,s.name AS submittedby,a.name AS admin,f.franchise_name,d.remarks AS submittedremarks,DATE(d.submitted_on) AS submitted_on,r.created_on  FROM pnh_t_receipt_info r LEFT JOIN `pnh_m_deposited_receipts`d ON d.receipt_id=r.receipt_id LEFT JOIN `pnh_m_bank_info` b ON b.id=d.bank_id LEFT JOIN king_admin s ON s.id=d.submitted_by JOIN pnh_m_franchise_info f ON f.franchise_id=r.franchise_id left outer join king_admin a on a.id=r.created_by WHERE  f.is_suspended=0 AND r.is_submitted=1 AND r.status=0  and r.is_active=1  order by d.submitted_on desc";
		}

		$res = $this->db->query($sql);

		$fr_receipt_list = array();
		$fr_receipt_heading="Receipt Details";
		$fr_receipt_list[] = '"Slno","FranchiseName","Receipt Id","Payment Mode","Receipt Amount","Instrument Number","Payment Date","Transit type","Bank Details","Receipt AddedOn"';
		if($res->num_rows()!=0)
		{
			foreach($res->result_array() as $row_f)
			{
				$fr_receipt_det = array();
				$fr_receipt_det[] = ++$i;
				$fr_receipt_det[] = ucwords($row_f['franchise_name']);
				$fr_receipt_det[] = ucwords($row_f['receipt_id']);
				$fr_receipt_det[] = $modes[$row_f['payment_mode']];
				$fr_receipt_det[] = $row_f['receipt_amount'];
				$fr_receipt_det[] = ucwords($row_f['instrument_no']);
				$fr_receipt_det[] = ucwords(date("d/m/Y",$row_f['instrument_date']));
				$fr_receipt_det[] = $transit_types[$row_f['in_transit']];
				$fr_receipt_det[] = ucwords($row_f['bank_name']);
				$fr_receipt_det[] = ucwords(date("d/m/Y",$row_f['created_on']));
				$fr_receipt_list[]='"'.implode('","',$fr_receipt_det).'"';
			}


			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename=PNH_Receipt_REPORT_'.date('d_m_Y_H_i').'.csv');
			header('Pragma: no-cache');
			echo implode("\r\n",$fr_receipt_list);
		}else
		{
			echo "<script>alert('No data found');return false;</script>";
			//redirect('/admin/pnh_receiptsbytype');
		}
		exit;
	}
	
}


