<?php namespace App\Models\Rebuildmachine;
 
use CodeIgniter\Model;
 
class ListpartModel extends Model
{
    protected $table = 'list_item_rebuild';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","part_no","qty", "brand", "description", "attachment", "parthauni_id", "created_by", "subassembly_id", "created_date", "modified_by", "modified_date", "flag"];
	
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
		$q = "select  date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
        c.name as created_by_name, 
        m.name as modified_by_name,
        sub.part_number, sub.sub_assembly,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
            left join subassembly sub on sub.id=s.parthauni_id
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