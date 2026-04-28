<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class RelatedModel extends Model
{
    protected $table = 'task_related';
    protected $returnType     = 'object';
    protected $primaryKey = 'task_id';
    protected $allowedFields = [
		"task_id","task_id_related"
	];
	
	protected function initialize()
    {
        $this->db = db_connect('planner');
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function read($json){
        helper(['Query_helper']);
		$ret = dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))."
				from task_related s ",
			[],
			$this->db
		);
		return $ret;

    }

	function post($json){
        helper(['Query_helper']);
		$values = (object)[
			"task_id" => $json->task_id,
			"task_id_related" => $json->task_id_related
		];

		if($json->task_id > $json->task_id_related){
			$values = (object)[
				"task_id" => $json->task_id_related,
				"task_id_related" => $json->task_id
			];
		}
		return dbInsert($values, $this->allowedFields, $this->table, $this->db);
	}

	/* function put($json){ 
        helper(['Query_helper']);
		return dbUpdate($json,  $this->allowedFields, $this->table, $this->db);
	} */

	function delete_data($json) {
        helper(['Query_helper']);
		return dbQuery(`delete from task_related where task_id = ? and task_id_related = ?`, [$json->task_id, $json->task_id_related], $this->db);
	}
}
