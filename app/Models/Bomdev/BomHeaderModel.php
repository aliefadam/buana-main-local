<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class BomHeaderModel extends Model
{
    protected $table = 'bom_header';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
    "project_id",
    "status",
    "notes",
	"no",
	"bom_no", 'flag'];
	
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
		$q = "`$this->table` s left join m_project m on m.id = s.project_id";
        $query = $db->query("select * from
		(select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", m.project_name, m.project_no from $q)s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
	
		$totalQuery = $db->query("select count(*) as total from (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", m.project_name, m.project_no from $q)s $where");
		if(!$totalQuery)
			return [$this->db->error(), false]; 
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }

	function lastNumber(){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT no FROM `$this->table` order by id desc limit 1), 0)+1, 5, 0) as no, IFNULL( (SELECT no FROM `$this->table` order by id desc limit 1), 0)+1 as number");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}