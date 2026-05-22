<?php namespace App\Models\Planner;
 
use CodeIgniter\Model;
 
class DashboardModel extends Model
{
    protected $table = 'task_field';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [
		"assigned_to_name","pic_name","assigned_to_id","pic","id",
		"subject","description","project_id","project_name","created_by",
		"created_date","parent_id","percent_done","category_id",
		"modified_date", "modified_by"
	];
	
	protected function initialize()
    {
        $this->db = db_connect('planner');
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function my_task($json){
        helper(['Query_helper']);

		$session = session();
		$s = $session->get();
		$user = '';
		if(isset($s["username"]))
			$user = $s["username"];
		return dbPaging($json, 
			"
				select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
				from (
					select * from 
					(
						select ROW_NUMBER() over(partition by s.id order by s.id desc) as rn, 
						p.name as project_name,
						k.name as assigned_to_name, k2.name as pic_name, s.* from (	
							select (select value from task_value tv where tv.task_field_id = 4 and tv.task_id = t.id order by id desc limit 1) as assigned_to_id
							, tm.username as pic, t.* from task t 
							left join task_member tm on tm.task_id = t.id
						)s
						left join buanamultiteknik_internal.users k on k.username = s.assigned_to_id
						left join buanamultiteknik_internal.users k2 on k2.username = s.pic
						left join project p on p.id = s.project_id
						where s.pic = '$user' or s.assigned_to_id = '$user'
					)s where rn = 1
				)s
			",
			[],
			$this->db
		);

    }
}
