<?php namespace App\Models\Warehouse;
 
use CodeIgniter\Model;
 
class SpbitemModel extends Model
{
    protected $table = 'stock';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","item_id","qty","allocation","po_id","created_date","created_by","modified_date","modified_by","purchase_order_item_id","flag","spb_id","notes","photo", "subkoli_part_id", "bom_spb_id"];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			c.name as created_by_name, m.name as modified_by_name, i.item_no, i.item_name as item_name, po.po_no, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, i.datasheet, sp.koli_id, sp.subkoli_id, br.machine_no, br.name as machine_name, br.po_id as bom_po_id,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join m_item i on i.id = s.item_id
			left join subkoli_part sp on sp.id=s.subkoli_part_id
			left join bom_receive br on br.id=sp.bom_receive_id
			left join purchase_order po on po.id = s.po_id
			";
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