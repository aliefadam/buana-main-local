<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class BomModel extends Model
{
    protected $table = 'bom_receive';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","bom_header_id","name","machine_no","po_id", "qty", "item_id", "note", "created_date","created_by","modified_date","modified_by", "flag","subkoli"];
	
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
			c.name as created_by_name, m.name as modified_by_name, p.po_no, concat(i.item_no, ' - ', i.item_name, ' - ', coalesce(i.original_manufacture, ''), ' - ', coalesce(i.manufacture_pn, ''))  as item_no, concat(s.name, ' - ', s.machine_no) as info,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join purchase_order p on p.id = s.po_id
			left join m_item i on i.id = s.item_id
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
}