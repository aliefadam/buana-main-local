<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class BomitemModel extends Model
{
    protected $table = 'bom_receive_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","bom_receive_id", "item_id", "item", "bom_unique_id", "part_number","unit_qty","description","material","mass","qty", "dwg_no", "flag", "created_date","created_by","modified_date","modified_by"];
	
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
         if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select 
			c.name as created_by_name, m.name as modified_by_name, b.name as bom_receive, i.id as itemid, i.item_no, concat(i.item_no, ' - ', i.item_name, ' - ', coalesce(i.original_manufacture, ''), ' - ', coalesce(i.manufacture_pn, '')) as full, concat(s.part_number, ' - ', s.description, ' - ', s.material) as info,  
			b.bom_header_id, coalesce(rs.qty, 0) as received_qty, b.po_id, i.specification,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join bom_receive b on b.id = s.bom_receive_id
			left join m_item i on i.id = s.item_id
			left join receive_scan rs on rs.code = concat(s.id, '.', i.id, '.', b.bom_header_id, '.1')
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";
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
    
        	function getUniqueid($bom_receive_id){
		$query = $this->db->query("select bom_unique_id FROM `$this->table` s where s.bom_receive_id=$bom_receive_id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}