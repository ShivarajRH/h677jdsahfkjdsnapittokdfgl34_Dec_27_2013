<?php
include APPPATH.'/controllers/analytics.php';
class Stream extends Analytics 
{
    /**
     * @access public
     * @param type $transid
     */
    function jx_batch_enable_disable($transid,$flag=1)
    {
            $user=$this->auth(true);
            //if($this->db->query("select batch_enabled from king_transactions where transid=?",$transid)->row()->batch_enabled==1)
            //        $flag=0;
            
            $this->db->query("update king_transactions set batch_enabled=? where transid=? limit 1",array($flag,$transid));
            $this->erpm->do_trans_changelog($transid,"Transaction ".($flag==1?"ENABLED":"DISABLED")." for batch process");
    }
    
    /**
     * Get transaction list by batch type, Like, Ready for processing or pending or not ready transaction
     * @param type $batch_type
     * @param type $from
     * @param type $to
     * @param type $pg
     * @param type $limit
     */
    function jx_get_transaction_list($batch_type,$from,$to,$terrid=0,$townid=0,$franchiseid=0,$menuid=0,$brandid=0,$pg=0,$limit=50) {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        
        if($from != '') {
                $s=date("Y-m-d", strtotime($from));
                $e=date("Y-m-d",strtotime($to));
                                
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

    /******** Orders Reservation**************/
    /**
     * Make transaction enabled for batch and allot stock
     * @param type $trans
     * @param type $ttl_num_orders
     * @param string $batch_remarks
     * @param type $updated_by
     */
    function batching_process($transid,$ttl_num_orders,$batch_remarks='',$updated_by) {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        $batch_remarks=$batch_remarks=='' ? 'Created by transaction reservation system' : $batch_remarks ;
        
        // Process to batch this transaction
        $this->erpm->do_batching_process($transid,$ttl_num_orders,$batch_remarks,$updated_by);
    }
    
    /**
     * Dispaly and process transaction batch status as 
     */
    function trans_reservation_status() {
        $user=$this->auth(PRODUCT_MANAGER_ROLE|STOCK_INTAKE_ROLE|PURCHASE_ORDER_ROLE);
        $data['pnh_menu'] = $this->db->query("select * from pnh_menu order by name")->result_array();
        $data['pnh_terr'] = $this->db->query("select * from pnh_m_territory_info order by territory_name")->result_array();
        $data['pnh_towns'] = $this->db->query("select id,town_name from pnh_towns order by town_name")->result_array();
        $data['pnh_menu'] = $this->db->query("select mn.id,mn.name from pnh_menu mn
                                                    join king_deals deal on deal.menuid=mn.id
                                                    where mn.status=1 
                                                    group by mn.id
                                                    order by mn.name")->result_array();

        $data['pnh_brands'] = $this->db->query("select br.id,br.name from king_brands br
                                    join king_orders o on o.brandid=br.id
                                    group by br.id
                                    order by br.name")->result_array();

        $data['user']=$user;
        //$data['s']=date("d/m/y",$from);
        //$data['e']=date("g:ia d/m/y",$to);

        $data['page']='trans_reservation_status';
        $this->load->view("admin",$data);
    }
    /********End Orders Reservation**************/
    
    /**
     * Function to get count of unreplied comments
     * @param type $stream_id
     */
    function jx_get_unreplied_posts($stream_id) {
        $user=$this->erpm->auth(true,true);
        $count_elt=$this->db->query("select count(*) as total from m_stream_posts sp where sp.stream_id=? and sp.id NOT IN (select post_id from m_stream_post_reply)",$stream_id)->row_array();
        echo $count_elt['total'];
	}
    
    /**
     * Function to store assigned user 
     */
    function jx_save_assign_user() {
        $user=$this->erpm->auth(ADMINISTRATOR_ROLE,true);
        $this->erpm->do_save_assign_user($user);
    }
    
    /**
     * Function to remove assigned user 
     */
    function jx_remove_assign_user() {
        $user=$this->erpm->auth(ADMINISTRATOR_ROLE,true);
        $this->erpm->do_remove_assign_user($user);
	}
    
    /**
     * Function to edit streams
     * @param type $streamid
     */
    function stream_edit($streamid='') {
        $user=$this->auth(ADMINISTRATOR_ROLE);
        if($_POST) {
            $this->erpm->do_updatestream($user);
        }
        if($streamid!='') { 
            $user=$this->auth(ADMINISTRATOR_ROLE);
            $data['streams']=$this->db->query("Select s.*,ka.username,ka.email,ka.mobile from m_streams s
                                join king_admin ka on ka.id=s.created_by
                                where s.id=?
                                order by s.created_time desc",$streamid)->row_array();

            $data['adminusers']=$this->db->query("select id,user_id,name,username from king_admin where account_blocked!=1 order by username asc")->result_array();
        }
        else {
            $data['status']='fail';
            $data['message']='Undefined streamid.';
        }

        $data['page']="stream_edit";
        $this->load->view("admin",$data);
    }
    
	/**
     * Manage Streams
     */
	function streams_manager() {
		$user=$this->auth(ADMINISTRATOR_ROLE);
		$data['streams']=$this->db->query("Select s.*,ka.username,ka.email,ka.mobile from m_streams s
												join king_admin ka on ka.id=s.created_by
												order by s.created_time desc")->result_array();
												//where s.created_by=?,$user['userid']
		$data['page']="streams_manager";
		$this->load->view("admin",$data);
	}
    /**
     * Function to display streams
     */
    function streams() 
    {
        $data['user']=$user=$this->erpm->auth();
            $ou_cond='';
            if(!$this->erpm->auth(true,true)) 
                $ou_cond=' and su.user_id='.$user['userid'];

                $data['streams']=$this->db->query("select s.*,su.* from m_streams s 
                                                join m_stream_users su on su.stream_id = s.id
                                                where status=1 ".$ou_cond." group by s.id order by s.title asc")->result_array();

                $data['users']=$this->db->query("select * from king_admin order by name asc")->result_array();
                $data['pg']=0;
                $data['page']="streams";
                $this->load->view("admin",$data);
    }
    /**
     * Function to add stream
     */
	function stream_create() 
	{
	    $user=$this->erpm->auth(ADMINISTRATOR_ROLE);
	        if($_POST) {
	            $this->erpm->do_addstream($user);
	        }
		$data['adminusers']=$this->db->query("select id,user_id,name,username from king_admin where account_blocked!=1 order by username asc")->result_array();
	    $data['page']="stream_create";
	    $this->load->view("admin",$data);
	}
	
	
	/**
	 * Function to get user stream notifications
	 * @param type $userid
	 */
	
    function jx_get_stream_notifications($userid,$update='') {
            $user=$this->erpm->auth();
	    if($update == 1) {
	        $this->db->query("update m_stream_post_assigned_users set viewed=1 where assigned_userid=?",$userid);
	    }
	    $rslt=  $this->db->query("select * from m_stream_post_assigned_users spau
	                                where spau.viewed=0 and spau.assigned_userid=?",$userid);
	    if($rslt->num_rows()) {
	        echo $rslt->num_rows();
	    }
	    else echo '';
	}
	
	/**
	* Replace links in text with html links
	*
	* @param  string $text
	* @return string
	*/
	function auto_link_text($text)
	{//'@(http)?(s)?(://)?(([-\w]+\.)+([^\s]+)+[^,.\s])@'
	  $data=preg_replace('@(http)?(s)?(://)+(([-\w]+\.?)+([^\s]+)+[^,.\s])@', '<a href="http$2://$4" target="_blank">$1$2$3$4</a>', $text);
	  return trim(nl2br($data));
	}
	
	function jx_store_subreplies($post_id) {
	    $user=$this->erpm->auth();
	    if($_POST) {
	        $this->erpm->do_store_post_reply($post_id);
	    }
	}
	
	function jx_get_admindetails($id) {
	    $user=$this->erpm->auth();
	    $rdata = $this->db->query('select id,name,username,usertype,access,email,mobile,gender,city img_url from king_admin where account_blocked="0" and id = ? limit 1',$id)->row_array();
	    echo json_encode($rdata);
	}
	
	function post_reply($post_id) {
	    $user=$this->erpm->auth();
	    $arr_replies = $this->db->query('select spr.*,ka.id,ka.username,ka.email,ka.img_url from m_stream_post_reply spr
	                                        join king_admin ka on ka.id=spr.replied_by
	                                        where status=1 and post_id = ? and account_blocked!=1 
	                                        order by replied_on desc limit 0,10',$post_id)->result_array(); 
	    if($arr_replies['img_url']=='' || $arr_replies['img_url']==null) 
	    { 
	        $divimgurl='<img src="'.base_url().'images/icon_comment.gif" alt="Reply"/>'; 
	    }
	    else 
	    { 
	        $divimgurl='<img src="'.$post['img_url'].'" alt="Image"/>'; 
	    }
	    $outdata='';
	    foreach($arr_replies as $replydata) {
	        $outdata.='<div class="subreply">
	                        <div class="img_div">'.$divimgurl.'</div>
	                        <div class="desc">'.$this->auto_link_text($replydata['description']).'  </div>
                                    <div class="clear"></div>
                                <div class="action_block"><a href="#_'.$replydata['id'].'">'.ucfirst($replydata['username']).'</a>
                                    <abbr class="timeago" title="'.date("Y-m-d H:i:s",$replydata['replied_on']).'">&nbsp;</abbr>
                                </div>
                               
                            </div>';
	    }
	    return $outdata;
	}

	function get_post_reply_list($post_id) {
	    $user=$this->erpm->auth();
	    $outdata=$this->post_reply($post_id);
	    return $outdata;
	}
	
	function jx_post_reply_list($post_id) {
	    $user=$this->erpm->auth();
	    $outdata=$this->post_reply($post_id);
	    echo $outdata;
	}
	
	function jx_get_streampostdetails($streamid,$pg=0,$limit=5) 
	{
		$user=$this->erpm->auth();
                $cond='';

                
                if($this->input->post('date_from') != '') {
                    $dt_st = strtotime($this->input->post('date_from'));
                }
                else {
                    $dt_st = strtotime(date('Y-m-d 00:00:00',  time()-60*60*24*30));
                }
                
                if( $this->input->post('date_to') != '') {
                    $dt_end= strtotime($this->input->post('date_to'));
                }
                else {
                    $dt_end= strtotime(date('Y-m-d 24:59:59',  time() ) );
                }
                $cond.="and (sp.posted_on between $dt_st and $dt_end )";
                
                if($this->input->post('search_text') != '') {
                        $search_text = $this->input->post('search_text');
                        $cond.=' and (sp.description like "%'.$search_text.'%" or spr.description like "%'.$search_text.'%")';
                }
               
                $output['date_output']="Posts from ".date("M/d/Y",$dt_st)." to ".date("M/d/Y",$dt_end);
         

				$sql="select sp.*,ka.id as userid,ka.username,ka.name,ka.email from m_stream_posts sp
	                                    join king_admin ka on ka.id=sp.posted_by
                                            left join m_stream_post_reply spr on spr.post_id=sp.id
	                                    where sp.stream_id=? and sp.status=1 $cond
	                                    group by sp.id order by sp.posted_on desc";
            
	    $total_items= $output['total_items']=$this->db->query($sql,array($streamid))->num_rows();
            
            
            $sql .=" limit $pg,$limit ";
            
            $arr_streams_rslt=$this->db->query($sql,array($streamid));
            $arr_streams=$arr_streams_rslt->result_array();
            
	    if($total_items>0) {
	        $output['items']=""; 
	        foreach($arr_streams as $post) 
	        {
                    
	            $streamed_users_list='';
	            $arr_streamed_users_list= $this->db->query("select sau.*,ka.name,ka.username,ka.email,ka.mobile,ka.gender,ka.img_url from m_stream_post_assigned_users sau
	 join king_admin ka on ka.id=sau.assigned_userid where ka.account_blocked!=1 and sau.post_id=?",$post['id'])->result_array();
	            $i=1;
	            foreach($arr_streamed_users_list as $sau) {
	                if($sau['assigned_userid']==$user['userid']) {
	                    $streamed_users_list.='<a href="" class="stream_assigned_users" id="'.$sau['assigned_userid'].'">you</a>';
	                }
	                else { 
	                    $streamed_users_list.='<a href="" class="stream_assigned_users" id="'.$sau['assigned_userid'].'">'.ucfirst($sau['username']).'</a>'; 
	                }
	                if($i< count($arr_streamed_users_list)) {
	                    $streamed_users_list.=', ';
	                }
	                $i++;
	            }
	            $streamed_users_list=($streamed_users_list=='')?'all':$streamed_users_list;
	            $username=($post['userid']==$user['userid'])?'Me':ucfirst($post['username']);
	            
	            if($post['img_url']=='' || $post['img_url']==null) 
	                $divimgurl='<img src="'.base_url().'images/unknown_man.jpg" alt="Image"/>'; 
	            else 
	                $divimgurl='<img src="'.$post['img_url'].'" alt="Image"reply_image"/>'; 
	            
	            $post_replies_arr=$this->get_post_reply_list($post['id']);
	            
	            $output['items'].='<div class="stream_item_admin_div">
	                                            <div class="reply_image_div">'.$divimgurl.'</div>
	                                            <div class="reply_box">
	                                                    <div class="title">
	                                                    <a name="stream_li" id="'.$post['id'].'">
	                                                        <strong>'.$username.'</strong>
	                                                    </a>
	                                                    </div>
	                                                    <div class="title_to"> &nbsp;&nbsp;to '.($streamed_users_list).'</div>
	                                                    
	                                                    <p class="reply_desc">'. $this->auto_link_text($post['description']). '</p>
	                                                    <div class="reply_actions">
	                                                        <abbr class="timeago reply_date" title="'.date("Y-m-d H:i:s",$post['posted_on']).'">&nbsp;</abbr>
	                                                        <span class="reply_link">
	                                                            <a href="javascript:void(0)" id="'.$post['id'].'" onclick="return reply_block(this,'.$post['userid'].','.$streamid.')" >Reply</a>
	                                                        </span>
	                                                    </div>
	                                                    <div class="sub_reply_list" id="sub_reply_list_'.$post['id'].'">'.$post_replies_arr.'</div>
	                                                    <div class="stream_item_reply_div" id="stream_item_reply_div_'.$post['id'].'"></div>
	                                            </div>
                                        </div>';
	        }
                            
//                  PAGINATION
                    $date_from=date("Y-m-d",$st_ts);
                    $date_to=date("Y-m-d",$en_ts);
                    
                    $this->load->library('pagination');
                   
                    $config['base_url'] = site_url("admin/jx_get_streampostdetails/".$streamid); //site_url("admin/orders/$status/$s/$e/$orders_by/$limit");
                    $config['total_rows'] = $total_items;
                    $config['per_page'] = $limit;
                    $config['uri_segment'] = 4; 
                    $config['num_links'] = 5;
                    
                    $this->config->set_item('enable_query_strings',false); 
                    $this->pagination->initialize($config); 
                    $posts_pagination = $this->pagination->create_links();
                    $this->config->set_item('enable_query_strings',TRUE);
//                  PAGINATION ENDS
                    
                    $output['pagination'].='<div class="stream_posts_pagination">'.$posts_pagination."</div>";
                    if($output['items']=='') { $output['status']='<div class="no_more_posts" align="center"><strong>No more posts to display.</strong></div>'; }
	    } 
            else { $output['items'].=''; $output['status']='<div class="no_more_posts" align="center"><strong>No results found.</strong></div>'; } 
            
            echo json_encode($output);
        }
	 
	function jx_get_assignto_list($streamid) 
	{
            $user=$this->erpm->auth();
            $output='';
    		$arr_userids=$this->db->query("select su.*,ka.name,ka.username from m_stream_users as su 
                                    join king_admin ka on ka.id=su.user_id 
                                    where stream_id=?
                                    group by su.user_id order by ka.name",$streamid)->result_array();
//            $output.="<option value='00'>All</option>";
    foreach($arr_userids as $assigneduser) {
        if($user['userid'] == $assigneduser['user_id']) {
            $output.="";
        }
        else {
            $output.="<option value='".$assigneduser['user_id']."'>".$assigneduser['name']."</option>";
               }
            }
        echo $output;
    }

    /**
     * Store the stream post
     */
    function jx_stream_post() 
    {
        $user=$this->erpm->auth();
        if($_POST) 
            $this->erpm->do_stream_post($user);
    }
	 
    
}
