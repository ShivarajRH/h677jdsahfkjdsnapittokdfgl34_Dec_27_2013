<?php

class Work extends Controller{

	function invoice()
	{
		$orders=$this->db->query("select * from king_orders where status!=0")->result_array();
		foreach($orders as $o)
		{
			if($this->db->query("select 1 from king_invoice where transid=? and invoice_no=?",array($o['transid'],$o['invoice_no']))->num_rows()==1)
				continue;
			$itemid=$o['itemid'];
			$item=$this->db->query("select phc,nlc,tax,service_tax from king_dealitems where id=?",$o['itemid'])->row_array();
			$this->db->query("insert into king_invoice(transid,invoice_no,phc,nlc,tax,service_tax) values(?,?,?,?,?,?)",array($o['transid'],$o['invoice_no'],$item['phc'],$item['nlc'],$item['tax'],$item['service_tax']));
		}
	}
	
	function index()
	{
		echo '<html>
				<head><title>Men at work</title></head>
				<body>
					<table width="100%" height="100%">
					<tr>
						<td align="center" valign="center">
							<img src="http://static.snapittoday.com/upgrade.png">
						</td>
					</tr>
				</body>

				</html>
		';
	}
	
	function gencoupons($n=1)
	{
		for($i=0;$i<$n;$i++)
			echo "ST".strtoupper(randomChars(8))."<br>";
	}
	
	function crusercoupon()
	{
		$users=$this->db->query("select * from king_users")->result_array();
		$inp=array("",COUPON_REFERRAL_VALUE,COUPON_REFERRAL_MIN,991231231231231,"",time());
		foreach($users as $user)
		{
			$name=preg_replace('/[^a-zA-Z0-9_\-]/','',$user['name']);
			$code=trim(strtoupper(substr($name,0,7)));
			$code.=strtoupper(randomChars(10-strlen($code)));
			$inp[0]=$code;
			$inp[4]=$user['userid'];
			$this->db->query("insert into king_coupons(code,value,min,expires,referral,mode,created,unlimited) values(?,?,?,?,?,1,?,1)",$inp);
		}
		echo "done";
	}
	
}