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

