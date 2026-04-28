<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListpoinprocessModel extends Model
{
    protected $table = 'purchase_order';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "po_no", 'title', 'approved', "created_by", "flag"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function poinprocessPo($json){
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
        $q = "select ROW_NUMBER() OVER(order by s.id desc) AS no,  s.id, s.po_no, s.approved, s.title,  s.flag, s.created_by, s.created_by_name from (select  ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", u.name as created_by_name FROM `$this->table` s
        left join users u on u.id = s.created_by
             where s.flag=1 and s.approved=0)s";

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