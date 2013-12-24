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

### Dec_17_2013 ###
update `shipment_batch_process_invoice_link` set `is_acknowlege_printed`='0' where 

select * from shipment_batch_process_invoice_link where is_acknowlege_printed>0


    Table: "picklist_log_reservation"
id
printcount
p_inv_no
created_by
createdon
####################################################################
create table `picklist_log_reservation` (  `id` bigint NOT NULL AUTO_INCREMENT , `group_no` bigint (20) DEFAULT '0', `p_inv_no` int (100) , `created_by` int (11) DEFAULT '0', `createdon` datetime , `printcount` int (100) , PRIMARY KEY ( `id`));
####################################################################

picklist_log_reservationpicklist_log_reservation

X insert into `picklist_log_reservation`(`id`,`group_no`,`p_inv_no`,`created_by`,`createdon`,`printcount`) values ( NULL,'1','114344','1',NULL,NULL);
X truncate table `snapittoday_db_nov`.`picklist_log_reservation`picklist_log_reservation;


INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114344', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114333', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114324', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114318', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114315', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114313', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114311', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114299', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114281', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114334', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114308', '1', '2013-12-17 07:29:26', 1)
INSERT INTO `picklist_log_reservation` (`group_no`, `p_inv_no`, `created_by`, `createdon`, `printcount`) VALUES (1387288766, '114319', '1', '2013-12-17 07:29:26', 1)
update picklist_log_reservation set printcount = `printcount` + 1 where id = 11 limit 1

## Dec_18_2013 ##

select DATE_FORMAT(shipped_on,"%w-%a") as day_of_week,DATE(shipped_on) as normaldate,shipped_on,shipped,invoice_no 
from shipment_batch_process_invoice_link 
where shipped_by=1 and day_of_week is not null .
order by shipped_on ASC

#7627rows

desc shipment_batch_process_invoice_link;

select * from shipment_batch_process_invoice_link

select * from shipment_batch_process

#73124rows

select week_day,shipped_on,shipped_on_time,shipped,invoice_no,shipped_by from (
select DATE_FORMAT(shipped_on,"%w") as week_day,shipped_on,unix_timestamp(shipped_on) as shipped_on_time,shipped,invoice_no,shipped_by
from shipment_batch_process_invoice_link
where shipped=1
order by shipped_on DESC
) as g where g.week_day is not null and shipped_on_time!=0 and shipped_by>0 and shipped_on_time between '1383284282' and '1385619678'

# =>5086rows/62ms

# Dec_19_2013

select week_day,shipped_on,shipped_on_time,shipped,invoice_no,shipped_by from (
select DATE_FORMAT(shipped_on,"%w") as week_day,shipped_on,unix_timestamp(shipped_on) as shipped_on_time,shipped,invoice_no,shipped_by
from shipment_batch_process_invoice_link
where shipped=1
order by shipped_on DESC
) as g where g.week_day is not null and shipped_on_time!=0 and shipped_by>0

# =>65078rows

select * from shipment_batch_process_invoice_link
select * from batch
select * from shipment_batch_process

select week_day,shipped_on,shipped_on_time,shipped,invoice_no,shipped_by from (
select DATE_FORMAT(shipped_on,"%w") as week_day,shipped_on,unix_timestamp(shipped_on) as shipped_on_time,shipped,invoice_no,shipped_by
from shipment_batch_process_invoice_link
where shipped=1
order by shipped_on DESC
) as g where g.week_day is not null and shipped_on_time!=0 and shipped_by>0

select * from pnh_m_territory_info;

select employee_id,name,email,gender,city,contact_no,if(job_title=4,'TM','BE') as job_role 
from m_employee_info 
where job_title in (4,5) and is_suspended=0 order by job_title ASC;
=>28rows

select distinct emp.employee_id,emp.name,emp.email,emp.gender,emp.city,emp.contact_no,if(emp.job_title=4,'TM','BE') as job_role,ttl.is_active
from m_employee_info emp
join m_town_territory_link ttl on ttl.employee_id = emp.employee_id and ttl.is_active=1
join pnh_m_territory_info t on t.id = ttl.territory_id
where job_title in (4) and is_suspended=0 #and t.id='1'
#group by emp.employee_id
order by job_title ASC;

select * from m_town_territory_link

select  * from m_employee_info w where w.name like '%Kantaraj Naik%' and is_suspended=0;

select * from proforma_invoices
select * from shipment_batch_process_invoice_link


select week_day,shipped_on,shipped_on_time,shipped,invoice_no,shipped_by from (
select DATE_FORMAT(shipped_on,"%w") as week_day,shipped_on,unix_timestamp(shipped_on) as shipped_on_time,shipped,invoice_no,shipped_by
from shipment_batch_process_invoice_link
where shipped=1
order by shipped_on DESC
) as g where g.week_day is not null and shipped_on_time!=0 and shipped_by>0 and shipped_on_time between '1383284282' and '1385619678';


select week_day,shipped_on_time,shipped,invoice_no,shipped_by from (
	select DATE_FORMAT(sd.shipped_on,'%w') as week_day,unix_timestamp(sd.shipped_on) as shipped_on_time,sd.shipped,sd.invoice_no,sd.shipped_by
	from shipment_batch_process_invoice_link sd
	join proforma_invoices pi on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1  
	join king_transactions tr on tr.transid = pi.transid
	join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
    where shipped=1 and sd.shipped_by>0 and f.territory_id ='4'
    order by shipped_on DESC
) as g where g.week_day is not null and g.shipped_on_time!=0 and g.shipped_on_time between 1383244200 and 1387391400 

#5346 rows => #18008


select distinct week_day,shipped,invoice_no,shipped_by from ( select DATE_FORMAT(sd.shipped_on,'%w') as week_day,sd.shipped,sd.invoice_no,sd.shipped_by from shipment_batch_process_invoice_link sd join proforma_invoices pi on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1  join king_transactions tr on tr.transid = pi.transid join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id where shipped=1 and f.territory_id ='3' and unix_timestamp(sd.shipped_on) !=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 order by shipped_on DESC ) as g where g.week_day is not null

# Dec_20_2013

select week_day,shipped_on,shipped,invoice_no,shipped_by from ( select DATE_FORMAT(sd.shipped_on,'%w') as week_day,sd.shipped_on,sd.shipped,sd.invoice_no,sd.shipped_by from shipment_batch_process_invoice_link sd join proforma_invoices pi on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1  join king_transactions tr on tr.transid = pi.transid join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and f.territory_id ='3' and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 order by shipped_on DESC ) as g where g.week_day is not null


select distinct week_day,shipped_on,shipped,invoice_no,shipped_by from (
select DATE_FORMAT(sd.shipped_on,'%w') as week_day,sd.shipped_on,sd.shipped,sd.invoice_no,sd.shipped_by
from shipment_batch_process_invoice_link sd
join proforma_invoices pi on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1
join king_transactions tr on tr.transid = pi.transid
join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and f.territory_id ='' and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800
order by shipped_on DESC
) as g where g.week_day is not null;

-- select distinct week_day,shipped_on,shipped,invoice_no_str,shipped_by from (
		    select sd.shipped_on,sd.shipped,group_concat(sd.invoice_no) as invoice_no_str,count(distinct sd.invoice_no) as ttl_invs,sd.shipped_by
		    from shipment_batch_process_invoice_link sd
		    join proforma_invoices pi on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
		    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 and f.territory_id ='3'
		   
			order by shipped_on DESC
--                                                 ) as g where g.week_day is not null;


select f.territory_id,dispatch_id,group_concat(distinct a.id) as man_id,group_concat(distinct b.invoice_no) as invs,count(distinct b.invoice_no) as ttl_invs
                                                               from pnh_m_manifesto_sent_log a
                                                               join shipment_batch_process_invoice_link b on a.manifesto_id = b.inv_manifesto_id and b.invoice_no != 0 #$cond_join
                                                               join proforma_invoices c on c.p_invoice_no = b.p_invoice_no and c.invoice_status = 1  
                                                               join king_transactions d on d.transid = c.transid 
                                                               join pnh_m_franchise_info f on f.franchise_id = d.franchise_id
                                                               where date(sent_on) between '2013-11-01 17:27:17' and '2013-11-27 18:44:22' and dispatch_id != 0  and f.territory_id='3'
                                                       group by d.franchise_id order by f.territory_id asc


## idea 1
select f.territory_id,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped,group_concat(sd.invoice_no) as invoice_no_str,count(distinct sd.invoice_no) as ttl_invs,sd.shipped_by
		    from pnh_m_manifesto_sent_log man
			join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
		    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
		    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 #and f.territory_id ='3'
			group by f.territory_id
			order by f.territory_id DESC


-- idea 2
select f.territory_id,t.territory_name,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped,group_concat(sd.invoice_no) as invoice_no_str,count(distinct sd.invoice_no) as ttl_invs,emp.employee_id		    
		from pnh_m_manifesto_sent_log man
			join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
		    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
 			join m_town_territory_link ttl on ttl.territory_id = f.territory_id and is_active=1
			join m_employee_info emp on emp.employee_id = ttl.employee_id


			join pnh_m_territory_info t on t.id = f.territory_id
                    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 #and f.territory_id ='3'
			group by f.territory_id
			order by f.territory_id DESC;
-- Outout: 19rows/312ms

select * from m_town_territory_link

# Dec_21_2013

select f.territory_id,t.territory_name,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped,group_concat(distinct sd.invoice_no) as invoice_no_str
			,count(distinct sd.invoice_no) as ttl_invs,count(distinct f.franchise_id) as ttl_franchises
		from pnh_m_manifesto_sent_log man
			join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
		    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
 			join m_town_territory_link ttl on ttl.territory_id = f.territory_id and is_active=1
			join m_employee_info emp on emp.employee_id = ttl.employee_id


			join pnh_m_territory_info t on t.id = f.territory_id
                    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 #and f.territory_id ='3'
			group by f.territory_id
			order by f.territory_id DESC;


#================================================================================================
alter table king_invoice add column `paid_status` tinyint(11) DEFAULT '0' after ref_dispatch_id;
alter table king_dealitems add column `billon_orderprice` tinyint(1) DEFAULT '0' after nyp_price;
alter table king_orders add column `billon_orderprice` tinyint(1) DEFAULT '0' after note;
alter table king_orders add column `is_paid` tinyint(1) DEFAULT '0' after offer_refid;
alter table king_orders add column `partner_order_id` varchar(30) DEFAULT '0' after offer_refid;

CREATE TABLE `king_partner_settelment_filedata` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) DEFAULT NULL,
  `payment_uploaded_id` bigint(20) DEFAULT '0',
  `file_name` varchar(255) DEFAULT NULL,
  `orderid` bigint(20) DEFAULT '0',
  `logged_on` bigint(20) DEFAULT '0',
  `processed_by` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
);

alter table king_tmp_orders change brandid `brandid` bigint(20) DEFAULT '0',change vendorid `vendorid` bigint(20) DEFAULT '0';

alter table king_tmp_orders add column `partner_order_id` varchar(30) DEFAULT '0' after user_note;
alter table king_tmp_orders add column `partner_reference_no` varchar(100) DEFAULT '0' after partner_order_id;

alter table king_transactions change partner_reference_no `partner_reference_no` varchar(100) NOT NULL;
alter table king_transactions add column `credit_days` int(11) DEFAULT '0' after trans_grp_ref_no;
alter table king_transactions add column `credit_remarks` varchar(255) DEFAULT NULL after credit_days;

alter table m_courier_info add column `ref_partner_id` int(11) DEFAULT '0' after remarks;


CREATE TABLE `m_partner_settelment_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `order_value` double DEFAULT NULL,
  `shipping_charges` double DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `payment_amount` double DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

alter table m_product_info add column `corr_status` tinyint(1) DEFAULT '0' after modified_by;
alter table m_product_info add column `corr_updated_on` datetime DEFAULT NULL after corr_status;

CREATE TABLE `m_product_update_log` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT '0',
  `type` varchar(255) DEFAULT NULL,
  `message` text,
  `logged_by` int(11) DEFAULT '0',
  `logged_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

alter table m_vendor_brand_link change applicable_from `applicable_from` bigint(20) DEFAULT NULL;
alter table m_vendor_brand_link change applicable_till `applicable_till` bigint(20) DEFAULT NULL;


CREATE TABLE `pnh_m_creditlimit_onprepaid` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `franchise_id` bigint(12) DEFAULT NULL,
  `book_id` bigint(12) DEFAULT NULL,
  `book_value` bigint(12) DEFAULT NULL,
  `receipt_id` bigint(12) DEFAULT NULL,
  `credit_limit_on_prepaid` double DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `valid_till` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);


CREATE TABLE `pnh_m_fran_security_cheques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `franchise_id` int(11) DEFAULT '0',
  `bank_name` varchar(255) DEFAULT NULL,
  `cheque_no` varchar(30) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `collected_on` date DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `returned_on` date DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `modified_on` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

alter table pnh_m_franchise_info change store_open_time `store_open_time` time DEFAULT NULL;
alter table pnh_m_franchise_info change store_close_time `store_close_time` time DEFAULT NULL;
# X
alter table pnh_m_franchise_info add column `purchase_limit` double DEFAULT '0' after reason;
alter table pnh_m_franchise_info add column `new_credit_limit` double DEFAULT '0' after purchase_limit;

alter table pnh_m_manifesto_sent_log add column `lrno_update_refid` bigint(11) DEFAULT '0' after lrno;


CREATE TABLE `pnh_m_states` (
  `state_id` int(11) NOT NULL AUTO_INCREMENT,
  `state_name` varchar(255) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT '0',
  PRIMARY KEY (`state_id`)
);

alter table pnh_m_territory_info add column `state_id` bigint(11) DEFAULT '0' after id;

alter table pnh_menu add column `voucher_credit_default_margin` double DEFAULT '0' after default_margin;


CREATE TABLE `pnh_menu_margin_track` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) DEFAULT NULL,
  `default_margin` double DEFAULT NULL,
  `balance_discount` double DEFAULT NULL,
  `balance_amount` bigint(20) DEFAULT NULL,
  `loyality_pntvalue` double DEFAULT NULL,
  `created_by` int(12) DEFAULT NULL,
  `created_on` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

alter table pnh_sms_log_sent add column `no_ofboxes` bigint(20) DEFAULT '0' after ticket_id;

CREATE TABLE `pnh_town_courier_priority_link` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `town_id` int(11) DEFAULT '0',
  `courier_priority_1` int(5) DEFAULT '0',
  `courier_priority_2` int(5) DEFAULT '0',
  `courier_priority_3` int(5) DEFAULT '0',
  `delivery_hours_1` int(3) DEFAULT '0',
  `delivery_hours_2` int(3) DEFAULT '0',
  `delivery_hours_3` int(3) DEFAULT '0',
  `delivery_type_priority1` int(3) DEFAULT '0',
  `delivery_type_priority2` int(3) DEFAULT '0',
  `delivery_type_priority3` int(3) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT '0',
  `modified_on` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `t_billedmrp_change_log` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` bigint(11) DEFAULT '0',
  `p_invoice_no` int(11) DEFAULT '0',
  `packed_mrp` double DEFAULT NULL,
  `billed_mrp` double DEFAULT NULL,
  `remarks` text,
  `logged_on` datetime DEFAULT NULL,
  `logged_by` int(5) DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `t_imei_update_log` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `imei_no` varchar(255) DEFAULT NULL,
  `product_id` bigint(11) DEFAULT '0',
  `stock_id` bigint(11) DEFAULT '0',
  `grn_id` bigint(11) DEFAULT '0',
  `alloted_order_id` bigint(11) DEFAULT '0',
  `alloted_on` datetime DEFAULT NULL,
  `invoice_no` bigint(11) DEFAULT '0',
  `return_id` bigint(11) DEFAULT '0',
  `is_cancelled` tinyint(1) DEFAULT '0',
  `cancelled_on` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `logged_on` datetime DEFAULT NULL,
  `logged_by` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `t_pnh_creditlimit_track` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `franchise_id` bigint(20) DEFAULT NULL,
  `payment_modetype` tinyint(1) DEFAULT NULL COMMENT '1:postpaid,2:prepaid using vouchers,3:prepaid by holding Acounts',
  `prepaid_credit_id` double DEFAULT NULL,
  `reconsolation_rid` bigint(11) DEFAULT NULL,
  `order_id` bigint(12) DEFAULT NULL,
  `transid` varchar(255) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `prepaid_creditlimit` double DEFAULT NULL,
  `purchase_limit` double DEFAULT '0',
  `init` bigint(20) DEFAULT NULL,
  `actiontime` bigint(20) DEFAULT NULL,
  `paid` double DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `t_prepaid_credit_receipt_track` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `receipt_id` bigint(11) DEFAULT NULL,
  `receipt_amount` double DEFAULT '0',
  `prepaid_credit` double DEFAULT '0',
  `franchise_id` bigint(11) DEFAULT NULL,
  `receipt_realizedon` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`)
);


CREATE TABLE `m_batch_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `batch_grp_name` varchar(150) DEFAULT NULL,
  `assigned_menuid` int(11) DEFAULT '0',
  `batch_size` int(11) DEFAULT '0',
  `group_assigned_uid` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

alter table shipment_batch_process add column `assigned_userid` int(11) DEFAULT '0' after status;
alter table shipment_batch_process add column `territory_id` int(11) DEFAULT '0' after assigned_userid;
alter table shipment_batch_process add column `batch_configid` int(11) DEFAULT '0' after territory_id;

CREATE TABLE `t_exotel_agent_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `callsid` varchar(255) DEFAULT NULL,
  `from` varchar(50) DEFAULT NULL,
  `dialwhomno` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);
#================================================================

select f.territory_id,t.territory_name,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped,group_concat(distinct sd.invoice_no) as invoice_no_str
			,count(distinct sd.invoice_no) as ttl_invs,count(distinct f.franchise_id) as ttl_franchises
		from pnh_m_manifesto_sent_log man
			join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
		    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
 			join m_town_territory_link ttl on ttl.territory_id = f.territory_id and is_active=1
			join m_employee_info emp on emp.employee_id = ttl.employee_id


			join pnh_m_territory_info t on t.id = f.territory_id
                    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and unix_timestamp(sd.shipped_on) between 1383244200 and 1387477800 #and f.territory_id ='3'
			group by f.territory_id
			order by f.territory_id DESC;

select f.territory_id,t.territory_name,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped,group_concat(distinct sd.invoice_no) as invoice_no_str,count(tr.franchise_id) as ttl_franchises
			,count(distinct sd.invoice_no) as ttl_invs,count(distinct f.franchise_id) as ttl_franchises
		from pnh_m_manifesto_sent_log man
			join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
		    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
		    join king_transactions tr on tr.transid = pi.transid
		    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
 			join m_town_territory_link ttl on ttl.territory_id = f.territory_id and is_active=1
			join m_employee_info emp on emp.employee_id = ttl.employee_id
			join pnh_m_territory_info t on t.id = f.territory_id
                    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and date(man.sent_on) between from_unixtime('1383244200') and from_unixtime('1387477800') #and f.territory_id ='3'
			group by f.territory_id
			order by f.territory_id DESC;

select group_concat(man.sent_invoices) grp_invs from pnh_m_manifesto_sent_log man where date(man.sent_on) between from_unixtime('1383244200') and from_unixtime('1387477800')

select  from pnh_m_manifesto_sent_log man
join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
where date(man.sent_on) between from_unixtime('1383244200') and from_unixtime('1387477800')


select m_town_territory_link

select f.territory_id,t.territory_name,pi.dispatch_id,group_concat(distinct man.id) as man_id,sd.shipped_on,sd.shipped
                    ,group_concat(distinct sd.invoice_no) as invoice_no_str,count(distinct sd.invoice_no) as ttl_invs,count(distinct f.franchise_id) as ttl_franchises		    
                                                from pnh_m_manifesto_sent_log man
                                                        join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
                                                    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
                                                    join king_transactions tr on tr.transid = pi.transid
                                                    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
                                                        join pnh_m_territory_info t on t.id = f.territory_id
                                                    where shipped=1 and sd.shipped_by>0 and unix_timestamp(sd.shipped_on)!=0 and dispatch_id != 0 and unix_timestamp(sent_on) between 1387564200 and 1387391400 
                                                group by f.territory_id
                                                order by t.territory_name ASC;
select from_unixtime('1383244200')

#=======================================================================================================
select unix_timestamp('2013-10-20') as utime;
select from_unixtime(1382207400) as time;
#=======================================================================================================

select group_concat(man.sent_invoices) grp_invs
from pnh_m_manifesto_sent_log man 
join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
join king_transactions tr on tr.transid = pi.transid
join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
join pnh_m_territory_info t on t.id = f.territory_id
where date(man.sent_on) between from_unixtime('1383244200') and from_unixtime('1387477800') and f.territory_id='3';

select * from pnh_m_manifesto_sent_log

select group_concat(man.sent_invoices) grp_invs
	    from pnh_m_manifesto_sent_log man 
	    join shipment_batch_process_invoice_link sd on sd.inv_manifesto_id = man.manifesto_id
	    join proforma_invoices `pi` on pi.p_invoice_no = sd.p_invoice_no and pi.invoice_status = 1 
	    join king_transactions tr on tr.transid = pi.transid
	    join pnh_m_franchise_info f on f.franchise_id = tr.franchise_id
	    join pnh_m_territory_info t on t.id = f.territory_id
	    where date(man.sent_on) between from_unixtime('3') and from_unixtime(1384281000) and f.territory_id=1384453800



set @dd = '2013-12-25';
select weekday(@dd),date_add(@dd,interval weekday(@dd) day );

4,3 
3-4 -1day 