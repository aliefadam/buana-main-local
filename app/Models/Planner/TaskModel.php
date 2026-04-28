<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class TaskModel extends Model
{
    protected $table = 'task';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"id","subject","description","project_id","percent_done",
		"category_id","category","project","created_by","created_date",
		"assigned_to","members","parent_id","modified_date","modified_by"
	];
	
	protected function initialize()
    {
        $this->db = db_connect('planner');
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

	function insert_search(){
        helper(['Query_helper']);
		return dbQuery("
			insert into `search`(id, search_text, `type`) select id, concat(name, '-=-', description) as search_text, 'project' as `type` from project where id > (select top 1 id from `search` where `type` = 'project' order by id desc)
			union ALL 
			select id, concat(subject, '-=-', description) as search_text, 'task' as `type` from task where id > (select top 1 id from `search` where `type` = 'task' order by id desc)
			union ALL 
			select id, concat('', '-=-', notes) as search_text, 'notes' as `type` from task_note where id > (select top 1 id from `search` where `type` = 'notes' order by id desc)
		", [], $this->db);
	}

    function read($json){
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
		$sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */

		$req = dbQuery("select concat(name, '__', id) as a, join_query, join_select from task_field", [], $this->db);
		if(!$req->status)
			return $req;
		$data = $req->data;
		$column = [];
		$pivot = [];
		$sel = [];
		$join = [];
		$select = ['tfv.value', 'tv.value'];
		foreach ($data as $key => $value) {
			$sel[] = "s.".$value->a.' as '.$value->a;
			$column[] = "`".$value->a."`";
			$pivot[] = "MAX(CASE WHEN (name='$value->a') THEN value_alias END) AS `$value->a`";
			if($value->join_query!=null){
				if($value->join_select!=null){
					$join[] = $value->join_query;
					array_unshift($select, $value->join_select);
				}
			}
		}

		$req = dbQuery("select concat('search__', name, '__', id) as a, join_query, join_select from task_field", [], $this->db);
		$column2 = [];
		$join2 = [];
		$pivot2 = [];
		foreach ($req->data as $key => $val) {
			$sel[] = 's.' . $val->a . ' as ' . $val->a;
			$column2[] = "`$val->a`";
			$pivot2[] = "MAX(CASE WHEN (name='$val->a') THEN value_alias END) AS `$val->a`";
			if($val->join_query!=null){
				if($val->join_select!=null){
					$join2[] = $val->join_query;
				}
			}
		}

		return dbPaging($json, 
			"
			select 
				s.`id` as `id`,s.`subject` as `subject`,s.`description` as `description`,s.`project_id` as `project_id`,
				s.`percent_done` as `percent_done`,s.`category_id` as `category_id`,c.`name` as `category`,p.`name` as `project`,
				s.`created_by` as `created_by`,s.`created_date` as `created_date`,g.`assigned_to` as `assigned_to`,s.`parent_id` as `parent_id`,
				f.*, h.*, tm.members
			from task s
			left join project p on p.id = s.project_id
			left join task_category c on c.id = s.category_id
			left join (
				select ss.id,
				GROUP_CONCAT(concat(k.name,';;', p.id) SEPARATOR ', ') as assigned_to
				from task ss 
				left join task_pic p on p.task_id = ss.id and flag = 1
				left join buanamultiteknik_internal.users k on k.username = p.pic
				group by ss.id
			)g on g.id = s.id
			left join (
				select rn, task_id, " . implode(', ', $pivot) . " from (
					select ROW_NUMBER() over (partition by tv.task_id, tv.task_field_id order by tv.id desc) as rn, 
					concat(tf.name, '__', tv.task_field_id) as name,
					concat(coalesce(" . implode(',', $select) . "), '=__=', tv.value, '=__=', coalesce(tv.task_note_id, '')) as value_alias,
					tv.task_id
					from task_value tv 
					" . implode('\n', $join) . "
					left join task_field tf on tf.id = tv.task_field_id
					left join task_field_value tfv on tfv.id = case when tv.value REGEXP '^[[:digit:]]+$' = 1 then tv.value else -1 end
					and tfv.field_id = tv.task_field_id
					where field is null
				) t group by task_id
			)f on f.task_id = s.id and f.rn = 1
			left join (
				select rn2, task_id2, " . implode(', ', $pivot2) . " from (
					select ROW_NUMBER() over (partition by tv.task_id, tv.task_field_id order by tv.id desc) as rn2, 
					concat('search__', tf.name, '__', tv.task_field_id) as name,
					tv.value as value_alias,
					tv.task_id as task_id2
					from task_value tv 
					" . implode('\n', $join2) . "
					left join task_field tf on tf.id = tv.task_field_id
					left join task_field_value tfv on tfv.id = case when tv.value REGEXP '^[[:digit:]]+$' = 1 then tv.value else -1 end
					and tfv.field_id = tv.task_field_id
				) t group by task_id2
				
			)h on h.task_id2 = s.id and h.rn2 = 1
			left join (
				select GROUP_CONCAT(username SEPARATOR ',') as members, task_id from task_member group by task_id
			)tm on tm.task_id = s.id
			",
			[],
			$this->db
		);

		/* $q = "select concat(s.kode,' - ',s.nama)as name,
			c.name as created_by_name, m.name as modified_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total]; */
    }

	function related($id){
        helper(['Query_helper']);
		$req = dbQuery("select concat(name, '__', id) as a, join_query, join_select from task_field", [], $this->db);
		//$req = $db->query("select concat(name, '__', id) as a, join_query, join_select from task_field");
		if(!$req->status)
			return $req;
		$data = $req->data;
		$column = [];
		$pivot = [];
		$sel = [];
		$join = [];
		$select = ['tfv.value', 'tv.value'];
		foreach ($data as $key => $value) {
			$sel[] = "s.".$value->a.' as '.$value->a;
			$column[] = "`".$value->a."`";
			$pivot[] = "MAX(CASE WHEN (name='$value->a') THEN value_alias END) AS `$value->a`";
			if($value->join_query!=null){
				if($value->join_select!=null){
					$join[] = $value->join_query;
					array_unshift($select, $value->join_select);
				}
			}
		}

		return dbQuery("select 
				s.`id` as `id`,s.`subject` as `subject`,s.`description` as `description`,s.`project_id` as `project_id`,
				s.`percent_done` as `percent_done`,s.`category_id` as `category_id`,c.`name` as `category`,p.`name` as `project`,
				s.`created_by` as `created_by`,s.`created_date` as `created_date`,g.`assigned_to` as `assigned_to`,s.`parent_id` as `parent_id`,
				f.*
			from task s
			left join project p on p.id = s.project_id
			left join task_category c on c.id = s.category_id
			left join (
				select ss.id,
				GROUP_CONCAT(concat(k.name,';;', p.id) SEPARATOR ', ') as assigned_to
				from task ss 
				left join task_pic p on p.task_id = ss.id and flag = 1
				left join buanamultiteknik_internal.users k on k.username = p.pic
				group by ss.id
			)g on g.id = s.id
			left join task_related r on r.task_id = s.id or r.task_id_related = s.id
			left join (
				select rn, task_id, " . implode(', ', $pivot) . " from (
					select ROW_NUMBER() over (partition by tv.task_id, tv.task_field_id order by tv.id desc) as rn, 
					concat(tf.name, '__', tv.task_field_id) as name,
					concat(coalesce(" . implode(',', $select) . "), '=__=', tv.value, '=__=', tv.task_note_id) as value_alias,
					tv.task_id
					from task_value tv 
					" . implode('\n', $join) . "
					left join task_field tf on tf.id = tv.task_field_id
					left join task_field_value tfv on tfv.id = case when tv.value REGEXP '^[[:digit:]]+$' = 1 then tv.value else -1 end
					and tfv.field_id = tv.task_field_id
					where field is null
				) t group by task_id
				
			)f on f.task_id = s.id and f.rn = 1
			where (r.task_id = ? or r.task_id_related = ?) and s.id != ?", [$id, $id, $id],
			$this->db);
	}

	function update_value($json, $pk){
        helper(['Query_helper']);
		$not = ['pk', 'id', 'subject', 'description', 'project_id', 'name', 'created_by', 'created_date', 'assigned_to', 'category_id', 'parent_id', 'percent_done'];
		$update = [];
		$proms = [];
		foreach ($json as $key => $val) {
			$keys = explode('__', $key);
			if(!in_array($key) && count($keys) > 1){
				if($val == null){
					$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value) values($pk, $keys[1], null, null);";
				}
				else{
					$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value) values($pk, $keys[1], null, '$val');";
				}
			}
			else{
				if($key != 'pk' && in_array($key)){
					if($val == null){
						$update[] = " $key = null ";
					}
					else{
						$update[] = " $key = '$val' ";
					}

					if($key == "percent_done"){
						if($val == null){
							$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value, field) values($pk, null, null, '$val', 'Percent Done');";
						}
						else{
							$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value, field) values($pk, null, null, '$val', 'Percent Done');";
						}
					}
				}
			}
		}

		$proms[] = "update task set " . implode(', ', $update) . ", modified_date = now() where id = $pk;";

		return $proms;
	}

	function insert_value($json){
        helper(['Query_helper']);
		$not = ['pk', 'id', 'subject', 'description', 'project_id', 'name', 'created_by', 'created_date', 'assigned_to', 'category_id', 'parent_id', 'percent_done'];
		$proms = [];
		$cols = [];
		$vals = [];
		$params = [];

		foreach ($json as $key => $val) {
			$keys = explode('__', $key);
			if(!in_array($key, $not) && count($keys) > 1){
				if($val == null){
					$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value) values(@newId, $keys[1], null, null);";
				}
				else{
					$proms[] = "insert into task_value(task_id, task_field_id, task_note_id, value) values(@newId, $keys[1], null, '" . str_replace("'", "''", $val) . "');";
				}
			}
			else{
				if($key != 'pk' && in_array($key, $not)){
					$cols[] = "`$key`";

					if($val === null){
						$vals[] = "null";
					}
					else{
						$vals[] = "'" . str_replace("'", "''", $val) . "'";
					}
				}
			}
		}

		array_unshift($proms, "select @newId := LAST_INSERT_ID();");
		array_unshift($proms, "insert into task(" . implode(', ', $cols) . ") values(" . implode(', ', $vals) . ");");
		return $proms;
	}

	function post($json){
        helper(['Query_helper']);
		return dbTransaction($this->insert_value($json), [], $this->db);
	}

	function put($json){
        helper(['Query_helper']);
		$ret = (object) [
			'status' => true,
			'data' => [],
		];
		if(!isset($json->parent_id)){
			if($json->parent_id == '')
				$json->parent_id = null;
		}
		return dbTransaction($this->update_value($json, $json->pk), [], $this->db);
		/* $this->db->transBegin();
		try {
			$ret = dbQuery(implode('\n', $this->update_value($json, $json->pk)), [], $this->db);
			$this->db->transCommit();
		} catch (\Throwable $th) {
			//throw $th;
			$this->db->transRollback();
			$ret->status = false;
			$ret->data = $th->getMessage();
		}
		return $ret; */
	}

	function delete_data($pk) {
        helper(['Query_helper']);
		return dbTransaction(["delete from task where id = ?;","
		delete from `search` where id = ? and `type` = 'task';"], [[$pk], [$pk]], $this->db);
	}
}
