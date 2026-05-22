<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class UserGroupModel extends Model
{
    protected $table = 'user_group';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["user_id","group_id","id", "flag", "modified_by", "modified_date", "created_by", "created_date"];
	
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
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." ,
			g.name as group_name, u.name as user_name, date(s.created_date) as crea_date, date(s.modified_date) as mod_date,  w.name as created_by_name, v.name as modified_by_name
			from `$this->table` s
			left join users u on u.id = s.user_id
			left join users g on g.id = s.group_id
            left join users w on u.id = s.created_by
        left join users v on v.id = s.modified_by";
        $query = $db->query("select * from
		($q)s $where  $order limit $offset, $limit");
        
		if(!$query)
			return [false, $this->db->error()]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}