<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class DepartmentModel extends Model
{
    protected $table = 'm_department';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","dept_code","dept_name", "approval_1", "approval_2","is_active","created_by","created_date","modified_by","modified_date", 'flag'];
	
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
		$q = "select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", date(s.created_date) as crea_date, date(s.modified_date) as mod_date, u.name as created_by_name, v.name as modified_by_name,
		a.name as approval_1_name,
		b.name as approval_2_name from `$this->table` s
        left join users u on u.id = s.created_by 
        left join users v on v.id = s.modified_by
		left join users a on a.id = s.approval_1
		left join users b on b.id = s.approval_2";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }
}