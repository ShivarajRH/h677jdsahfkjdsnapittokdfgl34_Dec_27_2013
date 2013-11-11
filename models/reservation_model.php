<?php
/**
 * Description of reservation_model
 *
 * @author SDEV
 * @access public
 */
class reservation_model extends Model
{
	
    function __construct()
    {
            parent::__construct();
    }
    
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
    
}

?>
