
use database snapittoday_db_oct;
## Nov_06_2013
select a.status from king_orders a where a.transid = 'PNH15791' and a.status = 0

select sum(a.status) as total from king_orders a where a.transid = 'PNHBZC28433' and a.status in (0,1)

set @tid = 'PNHBZC28433';
select (a.status) as total from king_orders a where a.status = 0 and a.transid = @tid;

select * from snapittoday_db_oct.support_tickets_msg;

select a.status,sd.batch_id,c.p_invoice_no as total from king_orders a left join proforma_invoices c on c.order_id = a.id left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no  where a.transid = @tid and a.status in (0,1)

set @tid = 'PNHBZC28433';
select * from (select a.transid,count(a.id) as num_order_ids,sum(a.status) as orders_status
		from king_orders a
		#join king_transactions tr on tr.transid = a.transid
            #left join proforma_invoices c on c.order_id = a.id
            #left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no
            where a.status in (0,1) #and a.transid=@tid
		group by a.transid) as ddd
			where ddd.orders_status=0
# => 34298/608ms => 95/203ms
#,sd.batch_id,c.p_invoice_no

select * from king_transactions where transid='7';

set @tid = 'PNHBZC28433';
select * from (select a.transid,count(a.id) as num_order_ids,sum(a.status) as orders_status
		from king_orders a
		join king_transactions tr on tr.transid = a.transid
            where a.status in (0,1) and tr.batch_enabled=1 #and a.transid=@tid
		group by a.transid) as ddd
			where ddd.orders_status=0
#==> 91/343ms => 85/593ms => 60/577ms

select o.* from king_transactions t 
join king_orders o on o.transid=t.transid and o.status=0 
where t.batch_enabled=1 #and t.transid=?
#==>

set @transid='PNH15791';
select c.invoice_status,a.status,sd.batch_id,c.p_invoice_no as total
from king_orders a 
 join proforma_invoices c on c.order_id = a.id 
left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no  
where a.status in (0,1) and c.invoice_status=1 and a.transid = @transid

#==>51877/62ms

select c.invoice_status,a.status,sd.batch_id,c.p_invoice_no as total from king_orders a join proforma_invoices c on c.order_id = a.id left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no where c.invoice_status=1 and a.status in (0,1)
 and a.transid = 'PNH15791';

select c.invoice_status,a.status,sd.batch_id,c.p_invoice_no as total 
from king_orders a 
left join proforma_invoices c on c.order_id = a.id 
left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
where a.status in (0,1) and a.transid = 'PNH14386'; #PNHZLA55363

# Nov_07_2013

select distinct c.invoice_status,a.status,sd.batch_id,c.p_invoice_no,
from king_orders a 
left join proforma_invoices c on c.order_id = a.id 
left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
where a.status in (0,1) and sd.status=0 and a.transid = 'PNHFDF78772'; #PNHQTX46549 #PNH73789#PNH14386#PNHZLA55363

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
		from king_transactions tr
		left join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
            WHERE tr.actiontime between 1380565800 and 1382639399 and o.status in (0,1) and tr.batch_enabled=1 and o.shipped=0 
            group by tr.transid order by tr.actiontime desc

#========================================================
alter table `pnh_m_manifesto_sent_log` add column `ref_box_no` bigint (10) DEFAULT '0' NULL  after `no_ofboxes`;

alter table `t_grn_product_link` add column `dp_price` decimal (15,4) DEFAULT '0' NULL  after `mrp`;
alter table `t_po_product_link` add column `dp_price` decimal (15,4) DEFAULT '0' NULL  after `mrp`;

select e.invoice_no,sd.packed,sd.shipped,e.invoice_status,sd.batch_id,sd.shipped_on,a.status,a.id,a.itemid,b.name,a.quantity,i_orgprice,i_price,i_discount,i_coup_discount,c.p_invoice_no 
                                                                        from king_orders a
                                                                        join king_dealitems b on a.itemid = b.id
                                                                        join king_deals dl on dl.dealid = b.dealid
                                                                        join king_transactions t on t.transid = a.transid   
                                                                        left join proforma_invoices c on c.order_id = a.id and c.invoice_status = 1 
                                                                        left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = c.p_invoice_no 
                                                                        left join king_invoice e on e.invoice_no = sd.invoice_no
                                                                where a.transid = ? and a.status in (0,1) and c.invoice_status = 1 
                                                                order by c.p_invoice_no desc



// check if any pending order for processing invoice 
select a.id,b.invoice_no 
	from king_orders a 
	left join king_invoice b on a.id = b.order_id and invoice_status = 1 
	where a.transid = 'PNHXKZ65744' and b.id is null 
group by a.id 

## Nov 08 ==========

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
	left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
	left join king_invoice i on o.id = i.order_id and i.invoice_status = 1 
            WHERE tr.actiontime between 1380565800 and 1382725799 and o.status in (0,1) and tr.batch_enabled=1 and i.id is not null
            group by tr.transid order by tr.actiontime desc

#==>105/1201ms ==>87/1185ms

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
            WHERE tr.actiontime between 1380565800 and 1382725799 and o.status in (0,1) and tr.batch_enabled=1 
            group by tr.transid order by tr.actiontime desc

select * from t_reserved_batch_stock;

alter table king_invoice add column ref_dispatch_id bigint(11) default 0;

#### Nov_09_2013 ####

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                ,i.invoice_status,sd.shipped
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
        left join king_invoice i on o.id = i.order_id and i.invoice_status = 1 
                  left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 
            WHERE tr.actiontime between 1380565800 and 1384021799 and o.status in (0,1) and tr.batch_enabled=1   and i.invoice_status=1 and sd.shipped=1
            group by tr.transid order by tr.actiontime desc

#Nov_11_2013

<section class="progress window">
        <h1>Copying "Really Achieving Your Childhood Dreams"</h1>
        <details>
         <summary>Copying... <progress max="375505392" value="97543282"></progress> 25%</summary>
         <dl>
          <dt>Transfer rate:</dt> <dd>452KB/s</dd>
          <dt>Local filename:</dt> <dd>/home/rpausch/raycd.m4v</dd>
          <dt>Remote filename:</dt> <dd>/var/www/lectures/raycd.m4v</dd>
          <dt>Duration:</dt> <dd>01:16:27</dd>
	</dl>
        </details>
       </section>
### .HTACCESS FILE:
#############################################################
#	GZIP Compression
#<ifModule mod_gzip.c>
#mod_gzip_on Yes
#mod_gzip_dechunk Yes
#mod_gzip_item_include file .(html?|txt|css|js|php|pl)$
#mod_gzip_item_include handler ^cgi-script$
#mod_gzip_item_include mime ^text/.*
#mod_gzip_item_include mime ^application/x-javascript.*
#mod_gzip_item_exclude mime ^image/.*
#mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
#</ifModule>
#   End GZIP Compression code

#############################################################
### EXPIRES CACHING ##
#<IfModule mod_expires.c>
#ExpiresActive On
#ExpiresByType image/jpg "access plus 1 year"
#ExpiresByType image/jpeg "access plus 1 year"
#ExpiresByType image/gif "access plus 1 year"
#ExpiresByType image/png "access plus 1 year"
#ExpiresByType text/css "access plus 1 month"
#ExpiresByType application/pdf "access plus 1 month"
#ExpiresByType text/x-javascript "access plus 1 month"
#ExpiresByType application/x-shockwave-flash "access plus 1 month"
#ExpiresByType image/x-icon "access plus 1 year"
#ExpiresDefault "access plus 2 days"
#</IfModule>
## EXPIRES CACHING ##
=============================================================================
### Nov_12_2013

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
        
            WHERE tr.actiontime between 1380565800 and 1384280999 and o.status in (0,1) and tr.batch_enabled=1 
            group by tr.transid order by tr.actiontime desc
	#=> 192/1544 ms
##########
select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id

		left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                  left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 

            WHERE tr.actiontime between 1380565800 and 1384280999 and o.status in (0,1) and tr.batch_enabled=1  and i.invoice_status=1 and sd.shipped=1
            group by tr.transid order by tr.actiontime desc
#=>15 / 1576 ms =>DONE / CANCEL RECORDS

select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_ords
		,tr.transid,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,o.*
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                
		from king_transactions tr
		join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id

		left join king_invoice i on o.id = i.order_id and i.invoice_status=0
                  left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 

            WHERE tr.actiontime between 1380565800 and 1384280999 and o.status in (0,1) and tr.batch_enabled=1 and i.id is not null
            group by tr.transid order by tr.actiontime desc
####
select * from (
select distinct from_unixtime(tr.init,'%D %M %h:%i:%s %Y') as str_time, count(tr.transid) as total_trans,tr.transid
		,sum(o.status) as is_pending,o.status,o.id,o.itemid,o.brandid,o.quantity,o.time,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
		,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
                
		from king_transactions tr
		left join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
        
		left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                  left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 
        
            WHERE tr.actiontime between 1380565800 and 1384280999 and o.status in (0,1) and tr.batch_enabled=1 and i.id is null  
            group by o.transid order by tr.actiontime desc
) as g where is_pending = 0 group by transid;

# is_pending=0 and
# group by tr.transid 
# transid='PNHRTR62345'
is_pending=0 => ready
is_pending=total_trans =>pending
is_pending < total_trans => partial_pending
####
select status from king_orders where transid='PNHYVV59449';

###################
# Nov_13_2013


select * from (
select distinct from_unixtime(tr.init,'%D %M %Y') as str_date,from_unixtime(tr.init,'%h:%i:%s %p') as str_time, count(tr.transid) as total_trans,tr.transid
		,sum(o.status) as is_pending,o.status,o.id,o.itemid,o.brandid,o.quantity,o.time,o.i_orgprice,o.bill_person,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
		,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
		,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
		,ter.territory_name
		,twn.town_name
		,dl.menuid,m.name as menu_name,bs.name as brand_name
        from king_transactions tr
		left join king_orders o on o.transid=tr.transid
		join king_dealitems di on di.id=o.itemid
		join king_deals dl on dl.dealid=di.dealid
		join pnh_menu m on m.id = dl.menuid
		join king_brands bs on bs.id = o.brandid
        left join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id
        left join pnh_m_territory_info ter on ter.id = f.territory_id 
        left join pnh_towns twn on twn.id=f.town_id
                left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                left join shipment_batch_process_invoice_link sd on sd.invoice_no = i.invoice_no 
        WHERE tr.actiontime between 1380565800 and 1384367399 and o.status in (0,1) and tr.batch_enabled=1 and i.id is null 
        group by o.transid order by tr.actiontime desc) as g where  g.is_pending = g.total_trans  group by transid

select from_days(6767)

SELECT STR_TO_DATE('10.31.2013',GET_FORMAT(DATE,'INR'));
        -> '2003-10-31'

#New work ---------------------------------------
CREATE TABLE `pnh_town_courier_priority_link` (  
	  `id` bigint(11) NOT NULL AUTO_INCREMENT,       
	  `town_id` int(11) DEFAULT '0',                 
	  `courier_priority_1` int(5) DEFAULT '0',       
	  `courier_priority_2` int(5) DEFAULT '0',       
	  `courier_priority_3` int(5) DEFAULT '0',       
	  `delivery_hours_1` int(3) DEFAULT '0',         
	  `delivery_hours_2` int(3) DEFAULT '0',         
	  `delivery_hours_3` int(3) DEFAULT '0',         
	  `is_active` tinyint(1) DEFAULT '0',            
	  `created_on` datetime DEFAULT NULL,            
	  `created_by` int(11) DEFAULT '0',              
	  `modified_on` datetime DEFAULT NULL,           
	  `modified_by` int(11) DEFAULT '0',             
	  PRIMARY KEY (`id`)
	);
#---------------------------------------------------------
select * from m_courier_info where is_active =1;

select * from `pnh_town_courier_priority_link` tcp
join pnh_towns tw on tw.id = tcp.town_id
where tcp.is_active=1;

# Nov_14_2013

select distinct tw.id as townid,tw.town_name,tcp.* from pnh_towns tw
left join `pnh_town_courier_priority_link` tcp on tcp.town_id=tw.id
order by town_name;

select town_id from `pnh_town_courier_priority_link` where town_id='3'

select * from `pnh_town_courier_priority_link` tcp;

select * from pnh_towns
select * from pnh_m_territory_info;

select ter.id,ter.territory_name from pnh_m_territory_info ter
join pnh_towns tw on tw.territory_id = ter.id
group by ter.id order by territory_name

select distinct tw.id as townid,tw.town_name,tcp.* from pnh_towns tw
    left join `pnh_town_courier_priority_link` tcp on tcp.town_id=tw.id and tcp.is_active=1    
    order by town_name;

# Nov_15_2013

select distinct tw.id as townid,tw.town_name,tcp.* from pnh_towns tw
    left join `pnh_town_courier_priority_link` tcp on tcp.town_id=tw.id and tcp.is_active=1    
    order by town_name;

select * from `pnh_town_courier_priority_link`;

select ci.courier_name,ci.ref_partner_id,is_active,pt.name,pt.trans_prefix,trans_mode from m_courier_info ci
left join `partner_info` as pt on pt.id = ci.ref_partner_id
order by courier_name asc;
#============================================
alter table `m_courier_info` add column `ref_partner_id` int (11) DEFAULT '0' NULL  after `remarks`;
#============================================
select * from partner_info;
select * from m_courier_info;

select * from partner_info order by name;

select * from m_courier_awb_series where courier_id='';
select * from m_courier_pincodes where courier_id='';
select * from m_courier_info where courier_id='';

#######################
delete from m_courier_awb_series where courier_id='21';
delete from m_courier_pincodes where courier_id='21';
delete from m_courier_info where courier_id='21';
#######################
select * from m_courier_pincodes where courier_id=20
select * from m_courier_awb_series where courier_id=21

insert into m_courier_info(courier_name,ref_partner_id,is_active) values('SHIVARAJ COUR','6',0)

select pt.*,ci.ref_partner_id,ci.is_active from partner_info pt
order by name;