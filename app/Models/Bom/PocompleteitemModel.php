<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class PocompleteitemModel extends Model
{
    protected $table = 'purchase_order_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
    "purchase_order_id",
    "bom_id",
    "price_per_item", "order_qty", "item_id", "active", 'flag', 'allocation'//, 'complete_qty'
];
	
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
		$q = "`$this->table` s 
		left join bom_header h on h.id = s.bom_id 
		left join m_item i on i.id = s.item_id 
		left join purchase_order p on p.id = s.purchase_order_id 
		left join pr_part t on t.id = s.pr_part_id
		left join pr r on r.id = t.pr_id";
		$q = "select 
        (select sum(order_qty * price_per_item) from purchase_order_item where purchase_order_id = s.purchase_order_id) as total_item_price , 
		coalesce((select sum(qty) from stock st
		left join spb pb on pb.id = st.spb_id
		 where st.purchase_order_item_id = s.id and pb.flag = 1 and (st.flag!=-1 or st.flag!=0)), 0) as complete_qty,
        s.* from
        (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", h.bom_no, i.item_name, i.item_no, i.unit, 
		i.original_manufacture, i.manufacture_pn, i.specification, p.currency, (s.order_qty*s.price_per_item) as total_price_per_item,
		coalesce(r.pr_no, h.bom_no) as bom_pr from $q) s $where";
        $query = $db->query("$q $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$totalQuery = $db->query("select count(*) as total from ($q)a");
		$totalQuery = $totalQuery->getResult();
        $res = $query->getResult();
        if(count($res) > 0){
            $id = $res[0]->purchase_order_id;
            $query = $db->query("
                select sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id
            ");
            $query = $query->getResult();
            $res[0]->grand_total = $query[0]->grand_total;
        }
        if(isset($json->debug))
            $res = "$q $order";
		return [$res, $totalQuery[0]->total];
    }

    /* readAll($pid){
        helper(['Query_helper']);
        $db = db_connect();
        $q = "`$this->table` s left join bom_header h on h.id = s.bom_id left join m_item i on i.id = s.item_id left join purchase_order p on p.id = s.purchase_order_id where purchase_order_id = $pid";
        $query = $db->query("select 
        (select sum(order_qty * price_per_item) from purchase_order_item where purchase_order_id = s.purchase_order_id) as total_item_price , 
        s.* from
        (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", h.bom_no, i.item_name, i.item_no, i.original_manufacture, i.manufacture_pn, i.specification, p.currency, (s.order_qty*s.price_per_item) as total_price_per_item from $q) s");
        
        if(!$query)
            return [$this->db->error(), false];
    
        $totalQuery = $db->query("select count(*) as total from $q");
        $totalQuery = $totalQuery->getResult();
        $res = $query->getResult();
        if(count($res) > 0){
            $id = $res[0]->purchase_order_id;
            $query = $db->query("
                select sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id
            ");
            $query = $query->getResult();
            $res[0]->grand_total = $query[0]->grand_total;
        }
        return [$res, $totalQuery[0]->total];
    } */

    function read_bom($id, $pid){
        helper(['Query_helper']);
        $db = db_connect();
		$q = " bom_header h inner join bom_item bi on bi.bom_header_id = h.id left join m_item i on i.id = bi.item_id where h.id = $id and i.is_active = 1";
        $query = $db->query("
            select s.id, s.item_id, s.order_qty from `$this->table` s where purchase_order_id = $pid and active = 1
            union all
            select null as id, i.id as item_id, bi.order_qty from $q
        ");
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }
}