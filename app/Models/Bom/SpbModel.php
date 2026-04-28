<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class SpbModel extends Model
{
    protected $table = 'bom_spb';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","notes","created_by","created_date","modified_by","modified_date", "flag", "arrival_date", "location", "approved", "kabag_id", "kelog_id", "approved_by", "approved2_by", "approved_date", "approved2_date"];
	
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
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; 
		$q = "select 
		date(s.created_date) as crea_date,
		date(s.modified_date) as mod_date,
		c.name as created_by_name, 
		m.name as modified_by_name, 
		v.name as approved2_by_name,
		a.name as approved_by_name,
		p.name as kelog_name,
		uk.name as kabag_name,
		r.name as rejected_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join users a on a.id = s.approved_by
			left join users v on v.id = s.approved2_by
			left join users r on r.id = s.rejected_by
			left join users p on p.id = s.kelog_id
			left join users uk on uk.id = s.kabag_id";
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
    
        function info($id){
		$query = $this->db->query("select s.*,  u.name as kelog_name, u.no_hp as kelog_phone, v.name as kabag_name, v.no_hp as kabag_phone, w.name as validator_name, x.name as approver_name, t.email as created_email, t.no_hp as created_phone, v.email as kabag_email  from bom_spb s
        left join users t on t.id = s.created_by
		left join users u on u.id = s.kelog_id
		left join users v on v.id = s.kabag_id
		left join users w on w.id = s.approved_by
		left join users x on x.id = s.approved2_by
		where s.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}