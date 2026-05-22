<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class RfqModel extends Model
{
    protected $table = 'rfq_header';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rfq_no", "rfq_group","title","status","priority","created_by","created_date","modified_by","modified_date","flag","assigned_to", "reference_no", "mr_task_no", "item_no", "item_description", "allocation", "project_id"];
	
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
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
        $isDebug = isset($json->debug);
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? [], $isDebug);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date, s.status as status_s, s.priority as priority_show, u.name as created_by_name, k.name as modified_by_name, a.name as assigned_to_name, (select distinct r.order_id from ragic_data r where r.ragic_id = s.ragic_id limit 1) as ragic_no, p.project_no, p.project_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." , r.status as ragic_status
			from `$this->table` s
            left join ragic r on r.order_id = s.reference_no
			left join users a on a.id = s.assigned_to
			left join users u on u.id = s.created_by
			left join users k on k.id = s.modified_by
            left join m_project p on p.id = s.project_id";
        $query = $db->query("select * from
		($q)s $where $order limit $offset, $limit");
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		$res = $query->getResult();
		//$res[] = $where;
		return [true, $res, $totalQuery[0]->total];
    }
}