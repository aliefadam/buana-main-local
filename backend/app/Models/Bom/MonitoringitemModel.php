<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class MonitoringitemModel extends Model
{
    protected $table = 'purchase_order_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
    "purchase_order_id",
    "bom_id",
    "price_per_item", "order_qty", "item_id", "active", 'flag', 'allocation', 'complete_qty', 'created_date', 'created_by', 'modified_date', 'modified_by','order_no', 'subledger_id'
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
		$q = "`$this->table` s 
		left join bom_header h on h.id = s.bom_id 
		left join m_item i on i.id = s.item_id 
		left join purchase_order p on p.id = s.purchase_order_id 
		left join pr_part t on t.id = s.pr_part_id
		left join pr r on r.id = t.pr_id";
        $query = $db->query("select 
        (select sum(order_qty * price_per_item) from purchase_order_item where purchase_order_id = s.purchase_order_id and purchase_order_item.flag=1) as total_item_price , 
        s.* from
        (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", h.bom_no, i.item_name, i.item_no, i.unit, i.original_manufacture, i.manufacture_pn, i.specification, p.currency, (s.order_qty*s.price_per_item) as total_price_per_item, coalesce(r.pr_no, h.bom_no) as bom_pr from $q) s 
		$where $order limit $offset, $limit");

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