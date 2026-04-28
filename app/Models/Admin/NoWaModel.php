<?php namespace App\Models\Admin;
 
use CodeIgniter\Model;
 
class NowaModel extends Model
{
    protected $table = 'm_nowa';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","wa_id", "description", "created_by", "created_date", "modified_by", "modified_date", "isGroup", "flag"];
	
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
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." , date(s.created_date) as crea_date, date(s.modified_date) as mod_date,  u.name as created_by_name, v.name as modified_by_name
			from `$this->table` s
			 left join users u on u.id = s.created_by
        left join users v on v.id = s.modified_by";
        $query = $db->query("select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error()]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}