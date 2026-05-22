<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListpritemreadyforpoModel extends Model
{
    protected $table = 'pr_part';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","item_id","supplier_id", "quotation_no", "quotation_doc_url","quotation_date","notes","pr_id", "order_no", "rev", "created_date","created_by","modified_date","modified_by", "flag"];
	
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
		$q = "select Row_number() over(partition by s.id order by ps.id) as rn,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", (select count(pi.id) from purchase_order_item pi left join purchase_order po on po.id=pi.purchase_order_id where pi.pr_part_id = s.id and ps.id=pi.subledger_id and po.approved>=0 and pi.flag=1) as in_po, i.item_no, i.item_name, i.original_manufacture, concat(i.item_no, '-', i.item_name) as itemfull,
			i.manufacture_pn, i.unit as unit, i.specification, i.item_type, i.datasheet,
			ss.name as supplier_name,
			c.name as created_by_name,
			m.name as modified_by_name
			from `$this->table` s
			left join pr_subledger ps on ps.pr_part_id=s.id and ps.flag=1
			left join m_item i on i.id = s.item_id
			left join m_supplier ss on ss.id = s.supplier_id
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
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

	function info($id){
		$query = $this->db->query("select pr.rev as pr_rev, i.item_id 
		from pr_part i
		left join pr on pr.id=i.pr_id
		where i.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function lastNoOrder($id){
		$query = $this->db->query("select i.id, max(i.order_no) as order_no
		from pr_part i
		left join pr on pr.id=i.pr_id
		where i.pr_id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
    
}