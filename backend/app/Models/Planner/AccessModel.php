<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class AccessModel extends Model
{
    protected $table = 'access';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","role_id", "username", "project_id", "task_id", "name"
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
		$code = $this->code();
		$ret = dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))."
				from access s",
			[],
			$this->db
		);
		foreach ($ret->data as $key => $value) {
			if(isset($code->{$value->name})){
				$value->name = $code->{$value->name};
			}
		}
		return $ret;

    }

	function post($json){
        helper(['Query_helper']);
		$code = (object)[
			'add_task' => 1,
			'edit_task' => 2,
			'delete_task' => 3,
			'add_project' => 4,
			'edit_project' => 5,
			'delete_project' => 6,
			'add_note' => 7,
			'add_reference' => 8,
			'add_project_member' => 9,
			'add_task_member' => 10
		];
		
		$params = (object)[
			"role_id" => !isset($json->role_id) ? null : $json->role_id,
			"username" => !isset($json->username) ? null : $json->username
		];

		$ins = [];

		foreach ($json as $key => $val) {
			if($val == 1){
				$tmp = (object)[
					"role_id" => !isset($json->role_id) ? null : $json->role_id,
					"username" => !isset($json->username) ? null : $json->username
				];

				if(isset($code->{$key})){
					$tmp->name = $code->{$key};
					$ins[] = $tmp;
				}
			}
		}

		if($params->username == null){
			dbQuery("delete from access where role_id = ?", [$params->role_id], $this->db);
		}
		else if($params->role_id == null){
			dbQuery("delete from access where username = ?", [$params->username], $this->db);
		}
		else
			dbQuery("delete from access where username = ? and role_id = ?", [$params->username, $params->role_id], $this->db);
		
		return dbInsertBatch($ins, $this->table, $this->db);
	}

	function put($json){ 
        helper(['Query_helper']);
		return dbUpdate($json,  $this->allowedFields, $this->primaryKey, $this->table, $this->db);
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbDelete("id", $pk,  $this->allowedFields, $this->table, $this->db, "flag");
	}

	function code(){
		return (object)[
			"1" => 'add_task',
			"2" => 'edit_task',
			"3" => 'delete_task',
			"4" => 'add_project',
			"5" => 'edit_project',
			"6" => 'delete_project',
			"7" => 'add_note',
			"8" => 'add_reference',
			"9" => 'add_project_member',
			"10" => 'add_task_member'
		];
	}

	function infoproject($opt){
        helper(['Query_helper']);
		$author = false;
		$access = [];
		$session = session();
		$s = $session->get();
		$user = isset($s["username"]) ? $s["username"] : null;
		
		$res = dbQuery("select * from project where id = ? and created_by = ?", [$opt->id, $user], $this->db);
		if($res->status)
			if(count($res->data)>0)
				$access[] = '$author$';
		//check member access
		$role_ids = [];
		$res = dbQuery("select role_id from project_member where username = ?", [$user], $this->db);
		if($res->status){
			$role_ids = array_map(function($val){
				return $val->role_id;
			}, $res->data);
		}
			
		if(count($role_ids) > 0){
			$res = dbQuery("select * from access where role_id in (" . implode(',', $role_ids) . ")", [], $this->db);
			foreach ($res->data as $key => $val) {
				$access[] = $val->name;
			}
		}
		
		if(in_array("administrator", explode(',',$s["groups"])))
			$access = explode(';', '1;2;3;4;5;6;7;8;9;10');

		return (object)["user" => $user, "res" => $res, "access" => $access];
	}

	function infotask($opt){
        helper(['Query_helper']);
		$author = false;
		$access = [];
		$session = session();
		$s = $session->get();
		$user = isset($s["username"]) ? $s["username"] : null;
		$projectId = -1;

		$res = dbQuery("select * from task where id = ?", [$opt->id], $this->db);
		if($res->status)
			if($res->data.length>0){
				$projectId = $res->data[0]->project_id;
				if($res->data[0]->created_by == $user)
					$access[] = '$author$';
			}
		
		$res = dbQuery("select top 1 t.*, v.value as val from task_value t left join task_value v on v.id = t.value where t.id = ? and t.task_field_id = 2", [], $this->db);
		if($res->status)
			if(count($res->data)>0)
				$access[] = 'priority:'.$res->data[0]->val;

		$res = dbQuery("select * from project where id = ? and created_by = ?", [$projectId, $user], $this->db);
		if($res->status)
			if(count($res->data)>0)
				$access[] = '$author$';
			
		//check member access
		$role_ids = [];
		$res = dbQuery("select role_id from project_member where username = ?", [$user], $this->db);
		if($res->status)
			$role_ids = array_map(function($val){
				return $val->role_id;
			}, $res->data);
		if(count($role_ids) > 0){
			$res = dbQuery("select * from access where role_id in (" . implode(',', $role_ids) . ")", [], $this->db);
			foreach ($res->data as $key => $val) {
				$access[] = $val->name;
			}
		}

		if(in_array("administrator", $s["groups"]))
			$access = explode(';', '1;2;3;4;5;6;7;8;9;10');

		return (object)["user" => $user, "res" => $res, "access" => $access];
	}

	function access($json){
		$project = (object)[];
		$task = (object)[];
		if(isset($json->project_id))
			$project = $this->infoproject((object)[
				"id" => $json->project_id
			]);
		if(isset($json->task_id))
			$task = $this->infotask((object)[
				"id" => $json->task_id
			]);
		return (object)["project" => $project, "task" => $task];
	}
}
