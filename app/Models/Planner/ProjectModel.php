<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class ProjectModel extends Model
{
    protected $table = 'project';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","name","description","parent_id", "client_id", "status", "project_type",
		"contract_no", "location","planned_start_date","planned_finish_date","planned_commissioning_date","planned_handover_date",
		"created_date","created_by","modified_by","modified_date"
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
		
		return dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", pm.members, c.name as client_name
				from project s
				left join client c on c.id = s.client_id
				left join 
				(select p.id, GROUP_CONCAT(pm.username separator ',') as members from project p 
				left join project_member pm on pm.project_id = p.id group by p.id)pm on pm.id = s.id
			",
			[],
			$this->db
		);

    }

	function post($json){
        helper(['Query_helper']);
		$ret = dbInsert($json, $this->allowedFields, $this->table, $this->db);
		$session = session();
		$s = $session->get();
		$username = '';
		if(isset($s["username"]))
			$username = $s["username"];
		dbQuery("insert into project_member(project_id, username, role_id) values(?, ?, ?)", [$ret->inserted_id, $username, 0], $this->db);
		return $ret;
	}

	function put($json){ 
        helper(['Query_helper']);
		$ret = dbUpdate($json, $this->allowedFields, 'id', $this->table, $this->db);
		dbQuery("update `search` set search_text = (select concat(name, '-=-', description) from project where id = ?) where id = ? and `type` = 'project'", [$json->pk, $json->pk], $this->db);
		return $ret;
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbTransaction(["delete from project where id = ?;",
		"delete from `search` where id = ? and `type` = 'project';"], [[$pk], [$pk]], $this->db);
	}

	function member($json) {
        helper(['Query_helper']);
		return dbPaging($json, "
		select *, (select group_concat(';'+gu.`username`+';' separator ';') from buanamultiteknik_internal.user_group u left join buanamultiteknik_internal.users gu on gu.id = u.group_id where username = s.username) as groups 
		from buanamultiteknik_internal.users s where username in (select username from project_member where project_id = $json->project_id)
		", [], $this->db);
	}
}
