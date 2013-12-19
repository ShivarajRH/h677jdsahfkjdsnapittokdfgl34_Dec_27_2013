<style>
.leftcont
{
	display:none;
}
#state_sales
{
	float: left;
    width: 100%;
    margin-left:15px;
}
#top_sale_det
{
	float: left;
    margin: 25px 0px 0px 18px;
    width: 544px;
}
#top_prd_brand_det
{
	float: left;
     margin: 28px 22px;
    width: 530px;
}
.sbutton span {
    color: #FFFFFF;

}
#stat_frm_to
{
	float: right;
	width:315px;
}
.heading_wrapper
{
	float:left;
}
#terr_stat
{
	float:left;width:100%;margin-left:15px;
}
#franch_stat
{
	float:left;width:100%;margin-left:15px;
}
#cat_stat
{
	float:left;width:100%;margin-left:15px;
}
#town_stat
{
	float:left;width:100%;margin-top:15px;
}
#brand_stat
{
	float:left;width:100%;margin-left:15px;margin-top:15px;
}
.sales_view
{
	margin-top:32px;
}
.terr_stat_view
{
	margin-top:32px;
}
.town_stat_view
{
	margin-top:32px;
}
.brand_stat_view
{
	margin-top:32px;
}
.franch_stat_view
{
	margin-top:32px;
}
.cat_stat_view
{
	margin-top:32px;
}
.alert_wrap
{
	text-align: center;color:#FF0000;font-weight: bold;font-size: 13px;
}
.alert_wrap_text
{
	text-align: center;
	color: #FF0000;
	font-weight: bold;
	padding-top: 164px;
	font-size: 13px;
}
.sbutton {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    -moz-user-select: none;
    background: -moz-linear-gradient(center top , rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%) repeat scroll 0 0 rgba(0, 0, 0, 0);
    border-color: rgba(0, 0, 0, 0.2) rgba(0, 0, 0, 0.2) rgba(0, 0, 0, 0.4);
    border-image: none;
    border-style: solid;
    border-width: 1px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    line-height: 1em;
    margin-right:7px;
    outline: medium none;
    overflow: visible;
    position: relative;
    white-space: nowrap;
}
.scroll_wrap
{
	overflow:auto;float:left;height:265px;width:200px;
}
.left_blk_wrap
{
	margin-top: 25px;float: left;
}
.sbutton.green {
    background-color: #91BD09;
}
.sbutton, .sbutton span {
    border-radius: 4px;
    display: inline-block;
}
.rightcont
{
	background:#f0f0f0;	padding:0px !important;
}
.green
{
	padding:2px;
}
.anal_head_wrap
{
	 float: left;
}
.brand_popup,.franch_popup
{
	float:right;margin:16px;cursor:pointer;
}
/*
 * Loader  Image Css
 */
/* 
Set the container for the bar
*/
.bar 
{
	  background-color: rgba(0, 0, 0, 0.1);
    border-radius: 20px 20px 20px 20px;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.03), 0 1px 0 rgba(0, 0, 0, 0.1) inset;
    height: 20px;
    margin: 66px auto 68px;
    padding: 10px;
    width: 200px;
}

/* 
This is the actual bar with stripes
*/	
.bar span {
	display:inline-block;
	height:100%;
	width:100%;
	border:1px solid #ff9a1a;
	border-bottom-color:#ff6201;
	background-color:#d3d3d3;
	-webkit-border-radius:20px;
	-moz-border-radius:20px;
	-ms-border-radius:20px;
	border-radius:20px;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	-ms-box-sizing:border-box;
	box-sizing:border-box;
	background-image:
		-webkit-linear-gradient(
		-45deg,
		rgba(255, 154, 26, 1) 25%,
		transparent 25%,
		transparent 50%,
		rgba(255, 154, 26, 1) 50%,
		rgba(255, 154, 26, 1) 75%,
		transparent 75%,
		transparent
	);
	background-image:
		-moz-linear-gradient(
		-45deg,
		rgba(255, 154, 26, 1) 25%,
		transparent 25%,
		transparent 50%,
		rgba(255, 154, 26, 1) 50%,
		rgba(255, 154, 26, 1) 75%,
		transparent 75%,
		transparent
	);
	background-image:
		-ms-linear-gradient(
		-45deg,
		rgba(255, 154, 26, 1) 25%,
		transparent 25%,
		transparent 50%,
		rgba(255, 154, 26, 1) 50%,
		rgba(255, 154, 26, 1) 75%,
		transparent 75%,
		transparent
	);
	background-image:
		linear-gradient(
		-45deg,
		rgba(255, 154, 26, 1) 25%,
		transparent 25%,
		transparent 50%,
		rgba(255, 154, 26, 1) 50%,
		rgba(255, 154, 26, 1) 75%,
		transparent 75%,
		transparent
	);
	-webkit-background-size:50px 50px;
	-moz-background-size:50px 50px;
	-ms-background-size:50px 50px;
	background-size:50px 50px;
	-webkit-animation:move 2s linear infinite;
	-moz-animation:move 2s linear infinite;
	-ms-animation:move 2s linear infinite;
	animation:move 2s linear infinite;
	-webkit-border-radius:20px;
	-moz-border-radius:20px;
	-ms-border-radius:20px;
	border-radius:20px;
	overflow: hidden;
	-webkit-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
	-moz-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
	-ms-box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
	box-shadow:inset 0 10px 0 rgba(255,255,255,.2);
}
#brand_popup,#franch_popup {
    min-height: 250px;
}
#brand_popup,#franch_popup{
    background-color: #FFFFFF;
    border-radius: 10px;
    box-shadow: 0 0 25px 5px #999999;
    color: #111111;
    display: none;
    min-width: 450px;
    padding: 25px;
}
.popclose_button.b-close, .popclose_button.bClose {
    border-radius: 7px;
    box-shadow: none;
    font: bold 131% sans-serif;
   padding: 6px 10px 5px;
    position: absolute;
    right: -7px;
    top: -7px;
}
.popclose_button > span {
    font-size: 84%;
}
.popclose_button {
    background-color: #2B91AF;
    border-radius: 10px;
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3);
    color: #FFFFFF;
    cursor: pointer;
    display: inline-block;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
}
.header_wrap {
background-color: #CCCCCC;
    border-bottom: 1px solid #AAAAAA;
    border-top: 1px solid #AAAAAA;
    color: #000;
    font-size: 14px;
    margin-top:0px;
  	width: 100%;
	float: left;
}
.header_wrap_content
{
	margin-left: 10px;
}
.header_wrap.is-active {
background-color: #fff;color:#000;
}
.rightcont .container
{
	padding:1px !important;
}

/*
Animate the stripes
*/	
@-webkit-keyframes move{
  0% {
  	background-position: 0 0;
  }
  100% {
  	background-position: 50px 50px;
  }
}	
@-moz-keyframes move{
  0% {
  	background-position: 0 0;
  }
  100% {
  	background-position: 50px 50px;
  }
}	
@-ms-keyframes move{
  0% {
  	background-position: 0 0;
  }
  100% {
  	background-position: 50px 50px;
  }
}	
@keyframes move{
  0% {
  	background-position: 0 0;
  }
  100% {
  	background-position: 50px 50px;
  }
}

#more_wrap1,#more_wrap2
{
	margin-left:5px;font-size: 11px !important;
}
</style>

<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/css/analytic.css" />

<?php
	$sid=$this->uri->segment(3);
	$i=1;$j=1;$k=1;$l=1;$m=1;$n=1;
?>

<div class="container">
	<div style="background:#CCCC99">
		 <?php $state_name=$this->db->query("select state_name as name from pnh_m_states where state_id='".$sid."'")->row()->name?>
	</div>
	
	<div class="header_wrap">
		<div style="float: right;margin:15px 0px 13px 0px;">
	    	<form id="stat_frm_to" method="post">
		        <div style="margin:2px 0px;font-size:12px;">
		        	<b>From</b> : <input type="text" style="width: 90px;" id="date_from"
		                    name="date_from" value="<?php echo date('d-m-Y',time()-90*60*60*24)?>" />
		            <b>To</b> : <input type="text" style="width: 90px;" id="date_to"
		                    name="date_to" value="<?php echo date('d-m-Y',time())?>" /> 
		            <button type="submit" class="sbutton small green"><span>Go</span></button>
		        </div>
		    </form>
		    <div style="float: right;margin-right: 0; width: 177px;">
				<b>State</b> : <select name="state" id="sel_state">
									<?php foreach($this->db->query("select * from pnh_m_states order by state_id asc")->result_array() as $s){ ?>
										<option value="<?=$s['state_id']?>" <?php echo (($sid==$s['state_id'])?'selected':'');?> ><?=$s['state_name']?></option>
									<?php } ?>
								</select>
			</div>
	    </div>
	    <h2 class="header_wrap_content"></h2>
	 </div>
	<table width="100%" style="margin-top:15px;">
		<tr>
			<td width="100%">
				<div id="state_sales">
					<h3 class="heading_wrapper"><span id="stat_head"></span></h3>
					<div class="sales_view">
					</div>
				</div>
				
				<div id="terr_stat">
					<h3 class="heading_wrapper"><span id="terr_head"></span><span id="more_wrap1">(Click On Territory to view more)</span></h3>
					<div class="terr_stat_view">
					</div>
				</div>
			</td>
		</tr>
	</table>
	
	<table width="100%">
		<tr>
			<td width="50%">
				<div id="town_stat">
					<h3 class="anal_head_wrap"><span id="town_head"></span><span id="more_wrap2">(Click On Town to view more)</span></h3>
					<div class="town_stat_view">
					</div>
				</div>
			</td>
			<td width="50%">
				<div id="brand_stat">
					<h3 class="anal_head_wrap"><span id="brand_head"></span></h3><a class="brand_popup">All Brands</a>
					<div class="brand_stat_view">
					</div>
				</div>
				<div id="brand_popup">
				</div>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td width="50%">
				<div id="franch_stat">
					<h3 class="anal_head_wrap"><span id="franch_head"></span></h3><a class="franch_popup">All Franchises</a>
					<div class="franch_stat_view">
					</div>
				</div>
				<div id="franch_popup">
				</div>
			</td>
			<td>
				<div id="cat_stat">
					<h3 class="anal_head_wrap"><span id="cat_head"></span></h3>
					<div class="cat_stat_view">
					</div>
				</div>
				
			</td>
		</tr>
	</table>
			
	<table style="display:none;">
		<tr>
			<td>
				<div id="top_prd_brand_det">
					<ul>
						<li><a href="#top_prd_list" onclick="load_prods()">Top sold Products</a></li>
						<li><a href="#top_brand_list" onclick="load_brands()">Top sold Brands</a></li>
					</ul>
					
					<div id="top_prd_list">
						<div class="list_data" style="overflow:auto;float:left;height:265px;width:490px;"> 
							<table class="datagrid" width="100%">
								<thead>
									<tr>
										<th>Sl.No</th>
										<th>Name</th>
										<th>Total Sales</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
					<div id="top_brand_list">
						<div class="list_data" style="overflow:auto;float:left;height:265px;width:490px;">
							<table class="datagrid" width="100%">
								<thead>
									<tr>
										<th>Sl.No</th>
										<th>Name</th>
										<th>Total Sales</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>
	
</div>

<script>
 (function(){
    $('.header_wrap').sticky({
        stickyClass: 'sticky',
        anchorClass: 'sticky-anchor'
    });
}());

$(function(){
	var state_name='<?php echo $state_name ?>';
	$('.header_wrap_content').html(state_name+' State Analytics for period of '+$('#date_from').val()+' and '+$('#date_to').val());
	$("#date_from,#date_to").datepicker({dateFormat:'dd-mm-yy'});
	$('#state_sales .sales_view').html('<div class="bar"><span></span></div>');
    $('#terr_stat .terr_stat_view').html('<div class="bar"><span></span></div>');
	order_stat();
	terr_stat();
	$('#top_sale_det').tabs();
	$('#top_prd_brand_det').tabs();
	load_prods();
	$('.brand_popup').hide();
	$('.franch_popup').hide();
	$('#more_wrap1').hide();
	$('#more_wrap2').hide();
	state_id ="<?php echo $this->uri->segment(3);?>";
	
	$("#sel_state").change(function(){
		v=$(this).val();
		if(v==0)
			v = '';
		location="<?=site_url("admin/pnh_state_analytics")?>/"+v;
	});
});

$("#stat_frm_to").bind("submit",function(e){
    e.preventDefault();
    order_stat();
    terr_stat();
    $('#brand_stat').hide();
    $('#town_stat').hide();
     $('#franch_stat').hide();
    $('#cat_stat').hide();
   
   $('#state_sales .sales_view').html('<div class="bar"><span></span></div>');
   $('#terr_stat .terr_stat_view').html('<div class="bar"><span></span></div>');
    $('#cat_stat .cat_stat_view').unbind('jqplotDataClick');
   	$('#terr_stat .terr_stat_view').unbind('jqplotDataClick');
    $('#brand_stat .brand_stat_view').unbind('jqplotDataClick');
    $('#town_stat .town_stat_view').unbind('jqplotDataClick');
    return false;
});

function order_stat()
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_state_sales/'+start_date+'/'+end_date+'/'+state_id,'',function(resp){
		if(resp.summary == 0)
		{
			$('#state_sales .sales_view').html("<div class='alert_wrap' style='padding:113px 0px'>No Sales statisticks found between "+start_date+" and "+end_date+"</div>" );	
		}
		else
		{
			// reformat data ;
			$('#stat_head').html("Sales from "+start_date+" to "+end_date);
			if(resp.date_diff <= 31)
		  	{
		  		var interval = 100000;
		    }
			else
			{
				var interval = 2500000;
			}
			$('#state_sales .sales_view').empty();
			plot2 = $.jqplot('state_sales .sales_view', [resp.summary], {
		       	
		       	 seriesDefaults: {
			        showMarker:false,
			        pointLabels: { show:true }
			      },
			      
				    
				  axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Date',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
				          min : 0,
						  tickInterval : interval,
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			});
		}
	});
}

function terr_stat()
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	
	//$('#stat_head').html("Sales from "+start_date+" to "+end_date);
	$.getJSON(site_url+'/admin/jx_getterritorybystateid/'+state_id+'/'+start_date+'/'+end_date,'',function(resp){
		$('#terr_head').html('Territory Sales');
		if(resp.summary == 0)
		{
			$('#terr_stat .terr_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No territory stats Found</div>" );	
		}
		else
		{
			// reformat data ;
			$('#more_wrap1').show();
			$('#terr_stat .terr_stat_view').empty();
			var resp=resp.summary;
			plot2 = $.jqplot('terr_stat .terr_stat_view', [resp], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Territories',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			     },
			      cursor: {
			        show: false
			      }
			});
		}
		//$.jqplot.eventListenerHooks.push(['jqplotClick', myClickHandler]);
		$('#terr_stat .terr_stat_view').bind('jqplotDataClick', function(ev,seriesIndex,pointIndex,data) {
				var terr_id = resp[pointIndex][2];
			 	var terr_name = resp[pointIndex][0];
			 	town_sales(terr_id,terr_name);
			 	brand_sales(terr_id,terr_name);
			 	franch_sales(terr_id,terr_name);
			 	all_ter_brands(terr_id,terr_name);
			 	all_ter_franchises(terr_id,terr_name);
			 	cat_sales(terr_id,terr_name);
			 	
			 	$('#town_head').html('Towns Sales for '+terr_name+' territory');
			 	$('#cat_head').html('Categories for '+terr_name+' territory');
			 	$('#brand_head').html('Brand Sales for '+terr_name+' territory');
			 	$('#franch_head').html('Top Franchise in '+terr_name+' territory');
			 	
				$('#town_stat .town_stat_view').unbind('jqplotDataClick');
				$('#town_stat .town_stat_view').html('<div class="bar"><span></span></div>');
				$('#franch_stat .franch_stat_view').unbind('jqplotDataClick');
				$('#franch_stat .franch_stat_view').html('<div class="bar"><span></span></div>');
				$('#cat_stat .cat_stat_view').html('<div class="bar"><span></span></div>');
				$('#brand_stat .brand_stat_view').html('<div class="bar"><span></span></div>');
		});
	});
}

function all_ter_brands(terr_id,terr_name)
{
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.post(site_url+'/admin/jx_getallbrandsbyterrid/',{terr_id:terr_id,start_date:start_date,end_date:end_date},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var prod_list_html = '';
		 prod_list_html += '<h4>Brand List for '+terr_name+' Territory</h4>';
		 prod_list_html +='<span class="popclose_button b-close"><span>X</span></span><div style="overflow:auto;float:left;height:265px;width:600px;">';
		 prod_list_html += '<table class="datagrid" width="100%"><thead><tr><th>Sl.No</th><th>Name</th><th>Qty</th><th>Total Sales</th></tr></thead><tbody>';
			$.each(resp.brand_list,function(a,b){
				prod_list_html += '<tr>'
										+'<td>'+(++a)+'</td>'
										+'<td><a target="blank" href="<?php echo site_url("admin/viewbrand/'+b.id+'") ?>#analytics">'+b.name+'</a></td>'
										+'<td>'+b.qty_sold+'</td>'
										+'<td>'+b.ttl+'</td>'
									+'</tr>';
			});
		prod_list_html += '</tbody></table></div>';
		$('#brand_popup').html(prod_list_html);
		$('.brand_popup').show();
	}
	},'json');
}

function all_ter_franchises(terr_id,terr_name)
{
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.post(site_url+'/admin/jx_getallfranchisesbyterrid/',{terr_id:terr_id,start_date:start_date,end_date:end_date},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var fran_list_html = '';
		 fran_list_html += '<h4>Franchise List for '+terr_name+' territory</h4>';
		 fran_list_html +='<span class="popclose_button b-close"><span>X</span></span><div style="overflow:auto;float:left;height:265px;width:600px;">';
		 fran_list_html += '<table class="datagrid" width="100%"><thead><tr><th>Sl.No</th><th>Name</th><th>Total Sales</th></tr></thead><tbody>';
			$.each(resp.fran_list,function(a,b){
				fran_list_html += '<tr>'
										+'<td>'+(++a)+'</td>'
										+'<td><a target="blank" href="<?php echo site_url("admin/pnh_franchise/'+b.franchise_id+'") ?>#analytics">'+b.franchise_name+'</a></td>'
										+'<td>'+b.ttl+'</td>'
									+'</tr>';
			});
		fran_list_html += '</tbody></table></div>';
		$('#franch_popup').html(fran_list_html);
		$('.franch_popup').show();
	}
	},'json');
	
}


$('.brand_popup').click(function(){
	$('#brand_popup').bPopup({
	easing: 'easeOutBack', //uses jQuery easing plugin
	    speed: 450,
	    transition: 'slideDown'
	});
});

$('.franch_popup').click(function(){
	$('#franch_popup').bPopup({
	easing: 'easeOutBack', //uses jQuery easing plugin
	    speed: 450,
	    transition: 'slideDown'
	});
});

function town_sales(terr_id,terr_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$('#more_wrap2').show();
	$.getJSON(site_url+'/admin/jx_gettownsbystateid/'+state_id+'/'+terr_id+'/'+start_date+'/'+end_date,'',function(resp){
		    	
    	if(resp.summary == 0)
		{
			$('#town_stat .town_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No Town stats</div>" );	
		}
		else
		{
			// reformat data ;
			var resp=resp.summary;
			$('#town_stat .town_stat_view').empty();
			plot2 = $.jqplot('town_stat .town_stat_view', [resp], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Towns',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			});
			$('#town_stat .town_stat_view').bind('jqplotDataClick', function(ev,seriesIndex,pointIndex,data) {
					var town_id = resp[pointIndex][2];
				 	var town_name = resp[pointIndex][0];
				 	
				 	$('#brand_head').html('Brand Sales for '+town_name+' town');
				 	$('#franch_head').html('Top Franchises for '+town_name+' town');
				 	$('#cat_head').html("Top categories sold in "+town_name+" town");
				 	$('#cat_stat .cat_stat_view').html('<div class="bar"><span></span></div>');
					$('#brand_stat .brand_stat_view').html('<div class="bar"><span></span></div>');
					$('#franch_stat .franch_stat_view').html('<div class="bar"><span></span></div>');
				 	town_brand_sales(town_id,town_name);
				 	town_franch_sales(town_id,town_name);
				 	town_cat_sales(town_id,town_name);
				 	all_town_brands(town_id,town_name);
				 	all_town_franchises(town_id,town_name);
			});
		  }
		});
	$('#town_stat').show();
}

function all_town_franchises(town_id,town_name)
{
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.post(site_url+'/admin/jx_getallfranchisesbytownid/',{town_id:town_id,start_date:start_date,end_date:end_date},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var fran_list_html = '';
		  fran_list_html += '<h4>Franchise List for '+town_name+' town</h4>';
		 fran_list_html +='<span class="popclose_button b-close"><span>X</span></span><div style="overflow:auto;float:left;height:265px;width:600px;">';
		 fran_list_html += '<table class="datagrid" width="100%"><thead><tr><th>Sl.No</th><th>Name</th><th>Total Sales</th></tr></thead><tbody>';
			$.each(resp.fran_list,function(a,b){
				fran_list_html += '<tr>'
										+'<td>'+(++a)+'</td>'
										+'<td>'+b.franchise_name+'</td>'
										+'<td>'+b.ttl+'</td>'
									+'</tr>';
			});
		fran_list_html += '</tbody></table></div>';
		$('#franch_popup').html(fran_list_html);
	}
	},'json');
}

function all_town_brands(town_id,town_name)
{
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.post(site_url+'/admin/jx_getallbrandsbytownid/',{town_id:town_id,start_date:start_date,end_date:end_date},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var prod_list_html = '';
		  prod_list_html += '<h4>Brand List for '+town_name+' Territory</h4>';
		 prod_list_html +='<span class="popclose_button b-close"><span>X</span></span><div style="overflow:auto;float:left;height:265px;width:600px;">';
		 prod_list_html += '<table class="datagrid" width="100%"><thead><tr><th>Sl.No</th><th>Name</th><th>Total Sales</th></tr></thead><tbody>';
			$.each(resp.brand_list,function(a,b){
				prod_list_html += '<tr>'
										+'<td>'+(++a)+'</td>'
										+'<td>'+b.name+'</td>'
										+'<td>'+b.ttl+'</td>'
									+'</tr>';
			});
		prod_list_html += '</tbody></table></div>';
		$('#brand_popup').html(prod_list_html);
	}
	},'json');
}

function brand_sales(terr_id,terr_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_get_territory_brand_sales_by_stateid/'+state_id+'/'+terr_id+'/'+start_date+'/'+end_date,'',function(resp){
		    	
    	if(resp.summary == 0)
		{
			$('#brand_stat .brand_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No Brand stats found</div>" );	
		}
		else
		{
			// reformat data ;
			$('#brand_stat .brand_stat_view').empty();
			plot2 = $.jqplot('brand_stat .brand_stat_view', [resp.summary], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Brands',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			  });
		   }
		});
	$('#brand_stat').show();
}

function town_brand_sales(town_id,town_name)
{
	
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_get_town_brand_sales_by_stateid/'+state_id+'/'+town_id+'/'+start_date+'/'+end_date,'',function(resp){
		    	
    	if(resp.summary == 0)
		{
			$('#brand_stat .brand_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No brand stats found</div>" );	
		}
		else
		{
			// reformat data ;
			$('#brand_stat .brand_stat_view').empty();
			plot2 = $.jqplot('brand_stat .brand_stat_view', [resp.summary], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Brands',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			  });
		   }
		});
	$('#brand_stat').show();
}

function franch_sales(terr_id,terr_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_getfranchisebyterritoryid/'+state_id+'/'+terr_id+'/'+start_date+'/'+end_date,'',function(resp){
		    	
    	
		if(resp.summary == 0)
		{
			$('#franch_stat .franch_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No Franchises Found</div>" );	
		}
		else
		{
			// reformat data ;
			$('#franch_stat .franch_stat_view').empty();
			plot2 = $.jqplot('franch_stat .franch_stat_view', [resp.summary], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Franchise',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			  });
		   }
		});
	$('#franch_stat').show();
}


function town_franch_sales(town_id,town_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_getfranchisebytown/'+state_id+'/'+town_id+'/'+start_date+'/'+end_date,'',function(resp){
		    	
    	if(resp.summary == 0)
		{
			$('#franch_stat .franch_stat_view').html("<div class='alert_wrap' style='padding:113px 0px'>No Franchises Found</div>" );	
		}
		else
		{
			// reformat data ;
			$('#franch_stat .franch_stat_view').empty();
			plot2 = $.jqplot('franch_stat .franch_stat_view', [resp.summary], {
		       	seriesDefaults:{
		            renderer:$.jqplot.BarRenderer,
		            rendererOptions: {
		                // Set the varyBarColor option to true to use different colors for each bar.
		                // The default series colors are used.
		                varyBarColor: true
		            },pointLabels: { show: true }
		        },
			    axesDefaults: {
			        tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
			        tickOptions: {
			          fontFamily: 'tahoma',
			          fontSize: '11px',
			          angle: -30
			        }
			    },
			    axes:{
			        xaxis:{
			          renderer: $.jqplot.CategoryAxisRenderer,
			          	label:'Franchise',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
			        },
			        yaxis:{
						  label:'Total Sales in Rs',
				          labelOptions:{
				            fontFamily:'Arial',
				            fontSize: '14px'
				          },
				          labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				        }
			      }
			  });
		   }
		});
	$('#franch_stat').show();
}


function cat_sales(terr_id,terr_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_catsalesbyterritoryid/'+state_id+'/'+terr_id+'/'+start_date+'/'+end_date,'',function(resp){
	  	$('#cat_head').html("Top Categories sold in "+terr_name+" territory");
		if(resp == 0)
		{
			$('#cat_stat .cat_stat_view').html("<div class='alert_wrap' style='padding:113px 0px;'>No Category sales Found</div>" );	
		}
		else
		{	
		// reformat data ;
			$('#cat_stat .cat_stat_view').empty();
			var resp = resp.summary;
			plot3 = jQuery.jqplot('cat_stat .cat_stat_view', [resp], 
			{
				seriesDefaults:{
		            renderer: jQuery.jqplot.PieRenderer,
		            pointLabels: { show: true },
	                rendererOptions: {
	                    // Put data labels on the pie slices.
	                    // By default, labels show the percentage of the slice.
	                    showDataLabels: true,
	                  }
		        },
		        highlighter: {
				    show: true,
				    useAxesFormatters: false, // must be false for piechart   
				    tooltipLocation: 's',
				    formatString:'Category : %s'
				},
				grid: {borderWidth:0, shadow:false,background:'#eaeaea'},
		       legend:{show:true,rendererOptions: {
				            numberColumns: 2
				        } }
		    });
		 }
	});
	$('#cat_stat').show();
}

function town_cat_sales(town_id,town_name)
{
	state_id ="<?php echo $this->uri->segment(3);?>";
	start_date= $('#date_from').val();
	end_date= $('#date_to').val();
	$.getJSON(site_url+'/admin/jx_catsalesbytown/'+state_id+'/'+town_id+'/'+start_date+'/'+end_date,'',function(resp){
	  	
		if(resp == 0)
		{
			$('#cat_stat .cat_stat_view').html("<div class='alert_wrap' style='padding:113px 0px;'>No Category sales Found</div>" );	
		}
		else
		{	
		// reformat data ;
			$('#cat_stat .cat_stat_view').empty();
			var resp = resp.summary;
			plot3 = jQuery.jqplot('cat_stat .cat_stat_view', [resp], 
			{
				seriesDefaults:{
		            renderer: jQuery.jqplot.PieRenderer,
		            pointLabels: { show: true },
	                rendererOptions: {
	                    // Put data labels on the pie slices.
	                    // By default, labels show the percentage of the slice.
	                    showDataLabels: true,
	                  }
		        },
		        highlighter: {
				    show: true,
				    useAxesFormatters: false, // must be false for piechart   
				    tooltipLocation: 's',
				    formatString:'Category : %s'
				},
				grid: {borderWidth:0, shadow:false,background:'#eaeaea'},
		       legend:{show:true,rendererOptions: {
				            numberColumns: 2
				        } }
		    });
		 }
	});
	$('#cat_stat').show();
}

function load_prods()
{
	var sid=<?php echo $this->uri->segment(3); ?>;
	$('#top_prd_list .list_data .datagrid tbody').html('<tr><td colspan="8"><div align="center"><img src="'+base_url+'/images/loading_bar.gif'+'"> </div></td></tr>');
	
	$.post(site_url+'/admin/jx_getproductsbystateid/',{sid:sid},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var prod_list_html = '';
				$.each(resp.prod_list,function(a,b){
					prod_list_html += '<tr>'
											+'<td>'+(++a)+'</td>'
											+'<td>'+b.product_name+'</td>'
											+'<td>'+b.ttl+'</td>'
										+'</tr>';
				});
			$('#top_prd_list .list_data .datagrid tbody').html(prod_list_html);
	}
},'json');

}

function load_brands()
{
	var sid=<?php echo $this->uri->segment(3); ?>;
	$('#top_brand_list .list_data .datagrid tbody').html('<tr><td colspan="8"><div align="center"><img src="'+base_url+'/images/loading_bar.gif'+'"> </div></td></tr>');
	
	$.post(site_url+'/admin/jx_getbrandsbystateid/',{sid:sid},function(resp){
	if(resp.status == 'error')
	{
		alert(resp.error);
	}else
	{
		 var brand_list_html = '';
				$.each(resp.brand_list,function(a,b){
					brand_list_html += '<tr>'
											+'<td>'+(++a)+'</td>'
											+'<td>'+b.name+'</td>'
											+'<td>'+b.ttl+'</td>'
										+'</tr>';
				});
			$('#top_brand_list .list_data .datagrid tbody').html(brand_list_html);
	}
},'json');

}


</script>
