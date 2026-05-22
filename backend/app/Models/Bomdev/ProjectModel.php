<?php namespace App\Models\Bomdev;
 
use CodeIgniter\Model;
 
class ProjectModel extends Model
{
    protected $table = 'm_project';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","project_no","project_name","category","plan_start_date","plan_end_date","status","pic","description","client_name", "prj_year", "no", 'flag'];
	
	protected function initialize()
    {
        $this->db = db_connect('dev');
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
        $db = db_connect('dev');
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "`$this->table` s left join users u on u.id = pic";
        $query = $db->query("select * from (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", u.name as pic_name from $q)s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from $q");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

	function lastNumber($year){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT no FROM `m_project` WHERE prj_year = '$year' order by id desc limit 1), 0)+1, 6, 0) as no, IFNULL( (SELECT no FROM `m_project` WHERE prj_year = '$year' order by id desc limit 1), 0)+1 as number");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}