<div class="page_wrap container" style="width: 98%;">
	
	<h2 class="page_title">IMEI Activation Log <span style="color:#555;font-size: 18px;"><?php echo ($st_d?(' for '.format_date($st_d).' To '.format_date($en_d)):''); ?></span></h2>
	
	<div class="page_topbar" >
		<div class="page_topbar_left fl_left" >
			<ol class="total_overview">
				<li><b><?php echo $ttl_actv; ?> </b>Total</li>
				<li><b><?php echo $ttl_purchased ;?></b>Purchased</li>
				<li><b><?php echo $ttl_activated_msch.'/'.($ttl_inactiv_msch+$ttl_activated_msch)?></b>Active</li>
				<li><b><?php echo 'Rs '.formatInIndianStyle($ttl_imei_activation_credit)?></b>Credits Given</li>
			</ol>
		</div>
		<div class="page_action_buttonss fl_right" align="right">
			
			<span>
				<b>Filter </b> : <input type="text" size="10" id="d_st" name="d_st" value=""> To <input size="10" id="d_en" type="text" name="d_en" value="">
				<input type="button" onclick="fil_bydate()" class="button button-tiny button-pill" value="Submit" > 
			</span>
			
			<a  id="add_delhub" href="<?php echo site_url('admin/pnh_franchise_activate_imei');?>" class="button button-rounded button-small button-flat-primary"> Activate IMEI Now</a>
		</div>
	</div>
	
	<div class="page_content clearboth" style="clear: both">
		<?php
			if(count($imei_actv_list['data']))
			{	
				
				$tbl_data_html = '<table cellpadding="5" cellspacing="0" class="datagrid" width="100%">';
				$tbl_data_html .= '<thead>';
				foreach($imei_actv_list['head'] as $th)
					$tbl_data_html .= '<th>'.$th.'</th>';
			
				$tbl_data_html .= '</thead>';
				$i = $pg;
				$tbl_data_html .= '<tbody>';
				foreach($imei_actv_list['data'] as $tdata)
				{
					$tbl_data_html .= '<tr>';
					foreach(array_keys($imei_actv_list['head']) as $th_i)
					{
						if($th_i == 'slno')
							$tdata[$th_i] = $i+1;
			
						$tbl_data_html .= '	<td>'.$tdata[$th_i].'</td>';
					}
					$tbl_data_html .= '</tr>';
			
					$i = $i+1;
				}
				$tbl_data_html .= '</tbody>';
				$tbl_data_html .= '</table>
					<div class="pagination" align="right">'.($pagination).'</div>
				';
			}else
			{
				$tbl_data_html = '<div align="center"> No data found</div>';
			}
			echo $tbl_data_html;
		?>
	</div>
</div>

<style>
	.leftcont{display: none}
	.fl_left{float: left;}
	.fl_right{float: right;}
	.clearboth{clear:both}
	
	.page_wrap{width: 99%;}
	.page_wrap .page_title{margin:10px 0px}
	.page_wrap .page_topbar{clear: both;overflow: hidden !important;margin-bottom: 3px;clear: both}
	
	.page_wrap .page_topbar .page_topbar_left{width: 49%;}
	.page_wrap .page_topbar .page_topbar_right{width: 49%;}
	.page_wrap .page_content{clear:both}
	
	.page_wrap .page_topbar .total_overview{padding:0px 0px;font-size: 15px;}
	
	.ordered_list{margin:0px;padding-left:10px;}
	ol.total_overview {margin:0px;}
	ol.total_overview li{display: inline-block;margin:0px;background: #ffffe0;padding:4px 8px;overflow: hidden;text-align: left;min-width: 60px;}
	ol.total_overview li b{margin-left: 5px;float: right;background: #FFF;padding:1px 5px;font-size: 12px;}
	ol.total_overview li:hover{background: #ffffa0;cursor:pointer;}
</style>
<script>
	prepare_daterange('d_st','d_en');
	
	function fil_bydate()
	{
		var d_st = $('#d_st').val()?$('#d_st').val():0;
		var d_en = $('#d_en').val()?$('#d_en').val():0;
		
			if(d_st == 0 || d_en == 0 )
			{
				alert("Please select valid date range");
				return false;
			}
		
		location.href = site_url+'/admin/pnh_imei_activation_log/'+d_st+'/'+d_en+'/0';
	}
	
</script>