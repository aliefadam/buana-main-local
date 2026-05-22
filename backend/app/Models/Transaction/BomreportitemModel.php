<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class BomreportitemModel extends Model
{
    protected $table = 'bom_receive_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
	
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
            $where .= ' and s.flag = 1';
		$q = "select s.*, (s.qty-received_qty) as remaining_qty, bi.unit_qty, bi.description, bi.material, bi.mass from (
				SELECT s.bom_receive_id, s.part_number, i.id as item_id,
				sum(s.qty) as qty, sum(coalesce(t.qty, 0)) as received_qty 
				FROM `bom_receive_item` s
				left join m_item i on i.item_name = s.part_number
				left join spb b on b.bom_receive_id = s.bom_receive_id
				left join stock t on t.spb_id = b.id and t.item_id = i.id and t.flag >= 0
				group by s.bom_receive_id, s.part_number, i.id
			)s
			left join (
				select row_number() over(partition by bom_receive_id, part_number order by id asc) as rn, bi.* from bom_receive_item bi
			)bi on bi.part_number = s.part_number and bi.rn = 1"; */
			
				$q = "select s.*, ((s.received_qty/s.qty)*100) as pct_part, (received_qty-s.qty) as remaining_qty from (
				SELECT s.bom_receive_id, s.bom_unique_id, s.part_number, s.unit_qty, s.description, s.material, s.mass, s.id, i.id as item_id, i.item_no,
				s.qty as qty, sum(coalesce(t.qty, 0)) as received_qty 
				FROM `bom_receive_item` s
				left join m_item i on i.id = s.item_id
				left join spb b on b.bom_receive_id = s.bom_receive_id
				left join stock t on t.spb_id = b.id and t.item_id = i.id and t.flag >= 0
				group by s.bom_receive_id, i.id
			)s";
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