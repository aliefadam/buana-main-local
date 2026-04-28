<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class ModulesModel extends Model
{
    protected $table = 'modules';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","name","url","background","color","group_id","icon", "flag", "sort"];
	
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
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", g.name as group_name
			from `$this->table` s
			left join users g on g.id = s.group_id";
        $query = $db->query("select * from
		($q)s $where $order limit $offset, $limit");
        
		if(!$query)
			return [false, $this->db->error()]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }

	function available($username=''){
		$result = $this->db->query("select m.*, u.username from modules m left join users u on u.id = m.group_id 
			where m.flag = 1 and (
				m.group_id in (select id from users where username = '$username')
				or m.group_id in (
					select group_id from user_group where user_id in (select group_id from user_group where user_id = (select id from users where username = '$username')) or user_id in (select username from users where username = '$username')
					union ALL 
					select group_id from user_group where user_id = (select id from users where username = '$username')
				)
			) order by m.sort asc
			");
		if(!$result)
			return [false, $this->db->error()]; 
		return [true, $result->getResult()];
	}
}