select * from m_batch_config

###########################################################################################
alter table `m_batch_config` #add column `territory_id` int (11) DEFAULT '0' NULL  after `assigned_uid`, add column `townid` int (11) DEFAULT '0' NULL  after `territory_id`
,change `batch_grp_name` `batch_grp_name` varchar (150)  NULL  COLLATE utf8_unicode_ci 
, change `assigned_menuid` `assigned_menuid` varchar (100)  NULL  COLLATE utf8_unicode_ci 
, change `assigned_uid` `assigned_uid` varchar (100)  NULL  COLLATE utf8_unicode_ci;

alter table `snapittoday_db_nov`.`m_batch_config` drop column `townid`, drop column `territory_id`, drop column `assigned_uid`;

###########################################################################################

select  *#d.menuid,sd.id,sd.batch_id,sd.p_invoice_no,from_unixtime(tr.init) 

from king_transactions tr
                                join king_orders as o on o.transid=tr.transid
                                join proforma_invoices as `pi` on pi.order_id = o.id
                                join shipment_batch_process_invoice_link sd on sd.p_invoice_no =pi.p_invoice_no
                                join king_dealitems dl on dl.id = o.itemid
                                join king_deals d on d.dealid = dl.dealid  #and d.menuid in (?)
                                where sd.batch_id=5000
                                order by tr.init asc;

select * from shipment_batch_process where batch_id='5000'
select * from shipment_batch_process_invoice_link where batch_id='5000';

select o.*,tr.transid,tr.amount,tr.paid,tr.init,tr.is_pnh,tr.franchise_id,di.name
                                ,o.status,pi.p_invoice_no,o.quantity
                                ,f.franchise_id,pi.p_invoice_no
                                from king_orders o
                                join king_transactions tr on tr.transid = o.transid and o.status in (0,1) and tr.batch_enabled = 1
                                join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                                left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                                left join proforma_invoices `pi` on pi.order_id = o.id and pi.invoice_status = 1 
                                join king_dealitems di on di.id = o.itemid 
                                where i.id is null # and tr.transid in ('PNHESC16249') # and f.franchise_id = ? $cond 
                                order by tr.init desc; #,di.name

#### Nov_03_2013 ###
select * from shipment_batch_process where batch_id=5000

D:--> added assigned_userid, territory_id,batch_configid fields to shipment_batch_process 

D:--> picklist-invoice id shoulkd carry 

--> generate picklist for un-grouped batches

###########################################################################################
alter table `shipment_batch_process` 
	add column `assigned_userid` int (11) DEFAULT '0' NULL  after `status`, 
	add column `territory_id` int (11) DEFAULT '0' NULL  after `assigned_userid`, 
	add column `batch_configid` int (11) DEFAULT '0' NULL  after `territory_id`;

alter table `snapittoday_db_nov`.`m_batch_config` drop column `assigned_uid`

alter table `snapittoday_db_nov`.`m_batch_config` add column `group_assigned_uid` varchar (120)  NULL  after `batch_size`;

alter table `snapittoday_db_nov`.`shipment_batch_process_invoice_link` drop column `assigned_userid`;

###########################################################################################


select * from m_batch_config;

select o.status,o.shipped,o.id,o.itemid,o.brandid,o.quantity,o.time,o.bill_person,o.ship_phone,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
                    ,di.name
                    ,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
                    ,pi.p_invoice_no
                    from king_orders o
                    join king_transactions tr on tr.transid = o.transid and o.status in (0,1) and tr.batch_enabled = 1
                    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                    left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                    left join proforma_invoices `pi` on pi.order_id = o.id and pi.invoice_status = 1
			#left join shipment_batch_process_invoice_link sd on sd.p_invoice_no=pi.p_invoice_no
                    join king_dealitems di on di.id = o.itemid 
                    where i.id is null #and tr.transid = ?
                    order by tr.init,di.name;

select * from proforma_invoices
select * from shipment_batch_process_invoice_link
select * from king_invoice

select distinct o.*,tr.transid,tr.amount,tr.paid,tr.init,tr.is_pnh,tr.franchise_id,di.name
                                ,o.status,pi.p_invoice_no,o.quantity
                                ,f.franchise_id,pi.p_invoice_no
                                ,sd.batch_id
                                from king_orders o
                                join king_transactions tr on tr.transid = o.transid and o.status in (0,1) and tr.batch_enabled = 1
                                join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                                left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                                left join proforma_invoices `pi` on pi.order_id = o.id and pi.invoice_status = 1
                                left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = pi.p_invoice_no 
                                join king_dealitems di on di.id = o.itemid 
                                where f.franchise_id = '83'  and i.id is null and tr.transid in ('PNHTQE79561') #('PNHEIP95585')
                                order by tr.init desc;


#Dec_04_2013

select * from shipment_batch_process_invoice_link

select * from king_admin

select * from ( 
                    select transid,TRIM(BOTH ',' FROM group_concat(p_inv_nos)) as p_inv_nos,status,count(*) as t,if(count(*)>1,'partial',(if(status,'ready','pending'))) as trans_status,franchise_id  
                    from (
                    select o.transid,ifnull(group_concat(distinct pi.p_invoice_no),'') as p_inv_nos,o.status,count(*) as ttl_o,tr.franchise_id,tr.actiontime
                            from king_orders o
                            join king_transactions tr on tr.transid=o.transid
                            left join king_invoice i on i.order_id = o.id and i.invoice_status = 1 
                            left join proforma_invoices pi on pi.order_id = o.id and o.transid  = pi.transid and pi.invoice_status = 1 
                            left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = pi.p_invoice_no 
                            left join shipment_batch_process sbp on sbp.batch_id = sd.batch_id
                            where o.status in (0,1)  and i.id is null and tr.franchise_id != 0  and sbp.assigned_userid = 37  and ((sd.packed=0 and sd.p_invoice_no > 0) or (sd.p_invoice_no is null and sd.packed is null ))
                            group by o.transid,o.status
                    ) as g 
                    group by g.transid )as g1 having g1.trans_status = 'ready';

select * from (
            select distinct from_unixtime(tr.init,'%D %M %Y') as str_date,from_unixtime(tr.init,'%h:%i:%s %p') as str_time, count(tr.transid) as total_trans,tr.transid
                    ,o.status,o.shipped,o.id,o.itemid,o.brandid,o.quantity,o.time,o.bill_person,o.ship_phone,o.i_orgprice,o.i_price,o.i_tax,o.i_discount,o.i_coup_discount,o.redeem_value,o.member_id,o.is_ordqty_splitd
                    ,tr.init,tr.actiontime,tr.status tr_status,tr.is_pnh,tr.batch_enabled
                    ,f.franchise_id,f.franchise_name,f.territory_id,f.town_id,f.created_on as f_created_on
                    ,ter.territory_name
                    ,twn.town_name
                    ,dl.menuid,m.name as menu_name,bs.name as brand_name
                    ,sd.batch_id
            from king_transactions tr
                    join king_orders o on o.transid=tr.transid
                    join king_dealitems di on di.id=o.itemid
                    join king_deals dl on dl.dealid=di.dealid
                    join pnh_menu m on m.id = dl.menuid
                    join king_brands bs on bs.id = o.brandid
            join pnh_m_franchise_info  f on f.franchise_id=tr.franchise_id #and f.is_suspended = 0
            join pnh_m_territory_info ter on ter.id = f.territory_id 
            join pnh_towns twn on twn.id=f.town_id
                    left join king_invoice i on o.id = i.order_id and i.invoice_status = 1
                    left join proforma_invoices pi on pi.order_id = o.id and pi.invoice_status = 1 
                    left join shipment_batch_process_invoice_link sd on sd.p_invoice_no = pi.p_invoice_no
            WHERE o.status in (0,1) and tr.batch_enabled=1 and i.id is null  and tr.transid in ('PNHEQC73122')
            group by o.transid) as g  group by transid order by  g.actiontime DESC
#=================================================

select distinct o.itemid,d.menuid,mn.name as menuname,f.territory_id,
	sd.id,sd.batch_id,sd.p_invoice_no,from_unixtime(tr.init) 
from king_transactions tr
                                    join king_orders as o on o.transid=tr.transid
                                    join proforma_invoices as `pi` on pi.order_id = o.id and pi.invoice_status=1
                                    join shipment_batch_process_invoice_link sd on sd.p_invoice_no =pi.p_invoice_no
                                    join king_dealitems dl on dl.id = o.itemid
                                    join king_deals d on d.dealid = dl.dealid
				
				join pnh_menu mn on mn.id=d.menuid
				join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                                    where sd.batch_id='5000' and f.territory_id='4'
                                    order by tr.init asc

#                                   limit 0,4
=>115rows

select * from pnh_m_territory_info

select * from shipment_batch_process where batch_id='5000';

####################################################################
delete from shipment_batch_process  where batch_id='5040';
####################################################################

select distinct * from shipment_batch_process_invoice_link where batch_id='5042' group by id

UPDATE `shipment_batch_process_invoice_link` SET `batch_id` = 5000 WHERE `batch_id` = '5040';

UPDATE `shipment_batch_process_invoice_link` SET `batch_id` = 5038 WHERE `id` = 372675
#=============================================================
CREATE TABLE `pnh_m_states` (                            
                `state_id` int(11) NOT NULL AUTO_INCREMENT,            
                `state_name` varchar(255) DEFAULT NULL,                
                `created_on` datetime DEFAULT NULL,                    
                `created_by` int(11) DEFAULT '0',                      
                PRIMARY KEY (`state_id`)                               
              );

#Dec_05_2013
select id,username from king_admin where account_blocked=0

select * from shipment_batch_process_invoice_link where p_invoice_no='114299';

select count(*) as ttl from proforma_invoices where p_invoice_no='114299'


select distinct d.pic,d.is_pnh,e.menuid,i.discount,p.product_id,p.mrp,p.barcode,i.transid,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no 
									from proforma_invoices i 
									join king_orders o on o.id=i.order_id and i.transid = o.transid 
									join m_product_deal_link pl on pl.itemid=o.itemid 
									join m_product_info p on p.product_id=pl.product_id 
									join king_dealitems d on d.id=o.itemid 
									join king_deals e on e.dealid=d.dealid 
									join shipment_batch_process_invoice_link sb on sb.p_invoice_no = i.p_invoice_no and sb.invoice_no = 0  
									where i.p_invoice_no='114299' and i.invoice_status=1 order by o.sno

select d.pic,d.is_pnh,e.menuid,i.discount,i.discount,p.product_id,p.mrp,i.transid,p.barcode,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no 
from proforma_invoices i 
join king_orders o on o.id=i.order_id and i.transid = o.transid 
join products_group_orders pgo on pgo.order_id=o.id 
join m_product_group_deal_link pl on pl.itemid=o.itemid 
join m_product_info p on p.product_id=pgo.product_id 
join king_dealitems d on d.id=o.itemid join king_deals e on e.dealid=d.dealid 
join shipment_batch_process_invoice_link sb on sb.p_invoice_no = i.p_invoice_no and sb.invoice_no = 0  
where i.p_invoice_no='114299' and i.invoice_status=1 order by o.sno 


### Dec_11_2013
#==============================================================================
update t_imei_no set status=0 and order_id=0 where imei_no = '356631059543977';
#==============================================================================

join king_orders o on o.id = rbs.order_id
                        join king_dealitems dlt on dlt.id = o.itemid
			join king_deals dl on dl.dealid = dlt.dealid
			join pnh_menu as mnu on mnu.id = dl.menuid and mnu.status=1




set @inv_no='114077';
select distinct o.time,e.menuid,mnu.name as menuname,d.pic,d.is_pnh,e.menuid,i.discount,p.product_id,p.mrp,p.barcode,i.transid,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no 
									from proforma_invoices i 
									join king_orders o on o.id=i.order_id and i.transid = o.transid 
									join m_product_deal_link pl on pl.itemid=o.itemid 
									join m_product_info p on p.product_id=pl.product_id 
									join king_dealitems d on d.id=o.itemid 
									join king_deals e on e.dealid=d.dealid
									left join pnh_menu as mnu on mnu.id = e.menuid and mnu.status=1
									join shipment_batch_process_invoice_link sb on sb.p_invoice_no = i.p_invoice_no and sb.invoice_no = 0  
									where i.invoice_status=1 
											and i.p_invoice_no in (@inv_no) 
									order by o.sno;
==> 187 rows
==> 152 rows



set @inv_no='114077';
select d.pic,d.is_pnh,e.menuid,i.discount,i.discount,p.product_id,p.mrp,i.transid,p.barcode,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no 
									from proforma_invoices i 
									join king_orders o on o.id=i.order_id and i.transid = o.transid 
									join products_group_orders pgo on pgo.order_id=o.id 
									join m_product_group_deal_link pl on pl.itemid=o.itemid 
									join m_product_info p on p.product_id=pgo.product_id 
									join king_dealitems d on d.id=o.itemid 
									join king_deals e on e.dealid=d.dealid 
									left join pnh_menu as mnu on mnu.id = e.menuid and mnu.status=1
									join shipment_batch_process_invoice_link sb on sb.p_invoice_no = i.p_invoice_no and sb.invoice_no = 0  
									where i.invoice_status=1 and i.p_invoice_no in (@inv_no) 
									order by o.sno;

select * from king_orders
desc king_orders;

#Dec_12_2013
select consider_mrp_chng from pnh_menu

#Dec_13_2013
select note from king_transaction_notes where transid=? and note_priority=1 order by id asc limit 1;
select transid from proforma_invoices where p_invoice_no in ($p_invno_list);

select note from king_transaction_notes tnote
join proforma_invoices `pi` on pi.transid=tnote.transid
where tnote.note_priority=1 and pi.p_invoice_no in ('10004')
order by tnote.id asc limit 1;

select * from proforma_invoices where invoice_status=1


select c.status,a.product_id,product_barcode,mrp,location_id,rack_bin_id,b.stock_id 
			from t_reserved_batch_stock a 
			join t_stock_info b on a.stock_info_id = b.stock_id 
			join t_imei_no c on c.product_id = b.product_id 
	#where a.p_invoice_no = ? and a.order_id = ? and imei_no = ?
#==> 7880888 rows / 374ms

select a.status,a.product_id,b.product_barcode,b.mrp,b.location_id as location_id,
										b.rack_bin_id as rack_bin_id,
										b.stock_id from (
									select a.status,a.product_id,b.product_barcode,ifnull(b.mrp,c.mrp) as mrp,ifnull(b.location_id,c.location_id) as location_id,
										ifnull(b.rack_bin_id,c.rack_bin_id) as rack_bin_id,
										b.stock_id
										from t_imei_no a 
										left join t_stock_info b on a.stock_id = b.stock_id and a.product_id = b.product_id
										join t_grn_product_link c on c.grn_id = a.grn_id and a.product_id = c.product_id 
										where imei_no = '356631059543977' 
									) as a 
									join t_stock_info b on a.product_id = b.product_id 
									where a.mrp = b.mrp and a.location_id = b.location_id and a.rack_bin_id = b.rack_bin_id

select status,product_id from t_imei_no where imei_no = '358956056763247';


select distinct o.time,e.menuid,mnu.name as menuname,d.pic,d.is_pnh,i.discount,p.product_id,p.mrp,p.barcode,i.transid,i.p_invoice_no,p.product_name,o.i_orgprice as order_mrp,o.quantity*pl.qty as qty,d.name as deal,d.dealid,o.itemid,o.id as order_id,i.p_invoice_no 
									from proforma_invoices i 
									join king_orders o on o.id=i.order_id and i.transid = o.transid 
									join m_product_deal_link pl on pl.itemid=o.itemid 
									join m_product_info p on p.product_id=pl.product_id 
									join king_dealitems d on d.id=o.itemid 
									join king_deals e on e.dealid=d.dealid
                                                                        left join pnh_menu as mnu on mnu.id = e.menuid and mnu.status=1
                                                                        join shipment_batch_process_invoice_link sb on sb.p_invoice_no = i.p_invoice_no and sb.invoice_no = 0  
									where i.invoice_status=1 order by e.menuid DESC # and i.p_invoice_no in ($inv_no)

# Dec_14_2013

select territory_name from pnh_m_territory_info where id='3';

select distinct o.itemid,d.menuid,mn.name as menuname,f.territory_id,sd.id,sd.batch_id,sd.p_invoice_no,from_unixtime(tr.init) from king_transactions tr
                                join king_orders as o on o.transid=tr.transid
                                join proforma_invoices as `pi` on pi.order_id = o.id and pi.invoice_status=1
                                join shipment_batch_process_invoice_link sd on sd.p_invoice_no =pi.p_invoice_no
                                join king_dealitems dl on dl.id = o.itemid
                                join king_deals d on d.dealid = dl.dealid # and d.menuid in ('')
                                
                                join pnh_menu mn on mn.id=d.menuid
                                join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id #and f.is_suspended = 0
                                
                                where sd.batch_id=5000  and f.territory_id = 3 
                                order by tr.init asc
                                limit 0,1;

select dispatch_id,group_concat(distinct a.id) as man_id,group_concat(distinct b.invoice_no) as invs 
                                                                                                    from pnh_m_manifesto_sent_log a
                                                                                                    join shipment_batch_process_invoice_link b on a.manifesto_id = b.inv_manifesto_id and b.invoice_no != 0 
                                                                                                    join proforma_invoices c on c.p_invoice_no = b.p_invoice_no and c.invoice_status = 1  
                                                                                                    join king_transactions d on d.transid = c.transid 
                                                                                                    where date(sent_on) between '2013-11-01' and '2013-11-07' and dispatch_id != 0  
                                                                                            group by franchise_id;

#100rows/93ms

select * from pnh_m_manifesto_sent_log

select unix_timestamp('2013-10-20') as utime;
select from_unixtime(1382207400) as time;


#Dec_16_2013

select dispatch_id,group_concat(distinct a.id) as man_id,group_concat(distinct b.invoice_no) as invs,f.territory_id
				    from pnh_m_manifesto_sent_log a
				    join shipment_batch_process_invoice_link b on a.manifesto_id = b.inv_manifesto_id and b.invoice_no != 0 
				    join proforma_invoices c on c.p_invoice_no = b.p_invoice_no and c.invoice_status = 1  
				    join king_transactions d on d.transid = c.transid 
				    join pnh_m_franchise_info f on f.franchise_id = d.franchise_id
				    where date(sent_on) between '2013-11-01' and '2013-11-07' and dispatch_id != 0 #and f.territory_id='3'
			    group by d.franchise_id
#100 rows/187ms

select * from shipment_batch_process_invoice_link

desc shipment_batch_process_invoice_link;

select f.territory_id,d.franchise_id,dispatch_id,group_concat(distinct a.id) as man_id,group_concat(distinct b.invoice_no) as invs 
	from pnh_m_manifesto_sent_log a 
	join shipment_batch_process_invoice_link b on a.manifesto_id = b.inv_manifesto_id and b.invoice_no != 0 
	join proforma_invoices c on c.p_invoice_no = b.p_invoice_no and c.invoice_status = 1 join king_transactions d on d.transid = c.transid 
	join pnh_m_franchise_info f on f.franchise_id = d.franchise_id where date(sent_on) between '2013-11-01' and '2013-12-16' and dispatch_id != 0 and f.territory_id='3' group by d.franchise_id;


set @invs='20141014918,20141014287,20141014389';
select a.transid,a.createdon as invoiced_on,b.bill_person,b.bill_address,b.bill_landmark,b.bill_city,b.bill_state,b.bill_pincode,d.init,b.itemid,c.name,if(c.print_name,c.print_name,c.name) as print_name,c.pnh_id,group_concat(distinct a.invoice_no) as invs,
                                                        sum((i_orgprice-(i_discount+i_coup_discount))*a.invoice_qty) as amt,
                                                        sum(a.invoice_qty) as qty 
                                                from king_invoice a 
                                                join king_orders b on a.order_id = b.id 
                                                join king_dealitems c on c.id = b.itemid
                                                join king_transactions d on d.transid = a.transid
                                                where a.invoice_no in (@invs) 
                                group by itemid



####################################################################
alter table `shipment_batch_process_invoice_link` add column `is_acknowlege_printed` int (11) DEFAULT '0' NULL  after `delivered_by`;
####################################################################

select * from pnh_m_territory_info where id=3

select f.territory_id,dispatch_id,group_concat(distinct a.id) as man_id,group_concat(distinct b.invoice_no) as invs,count(distinct b.invoice_no) as ttl_invs
                                                            from pnh_m_manifesto_sent_log a
                                                            join shipment_batch_process_invoice_link b on a.manifesto_id = b.inv_manifesto_id and b.invoice_no != 0 and b.is_acknowlege_printed = 0
                                                            join proforma_invoices c on c.p_invoice_no = b.p_invoice_no and c.invoice_status = 1  
                                                            join king_transactions d on d.transid = c.transid 
                                                            join pnh_m_franchise_info f on f.franchise_id = d.franchise_id
                                                            where date(sent_on) between '2013-11-01' and '2013-12-16' and dispatch_id != 0  and f.territory_id=16
                                                    group by d.franchise_id order by f.territory_id asc;

update `shipment_batch_process_invoice_link` set `is_acknowlege_printed`='1' where 

select * from shipment_batch_process_invoice_link where is_acknowlege_printed>1