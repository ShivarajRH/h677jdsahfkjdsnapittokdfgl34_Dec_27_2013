<?php
	$this->load->plugin('barcode');
?>
<div class="container">
    <div style="width: 100%;margin: 0px auto">
	<?php if($this->session->userdata("admin_user")){ ?>
	<div style="margin:10px;" class="hideinprint">
            <div style="float:right;"><input type="button" value="Print invoice acknowlegement" onclick='print_taxinv_acknowledgement(this)'></div>
            <!--<div style="float:left;"><input type="button" value="Refresh" onclick='page_refresh(this)'></div>-->
	</div>
	<?php }  ?>
    </div>
    <div class="clear"></div>
    <div style="font-family: arial" id="grouped_tax_inv_ack_copy">
        <style>
            .leftcont { display: none; }
            table {
                    font-family:arial; font-size:12px;
            }
            .tax_inv_ack_copy { page-break-after:always;font-family:arial; font-size: 12px; }
            
            @media print {
                table {
                    font-family:arial; font-size:12px;
                }
                .tax_inv_ack_copy { page-break-after:always; font-family:arial; font-size: 12px; }
            }
        </style>
<?php
$list_invs_group_arr=array();
        //foreach($dispatch_list as $dispatch_det)
        //{
                    $ttl_inv_amt = 0;

//                    $dispatch_id = $dispatch_det['dispatch_id'];
                    $invs = $dispatch_det['invs'];
                    $invoice_list = explode(',',$invs);
                    $orderslist_byproduct = $this->db->query("select terr.territory_name,a.transid,a.createdon as invoiced_on,b.bill_person,b.bill_address,b.bill_landmark,b.bill_city,b.bill_state,b.bill_pincode,bill_phone,d.init,b.itemid,c.name,if(c.print_name,c.print_name,c.name) as print_name,c.pnh_id,group_concat(distinct a.invoice_no) as invs,
                                                                    ((a.mrp-(a.discount))) as amt,
                                                                    sum(a.invoice_qty) as qty 
                                                            from king_invoice a 
                                                            join king_orders b on a.order_id = b.id 
                                                            join king_dealitems c on c.id = b.itemid
                                                            join king_transactions d on d.transid = a.transid
                                                            join pnh_m_franchise_info f on f.franchise_id = d.franchise_id
                                                            join pnh_m_territory_info terr on terr.id=f.territory_id
                                                            where a.invoice_no in ($invs) 
                                                            group by itemid,amt
                                                            order by c.name")->result_array();
                    $order = $orderslist_byproduct[0];									
?>
                
                <div class="tax_inv_ack_copy" style="font-family:arial;">
                    <div style="border-bottom:1px solid #000;padding:5px;font-weight:bold;text-align:center;overflow: hidden;text-align: center; ">
                            Acknowledgement Copy 
                    </div>
                    
                        <table width="100%" style="margin-top:10px">
                            <tr>
                                    <td valign="top">
                                    <?php 
                                            $tin_no = '29230678061';
                                            $service_no = 'AACCL2418ASD001';	
                                            echo 'Local Cube commerce Pvt Ltd<br>1060,15th cross, BSK 2nd stage, bangalore -560070';
                                    ?>
                                    </td>
                                    <td align="right" valign="top">
                                            <table border=1 cellspacing=0 cellpadding=5>
                                                    <tr>
                                                        <td>From:</td>
                                                            <td width="60"><b><?=date("d/m/Y",(strtotime($sdate)))?></b></td>

                                                        <td>To:</td>
                                                            <td width="60"><b><?=date("d/m/Y",(strtotime($edate)))?></b></td>

                                                        <td>Territory</td>
                                                            <td width="75"><b><?=$order['territory_name']; ?></b></td>
                                                    </tr>
                                            </table>
                                    </td>

                            </tr>

                    </table>

                    <table cellspacing=0 border=1 cellpadding=3 width="100%">
                            <tr><th width="100">Franchise Name</th>
                                    <th colspan="3" align="left"><?=$order['bill_person']?></th>
                            </tr>
                            <tr>
                                <td><b>Address :</b></td>
                                <td colspan="3">
                                    <?=nl2br($order['bill_address'])?>, <?=$order['bill_landmark']?>, <?=$order['bill_city']?> <?=$order['bill_state']?> - <?=$order['bill_pincode']?> 
                                    Mobile : <?=$order['bill_phone']?>
                                </td>
                            </tr>
                    </table>
                    <br>		 
                    <table width="100%" cellpadding="5" cellspacing="0" border="1">
                            <tr>
                                    <th>No</th>
                                    <th>Item</th>
                                    <th width="70">Amount</th>
                                    <th width="40">Qty</th>
                                    <!--<th width="">Invoices</th>-->
                                    <th width="70">Total</th>
                            </tr>
                            <?php 
                                    $k1=0;
                                    $list_invs_group='';
                                    foreach($orderslist_byproduct as $itm_ord) {
                                        $inv_amt =  $itm_ord['amt']*$itm_ord['qty'];
                                        
                                        $list_invs_group_arr[] = $itm_ord['invs'];
                            ?>
                            <tr>
                                    <td><?php echo ++$k1; ?></td>
                                    <td>
                                            <span class="showinprint"><?php echo $itm_ord['print_name'].'-'.$itm_ord['pnh_id'];?></span>
                                            <span class="hideinprint"><?php echo $itm_ord['name'].'-'.$itm_ord['pnh_id'];?></span>
                                    </td>
                                    <td><?php echo $itm_ord['amt'];?></td>
                                    <?php //<td>
                                    //$ind_invs = $itm_ord['invs'];
                                    //echo str_replace(',',', ',$itm_ord['invs']);
                                    //echo "<br>".count($list_invs_group_arr);
                                    //</td>?>
                                    <td><?php echo $itm_ord['qty'];?></td>
                                    <td><?php echo $inv_amt;?></td>
                            </tr>
                            <?php 		
                                            
                                            //$list_invs_group .= $itm_ord['invs'].',';
                                            $ttl_inv_amt +=  $inv_amt;
                                    }
                            ?>
                            <tr>
                                    <td colspan="4" align="right">
                                        
                                        <span  style="margin-right: 20px;">Total amount to be collected</span>
                                    </td>
                                    <td><b><?php echo format_price($ttl_inv_amt,2);?></b></td>
                            </tr>	
                    </table>

                </div>

<?php       //}

       $list_invs_group_arr = array_unique($list_invs_group_arr);
       $list_invs_group_str = implode(',',$list_invs_group_arr);
       
       //$list_invs_arr = explode(",",$list_invs_group_str);
       //echo ''.count($list_invs_arr);
       
?>
        <input type="hidden" name="all_inv_list" id="all_inv_list" value="<?=$list_invs_group_str; ?>"
        </div>
</div>
<script>
function print_taxinv_acknowledgement(ele){ 
        ele.value="RePrint Invoice Acknowledgement Copy";
        log_printcount();
        myWindow=window.open('','','width=950,height=600,scrollbars=yes,resizable=yes');
        myWindow.document.write($("#grouped_tax_inv_ack_copy").html());//+''+$("#customer_acknowlegment").html());
        myWindow.focus();
        myWindow.print();
}

function log_printcount()
{
    var all_inv_list = $("#all_inv_list").val();
        $.post(site_url+'/admin/jx_update_acknowledge_print_log','all_inv_list='+all_inv_list);
        //,function(resp){ alert(resp);});
}
function page_refresh(elt) {
    window.location.href=site_url+"admin/print_invoice_acknowledgementbydate";
}
</script>