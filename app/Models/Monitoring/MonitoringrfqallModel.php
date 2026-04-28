<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class MonitoringrfqallModel extends Model
{
    protected $table = 'pr';
    protected $returnType = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","pr_no","pr_date","pr_subject", "status", "pr_notes","ask_approval","pr_peminta","pr_menyetujui", "peminta_setuju", "penyetuju_setuju","created_date", "rejected_by", "created_by","modified_date", "rejected_date", "reject_notes","modified_by", "attachment", "askapproval_notes","approval1_notes","approval2_notes", "rev", "rev_date", "isAn", "filepath","ragic_id","rfq_id","revisi_notes", "delete_by", "delete_notes", "delete_date", "flag", "revise_by", "revise_date", "revise_notes",
    "ask_canceled_date", "canceled_by", "ask_canceled_note", "canceled_note", "canceled_date", "canceled_2_by", "canceled_2_date"];
	
	protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
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
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; 
        
        $q = "select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." ,  case when s.status=0 then 'New' when s.status=1 then 'Asking for Approval 1' When s.status=2 then 'Asking for Approval 2' when s.status=3 then 'Final Approved' when s.status=-1 then 'PR Rejected' end as status_pr, po.po_no,  case when po.approved=0 then 'New' when po.approved=1 then 'Asking for Approval 1' When po.approved=2 then 'Asking for Approval 2' when po.approved=3 then 'Final Approved' when po.approved=-1 then 'PO Rejected' end as status_po, i.grand_total_price+po.charge1+po.charge2-po.discount as grand_total, po.title as po_title, po.currency, po.approval_2_date as released_date, po.eta_date,date(po.promised_delivery_date),  po.note as po_note, coalesce((select sum(payment_pct) from invoice where po_id = po.id and po.flag = 1), 0) as total_payment_pct , (select case when is_paid=0 then 'Not Yet' when is_paid=1 then 'Paid' else 'Not Yet' end as 'is_paid' from invoice where po_id = po.id and po.flag = 1 and flag=1 order by id limit 1) as is_paid from `$this->table` s
        left join purchase_order po on po.pr_id=s.id
        left join (select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
                from purchase_order_item where flag = 1
                group by purchase_order_id
            ) i on i.purchase_order_id = po.id";

            $query = $db->query("select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
            
        
		if(!$query)
			return [false, "select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}