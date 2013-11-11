
    function reallot_stock_for_all_transaction(userid,pg) {
        if(!confirm("Are you sure you want to reserve available stock for all pending or partial transactions?")) {
            return false;
            //var batch_remarks=prompt("Enter remarks?");
        }
        /*
        var batch_remarks='';'+transid+'/'+ttl_num_orders+'/'+batch_remarks+'/'*/
        var updated_by = userid;
//        $(".working_status").html("<div class='loading'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading...</div>");
        $.post('reserve_avail_stock_all_transaction/'+updated_by,"",function(rdata) {
            loadTransactionList(pg);
        });
//        $(".working_status").html("");
        return false;
    }
    
    function cancel_proforma_invoice(p_invoice_no,pg) {
        if(!confirm("Are you sure you want to cancel proforma invoice?")) {
            return false;
        }
        $.post(site_url+"admin/reservation_cancel_proforma_invoice/"+p_invoice_no,{},function() {
            loadTransactionList(pg);
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
            loadTransactionList(pg);
        });
        
        return false;
    }
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
                alert(rdata);
//                $(".pg").val(pg);
                
                loadTransactionList(pg);
                
            }).done(done).fail(fail);
        }
    }
    */