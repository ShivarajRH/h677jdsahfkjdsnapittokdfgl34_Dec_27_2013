<?php
/**
 * Description of Pending_functions_to_add_with_server
 *
 * @author User
 */
class Pending_functions_to_add_with_server {
    		
    function pnh_calls_log($pg=0)
    {
            $this->erpm->auth();
            $data['page']='pnh_calls_log';
            $data['pg']=$pg;
            $this->load->view("admin",$data);
    }
    function calls_fun1($p1,$p2,$c) {
        if($p1=='callsmade') {
                switch($c) {
                    case 'tofranchise': $sql='select frn.franchise_id callerid,frn.franchise_name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa 
join pnh_m_franchise_info frn on frn.login_mobile1 = substr(exa.from,2) ';
                        break;
                    case 'toexecutive': $sql='select emp.employee_id callerid,emp.name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa 
join m_employee_info emp on emp.contact_no = substr(exa.from,2) ';
                        break;
                    case 'tounknown': $sql='select emp.employee_id callerid,emp.name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa
LEFT join m_employee_info emp on emp.contact_no = substr(exa.from,2)
LEFT join pnh_m_franchise_info frn on frn.login_mobile1 = substr(exa.from,2)
WHERE emp.employee_id IS NOT NULL and emp.name IS NOT NULL ';
                        break;
                    default:$this->json_error_show("Invalid input. <br>$p1,$p2,$c,$pg");
                        break;
                }
                return $sql;
        }
        elseif($p1=='receivedcalls') {
                switch($c) {
                    case 'tofranchise': $sql='select frn.franchise_id callerid,frn.franchise_name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa 
join pnh_m_franchise_info frn on frn.login_mobile1 = substr(exa.dialwhomno,2) ';
                        break;
                    case 'toexecutive': $sql='select emp.employee_id callerid,emp.name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa 
join m_employee_info emp on emp.contact_no = substr(exa.dialwhomno,2) ';
                        break;
                    case 'tounknown': $sql='select emp.employee_id callerid,emp.name callername,exa.from mobile,exa.callsid,exa.dialwhomno as towhom,exa.status,exa.created_on as calledtime from t_exotel_agent_status exa
LEFT join m_employee_info emp on emp.contact_no = substr(exa.dialwhomno,2)
LEFT join pnh_m_franchise_info frn on frn.login_mobile1 = substr(exa.dialwhomno,2)
WHERE emp.employee_id IS NULL OR emp.name IS NULL ';
                        break;
                    default:$this->json_error_show("Invalid input. <br>$p1,$p2,$c,$pg");
                        break;
                }
        }
        else {
            $this->json_error_show("1. Invalid input. <br>$p1,$p2,$c,$pg");
        }
        return $sql;
    }
    function json_error_show($string) {
        echo json_encode(array("status"=> "fail","response" => $string)); 
        die();
    }
    /**
     * Ajax function to load pnh calls log details by type and territory
     * @param unknown_type $p1 (parent 1)
     * @param unknown_type $p2 (parent 2)
     * @param unknown_type $c (Child)
     * @param unknown_type $pg (page)
     */
    function jx_getpnh_calls_log($p1,$p2,$c,$pg=0)
    {
        //$this->json_error_show("$p1,$p2,$c,$pg");
        $presql='';
        $limit = 25;
        $tbl_total_rows=0;
//                    if($p1=='callsmade') {
                //$presql=" join m_employee_info emp on emp.contact_no = substr(exa.from,2) ".$presql;

                $presql.=$this->calls_fun1($p1,$p2,$c);
                if($p2=='all_calls') {
                    $presql.=' ';

                }
                elseif($p2=='busy_calls') {
                    $presql.=' and exa.status="busy" ';

                }
                elseif($p2=='attended_calls') {
                    $presql.=' and exa.status="free" ';

                }
                else $this->json_error_show("2. Invalid input. <br>$p1,$p2,$c,$pg");

                $sql_total = $presql;

                //$this->json_error_show("$sql_total");

                $tbl_total_rows = $this->db->query($sql_total)->num_rows();

                $sql = $sql_total." order by calledtime DESC limit $pg,$limit";


                    $log_calls_details_res=$this->db->query($sql);

                    $tbl_head = array('slno'=>'Slno','callerid'=>'Caller ID','callername'=>'Caller Name','mobile'=>'Mobile Num.','callsid'=>'Calls ID','towhom'=>'To Whom','status'=>'Status','calledtime'=>'Called Time');

                    if($log_calls_details_res->num_rows())
                    {
                            foreach($log_calls_details_res->result_array() as $i=>$log_det)
                            {
                                    $tbl_data[] = array('slno'=>$i+1,
                                        'callerid'=>$log_det['callerid'],
                                        'callername'=> ($log_det['callername']!='') ? anchor('admin/view_employee/'.$log_det['callerid'],$log_det['callername']) : '',
                                        'mobile'=>$log_det['mobile'],
                                        'callsid'=>$log_det['callsid'],
                                        'towhom'=>$log_det['towhom'],
                                        'status'=>$log_det['status'],
                                        'calledtime'=>$log_det['calledtime']);
                            }
                    }

            //$this->json_error_show(wordwrap($sql,70,'<br>')."<br>$tbl_total_rows<br>$p1,$p2,$c,$pg");
            if(count($tbl_data)) {
                    $tbl_data_html = '<div class="dash_bar" id="dash_bar">Showing <strong>'.($pg+1).'</strong> to <strong>'.($pg+1*$limit).'</strong> of <strong>'.$tbl_total_rows.'</strong></div>';
                    $tbl_data_html .= '<table cellpadding="5" cellspacing="0" class="datagrid datagridsort">';
                    $tbl_data_html .= '<thead>';
                    foreach($tbl_head as $th)
                            $tbl_data_html .= '<th>'.$th.'</th>';

                    $tbl_data_html .= '</thead>';
                    $i = $pg;
                    $tbl_data_html .= '<tbody>';
                    foreach($tbl_data as $tdata)
                    {
                            $tbl_data_html .= '<tr>';
                            foreach(array_keys($tbl_head) as $th_i)
                            {
                                    if($th_i == 'slno')
                                            $tdata[$th_i] = $i+1;

                                    $tbl_data_html .= '	<td>'.$tdata[$th_i].'</td>';
                            }
                            $tbl_data_html .= '</tr>';

                            $i = $i+1;
                    }
                    $tbl_data_html .= '</tbody>';
                    $tbl_data_html .= '</table>';


                    $this->load->library('pagination');

                    $config['base_url'] = site_url('admin/jx_getpnh_calls_log/'.$p1.'/'.$p2.'/'.$c);
                    $config['total_rows'] = $tbl_total_rows;
                    $config['per_page'] = $limit;
                    $config['uri_segment'] = 6;

                    $this->config->set_item('enable_query_strings',false);
                    $this->pagination->initialize($config);
                    $pagi_links = $this->pagination->create_links();
                    $this->config->set_item('enable_query_strings',true);

                    $pagi_links = '<div class="log_pagination">'.$pagi_links.'</div>
                                        ';

                    echo json_encode(array('status'=>"success",'log_data'=>$tbl_data_html,'tbl_total_rows'=> $tbl_total_rows,'limit'=>(($pg+1)*$limit),'newpg'=>($pg+1),'pagi_links'=>$pagi_links,'p1'=>$p1,'p2'=>$p2,
                        'c'=>$c,'pg'=>$pg,'items_info'=>""));

            }
            else {
                    $tbl_data_html = '<div align="center"> No data found</div>';
                    echo json_encode(array('status'=>"fail",'response'=>$tbl_data_html,'tbl_total_rows'=>$tbl_total_rows,'limit'=>$limit,'pagi_links'=>'','p1'=>'','p2'=>'','c'=>'','pg'=>0,'items_info'=>"Showing <strong>0</strong>"));
            }
    }
    //END PNH Calls log files
    
    function addproduct()
    {
            $user=$this->auth(PRODUCT_MANAGER_ROLE);
            $input_fields=array('pname',"pdesc","psize","puom","pmrp","pvat","pcost","pbarcode","pisoffer","pissrc","pbrand","prackbin","pmoq","prorder","prqty","premarks","pissno");

            $this->form_validation->set_rules('pname', 'Product Name', 'required');
            $this->form_validation->set_rules('pdesc', 'Product Description', 'required');

// 		$this->form_validation->set_rules('psize', 'Size', 'required');
// 		$this->form_validation->set_rules('puom', 'Unit of Measurment', 'required');
// 		$this->form_validation->set_rules('pmrp', 'MRP', 'required');
// 		$this->form_validation->set_rules('pvat', 'VAT', 'required');
// 		$this->form_validation->set_rules('pcost', 'Purchase Cost', 'required');
// 		$this->form_validation->set_rules('pbarcode', 'Barcode', 'required');
// 		$this->form_validation->set_rules('pisoffer', 'Is Offer', 'required');
// 		$this->form_validation->set_rules('pissrc', 'Is sourceable', 'required');
// 		$this->form_validation->set_rules('pbrand', 'Brand', 'required');
// 		$this->form_validation->set_rules('prackbin', 'prackbin', 'required');
// 		$this->form_validation->set_rules('pmoq', 'MOQ', 'required');
// 		$this->form_validation->set_rules('prorder', 'Reorder Level', 'required');
// 		$this->form_validation->set_rules('prqty', 'Reorder Qty', 'required');
// 		$this->form_validation->set_rules('premarks', 'Remarks', 'required');
// 		$this->form_validation->set_rules('pissno', 'Is Active', 'required');

            if ($this->form_validation->run() == FALSE) {
                    //ERRORS
            } 
            else {// No errors
                            $inp=array("P".rand(10000,99999));
                            foreach($input_fields as $i) 
                                    $inp[]=$this->input->post($i);

                            $inp[] = $user['userid'];	
                            $this->db->query("insert into m_product_info(product_code,product_name,short_desc,size,uom,mrp,vat,purchase_cost,barcode,is_offer,is_sourceable,brand_id,default_rackbin_id,moq,reorder_level,reorder_qty,remarks,is_serial_required,created_on,created_by)
                                                                                                                                                                            values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,now(),?)",$inp);
                            $pid=$this->db->insert_id();
                            $rackbin=0;$location=0;
                            $raw_rackbin=$this->db->query("select l.location_id as default_location_id,l.id as default_rack_bin_id from m_rack_bin_brand_link b join m_rack_bin_info l on l.id=b.rack_bin_id where b.brandid=?",$this->input->post("pbrand"))->row_array();
                            if(!empty($raw_rackbin))
                            {
                                    $rackbin=$raw_rackbin['default_rack_bin_id'];
                                    $location=$raw_rackbin['default_location_id'];
                            }
                            $this->db->query("insert into t_stock_info(product_id,location_id,rack_bin_id,mrp,available_qty,product_barcode) values(?,?,?,?,0,'')",array($pid,$location,$rackbin,$pmrp));
                            redirect("admin/products");
            }
            $data['page']="addproduct";
            $this->load->view("admin",$data);
    }
    
    function products($pg=0)
	{
                $user=$this->auth(PRODUCT_MANAGER_ROLE);
		$data['page']="products";
		$this->load->view("admin",$data);
	}
        function jx_products($pg=0) {
            //print_r($_POST); die();
            $user=$this->auth(PRODUCT_MANAGER_ROLE);
		$data['products']=$this->erpm->getproducts();
                
                $output='';
                $limit=30;
               
                //$sql="select sum(s.available_qty) as stock,p.*,b.name as brand from m_product_info p join king_brands b on b.id=p.brand_id left outer join t_stock_info s on s.product_id=p.product_id group by p.product_id order by p.product_id desc ";
                if($_POST['idname'] != '' or $_POST['classname'] != '') {
                    $idname = $_POST['idname'];
                    $classes = explode(' ', trim($_POST['classname']));
                    
                    $classname=$classes[1];
                    
                    switch($idname) {
                        case 'th_pname': 
                            if($classname == 'headerSortDown') { 
                                $oderby=" p.product_name desc "; 
                            }
                            else { 
                                $oderby=" p.product_name ASC ";  
                            }
                            break;
                        case 'th_mrp' : 
                            if($classname == 'headerSortDown') { 
                                $oderby=" s.mrp ASC "; 
                            }   
                            else {   
                                $oderby=" s.mrp desc "; 
                            }
                            break;
                        case 'th_stock': 
                            if($classname == 'headerSortDown') { 
                                $oderby=" s.available_qty ASC "; 
                            }   
                            else {   
                                $oderby=" s.available_qty desc "; 
                            }
                            break;
                        case 'th_barcode': 
                            if($classname == 'headerSortDown') { 
                                $oderby=" s.barcode ASC "; 
                            }   
                            else {   
                                $oderby=" s.barcode desc "; 
                            }
                            break;
                        case 'th_brand' : 
                            if($classname == 'headerSortDown') { 
                                $oderby=" b.name ASC "; 
                            }   
                            else {   
                                $oderby=" b.name desc "; 
                            }
                            break;
                        default :
                            $oderby=" p.product_id desc ";
                            break;
                    }
                } else { $oderby=" p.product_id desc ";  }
                
               
                $sql="select sum(s.available_qty) as stock,p.*,b.name as brand from m_product_info p join king_brands b on b.id=p.brand_id left outer join t_stock_info s on s.product_id=p.product_id group by p.product_id order by".$oderby;
               
                //echo $output.= "<br>".$oderby."</br>"; die();
                
		$total_products=$this->db->query($sql)->num_rows();
		$sql.=" limit $pg , 30 ";
		//$data['products']=
                $products=$this->db->query($sql)->result_array();
		
                //echo json_encode($data['products']); die();
		
                //pagination
		$this->load->library('pagination');
		$config['base_url'] = site_url("admin/jx_products");
		$config['total_rows'] = $total_products;
		$config['per_page'] = $limit;
		$config['uri_segment'] = 3;
		$config['num_links'] = 5;
                $config['enable_query_strings']=false;
                
                $config['full_tag_open'] = '<div class="link"><strong>';
	        $config['full_tag_close'] = '</strong></div>';
                
                $this->pagination->initialize($config);
		$pagination = $data['pagination'] = $this->pagination->create_links();
                $config['enable_query_strings']=true;
		//pagination end//
               
                

                foreach($products as $p){
                    $style=$p['is_sourceable'] ? "#aaffaa" : "#ffaaaa;";
                     
                    $output.= '<tr style="background:'.$style.'">
                    <td><input type="checkbox" value="'.$p['product_id'].'" class="p_check"></td>
                    <td><a class="link" href="'.site_url("admin/product/{$p['product_id']}").'">'.$p['product_name'].'</a></td>
                    <td>'.$p['mrp'].'</td>
                    <td>'.$p['stock'].'</td>
                    <td>
                            <img src="'.IMAGES_URL.'loading_maroon.gif" class="busy">
                        <form action="'.site_url("admin/update_barcode").'" method="post" class="barcode_forms">
                            <input type="hidden" name="pid" value="'.$p['product_id'].'">
                            <input type="text" class="barcode_inp" name="barcode" value="'.(string)$p['barcode'].'" size=10>
                        </form>
                    </td>
                    <td>'.$p['brand'].'</td>
                    <td>
                    <a href="'.site_url("admin/editproduct/{$p['product_id']}").'">edit</a> &nbsp;&nbsp;&nbsp;&nbsp; 
                    <a href="'.site_url("admin/viewlinkeddeals/{$p['product_id']}").'">view linked deals</a>
                    </td>
                    </tr>';
                }
                
                $output.= '<tr>
                        <td colspan="8" align="left" class="pagination">'.$pagination.' <div class="loading">&nbsp;</div></td>
                </tr>
                
                ';
                echo $output;
                
        }
        
        
//	function jx_get_group_attibutes($limit=100)
//	{
//		$user=$this->auth();
//                $product_id=$this->input->post("product_id");
//                $group_id=$this->input->post("group_id");
//                $result= $this->db->query("select pgp.id,pgp.group_id,pgp.product_id,pga.attribute_name_id as name_id,pga.attribute_name as name_val,pgav.attribute_value_id as att_val_id,pgav.attribute_value as att_value from products_group_pids as pgp  
//                        join products_group_attributes pga on pga.group_id=pgp.group_id
//                        join products_group_attribute_values pgav on pgav.group_id=pgp.group_id
//                        where pgp.group_id in (?)
//                        group by att_val_id",$group_id)->result_array();
//                
//                if(in_array($product_id,$result)) {
//                    $res['status']='fail';
//                    $res['result'] ="Product id already exists.";
//                }
//                else {
//                    $res['status']='success';
//                    $res['result'] =$result;
//                }
//                //$res['query']=$this->db->last_query(); 
//                
//                echo json_encode($res);
//        }

        //STOCK UPDATE CODE
       /**
        * function to get product current stock from table
        */
       function _get_product_stock($product_id = 0)
       {
           return @$this->db->query("select sum(available_qty) as t from t_stock_info where product_id = ? and available_qty >= 0 ",$product_id)->row()->t;
       }
       
       function chk_bc_assign()
        {
           //$prod_id=0,$mrp=0,$bc='',$loc_id=0,$rb_id=0,$p_stk_id=0,$qty=0,$update_by=0,$stk_movtype=0,$update_by_refid=0,$mrp_change_updated=-1,$msg=''
        if($this->erpm->_upd_product_stock(2360,8000,'8806085354760',1,1,0,3,1,1,12312))
        {
        echo 'ok';
        }else
        {
        echo 'failed';
        }

        echo $this->_get_product_stock(8698);
        }
       //END STOCK UPDATE CODE
}

?>
<script>
    var obj = {};

$.getJSON("displayjson.php",function (data) {
    $.each(data.news, function (i, news) {
        obj[news.title] = news.link;
    });                      
});

// later:
$.each(obj, function (index, value) {
    alert( index + ' : ' + value );
});
In JavaScript, objects fulfill the role of associative arrays. Be aware that objects do not have a defined "sort order" when iterating them (see below).

However, In your case it is not really clear to me why you transfer data from the original object (data.news) at all. Why do you not simply pass a reference to that object around?

You can combine objects and arrays to achieve predictable iteration and key/value behavior:

var arr = [];

$.getJSON("displayjson.php",function (data) {
    $.each(data.news, function (i, news) {
        arr.push({
            title: news.title, 
            link:  news.link
        });
    });                      
});

// later:
$.each(arr, function (index, value) {
    alert( value.title + ' : ' + value.link );
});
</script>