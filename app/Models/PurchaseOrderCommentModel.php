<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PurchaseOrderCommentModel extends Model
{
    protected $table = 'purchase_order_comment';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "notes", "purchase_order_id", "created_date", "created_by", "type"
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
            $q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." from `$this->table` s  ";
		
        $query = $db->query("select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
        
        if(isset($json->debug))
            return [$q, 0];
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
				select sum(order_qty * price_per_item) as total_price, sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id and s.flag = 1
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
		$query = $this->db->query("select i.supplier_id, day(i.approval_date) as approval_day, i.approved, i.ref_internal_bmt, i.po_no, i.po_date, day(i.po_date) as po_date_day, i.title, s.name as supplier, i.ask_approval_date, day(i.ask_approval_date) as ask_day, i.approval_date, i.approval_by, u.name as approved_by_name, approval_2_date, t.name as approved2_by_name, i.rev from purchase_order i
		left join m_supplier s on s.id = i.supplier_id 
		left join users u on u.id = i.approval_by
		left join users t on t.id = i.approval_2_by
		where i.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}