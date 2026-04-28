<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListprinprocessModel extends Model
{
    protected $table = 'pr';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","pr_no", "pr_subject","status", "flag", "created_by"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function prinprocessPr($json){
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
        $q = "select ROW_NUMBER() OVER(order by s.pr_no desc) AS no,  s.id, s.pr_no, s.pr_subject, s.status, s.flag, s.created_by, created_by_name from (select  ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", u.name as created_by_name  FROM `$this->table` s
        left join users u on u.id = s.created_by
             where (s.status between 0 AND 2) and s.flag=1)s";

        $fields="*";
        if(isset($json->optionUser))
			$fields = "distinct created_by, created_by_name";


            $query = $db->query("select $fields from ($q) s $where $order ");
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
            $totalQuery = $db->query("select count($fields) as total from ($q) s $where");
            $totalQuery = $totalQuery->getResult();
            return [$query->getResult(), $totalQuery[0]->total];
    }
}