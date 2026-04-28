<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class SupplierModel extends Model
{
    protected $table = 'm_supplier';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'name', 'pic_name', 'email', 'phone', 'address', 'bank', 'bank_account_no', 'currency', 'bank_account_name', 'bic_swift_code', 'bank_account_type', 'bank_account_residence', 'flag','salutation', 'is_reimburse', 'created_by', 'created_date', 'modified_by', 'modified_date'];
	
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
		$q = "select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", concat(s.name, ' - ', s.bank_account_name, ' - ', s.bank_account_no) as bank_info, date(s.created_date) as crea_date, date(s.modified_date) as mod_date, concat('SUP', LPAD(s.id, 4, 0)) as supplier_id, u.name as created_by_name, v.name as modified_by_name from `$this->table` s left join users u on u.id = s.created_by left join users v on v.id = s.modified_by";
        $query = $db->query("select * from ($q)s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }
}