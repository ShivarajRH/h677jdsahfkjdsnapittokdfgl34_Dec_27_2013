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
update t_imei_no set status=0 and order_id=0 where imei_no = '356605050227475';
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