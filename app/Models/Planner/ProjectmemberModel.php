<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class ProjectmemberModel extends Model
{
    protected $table = 'project_member';
    protected $returnType     = 'object';
    protected $primaryKey = 'project_id';
    protected $allowedFields = [
		"project_id","role_id", "username", "id"
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
		//$code = $this->code();
		$ret = dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", r.name as role_name, k.name as name
				from project_member s 
				left join role r on r.id = s.role_id
				left join buanamultiteknik_internal.users k on k.username = s.username",
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
