<?php namespace App\Models\Admin;
 
use CodeIgniter\Model;
 
class UsersModel extends Model
{
    protected $table = 'users';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","username","name","is_group","is_active","flag","email"
	];
	
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
		$ret = dbPaging($json, 
			"
				select (select GROUP_CONCAT(username) from users u1 left join user_group ug1 on ug1.group_id = u1.id where ug1.user_id = s.id and u1.flag=1 and ug1.flag=1) AS groups, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))."
				from users s ",
			[],
			$this->db
		);
		return $ret;

    }

	function post($json){
        helper(['Query_helper']);
		return dbInsert($json, $this->allowedFields, $this->table, $this->db);
	}

	function put($json){ 
        helper(['Query_helper']);
		return dbUpdate($json,  $this->allowedFields, $this->table, $this->db);
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbDelete("id", $pk,  $this->allowedFields, $this->table, $this->db, "flag");
	}
}
