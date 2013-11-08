
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
