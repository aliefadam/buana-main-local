<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class BomItemModel extends Model
{
    protected $table = 'bom_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
    "bom_header_id",
    "item_id",
    "required_qty",
    "required_qty_nis",
    "order_qty",
    "remark", 'flag'];
	
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
		$q = "select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", m.item_no, m.item_name, m.original_manufacture, m.manufacture_pn from `$this->table` s left join m_item m on m.id = s.item_id";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
        	return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
        return [$query->getResult(), $totalQuery[0]->total];
    }
}