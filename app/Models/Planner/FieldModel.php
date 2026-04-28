<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class FieldModel extends Model
{
    protected $table = 'task_field';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","name","sort","field_type","value_type",
		"default_value","flag","url","params","created_date",
		"created_by","modified_date","modified_by","required",
		"join_query", "join_select", "all"
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
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
				from task_field s
			",
			[],
			$this->db
		);

    }

	function task_category($category_id){
        helper(['Query_helper']);

		return dbQuery("
			select s.*, (
				select GROUP_CONCAT(concat(id,'$$',value) separator ';;') from task_field_value tfv where field_id = s.id order by sort
			) as task_value
			from task_field s
			left join task_category_field a on a.task_field_id = s.id
			where s.flag = 1 and (s.`all`  = 1 or a.task_category_id = ?)
		", [!isset($category_id) ? -1 : $category_id],
		$this->db);
	}

	function post($json){
        helper(['Query_helper']);
		return dbInsert($json,  $this->allowedFields, $this->table, $this->db);
	}

	function put($json){ 
        helper(['Query_helper']);
		return dbUpdate($json,  $this->allowedFields, $this->primaryKe, $this->table, $this->dby);
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbDelete("id", $pk,  $this->allowedFields, $this->table, $this->db, "flag");
	}
}
