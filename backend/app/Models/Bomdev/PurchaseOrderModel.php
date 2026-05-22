<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_order';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"po_no",
	"dept_id",
	"po_date",
	"order_type",
	"supplier_id",
	"promised_delivery_date",
	"currency",
	"exchange_rate",
	"rate_date",
	"final_quote_url",
	"signed_po_url", "no", "year", "charge1", "charge2", "charge1_desc", "charge2_desc", "note", "title", 'flag', 'approved', 'ref_offer_no', 'ref_internal_bmt', 'ask_approval_date', 'approval_date', 'approval_by'];
	
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
		$q = "select (select count(*) from purchase_order_item where flag = 1 and purchase_order_id = s.id) as item_count, (select notes from purchase_order_comment where purchase_order_id = s.id limit 1) as comment, coalesce((select sum(payment_pct) from invoice where po_id = s.id and flag = 1), 0) as total_payment_pct, ms.pic_name, ms.address, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", d.dept_code, d.dept_name, ms.name as supplier_name from `$this->table` s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

    function readAll($id){
        helper(['Query_helper']);
        $db = db_connect();
		$q = "`$this->table` s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = $id";
        $query = $db->query("select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", d.dept_code, d.dept_name, ms.name as supplier_name from $q");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$res = $query->getResult();
		if(count($res) > 0){
			$id = $res[0]->id;
			$query = $db->query("
				select sum(order_qty * price_per_item) as total_price, sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id
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
		$query = $this->db->query("select i.po_no, i.po_date, i.title, s.name as supplier, i.ask_approval_date, i.approval_date, i.approval_by, u.name as approved_by_name from purchase_order i
		left join m_supplier s on s.id = i.supplier_id 
		left join users u on u.id = i.approval_by
		where i.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}