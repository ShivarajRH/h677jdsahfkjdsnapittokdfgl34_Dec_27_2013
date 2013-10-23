mklink /j "%APPDATA%\Microsoft\Sticky Notes" "C:\Users\User\Google Drive\Sticky Notes"
mklink /j "C:\Users\User\Downloads" "C:\Users\User\Google Drive\Downloads"

#List of Ajax requests in pnh_offline_order page:

admin/pnh_place_quote+$("#fr_req_quote_frm").serialize()

admin/pnh_jx_show_schemes+{id:fid}

admin/pnh_jx_loadmemids+{fid:fid}

admin/pnh_jx_loadfranchisebyid+{fid:fid}

admin/pnh_jx_loadfranchisebymobile+{mobile:fmobile}

/admin/jx_reg_newmem+$('#reg_mem_frm').serialize()

admin/pnh_jx_checkstock_order+{attr:attr,pids:ppids.join(","),qty:qty.join(","),fid:$('#i_fid').val(),mid:$("input[name='mid']",$(this)).val()}

admin/pnh_jx_searchdeals+{fid:$("#i_fid").val(),q:q}

admin/pnh_jx_loadpnhprodbybarcode+{fid:$("#i_fid").val(),barcode:barcode}

admin/pnh_jx_loadpnhprod+{pid:pid,fid:$("#i_fid").val()}

admin/jx_pnh_getmid+{mid:$(this).val(),more:1}

/admin/jx_to_load_productdata+{pids:ppids.join(","),fid:$("#i_fid").val()}

/admin/jx_pnh_ord_prod_unshipped+{fid:$("#i_fid").val()}

admin/jx_pnh_fran_cancelledorders+{pid:pid,fid:$("#i_fid").val()}

admin/pnh_jx_load_scheme_details+{fid:fid}

select * from t_stock_info order by available_qty desc; // where tmp_brandid=1532657
select * from m_product_info where product_id=1532657;

select * from king_dealitems where is_pnh=1 order by is_pnh desc;and sno = 1532657
select * from king_deals where dealid=1532657

select d.menuid,m.default_margin as margin,available as available_qty from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu m ON m.id=d.menuid where i.is_pnh=1 and i.pnh_id=1532657

select ifnull(sum(s.available_qty),0) as stock,p.*,b.name as brand from m_product_info p left outer join t_stock_info s on s.product_id=p.product_id join king_brands b on b.id=p.brand_id where p.product_id=758

SELECT i.name,i.pnh_id,i.orgprice AS mrp,i.price,i.store_price,d.menuid
									 FROM king_dealitems i
									 JOIN king_deals d ON d.dealid=i.dealid
									 JOIN `pnh_franchise_menu_link` m ON m.menuid=d.menuid
									  WHERE m.status=1 and publish = 1 and live = 1 

select i.name,i.pnh_id,i.orgprice as mrp,i.price,i.store_price,i.available as stk_available from king_dealitems i where i.is_pnh=1

SELECT i.pnh_id AS pid,i.available as stk_available  
SELECT *  
						FROM m_product_info p  
						JOIN m_product_deal_link l ON l.product_id=p.product_id 
						JOIN king_dealitems i ON i.id=l.itemid AND i.is_pnh=1 
						JOIN king_deals d ON d.dealid=i.dealid  
						JOIN `pnh_franchise_menu_link` m ON m.menuid=d.menuid 
						WHERE p.barcode=?

select * from king_dealitems

select distinct * from t_stock_info si
left join m_product_info pi on pi.product_id=si.product_id
order by available_qty desc;

select count(*) as t from pnh_franchise_menu_link where status = 1 and menuid in (select menuid 
													from king_dealitems a
													join king_deals b on a.dealid = b.dealid  )

select * from king_dealitems
 where pnh_id != 0 and is_pnh=1 and available!=0
order by available desc

desc king_dealitems;

select * from king_deals

select distinct * from t_stock_info si
left join m_product_info pi on pi.product_id=si.product_id
order by available_qty desc;

select si.*,d.* from t_stock_info si
join m_product_deal_link pdl
join king_dealitems as d on d.dealid=pdl.itemid
where si.product_id=pdl.product_id


select di.max_allowed_qty,si.available_qty from king_dealitems di
join m_product_deal_link pdl on pdl.itemid=di.dealid
join t_stock_info si on si.product_id=pdl.product_id
where di.pnh_id = 151


#====== Oct-08-2013 =============
select * from king_transactions 
where transid='PNH19996'
order by init desc;

select * from pnh_menu;

select i.*,d.publish,c.loyality_pntvalue,d.menuid from king_dealitems i join king_deals d on d.dealid=i.dealid JOIN pnh_menu c ON c.id = d.menuid where i.is_pnh=1 and  i.pnh_id='PNH51365' and i.pnh_id!=0

#OCT-09
select * from pnh_order_margin_track order by id desc;

#Oct-10
 select * from king_orders order by sno desc

select * from t_stock_info order by created_on desc;
select * from t_reserved_batch_stock order by reserved_on desc;
select * from grn_product_link  order by id desc;

cod_pincodes

#Oct-11
select * from king_deals
select * from m_product_info
select * from king_dealitems where id=9893764619
select * from m_product_group_deal_link where itemid=9893764619
select * from m_product_groups

=============================================================================================================================================================================================
# n
create database snapittoday_db_oct;
use snapittoday_db_oct


select md5("admin123"); #0192023a7bbd73250516f069df18b500
# superadmin1 9027da57d66aa309df4d13q0e6ab0d06
select md5("superadmin"); 17c4520f6cfd1ab53d8745e84681eb49


select distinct tr.batch_enabled,d.franchise_id,deal.brandid,deal.menuid,m.name as menu_name,br.name as brand_name,tr.transid,sum(o.i_coup_discount) as com,tr.amount,o.transid,o.status,o.time,o.actiontime,mi.user_id as userid,mi.pnh_member_id from king_orders o
                                join king_transactions tr on tr.transid=o.transid
                                join pnh_member_info mi on mi.user_id=o.userid 
                                join pnh_m_franchise_info d on d.franchise_id = tr.franchise_id
                                join pnh_m_territory_info f on f.id = d.territory_id
                                join pnh_towns e on e.id = d.town_id 
                                join king_dealitems dl on dl.id=o.itemid
                                join king_deals deal on deal.dealid=dl.dealid
                                join king_brands br on br.id = deal.brandid 
                                join pnh_menu m on m.id = deal.menuid 
                                where tr.batch_enabled=0 and o.status=0 
                                group by tr.transid
                                order by tr.init desc

select * from king_dealitems print_name, max_allowed_qty
select print_name, max_allowed_qty from king_dealitems where print_name is not null and max_allowed_qty!=0

select d.*,i.*,d.description,d.keywords,d.tagline from king_dealitems i join king_deals d on d.dealid=i.dealid where i.id=9354839395

=============================================================================================================================================================================================

# Oct-17

select * from pnh_m_franchise_info fi where 1=1 is_suspended =1
where fi.territory_id=? and town_id= and franchise_id=?
335= 82 +248
select 82 +248
select count(*) from pnh_m_franchise_info fi where  fi.is_suspended=0

select count(*) as total from pnh_m_franchise_info

# Oct-18
select count(*) as total from pnh_m_franchise_info where 1=1  and fi.territory_id=14 and fi.town_id=67

select * from pnh_m_franchise_info fi where fi.franchise_id=5 limit 1

select * from pnh_m_franchise_info fi where 1=1 order by fi.created_on asc
select * from pnh_m_franchise_info fi where 1=1 and fi.territory_id=3 and fi.is_suspended=1 order by fi.created_on asc
select * from pnh_m_franchise_info fi where 1=1 and fi.territory_id=3 order by fi.created_on asc

select * from pnh_menu order by name

select mn.id,mn.name from pnh_menu mn

    where mn.status=1 
    group by mn.id
    order by mn.name

    join king_deals deal on deal.menuid=mn.id

select menuid from king_deals group by menuid

#Oct-22-2013
select * from king_deals

select * from king_dealitems dl
join king_deals as d on d.dealid=dl.dealid
where 

select * from king_orders

join m_product_info as pi on pi.product_id=dl.product_id
where dl.dealid=5348265767


select * from king_dealitems