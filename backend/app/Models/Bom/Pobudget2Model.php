<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class Pobudget2Model extends Model
{
    protected $table = 'po_budget';
    protected $returnType     = 'object';
    protected $primaryKey = 'po_no';
    protected $allowedFields = ["po_no","po_month","po_id","cny","idr","eur","usd","total_in_idr","exchange_rate"];
	
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
		$q = "select p.approved as approved_i, case when p.approved = 3 then 'approved 2' when p.approved = 2 then 'approved 1' when p.approved = 1 then 'asking approval' else '-' end as approved,  
		 case when py.approved = 4 then 'Approved (Approval 2)' when py.approved = 3 then 'Asking for approval (Approval 2)' when py.approved = 2 then 'Approved (Approval 1)' when py.approved = 1 then 'Asking for approval (Approval 1)' else '-' end as payment_approved,
		(
		    select count(distinct i.id) from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id 
		    left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1
		) as total_invoice_in_payment,  (
		    select count(distinct py.id) from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id 
		    left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1 and py.done = 0
		) as payment_not_done,
		(
		    select sum(case when i.payment_pct_fiat != 0 then i.payment_pct_fiat else (i.grand_total_price * (i.payment_pct/100)) end) from invoice i where i.id in (select distinct i.id from payment_item p left join invoice i on i.flag = 1 and i.id = p.invoice_id and i.flag > 0
		    left join payment py on py.flag >=0 and py.approved >=0 and py.id = p.payment_id where i.po_id = s.po_id and p.flag = 1)
		) as total_payment,
		i.payment_pct as total_payment_pct,
		p.title, p.supplier_id, u.name as supplier_name,
		concat(s.po_no, ' ', i.invoice_no, ' ', i.payment_pct, '% ', py.payment_no, ' ', case when py.approved = 4 then 'Approved (Approval 2)' when py.approved = 3 then 'Asking for approval (Approval 2)' when py.approved = 2 then 'Approved (Approval 1)' when py.approved = 1 then 'Asking for approval (Approval 1)' when py.approved = 0 then 'New' else '-' end) as info,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s 
			left join invoice i on i.po_id = s.po_id and i.flag = 1
            left join payment_item pi on pi.invoice_id = i.id and pi.flag = 1
            left join payment py on py.id = pi.payment_id and py.flag = 1
			left join purchase_order p on p.id = s.po_id
			left join m_supplier u on u.id=p.supplier_id"
			;
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(isset($json->debug))
			if($json->debug=='8080')
				return [true, $q, 0]; 
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}