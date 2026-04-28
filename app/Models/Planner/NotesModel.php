<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class NotesModel extends Model
{
    protected $table = 'task_note';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","notes", "task_id", "f_task_id",
		"created_date", "created_by"
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
		$req = dbQuery("select concat(name, '__', id) as a, join_query, join_select from task_field where flag = 1", [], $this->db);
		$column = [];
		$join = [];
		$pivot = ['rn', 'f_task_id', 'task_note_id'];
		$select = ['tfv.value', 'tv.value'];
		$req->data[] = (object)[
			"a" => 'Percent Done__0',
			"join_query" => null,
			"join_select" => null
		];
		
		foreach ($req->data as $key => $val) {
			$this->allowedFields[] = "`$val->a`";
			$column[] = "`$val->a`";
			$pivot[] = "MAX(CASE WHEN (name='$val->a') THEN value_alias END) AS `$val->a`";
			if($val->join_query!=null){
				if($val->join_select!=null){
					$join[] = $val->join_query;
					$select[] = $val->join_select;
				}
			}
		};
		
		$ret = dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", created
				from (
					select s.*, f.*, k.name as created
					from task_note s
					left join buanamultiteknik_internal.users k on k.username = s.created_by
					left join (
						select " . implode(', ', $pivot) . " from (
							select ROW_NUMBER() over (partition by tv.task_id, tv.task_field_id , tv.task_note_id order by tv.id desc) as rn, 
							case when tv.field is not null THEN concat(tv.field, '__0')
							else
								concat(tf.name, '__', tv.task_field_id) end as name,
							concat(coalesce(" . implode(',', $select) . "), '=__=', coalesce(tv.value, ''), '=__=', coalesce(tv.task_note_id, '')) as value_alias,
							tv.task_id as f_task_id, tv.task_note_id
							from task_value tv 
							" . implode('\n', $join) . "
							left join task_field tf on tf.id = tv.task_field_id
							left join task_field_value tfv on tfv.id = case when tv.value REGEXP '^[[:digit:]]+$' = 1 then tv.value else -1 end
							and tfv.field_id = tv.task_field_id
						) t group by f_task_id
					)f on f.task_note_id = s.id and f.f_task_id = s.task_id and f.rn = 1
				)s",
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

	function update_value($json, $pk){
        helper(['Query_helper']);
		$not = ['pk', 'id', 'subject', 'description', 'project_id', 'name', 'created_by', 'created_date', 'assigned_to', 'category_id', 'parent_id', 'percent_done'];
		$update = [];
		$proms = [];
		foreach ($json as $key => $val) {
			$keys = explode('__', $key);
			if(!in_array($key, $not) && count($keys) > 1){
				if($val == null){
					$proms[] = "
							insert into task_value(task_id, task_field_id, task_note_id, value) select $pk, $keys[1], @newId, null where coalesce((select value from task_value tv where task_field_id = $keys[1] and task_id = $pk order by id desc limit 1), ';;novalue;;') is not null ;
						";
				}
				else{
					$proms[] = "
							insert into task_value(task_id, task_field_id, task_note_id, value) select $pk, $keys[1], @newId, '$val' where coalesce((select value  from task_value tv where task_field_id = $keys[1] and task_id = $pk order by id desc limit 1), ';;novalue;;') != '$val';
						";
				}
			}
			else{
				if($key != 'pk' && in_array($key, $not)){
					if($val == null){
						$update[] = " $key = null ";
					}
					else{
						$update[] = " $key = '$val' ";
					}

					if($key == "percent_done"){
						if($val == null){
							$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value, field) values($pk, null, @newId, '$val', 'Percent Done');";
						}
						else{
							$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value, field) values($pk, null, @newId, '$val', 'Percent Done');";
						}
					}
				}
			}
		}

		$session = session();
		$s = $session->get();
		$author = isset($s["username"]) ? $s["username"] : null;
		$proms[] = "update task set " . implode(', ', $update) . ", modified_date = now() where id = $pk;";
		array_unshift($proms, "select @newId := LAST_INSERT_ID();");
		array_unshift($proms, "insert into task_note(notes, created_by, task_id) values(?, '$author', '$pk');");
		return (object)["query" => $proms, "params" => [[$json->notes]]];
	}

	function add($json){
		helper(['Query_helper']);
		$ret = (object) [
			'status' => true,
			'data' => [],
		];
		if(!isset($json->parent_id)){
			if($json->parent_id == '')
				$json->parent_id = null;
		}
		$q = $this->update_value($json, $json->pk);
		return dbTransaction($q->query, $q->params, $this->db);
	}
}
