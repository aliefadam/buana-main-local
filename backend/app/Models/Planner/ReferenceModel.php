<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class ReferenceModel extends Model
{
    protected $table = 'reference';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","url", "category", "task_id", "note_id", "alias"
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
				from reference s ",
			[],
			$this->db
		);
		return $ret;

    }

	function post($json){
        helper(['Query_helper']);
		return dbInsert($json,  $this->allowedFields, $this->table, $this->db);
	}

	function put($json){ 
        helper(['Query_helper']);
		return dbUpdate($json,  $this->allowedFields, $this->primaryKey, $this->table, $this->db);
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbDelete("id", $pk,  $this->allowedFields, $this->table, $this->db);
	}
}
