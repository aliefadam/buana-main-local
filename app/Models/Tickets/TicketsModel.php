<?php namespace App\Models\Tickets;
 
use CodeIgniter\Model;
 
class TicketsModel extends Model
{
    protected $table = 'tickets';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","user_id","subject","description","status","priority","assigned_agent","flag","created_at","updated_at","category_id","ticket_no"];
	
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
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			c.name as created_by_name, b.name as assigned_name, a.name as category_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.user_id
            left join users b on b.id = s.assigned_agent
            left join categories a on a.id = s.category_id
			";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }

    function lastNumber(){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT CAST(SUBSTRING(ticket_no, 7) AS UNSIGNED) as no FROM `$this->table` where SUBSTRING(ticket_no, 1, 6) = DATE_FORMAT(now(), '%Y%m') order by id desc limit 1), 0)+1, 3, 0) as no");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

    function monitorSummary(){
        $db = $this->db;
        $query=$db->query('
            SELECT t.status, COUNT(t.status) AS status_count
            FROM tickets t
            GROUP BY t.status');
        if (!$query) 
            return [false, $this->db->error()];
        return ($query->getResult());
    }
}