<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class KoliModel extends Model
{
    protected $table = 'koli';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","koli_no","description", "container_id", "created_date","created_by","modified_date","modified_by", "flag"];
	
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
			c.name as created_by_name, m.name as modified_by_name, date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from (
				SELECT distinct k.id, k.koli_no, k.description, k.created_date, k.created_by, k.modified_date, k.modified_by, k.flag, p.container_id FROM `koli` k left join subkoli_part p on p.koli_id = k.id where p.koli_id is not null
			) s
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
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}