<?php namespace App\Models\Barcode;
 
use CodeIgniter\Model;
 
class ItemallModel extends Model
{
    protected $table = 'tracking_item_all';
    protected $returnType     = 'object';
    protected $primaryKey = 'barcode';
    protected $allowedFields = ["id", "barcode","process", "parent_id"];//,"created_date","ln","lt"
	
	protected function initialize()
    {	
		$this->dev = false;
		if(isset($_REQUEST['_app_root']))
			if(preg_match("/dev$/i", $_REQUEST['_app_root']))
				$this->dev = true;
		if($this->dev)
			$this->db = db_connect('dev');
        else
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select distinct 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", p.part_name
			from `$this->table` s
			left join tracking_m_part p on p.barcode = s.barcode";
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