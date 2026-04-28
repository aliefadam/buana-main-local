<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class MonitoringPrModel extends Model
{
    protected $table = 'pr';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
		"po_no",
		"dept_id",
		"po_date",
		"eta_date",
		"received_date",
		"order_type",
		"supplier_id",
		"promised_delivery_date",
		"currency",
		"exchange_rate",
		"rate_date",
		"final_quote_url",
		"signed_po_url", "signed_pr_url", "no", "year", "charge1", "charge2", "charge1_desc", "charge2_desc", "note", "title", 'flag', 'approved', 'ref_offer_no', 'ref_internal_bmt', 'pr_id', 'rfq_id', 'askapproval_note', 'ask_approval_date', 'approval_date', 'approval_by', 'approval_2_date', 'approval_2_by', 'rejected_by', 'rejected_date', 'modified_date', 'modified_by', "approval_note", "created_by", "created_date", "new_po_no",
		"reject_note1",
		"reject_note2",
		"payment_term",
		"despatch",
		"shipping_address",
		"miscellaneous",
		"discount_desc",
		"other_charge",
		"discount",
		"complete",
        "ask_canceled_date",
        "ask_canceled_note",
        "canceled_by",
        "canceled_note",
        "canceled_date",
        "canceled_2_by",
        "canceled_2_date"
	];
	
	protected function initialize()
    {
        $this->db = db_connect();
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function read($json){
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
		$sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; 
		/*$q = "select r.rfq_no, r.mr_task_no, r.modified_date as rfq_modified_date, r.rfq_group, r.allocation,
			r.created_date as rfq_created_date, s.id, s.id as po_id, r.id as rfq_id, concat(rg.id, '-', rg._ragicId) as ragic_id,
			(select coalesce(modified_date, created_date) as date from rfq_comment where rfq_id = r.id order by created_date desc limit 1) as rfq_hist_date, s.currency,
			r.status as rfq_status, s.po_no, s.title, s.dept_id, s.approved, s.created_date as po_created_date, s.signed_pr_url, s.final_quote_url, s.approved as po_approved, s.new_po_no, s.is_complete, s.complete, pr.pr_no, s.flag, c.grand_total_price,
			rg.order_id, rg.status as ragic_status,  u.name as po_created_by, coalesce((select sum(i.payment_pct) from invoice i where i.po_id = s.id and i.flag > 0), 0) as total_payment_pct, c.grand_total_price+s.charge1+s.charge2-s.discount as grand_total, COALESCE((SELECT (sum( pi.complete_qty/pi.order_qty))/(count(pi.item_id))*100 from purchase_order_item pi WHERE pi.purchase_order_id=s.id and pi.flag=1),0) as total_received_pct
			 from `$this->table` s 
			left join pr pr on pr.id=s.pr_id
			left join rfq_header r on r.id = s.rfq_id
			left join ragic rg on rg.order_id = r.reference_no
			left join users u on u.id = s.created_by
			left join (
                select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
                from purchase_order_item where flag = 1
                group by purchase_order_id
            ) c on c.purchase_order_id = s.id";*/
		$q = "select 
		coalesce(r.rfq_no, '--') as rfq_no, 
		r.mr_task_no, 
		r.modified_date as rfq_modified_date, 
		r.rfq_group, 
		r.allocation, 
		r.created_date as rfq_created_date, 
		s.id, 
		pr.id as pr_id,
		s.id as po_id, 
		r.id as rfq_id, 
		concat(rg.id, '-', rg._ragicId) as ragic_id, 
		(
			select 
			coalesce(modified_date, created_date) as date 
			from 
			rfq_comment 
			where 
			rfq_id = r.id 
			order by 
			created_date desc 
			limit 
			1
		) as rfq_hist_date, 
		s.currency, 
		r.status as rfq_status, 
		coalesce(s.po_no, '--') as po_no, 
		s.title, 
		s.dept_id, 
		s.approved, 
		s.created_date as po_created_date, 
		s.signed_pr_url, 
		s.final_quote_url, 
		s.approved as po_approved, 
		s.new_po_no, 
		s.is_complete, 
		s.complete, 
		coalesce(pr.pr_no, '--') as pr_no, 
		pr.flag, 
		c.grand_total_price, 
		rg.order_id, 
		rg.status as ragic_status, 
		u.name as po_created_by, 
		coalesce(
			(
			select 
				sum(i.payment_pct) 
			from 
				invoice i 
			where 
				i.po_id = s.id 
				and i.flag > 0
			), 
			0
		) as total_payment_pct, 
		c.grand_total_price + s.charge1 + s.charge2 - s.discount as grand_total, 
		COALESCE(
			(
			SELECT 
				(
				sum(pi.complete_qty / pi.order_qty)
				)/(
				count(pi.item_id)
				)* 100 
			from 
				purchase_order_item pi 
			WHERE 
				pi.purchase_order_id = s.id 
				and pi.flag = 1
			), 
			0
		) as total_received_pct 
		from 
		`$this->table` pr 
		left join purchase_order s on pr.id = s.pr_id 
		left join rfq_header r on r.id = s.rfq_id 
		left join ragic rg on rg.order_id = r.reference_no 
		left join users u on u.id = s.created_by 
		left join (
			select 
			sum(order_qty * price_per_item) as grand_total_price, 
			purchase_order_id 
			from 
			purchase_order_item 
			where 
			flag = 1 
			group by 
			purchase_order_id
		) c on c.purchase_order_id = s.id
		";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}