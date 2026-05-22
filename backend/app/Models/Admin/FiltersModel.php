<?php namespace App\Models\Admin;
 
use CodeIgniter\Model;
 
class FiltersModel extends Model
{
    protected $table = 'filters';
    protected $returnType     = 'object';
    protected $primaryKey = 'filter_id';
    protected $allowedFields = ["filter_id","target","filter","filter_type","filter_pills","created_by","status","created_date","modified_date","name","model_selected"];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select u.name as created_by_name, k.name as modified_by_name, 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users u on u.id = s.created_by
			left join users k on k.id = s.modified_by";
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

	function available($json){
		$c = $json->created_by ?? '-';
		$query = $this->db->query("select s.* from filters s left join users u on u.id = s.created_by where (target = '$json->target' and u.username = '$c') or (target = '$json->target' and status = 1)");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}