<?php namespace App\Models\Project;
 
use CodeIgniter\Model;
 
class ProjectModel extends Model
{
    protected $table = 'm_project';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","project_no","project_name","category","plan_start_date","plan_end_date","status","pic","description","client_name","prj_year","no","flag", "created_by", "created_date", "modified_by", "modified_date"];
	
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
		$q = "select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", date(s.created_date) as crea_date, date(s.modified_date) as mod_date, concat(s.project_no, ' - ', s.project_name) as full, u.name as pic_name, v.name as created_by_name, w.name as modified_by_name from `$this->table` s left join users u on u.id = s.pic
        left join users v on v.id=s.created_by
        left join users w on w.id=s.modified_by";
        $fields="*";
        if(isset($json->category_item))
        $fields="distinct id, full, category";

        $query = $db->query("select $fields from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));

		if(!$query)
			return [$this->db->error(), false]; 
		
            $totalQuery = $db->query("select count($fields) as total from ($q) s $where");
            $totalQuery = $totalQuery->getResult();
            return [$query->getResult(), $totalQuery[0]->total];
    }
}