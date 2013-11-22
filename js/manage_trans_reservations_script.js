//var GM_TIMING_END_CHUNK1=(new Date).getTime();
//*************/
$("#show_picklist_block").dialog({
    autoOpen: false,
    open:function() {
      $("form",this).submit();  
    },
    height: 650,
    width:950,
    modal: true
});
$("#pick_all").live("change",function() {
    var checkBoxes=$(".pick_list_trans_ready");
    if($(this).is(":checked")) {
        checkBoxes.attr("checked", !checkBoxes.attr("checked"));
    }
    else {
        checkBoxes.removeAttr("checked", checkBoxes.attr("checked"));
    }
});

$("#btn_generate_pick_list").live("click",function(){
    var pick_list_trans_ready=$("input.pick_list_trans_ready:checked").length;
    var pick_list_trans_partial=$("input.pick_list_trans_partial:checked").length;
    var total=(pick_list_trans_ready+pick_list_trans_partial);
    if(total==0) { alert("Please select any of transaction to generate pick list"); return false;}
    var p_invoice_ids=[];
    $.each($("input.pick_list_trans_ready:checked"),function() {
        p_invoice_ids.push($(this).val());
    });
    $.each($("input.pick_list_trans_partial:checked"),function() {
        p_invoice_ids.push($(this).val());
    });
    var p_invoice_ids_str = p_invoice_ids.join(",");
    
    $("#show_picklist_block input[name='pick_list_trans']").val(p_invoice_ids_str);
    $("#show_picklist_block").dialog("open").dialog('option', 'title', 'Pick List for '+p_invoice_ids.length+" proforma invoice/s");
});
/* end picklist code*/

$(".reservation_action_status").dialog({
    autoOpen: false,
    open:function() {   //$("form",this).submit();
    },
    top:120,
    height: 133,
    width:433,
    modal: true
});
function reallot_stock_for_all_transaction(userid,pg) {
    if(!confirm("Are you sure you want to reserve available stock for all pending or partial transactions?")) {
        return false;
    }
    var updated_by = userid;
    $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
    $.post(site_url+"admin/reserve_avail_stock_all_transaction/"+updated_by,"",function(rdata) {
            if(rdata == '') {
                rdata=("No transaction processed for allotment.");
            }
            else {
                loadTransactionList(pg);
            }
            $(".reservation_action_status").html(rdata).dialog("open").dialog('option', 'title', 'Re-allot Transaction Reservation report');
    });
    return false;
}
 
function reserve_stock_for_trans(userid,transid,pg) {
    if(!confirm("Are you sure you want to process \nthis transaction for batch?")) {
        return false;
        //var batch_remarks=prompt("Enter remarks?");
    }
    var ttl_num_orders=$("."+transid+"_total_orders").val();
    var batch_remarks='';
    var updated_by = userid;

    $.post('reserve_stock_for_trans/'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'+updated_by+'',"",function(rdata) {
        
            if(rdata == '') {
                rdata=("No transaction processed for allotment.");
            }else {
                loadTransactionList(pg);
            }
            $(".reservation_action_status").html(rdata).dialog("open").dialog('option', 'title', 'Re-allot Transaction Reservation report');

    });
    return false;
}

function cancel_proforma_invoice(p_invoice_no,userid,pg) {
    if(!confirm("Are you sure you want to cancel proforma invoice?")) {
        return false;
    }
    $.post(site_url+"admin/cancel_reserved_proforma_invoice/"+p_invoice_no+"/"+userid,{},function(rdata) {
            if(rdata == '') {
                rdata=("Unable to cancel the proforma.");
            }else {
                loadTransactionList(pg);
            }
            $(".reservation_action_status").html(rdata).dialog("open").dialog('option', 'title', 'Cancel proforma invoice report');
    });
    return false;
}
    
//filter box show/hide
$(".close_filters").toggle(function() {
    $(".close_filters .close_btn").html("Hide");
    $(".filters_block").slideDown();
//    $(".level1_filters").animate({"width":"100%"});
},function() {
    $(".filters_block").slideUp();
    
    $(".close_filters .close_btn").html("Show");
    //    $(".level1_filters").animate({"width":"auto"});
});
   
//Onchange limit
$("#limit_filter").live("change",function() {
    loadTransactionList(0);
    return false;
});
// Onclick tab button
$(".tab_list a").bind("click",function(e){
    $(".tab_list a.selected").removeClass('selected');
    $(this).addClass('selected');
    loadTransactionList(0);
});

//Show between date ranges
$("#trans_date_form").submit(function() {
    loadTransactionList(0);
    return false;
});
//ONCHANGE Batch_type
$("#batch_type").live("change",function() {
    loadTransactionList(0);
    return false;
});


//Paginations
$(".trans_pagination a").live("click",function(e) {
    e.preventDefault();
    $(".page_num").val=pg;
    $('#trans_list_replace_block').html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
    $.post($(this).attr("href"),{},function(rdata) {
        $("#trans_list_replace_block").html(rdata);
    });
    return false;
});

$("#sel_menu").live("change",function() {
        var menuid=$(this).find(":selected").val();//text();

        $.post(site_url+"admin/jx_get_brandsbymenuid/"+menuid,{},function(resp) {
                if(resp.status=='success') {
                     var obj = jQuery.parseJSON(resp.brands);
                    $("#sel_brands").html(objToOptions_brands(obj));
                }
                else {

                    //$(".sel_status").html(resp.message);
                }
            },'json').done(done).fail(fail);

            $("#sel_territory").val($("#sel_territory option:nth-child(0)").val());
            $("#sel_town").val($("#sel_town option:nth-child(0)").val());
            $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
            $("#sel_brands").val($("#sel_brands option:nth-child(0)").val());

    loadTransactionList(0);
    return false;
});

$("#sel_brands").live("change",function() {
    loadTransactionList(0);
    return false;
});
$("#sel_franchise").live("change",function() {
            /*var franchiseid=($("#sel_franchise").val()=='00')? 00 :$("#sel_franchise").val();
            if(franchiseid==00) {
                $(".sel_status").html("");
            }   $.post("<?php echo site_url("admin/jx_franchise_creditnote"); ?>"+"/"+franchiseid,{},function(resp) {
                if(resp.status=='success') {
                     $(".sel_status").html(resp);
                }
                else {
                    $(".sel_status").html(resp);
                }
            }).done(done).fail(fail);*/
    loadTransactionList(0);
    return false;
});

//ENTRY 6
$("#sel_town").live("change",function() { 
    var townid=$(this).find(":selected").val();//text();
    var terrid=$("#sel_territory").find(":selected").val();//text();
    $.post(site_url+"admin/jx_suggest_fran/"+terrid+"/"+townid,function(resp) {
            if(resp.status=='success') {
                 var obj = jQuery.parseJSON(resp.franchise);
                $("#sel_franchise").html(objToOptions_franchise(obj));
            }
            else {
                $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
                //$(".sel_status").html(resp.message);
            }
        },'json').done(done).fail(fail);

    loadTransactionList(0);
    return false;
});


//ONCHANGE Territory
$("#sel_territory").live("change",function() {
    var terrid=$(this).find(":selected").val();//text();
//        if(terrid=='00') {          $(".sel_status").html("Please select territory."); return false;        }

   // $("table").data("sdata", {terrid:terrid});

    $.post(site_url+"admin/jx_suggest_townbyterrid/"+terrid,function(resp) {
        if(resp.status=='success') {
             //print(resp.towns);
             var obj = jQuery.parseJSON(resp.towns);
            $("#sel_town").html(objToOptions_terr(obj));
        }
        else {
            $("#sel_town").val($("#sel_town option:nth-child(0)").val());
            $("#sel_franchise").val($("#sel_franchise option:nth-child(0)").val());
                        //$(".sel_status").html(resp.message);
        }
    },'json').done(done).fail(fail);
    loadTransactionList(0);
    return false;
});


/*  *********************************************************************** */
/***  REPEATED FUNCTIONS ****************/



function objToOptions_brands(obj) {
    var output='';
        output += "<option value='00' selected>All Brands</option>\n";
    $.each(obj,function(key,elt){
        if(obj.hasOwnProperty(key)) {
            output += "<option value='"+elt.id+"'>"+elt.name+"</option>\n";
        }
    });
    return(output);
}
function objToOptions_terr(obj) {
    var output='';
        output += "<option value='00' selected>All Towns</option>\n";
    $.each(obj,function(key,elt){
        if(obj.hasOwnProperty(key)) {
            output += "<option value='"+elt.id+"'>"+elt.town_name+"</option>\n";
        }
    });
    return(output);
}
function objToOptions_franchise(obj) {
    var output='';
        output += "<option value='00' selected>All Franchise</option>\n";
    $.each(obj,function(key,elt){
        if(obj.hasOwnProperty(key)) {
            output += "<option value='"+elt.franchise_id+"'>"+elt.franchise_name+"</option>\n";
        }
    });
    return(output);
}
/*
function batch_enable_disable(transid,flag,pg) {
    var d_msg=(flag==1)?"enable":"disable";
    if(confirm("Are you sure you want to "+d_msg+" for batch?")) {
        $.post(site_url+"admin/jx_batch_enable_disable/"+transid+"/"+flag,{},function(rdata) {
            loadTransactionList(pg);
        }).done(done).fail(fail);
    }
}
*/
function f1(){
    //21/12/2013
    
    var re= /[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/;
    var el= document.getElementById('temp_date');
    var M;
        if (M= el.value.match(re));
        return false;
    return true;
}