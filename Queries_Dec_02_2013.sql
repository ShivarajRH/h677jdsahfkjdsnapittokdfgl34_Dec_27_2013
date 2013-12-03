select * from m_batch_config

###########################################################################################
alter table `snapittoday_db_nov`.`m_batch_config` add column `territory_id` int (11) DEFAULT '0' NULL  after `assigned_uid`, add column `townid` int (11) DEFAULT '0' NULL  after `territory_id`,change `batch_grp_name` `batch_grp_name` varchar (150)  NULL  COLLATE utf8_unicode_ci , change `assigned_menuid` `assigned_menuid` varchar (100)  NULL  COLLATE utf8_unicode_ci , change `assigned_uid` `assigned_uid` varchar (100)  NULL  COLLATE utf8_unicode_ci;
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
select * from shipment_batch_process_invoice_link where batch_id='5000'