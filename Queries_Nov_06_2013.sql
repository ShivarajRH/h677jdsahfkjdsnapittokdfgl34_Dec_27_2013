
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
where a.status in (0,1) and a.transid = 'PNHZLA55363';

