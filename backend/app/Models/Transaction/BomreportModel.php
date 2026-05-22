<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class BomreportModel extends Model
{
    protected $table = 'bom_receive';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","bom_header_id","name","machine_no","po_id", "qty", "item_id","created_date","created_by","modified_date","modified_by", "flag"];
	
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
			c.name as created_by_name, m.name as modified_by_name, p.po_no, concat(i.item_no, ' - ', i.item_name, ' - ', coalesce(i.original_manufacture, ''), ' - ', coalesce(i.manufacture_pn, ''))  as item_no, brh.description as desc_header, z.bom_receive_id as bom_receive_id, COALESCE(z.pct_part,0) as pct_part, brh.isPack,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
            left join bom_receive_header brh on brh.id=s.bom_header_id
			left join purchase_order p on p.id = s.po_id
			left join m_item i on i.id = s.item_id
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
            left join( select c.bom_receive_id, (sum(c.pct_part)/count(c.bom_receive_item_id)) as pct_part  from (select case when (b.received_qty/b.qty)*100>100 then 100 else (b.received_qty/b.qty)*100 end as pct_part, b.bom_receive_id as bom_receive_id, b.bom_receive_item_id from
               (SELECT a.bom_receive_id as bom_receive_id, a.id as bom_receive_item_id, i.id as item_id,
				a.qty as qty, ((sum(coalesce(t.qty, 0)))*100) as received_qty 
				FROM bom_receive_item a
				left join m_item i on i.id = a.item_id
				left join spb b on b.bom_receive_id = a.bom_receive_id
				left join stock t on t.spb_id = b.id and t.item_id = i.id and t.flag >= 0
				group by a.bom_receive_id, a.item_id, a.id)b)c group by c.bom_receive_id)z on z.bom_receive_id = s.id";
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