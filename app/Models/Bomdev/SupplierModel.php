<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class SupplierModel extends Model
{
    protected $table = 'm_supplier';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'pic_name', 'email', 'phone', 'address', 'bank', 'bank_account_no', 'currency', 'bank_account_name', 'bic_swift_code', 'flag'];
	
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
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "`$this->table` s $where";
        $query = $db->query("select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", concat('SUP', LPAD(s.id, 4, 0)) as supplier_id from $q $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from $q");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }
}