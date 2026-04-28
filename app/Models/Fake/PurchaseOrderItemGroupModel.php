<?php namespace App\Models\Fake;
 
use CodeIgniter\Model;
 
class PurchaseOrderItemGroupModel extends Model
{
    protected $table = 'fake_purchase_order_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
    "purchase_order_id",
    "bom_id",
    "price_per_item", "order_qty", "item_id", "active", 'flag', 'pr_part_id', "allocation", 'complete_qty', 'created_date', 'created_by', 'modified_date', 'modified_by','order_no','subledger_id'
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
		$q = "
		SELECT i.datasheet, ms.pic_name, p.currency, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
			select s.order_no, s.id, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, sum(s.order_qty) as order_qty, sum(s.complete_qty) as complete_qty,
			(select price_per_item from fake_purchase_order_item where purchase_order_id = s.purchase_order_id and item_id = s.item_id and flag=1 limit 1) as price_per_item 
			from fake_purchase_order_item s left join m_item i on i.id = s.item_id
			where i.id is not null and s.flag = 1
			group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, i.article_no, s.purchase_order_id) s
		left join m_item i on i.id = s.item_id
		left join fake_purchase_order p on p.id = s.purchase_order_id
		left join m_supplier ms on ms.id = p.supplier_id
		";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
        $res = $query->getResult();
        if(count($res) > 0){
            $id = $res[0]->purchase_order_id;
            $query = $db->query("
                select a.charge1, a.charge2, (select sum(total) as total from (select order_qty*price_per_item as total from purchase_order_item where purchase_order_id = $id and flag=1)k) as total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id and s.flag=1 limit 1
            ");
            $query = $query->getResult();
            $res[0]->grand_total = $query[0]->total+$query[0]->charge1+$query[0]->charge2;
        }
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
            select s.id, s.item_id, s.order_qty from `$this->table` s where purchase_order_id = $pid and active = 1 and flag = 1
            union all
            select null as id, i.id as item_id, bi.order_qty from $q
        ");
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }

    function read_pr_part($id){
        helper(['Query_helper']);
        $db = $this->db;
        $query = $db->query("
		SELECT s.id, s.allocation, s.qty as order_qty, p.item_id FROM `pr_subledger` s
		left join pr_part p on p.id=s.pr_part_id
		WHERE s.id= $id
        ");
		/* $query = $db->query("
            select  *, (select sum(qty) from pr_subledger where pr_part_id = $id) as order_qty, (select allocation from pr_subledger where pr_part_id = $id) as allocation from pr_part where id = $id
        "); */
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }
}