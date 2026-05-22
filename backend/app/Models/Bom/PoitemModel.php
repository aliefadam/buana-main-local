<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class PoitemModel extends Model
{
    protected $table = 'po_budget';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
	
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
		$q = "select s.*, a.item_id as item_id, u.name as created_by_name, ms.name as supplier_name, d.dept_code, d.dept_name, a.price_per_item as price_per_item from purchase_order s 
        left join (select distinct po.id as po_id, i.id as item_id, pi.price_per_item from m_item i left join purchase_order_item pi on pi.item_id = i.id left join purchase_order po on po.id = pi.purchase_order_id) a on a.po_id = s.id
        left join m_department d on d.id = s.dept_id
        left join m_supplier ms on ms.id = s.supplier_id
        left join users u on u.id = s.created_by
		"
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