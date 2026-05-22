<?php namespace App\Models\Warehouse;
 
use CodeIgniter\Model;
 
class ItemModel extends Model
{
    protected $table = 'warehouse_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rack_id","item_id","qty","created_by","created_date","modified_by","modified_date"];
	
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
		$q = "select 
			c.name as created_by_name, m.name as modified_by_name,
            i.item_name as item, r.name as rack,
            sum(s.qty), s.item_id
			from `$this->table` s
            left join m_item i on i.id = s.item_id
            left join warehouse_rack r on r.id = s.rack_id
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
            group by s.item_id, s.rack_id";
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