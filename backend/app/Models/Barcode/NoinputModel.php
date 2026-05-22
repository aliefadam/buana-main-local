<?php namespace App\Models\Barcode;
 
use CodeIgniter\Model;
 
class NoinputModel extends Model
{

	/*
	insert into tracking_item(barcode, process, ln, lt, created_date, manual)
select barcode, process, '-8.13012210' as ln, '112.58344870' as lt, '19-01-2023' as created_date, 1 as manual from tracking_item_without_input where area = 'a' and group_name = 'mech' and process = 'packing_part'
 */
    protected $table = 'tracking_item_without_input';
    protected $returnType     = 'object';
    protected $primaryKey = 'barcode';
    protected $allowedFields = ['barcode', 'part_name', 'group_name', 'area', 'process'];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s";
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

	function alreadytracked($json){
		$q = "select count(barcode) as c from tracking_m_part a where barcode in (select i.barcode
		from buanamultiteknik_internal.tracking_item_all i
		where i.process = '".$json->process."') and a.group_name = '".$json->group_name."' and a.area = '".$json->area."'";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}