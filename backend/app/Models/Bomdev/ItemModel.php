<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class ItemModel extends Model
{
    protected $table = 'm_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'item_no', 'item_name', 'unit', 'original_manufacture', 'manufacture_pn', 'specification', 'item_type', 'is_active', 'created_by', 'created_date', 'modified_by', 'modified_date', 'noid', 'flag', 'datasheet'];
	
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
		$q = "select concat(s.item_no, ' - ', s.item_name, ' - ', s.original_manufacture, ' - ', s.manufacture_pn) as full, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." from `$this->table` s";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

	function lastNumber($type){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT CAST(noid AS UNSIGNED) FROM `m_item` WHERE item_type = '$type' order by id desc limit 1), 0)+1, 4, 0) as no, IFNULL( (SELECT CAST(noid AS UNSIGNED) FROM `m_item` WHERE item_type = '$type' order by id desc limit 1), 0)+1 as number");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}