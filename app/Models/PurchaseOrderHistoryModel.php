<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PurchaseOrderHistoryModel extends Model
{
    protected $table = 'purchase_order_history';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"purchase_order_id",
		"id",
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
		"signed_po_url", "signed_pr_url", "no", "year", "charge1", "charge2", "charge1_desc", "charge2_desc", "note", "title", 'flag', 'approved', 'ref_offer_no', 'ref_internal_bmt', 'ask_approval_date', 'approval_date', 'approval_by', 'approval_2_date', 'approval_2_by', 'modified_date', 'modified_by', "approval_note", "created_by", "created_date", "new_po_no",
		"reject_note1",
		"reject_note2",
		"payment_term",
		"despatch",
		"shipping_address",
		"miscellaneous",
		"other_charge",
		"discount",
		"rev",
		"revision_by",
		"revisi_note"
	];
	
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
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select (select count(*) from purchase_order_item_history where flag = 1 and purchase_order_id = s.purchase_order_id) as item_count, 
		(select notes from purchase_order_comment_history where purchase_order_id = s.purchase_order_id order by id desc limit 1) as comment, 
		(select u.name from purchase_order_comment_history c left join users u on u.id = c.created_by where purchase_order_id = s.purchase_order_id order by c.id desc limit 1) as comment_creator, 
		coalesce((select sum(payment_pct) from invoice where po_id = s.purchase_order_id and flag = 1), 0) as total_payment_pct, ms.pic_name, ms.address, 
		".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", d.dept_code, d.dept_name, ms.name as supplier_name, u.name as created_by_name, v.name as approved_by_name, w.name as approved2_by_name, r.name as revision_by_name,
		i.grand_total_price, ms.email
		from `$this->table` s 
		left join m_supplier ms on ms.id = s.supplier_id 
		left join m_department d on d.id = s.dept_id
		left join users u on u.id = s.created_by
		left join users v on v.id = s.approval_by
		left join users w on w.id = s.approval_2_by
		left join users r on r.id = s.revision_by
		left join (
			select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
			from purchase_order_item_history where flag = 1
			group by purchase_order_id
		) i on i.purchase_order_id = s.purchase_order_id
		";
		
        $query = $db->query("select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [$this->db->error(), false];
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

    function readAll($id){
        helper(['Query_helper']);
        $db = db_connect();
		$q = "`$this->table` s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.purchase_order_id = $id";
        $query = $db->query("select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", d.dept_code, d.dept_name, ms.name as supplier_name from $q");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$res = $query->getResult();
		if(count($res) > 0){
			$id = $res[0]->id;
			$query = $db->query("
				select sum(order_qty * price_per_item) as total_price, sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item_history s left join purchase_order_history a on a.purchase_order_id = s.purchase_order_id where s.purchase_order_id = $id and s.flag = 1
			");
			if(!$query)
				return [$this->db->error(), false];
			$query = $query->getResult();
			$res[0]->grand_total = $query[0]->grand_total;
			$res[0]->total_price = $query[0]->total_price;
		}
		return $res;
    }

	function lastNumber($y, $id){
		$query = $this->db->query("select (select dept_code from m_department where id = '$id') as dept_code, LPAD(IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by no desc limit 1), 0)+1, 4, 0) as no, IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by no desc limit 1), 0)+1 as number");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function info($id){
		$query = $this->db->query("select i.supplier_id, day(i.approval_date) as approval_day, i.approved, i.ref_internal_bmt, i.po_no, i.po_date, day(i.po_date) as po_date_day, i.title, s.name as supplier, i.ask_approval_date, day(i.ask_approval_date) as ask_day, i.approval_date, i.approval_by, u.name as approved_by_name, approval_2_date, t.name as approved2_by_name, i.rev from purchase_order_history i
		left join m_supplier s on s.id = i.supplier_id 
		left join users u on u.id = i.approval_by
		left join users t on t.id = i.approval_2_by
		where i.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}