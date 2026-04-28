<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class NpbitemModel extends Model
{
    protected $table = 'npb_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","npb_id","item_id","qty","po_id","allocation","notes", "subassembly_id"];
	
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select concat(i.item_no, ' - ', i.item_name, ' - ', i.manufacture_pn, ' - ', s.qty, ' - ', s.allocation) as full,  i.item_no , i.item_name,  i.original_manufacture, i.manufacture_pn, i.specification, i.unit, concat(LPAD(n.idx, 4, 0),'/NPB/',date_format(n.created_date, '%m/%Y'), 
				case when n.rev > 1 then
					concat('-REV', LPAD(n.REV, 2, 0))
				else '' end
			) as npbno, msub.subassembly, p.po_no as po_no_item,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s 
			left join npb n on n.id = s.npb_id
			Left join purchase_order p on p.id = s.po_id
			left join m_item i on i.id = s.item_id
            left join m_subassembly msub on msub.id = s.subassembly_id";

            $fields="*";
            if(isset($json->full))
                $fields = "id, full, i.specification, notes, qty";

        $query = $db->query("select $fields from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count($fields) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}