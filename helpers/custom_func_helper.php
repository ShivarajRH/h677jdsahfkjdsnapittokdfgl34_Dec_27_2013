<?php
if(!function_exists('cur_datetime')){
	function cur_datetime(){
		return date('Y-m-d H:i:s');
	}
}
if(!function_exists('date_diff_days')){
	function date_diff_days($date_1='',$date_2=''){
		return (strtotime($date_1)-strtotime($date_2))/(24*60*60);
	}
}
	
?>