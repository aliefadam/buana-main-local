<?php namespace App\Models\Nomenclature;
 
use CodeIgniter\Model;
 
class BastModel extends Model
{
    protected $table = 'bast';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "bast_no", "bast_date", "po_no","client_id", "notes", "status", "file_upload", "created_by", "created_date", "modified_by", "modified_date", "flag"];
	
	protected function initialize()
    {
        $this->db = db_connect();
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
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
			c.name as created_by_name,
			m.name as modified_by_name,
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
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
 
	function lastNumber(){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT cast(split_str(bast_no, '/', 1) as UNSIGNED) as no FROM `bast` where SPLIT_STR(bast_no, '/', 4) = DATE_FORMAT(now(), '%Y') and flag=1 order by id desc limit 1), 0)+1, 3, 0) as no");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}
