<?php namespace App\Models\Po;
 
use CodeIgniter\Model;
 
class RequestarivalModel extends Model
{
    protected $table = 'purchase_order';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","po_no","pr_no","pr_id","po_date","title","dept_id","order_type","note","supplier_id","promised_delivery_date","currency","exchange_rate","rate_date","charge1","charge2","charge1_desc","charge2_desc","other_charge","discount","eta_date","miscellaneous","received_date","payment_term","despatch","shipping_address","final_quote_url","signed_po_url","signed_pr_url","no","year","approved","is_complete","ref_offer_no","ref_internal_bmt","askapproval_note","ask_approval_date","approval_by","approval_date","approval_note","reject_note1","approval_2_by","approval_2_date","rejected_by","rejected_date","reject_note2","rfq_id","ragic_id","rev","new_po_no","created_by","created_date","modified_by","modified_date","flag","complete","discount_desc","ask_canceled_date","ask_canceled_note","canceled_by","canceled_note","canceled_date","canceled_2_by","canceled_2_date","ask_draft_date","ask_draft_note","ask_draft_by","approval_draft_by","approval_draft_date","rev_date","ask_canceled_by","send_po_by","send_po"];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
        $q = "
        SELECT 
        po.id,
        po.po_no, 
        po.year,
        CASE 
            WHEN po.approved = 0 THEN 'New'
            WHEN po.approved = 1 THEN 'Asking Approval 1' 
            WHEN po.approved = 2 THEN 'Asking Approval 2'
            WHEN po.approved = 3 THEN 'Final Approved'
            WHEN po.approved = 4 THEN 'Clarification'
            WHEN po.approved = 5 THEN 'Asking for Draft PO'
            WHEN po.approved = 6 THEN 'Approval 2 Approved Draft'
            WHEN po.approved = -1 THEN 'Rejected'
            WHEN po.approved = -2 THEN 'Asking for canceled 1'
            WHEN po.approved = -3 THEN 'Asking for canceled 2'
            WHEN po.approved = -4 THEN 'Canceled'
            ELSE 'Other Status'
        END AS approval_status,
        r.order_id,
        rfq.rfq_no,
        pr.pr_no,
        po.note,
        po.title,
        r._ragicId,
        po.po_date as po_created,
        po.approval_2_date,
        po.approval_date,
        po.send_po,
        po.eta_date,
        po.signed_pr_url,
        po.signed_po_url,
        pr.user_request_date,
        po.ask_approval_date,
        po.promised_delivery_date,
        po.requested_delivery_date,
        po.delivery_time,
        CASE 
            WHEN po.complete = 0 THEN 'Not Complete'
            WHEN po.complete = 1 THEN 'Completed'
            else 'other status'
        end as status_penerimaan
        FROM purchase_order po
        LEFT JOIN pr ON pr.id = po.pr_id OR pr.pr_no = po.pr_no
        LEFT JOIN rfq_header rfq ON rfq.id = pr.rfq_id
        LEFT JOIN ragic r on r.order_id = rfq.reference_no
        left join spb spb on spb.po_id = po.id
        where po.flag = 1
        group by po.po_no
        ";
		// $q = "select 
		// 	c.name as created_by_name, m.name as modified_by_name,
		// 	".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
		// 	from `$this->table` s
		// 	left join users c on c.id = s.created_by
		// 	left join users m on m.id = s.modified_by";
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