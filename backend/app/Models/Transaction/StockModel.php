<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class StockModel extends Model
{
    protected $table = 'final_stock';
    protected $returnType     = 'object';
    protected $primaryKey = 'item_id';
    protected $allowedFields = ["qty","item_id","allocation","po_id","item_name","po_no","flag"];
	
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
		$q = "select i.item_no, i.item_type, i.manufacture_pn, i.original_manufacture, i.specification, i.unit, concat(i.item_no, ' - ', i.item_name, ' - ', 
			coalesce(i.original_manufacture, ''), ' - ', coalesce(i.manufacture_pn, '')) as full, po.title,
			(
				select GROUP_CONCAT(location SEPARATOR ', ') from (
					select distinct p.location, coalesce(p.po_id, r.po_id) as po_id, a.item_id, a.allocation from stock a 
					left join spb p on p.id = a.spb_id
					left join bom_receive r on r.id = p.bom_receive_id
				)a
				where a.item_id = s.item_id and a.allocation = s.allocation and a.po_id = s.po_id
				group by po_id, item_id
			) as location,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join m_item i on i.id = s.item_id
            left join purchase_order po on po.id=s.po_id";
		$fields = '*';
		if(isset($json->fields)){
			$tmp = $json->fields;//json_decode($json->fields, true);
			if(count($tmp)>0)
				$fields = join(',', array_map(array($this, 'addPrefix'), $tmp));
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