<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class RfqcommentModel extends Model
{
    protected $table = 'rfq_comment';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rfq_id","created_by","created_date","modified_by","modified_date","comment", "flag", "hist","filepath"];
	
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
		$q = "select u.name as created_by_name, k.name as modified_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users u on u.id = s.created_by
			left join users k on k.id = s.modified_by";
        $query = $db->query("select * from
		($q)s $where $order order by id desc ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error()]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		$res = $query->getResult();
		//check assigend to id
		$id = [];
		$a = [];
		foreach ($res as $key => $value) {
			$json = $value->hist ?? '{}';
			if(trim($json) == '')
				$json = '{}';
			$json = json_decode($json, true);
			$res[$key]->hist = $json;
			if(isset($json["assigned_to"]))
				array_push($id, $json["assigned_to"]);
		}

		if(count($id)>0){
			$ids = $this->db->query("select id, name from users where id in (".join(",", $id).")");
			$ids = $ids->getResult();
			$idObj = [];
			foreach ($ids as $value) {
				$idObj[$value->id] = $value->name;
			}
			
			foreach ($res as $value) {
				if(isset($value->hist["assigned_to"])){
					//if($idObj[$value->hist["assigned_to"]])
						$res[$key]->hist["assigned_to_name"] = $idObj[$value->hist["assigned_to"]] ?? null;
					/* else
						$res[$key]["assigned_to_name"] = null; */
				}
			}

		}
		return [true, $res, $totalQuery[0]->total];
    }
}