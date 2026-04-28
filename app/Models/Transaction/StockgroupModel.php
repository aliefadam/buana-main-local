<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class StockgroupModel extends Model
{
    protected $table = 'final_stock';
    protected $returnType     = 'object';
    protected $primaryKey = 'item_id';
    protected $allowedFields = ["item_id","item_name", "flag"];
	
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
        $distinct = ($json->distinct ?? false) ? 'distinct' : '';
		$sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select sum(s.qty) as qty, i.item_no, i.item_type, i.manufacture_pn, i.original_manufacture, i.article_no, i.specification, i.unit, i.subassembly, i.subassy_desc, i.equivalent, i.datasheet,
			(
				select group_concat(wi.rack_id, ', ') from (select distinct rack_id, item_id from warehouse_item) wi where wi.item_id = s.item_id
			)
			as racks,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join m_item i on i.id = s.item_id where s.flag = 1 group by s.item_id, s.item_name, i.item_no, i.item_type, i.manufacture_pn, i.original_manufacture, i.specification, i.unit";
		$fields = '*';
		if(isset($json->fields)){
			$tmp = explode(',', $json->fields);
			if(count(tmp)>0)
				$fields = join(',', array_map(array($this, 'addPrefix'), $fields));
		}

        $query = $db->query("select $distinct $fields from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), $q]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}