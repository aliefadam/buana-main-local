<?php namespace App\Models\Machine;
 
use CodeIgniter\Model;
 
class shiftdataModel extends Model
{
    protected $table = 'shift_data';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "machine_id", "shift_id", "rejects", "stop_frequency", "other_values", "shift_date", "brand", "shift_start", "shift_time", "break_time", "total_production", "total_waste", "good_production", "utilization_t", "utilization_p", "efficiency_t", "efficiency_p", "stop_history", "uptime", "downtime"];
	
	protected function initialize()
    {
        $this->db = \Config\Database::connect('machine_gb');//db_connect('machine_gb');
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
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select row_number() OVER (PARTITION BY shift_date ORDER BY shift_date asc) as shift, 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s";
        $query = $this->db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $this->db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
   
    function data($q, $params){
        $db = db_connect('machine_gb');
        $query = $db->query("$q", $params);
        
		if(!$query)
			return [false, $this->db->error()]; 
		return [true, is_bool($query) ? 'executed!' : $query->getResult()];
    }
}